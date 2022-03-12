<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Helper;

use Adbar\Dot;
use Micayael\NativeQueryFromFileBuilderBundle\Event\ProcessQueryParamsEvent;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryDirectoryException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryFileException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryKeyException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Cache\ItemInterface;

class NativeQueryBuilderHelper
{
    public const REQUIRED_ID_PATTERN = '/@{.+?}/';

    public const OPTIONAL_ID_PATTERN = "/@\[.+?\]/";

    public const KEY_PATTERN = '/[a-z0-9._]+/';

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var AdapterInterface|null
     */
    private $cache;

    private $queryDir;

    private $fileExtension;

    public function __construct(?EventDispatcherInterface $eventDispatcher, ?CacheItemPoolInterface $cache, array $bundleConfig)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = null;

        if ($bundleConfig['cache_sql']) {
            $this->cache = $cache;
        }

        $this->queryDir = $bundleConfig['sql_queries_dir'];
        $this->fileExtension = $bundleConfig['file_extension'];
    }

    /**
     * Get a SQL query from a query key in a yaml file.
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
            $dot = $this->cache->get('nqbff_'.$fileKey, function (ItemInterface $item) use ($fileKey) {
                return $this->getQueryFileContent($fileKey);
            });
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

        if (isset($params['orderby'])) {
            $sql = preg_replace('/:\w+:/', $params['orderby'], $sql);
            $sql = str_replace(':orderby', $params['orderby'], $sql);
        }

        return $sql;
    }

    /**
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
                                $this->eventDispatcher->dispatch($event);
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
