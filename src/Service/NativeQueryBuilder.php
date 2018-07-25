<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Micayael\NativeQueryFromFileBuilderBundle\Helper\NativeQueryBuilderHelper;

class NativeQueryBuilder implements NativeQueryBuilderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    private $helper;

    public function __construct(EntityManagerInterface $em, string $sqlQueriesDir)
    {
        $this->em = $em;

        $this->helper = new NativeQueryBuilderHelper($sqlQueriesDir);
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
