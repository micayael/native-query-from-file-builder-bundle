<?php

namespace Micayael\NativeQueryFromFileBuilderBundle\Tests;

use Micayael\NativeQueryFromFileBuilderBundle\Service\NativeQueryBuilder;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new AppTestKernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        $nativeQueryBuilderService = $container->get(NativeQueryBuilder::class);

        $this->assertInstanceOf(NativeQueryBuilder::class, $nativeQueryBuilderService);
    }
}
