<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Helper;

use Adbar\Dot;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryDirectoryException;
use Micayael\NativeQueryFromFileBuilderBundle\Exception\NonExistentQueryFileException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class NativeQueryBuilderHelper
{
    const REQUIRED_ID_PATTERN = '/@{.+?}/';
    const OPTIONAL_ID_PATTERN = "/@\[.+?\]/";

    const KEY_PATTERN = '/[a-z0-9._]+/';

    private $queryDir;

    public function __construct(string $queryDir)
    {
        $this->queryDir = $queryDir;
    }

    public function getSqlFromYamlKey(string $queryFullKey, array $params = []): string
    {
        $queryFullKey = explode(':', $queryFullKey);

        $fileKey = $queryFullKey[0];
        $queryKey = $queryFullKey[1];

        $fileSystem = new Filesystem();

        if (!$fileSystem->exists($this->queryDir)) {
            throw new NonExistentQueryDirectoryException($this->queryDir);
        }

        $filename = $this->queryDir."/$fileKey.yaml";

        if (!$fileSystem->exists($filename)) {
            throw new NonExistentQueryFileException($filename);
        }

        $data = Yaml::parseFile($filename);

        $dot = new Dot($data);

        $sql = $dot->get($queryKey);

        $sql = $this->resolveRequiredKeys($dot, $sql);
        $sql = $this->resolveOptionalKeys($dot, $sql, $params);

        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        return $sql;
    }

    private function resolveRequiredKeys(Dot $dot, string $sql): string
    {
        $snippetIds = [];

        preg_match_all(self::REQUIRED_ID_PATTERN, $sql, $snippetIds);

        foreach ($snippetIds[0] as $snippetId) {
            preg_match(self::KEY_PATTERN, $snippetId, $matches);

            $snippetKey = $matches[0];

            if (empty($snippetKey)) {
                continue;
            }

            $snippetSql = $dot->get($snippetKey);

            $sql = str_replace($snippetId, $snippetSql, $sql);
        }

        return $sql;
    }

    private function resolveOptionalKeys(Dot $dot, string $sql, array $params = []): string
    {
        $snippetIds = [];

        $paramKeys = array_keys($params);

        $glue = false === strpos($sql, 'WHERE') ? 'WHERE ' : 'AND ';

        preg_match_all(self::OPTIONAL_ID_PATTERN, $sql, $snippetIds);

        foreach ($snippetIds[0] as $snippetId) {
            $requestedSnippets = [];

            preg_match(self::KEY_PATTERN, $snippetId, $matches);

            $snippetKey = $matches[0];

            if (empty($snippetKey)) {
                continue;
            }

            $snippets = $dot->get($snippetKey);

            foreach ($snippets as $type => $filters) {
                foreach ($filters as $filter) {
                    foreach ($paramKeys as $paramKey) {
                        if (false !== strpos($filter, ':'.$paramKey)) {
                            $requestedSnippets[] = $glue.$filter;

                            $glue = 'AND ';
                        }
                    }
                }
            }

            if (!empty($requestedSnippets)) {
                $sliceSql = implode(' ', $requestedSnippets);

                $sql = str_replace($snippetId, $sliceSql, $sql);
            } else {
                $sql = str_replace($snippetId, '', $sql);
            }
        }

        return $sql;
    }
}
