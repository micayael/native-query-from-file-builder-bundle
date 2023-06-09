<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

interface NativeQueryBuilderInterface
{
    public function changeEntityManager(EntityManagerInterface $entityManager);

    public function findFromSqlKey(string $key, array $params = [], ?string $orderBy = null, string $connectionName = null, ResultSetMapping $rsm = null): array;

    public function findOneFromSqlKey(string $key, array $params = [], string $connectionName = null, ResultSetMapping $rsm = null);

    public function findScalarFromSqlKey(string $key, array $params = [], string $connectionName = null);
}
