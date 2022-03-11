<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('native_query_from_file_builder');

        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('native_query_from_file_builder');
        }

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
