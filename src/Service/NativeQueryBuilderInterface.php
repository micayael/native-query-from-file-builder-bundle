<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\Query\ResultSetMappingBuilder;

interface NativeQueryBuilderInterface
{
    public function findOneFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null);

    public function findFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null): array;
}
