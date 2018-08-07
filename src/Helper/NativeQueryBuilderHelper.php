<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Helper;

use Adbar\Dot;
use Micayael\NativeQueryFromFileBuilderBundle\Event\NativeQueryFromFileBuilderEvents;
use Micayael\NativeQueryFromFileBuilderBundle\Event\ProcessQueryParamsEvent;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryDirectoryException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryFileException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryKeyException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class NativeQueryBuilderHelper
{
    const REQUIRED_ID_PATTERN = '/@{.+?}/';

    const OPTIONAL_ID_PATTERN = "/@\[.+?\]/";

    const KEY_PATTERN = '/[a-z0-9._]+/';

    /**
     * @var null|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var null|AdapterInterface
     */
    private $cache;

    private $queryDir;

    private $fileExtension;

    public function __construct(?EventDispatcherInterface $eventDispatcher, ?AdapterInterface $cache, string $queryDir, string $fileExtension = 'yaml')
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;

        $this->queryDir = $queryDir;
        $this->fileExtension = $fileExtension;
    }

    /**
     * Get a SQL query from a query key in a yaml file.
     *
     * @param string $queryFullKey
     * @param array  $params
     *
     * @return string
     *
     * @throws NonExistentQueryDirectoryException
     * @throws NonExistentQueryFileException
     * @throws NonExistentQueryKeyException
     */
    public function getSqlFromYamlKey(string $queryFullKey, array &$params = []): string
    {
        $queryFullKey = explode(':', $queryFullKey);

        $fileKey = $queryFullKey[0];
        $queryKey = $queryFullKey[1];

        if ($this->cache) {
            $cacheItem = $this->cache->getItem('nqbff_'.$fileKey);

            if (!$cacheItem->isHit()) {
                $dot = $this->getQueryFileContent($fileKey);

                $cacheItem->set($dot);
                $this->cache->save($cacheItem);
            }

            $dot = $cacheItem->get();
        } else {
            $dot = $this->getQueryFileContent($fileKey);
        }

        if (!$dot->has($queryKey)) {
            throw new NonExistentQueryKeyException($queryKey);
        }

        $sql = $dot->get($queryKey);

        $sqlParts = $this->resolveRequiredKeys($dot, $sql);

        array_push($sqlParts, $sql);

        $sql = $this->resolveOptionalKeys($dot, $sqlParts, $params);

        // Reemplaza espacios adicionales
        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        return $sql;
    }

    /**
     * @param string $fileKey
     *
     * @return Dot
     *
     * @throws NonExistentQueryDirectoryException
     * @throws NonExistentQueryFileException
     */
    private function getQueryFileContent(string $fileKey): Dot
    {
        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($this->queryDir)) {
            throw new NonExistentQueryDirectoryException($this->queryDir);
        }

        $filename = sprintf('%s/%s.%s', $this->queryDir, $fileKey, $this->fileExtension);

        if (!$fileSystem->exists($filename)) {
            throw new NonExistentQueryFileException($filename);
        }

        $data = Yaml::parseFile($filename);

        $dot = new Dot($data);

        return $dot;
    }

    /**
     * @param Dot    $dot
     * @param string $sql
     *
     * @return array An array of query parts depending on required keys @{file:query}
     *
     * @throws NonExistentQueryKeyException
     */
    private function resolveRequiredKeys(Dot $dot, string $sql): array
    {
        $queryParts = [];

        $snippetIds = [];

        preg_match_all(self::REQUIRED_ID_PATTERN, $sql, $snippetIds);

        foreach ($snippetIds[0] as $snippetId) {
            preg_match(self::KEY_PATTERN, $snippetId, $matches);

            $snippetKey = $matches[0];

            if (empty($snippetKey)) {
                continue;
            }

            if (!$dot->has($snippetKey)) {
                throw new NonExistentQueryKeyException($snippetKey);
            }

            $snippetSql = $dot->get($snippetKey);

            $queryParts[$snippetId] = trim(preg_replace('/\s+/', ' ', $snippetSql));
        }

        return $queryParts;
    }

    /**
     * @param Dot   $dot
     * @param array $sqlParts
     * @param array $params
     *
     * @return string Processed SQL with parameters defined by @[file:query:params]
     *
     * @throws NonExistentQueryKeyException
     */
    private function resolveOptionalKeys(Dot $dot, array $sqlParts, array &$params = []): string
    {
        $paramKeys = array_keys($params);

        // Se recorren los sqlParts y por cada uno se van agregando los filtros opcionales.
        // El query principal se evalúa al final
        foreach ($sqlParts as $sqlPartKey => $sql) {
            $snippetIds = [];

            $whereConnector = (false === strpos(strtoupper($sql), 'WHERE')) ? 'WHERE ' : 'AND ';

            preg_match_all(self::OPTIONAL_ID_PATTERN, $sql, $snippetIds);

            foreach ($snippetIds[0] as $snippetId) {
                $requestedSnippets = [];

                preg_match(self::KEY_PATTERN, $snippetId, $matches);

                $snippetKey = $matches[0];

                if (empty($snippetKey)) {
                    continue;
                }

                if (!$dot->has($snippetKey)) {
                    throw new NonExistentQueryKeyException($snippetKey);
                }

                $snippets = $dot->get($snippetKey);

                foreach ($snippets as $filter) {
                    $filterType = null;

                    if (is_array($filter)) {
                        $filterType = key($filter);
                        $filter = $filter[$filterType];
                    }

                    foreach ($paramKeys as $paramKey) {
                        if (false !== strpos($filter, ':'.$paramKey)) {
                            $event = new ProcessQueryParamsEvent($snippetKey, $filterType, $paramKey, $params, $filter);

                            if ($this->eventDispatcher) {
                                $this->eventDispatcher->dispatch(NativeQueryFromFileBuilderEvents::PROCESS_QUERY_PARAMS, $event);
                            }

                            $params = $event->getProcessedParams();
                            $filter = $event->getProcessedFilter();

                            // Si no existe en la lista de filtros a usar, se agrega
                            if (!in_array($filter, array_keys($requestedSnippets))) {
                                $requestedSnippets[$filter] = $whereConnector.'('.$filter.')';
                            }

                            $whereConnector = 'AND ';
                        }
                    }
                }

                if (!empty($requestedSnippets)) {
                    $snippetSql = implode(' ', $requestedSnippets);

                    $sqlParts[$sqlPartKey] = str_replace($snippetId, $snippetSql, $sql);
                } else {
                    $sqlParts[$sqlPartKey] = str_replace($snippetId, '', $sql);
                }
            }
        }

        // Obtiene la última posición del array que corresponde al SQL base
        $sql = array_pop($sqlParts);

        // Recorre las partes y las va reemplazando en el SQL base
        foreach ($sqlParts as $sqlPartKey => $sqlPart) {
            $sql = str_replace($sqlPartKey, $sqlPart, $sql);
        }

        return $sql;
    }
}
