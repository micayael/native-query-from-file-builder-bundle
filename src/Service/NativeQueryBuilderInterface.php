<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\Query\ResultSetMappingBuilder;

interface NativeQueryBuilderInterface
{
    public function findOneFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null);

    public function findFromSqlKey(string $key, array $params = [], ?string $orderBy, ResultSetMappingBuilder $rsm = null): array;

    public function findScalarFromSqlKey(string $key, array $params = []);
}
