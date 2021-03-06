<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class NativeQueryFromFileBuilderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $nativeQueryBuilderDefinition = $container->getDefinition('native_query_from_file_builder.services.native_query_builder');
        $nativeQueryBuilderDefinition->setArgument(3, $config);
    }
}
