<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Micayael\NativeQueryFromFileBuilderBundle\Helper\NativeQueryBuilderHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NativeQueryBuilder implements NativeQueryBuilderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    private $helper;

    public function __construct(EntityManagerInterface $em, ?EventDispatcherInterface $eventDispatcher, array $config)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;

        $this->helper = new NativeQueryBuilderHelper($this->eventDispatcher, $config['sql_queries_dir'], $config['file_extension']);
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

    public function findFromSqlKey(string $key, array $params = [], ResultSetMappingBuilder $rsm = null): array
    {
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
}
