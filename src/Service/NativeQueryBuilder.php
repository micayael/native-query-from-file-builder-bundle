<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Micayael\NativeQueryFromFileBuilderBundle\Helper\NativeQueryBuilderHelper;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NativeQueryBuilder implements NativeQueryBuilderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var null|EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var null|AdapterInterface
     */
    private $cache;

    private $helper;

    public function __construct(EntityManagerInterface $em, ?EventDispatcherInterface $eventDispatcher, ?AdapterInterface $cache, array $config)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;

        $this->helper = new NativeQueryBuilderHelper($this->eventDispatcher, $this->cache, $config);
    }

    public function findOneFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null)
    {
        try {
            $sql = $this->helper->getSqlFromYamlKey($key, $params);

            $ret = [];

            if ($rsm) {
                $nativeQuery = $this->em
                    ->createNativeQuery($sql, $rsm);

                foreach ($params as $key => $value) {
                    $nativeQuery->setParameter($key, $value);
                }

                $ret[] = $nativeQuery->getSingleResult();
            } else {
                $ret = $this->em
                    ->getConnection()
                    ->fetchAll($sql, $params);
            }

            if (empty($ret)) {
                return null;
            }

            if (count($ret) > 1) {
                throw new NonUniqueResultException(sprintf('Se han encontrado %d filas', count($ret)));
            }

            return $ret[0];
        } catch (NoResultException $e) {
            return null;
        }
    }

    public function findFromSqlKey(string $key, array $params = [], ?string $orderBy, ResultSetMappingBuilder $rsm = null): array
    {
        if ($orderBy) {
            $params['orderby'] = $orderBy;
        }

        $sql = $this->helper->getSqlFromYamlKey($key, $params);

        if ($rsm) {
            $nativeQuery = $this->em
                ->createNativeQuery($sql, $rsm)
            ;

            foreach ($params as $key => $value) {
                $nativeQuery->setParameter($key, $value);
            }

            $ret = $nativeQuery->getResult();
        } else {
            $ret = $this->em
                ->getConnection()
                ->fetchAll($sql, $params);
        }

        return $ret;
    }

    public function findScalarFromSqlKey(string $key, array $params = [])
    {
        $sql = $this->helper->getSqlFromYamlKey($key, $params);

        $ret = $this->em
            ->getConnection()
            ->fetchColumn($sql, $params);

        return $ret;
    }
}
