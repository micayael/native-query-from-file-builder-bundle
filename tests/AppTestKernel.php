<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Micayael\NativeQueryFromFileBuilderBundle\NativeQueryFromFileBuilderBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class AppTestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new NativeQueryFromFileBuilderBundle(),
            new FrameworkBundle(),
            new DoctrineBundle(),
        ];
    }

    protected function configureContainer(ContainerConfigurator $c): void
    {
        $c->extension('framework', [
            'secret' => 'devsecret',
            'router' => [
                'utf8' => true,
            ],
        ]);

        $c->extension('doctrine', [
            'dbal' => [
                'default_connection' => 'default',
                'connections' => [
                    'default' => [
                        'dbname' => null,
                        'host' => 'localhost',
                        'port' => null,
                        'user' => 'root',
                        'password' => null,
                    ],
                ],
            ],
            'orm' => [
                'default_entity_manager' => null,
            ],
        ]);

        $c->extension('native_query_from_file_builder', [
            'sql_queries_dir' => 'config/queries',
            'cache_sql' => false,
        ]);
    }
}
