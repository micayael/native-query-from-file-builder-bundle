<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('native_query_from_file_builder');

        $rootNode
            ->children()

                ->scalarNode('sql_queries_dir')->end()
                ->enumNode('file_extension')
                    ->values(['yaml', 'yml'])
                    ->defaultValue('yaml')
                ->end()

            ->end();

        return $treeBuilder;
    }
}
