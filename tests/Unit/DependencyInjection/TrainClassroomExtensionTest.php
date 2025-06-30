<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\TrainClassroomBundle\DependencyInjection\TrainClassroomExtension;

class TrainClassroomExtensionTest extends TestCase
{
    public function testExtensionCanBeInstantiated(): void
    {
        $extension = new TrainClassroomExtension();
        $this->assertInstanceOf(TrainClassroomExtension::class, $extension);
    }

    public function testLoadMethod(): void
    {
        $extension = new TrainClassroomExtension();
        $container = new ContainerBuilder();
        
        // 测试load方法不抛出异常
        $extension->load([], $container);
        $this->addToAssertionCount(1);
    }
}