<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

interface NativeQueryBuilderInterface
{
    public function changeEntityManager(EntityManagerInterface $em);

    public function findOneFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null);

    public function findFromSqlKey(string $key, array $params = [], ?string $orderBy, ResultSetMappingBuilder $rsm = null): array;

    public function findScalarFromSqlKey(string $key, array $params = []);
}
