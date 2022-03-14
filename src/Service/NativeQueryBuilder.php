<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Micayael\NativeQueryFromFileBuilderBundle\Helper\NativeQueryBuilderHelper;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NativeQueryBuilder implements NativeQueryBuilderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cache;

    private $helper;

    private $bundleConfig;

    public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $doctrine, ?EventDispatcherInterface $eventDispatcher, ?CacheItemPoolInterface $cache, array $bundleConfig)
    {
        $this->entityManager = $entityManager;
        $this->doctrine = $doctrine;
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;
        $this->bundleConfig = $bundleConfig;

        $this->helper = new NativeQueryBuilderHelper($this->eventDispatcher, $this->cache, $bundleConfig);
    }

    public function changeEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findFromSqlKey(string $key, array $params = [], ?string $orderBy = null, string $connectionName = null, ResultSetMappingBuilder $rsm = null): array
    {
        if ($orderBy) {
            $params['orderby'] = $orderBy;
        }

        $sql = $this->helper->getSqlFromYamlKey($key, $params);

        if ($rsm) {
            $nativeQuery = $this->entityManager
                ->createNativeQuery($sql, $rsm)
            ;

            foreach ($params as $key => $value) {
                $nativeQuery->setParameter($key, $value);
            }

            $ret = $nativeQuery->getResult();
        } else {
            $result = $this->getResultFromConnection($connectionName, $sql, $params);

            $ret = $result->fetchAllAssociative();
        }

        return $ret;
    }

    public function findOneFromSqlKey(string $key, array $params = [], string $connectionName = null, ResultSetMappingBuilder $rsm = null)
    {
        try {
            $sql = $this->helper->getSqlFromYamlKey($key, $params);

            $ret = [];

            if ($rsm) {
                $nativeQuery = $this->entityManager
                    ->createNativeQuery($sql, $rsm);

                foreach ($params as $key => $value) {
                    $nativeQuery->setParameter($key, $value);
                }

                $ret[] = $nativeQuery->getSingleResult();
            } else {
                $result = $this->getResultFromConnection($connectionName, $sql, $params);

                $ret = $result->fetchAssociative();
            }

            if (empty($ret)) {
                return null;
            }

            return $ret;
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function findScalarFromSqlKey(string $key, array $params = [], string $connectionName = null)
    {
        $sql = $this->helper->getSqlFromYamlKey($key, $params);

        $result = $this->getResultFromConnection($connectionName, $sql, $params);

        $ret = $result->fetchOne();

        return $ret;
    }

    private function getResultFromConnection(?string $connectionName, string $sql, array $params = []): Result
    {
        $conn = $this->doctrine->getConnection($connectionName ?: $this->bundleConfig['default_connection']);

        $stmt = $conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $ret = $stmt->executeQuery();

        return $ret;
    }
}
