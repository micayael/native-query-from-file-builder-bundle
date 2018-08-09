<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Micayael\NativeQueryFromFileBuilderBundle\NativeQueryFromFileBuilderBundle;
use Micayael\NativeQueryFromFileBuilderBundle\Service\NativeQueryBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new NativeQueryFromFileBuilderTestingKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $nativeQueryBuilderService = $container->get('native_query_from_file_builder.services.native_query_builder');

        $this->assertInstanceOf(NativeQueryBuilder::class, $nativeQueryBuilderService);
    }
}

class NativeQueryFromFileBuilderTestingKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles()
    {
        return [
            new NativeQueryFromFileBuilderBundle(),
            new FrameworkBundle(),
            new DoctrineBundle(),
        ];
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'devsecret',
        ]);

        $c->loadFromExtension('doctrine', [
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

        $c->loadFromExtension('native_query_from_file_builder', [
            'sql_queries_dir' => 'config/queries',
            'debug' => true,
        ]);
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/logs';
    }
}
