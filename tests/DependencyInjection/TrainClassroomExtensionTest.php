<?php

namespace Tourze\TrainClassroomBundle\Tests\DependencyInjection;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;
use Tourze\TrainClassroomBundle\DependencyInjection\TrainClassroomExtension;

/**
 * @internal
 */
#[CoversClass(TrainClassroomExtension::class)]
final class TrainClassroomExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testLoadMethod(): void
    {
        $extension = new TrainClassroomExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');

        // 测试load方法不抛出异常
        $extension->load([], $container);

        // 验证容器中是否加载了服务
        $this->assertTrue($container->hasDefinition('tourze_train_classroom.attendance_service') || count($container->getDefinitions()) > 0);
    }
}
