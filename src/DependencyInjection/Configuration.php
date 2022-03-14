<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('native_query_from_file_builder');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()

                ->scalarNode('sql_queries_dir')
                    ->defaultValue('%kernel.project_dir%/config/app/queries')
                ->end()
                ->scalarNode('default_connection')
                    ->defaultValue('default')
                ->end()
                ->booleanNode('cache_sql')
                    ->defaultTrue()
                ->end()
                ->enumNode('file_extension')
                    ->values(['yaml', 'yml'])
                    ->defaultValue('yaml')
                ->end()

            ->end();

        return $treeBuilder;
    }
}
