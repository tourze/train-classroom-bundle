<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainClassroomBundle\TrainClassroomBundle;

class TrainClassroomBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new TrainClassroomBundle();
        $this->assertInstanceOf(TrainClassroomBundle::class, $bundle);
    }

    public function testBundleClassName(): void
    {
        $bundle = new TrainClassroomBundle();
        $this->assertEquals(TrainClassroomBundle::class, get_class($bundle));
    }

    public function testBundleBuild(): void
    {
        $bundle = new TrainClassroomBundle();
        $container = new ContainerBuilder();
        
        // 测试build方法不抛出异常
        $bundle->build($container);
        $this->addToAssertionCount(1);
    }
}