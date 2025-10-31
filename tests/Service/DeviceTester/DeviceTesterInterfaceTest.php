<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\DeviceTester;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\DeviceTester\DeviceTesterInterface;

/**
 * DeviceTesterInterface测试类
 *
 * 测试设备测试器接口的定义
 *
 * @internal
 */
#[CoversClass(DeviceTesterInterface::class)]
final class DeviceTesterInterfaceTest extends TestCase
{
    /**
     * 测试接口存在
     */
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(DeviceTesterInterface::class));
    }

    /**
     * 测试test方法存在
     */
    public function testTestMethodExists(): void
    {
        $reflection = new \ReflectionClass(DeviceTesterInterface::class);
        $this->assertTrue($reflection->hasMethod('test'));

        $method = $reflection->getMethod('test');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('deviceConfig', $parameters[0]->getName());
    }

    /**
     * 测试supports方法存在
     */
    public function testSupportsMethodExists(): void
    {
        $reflection = new \ReflectionClass(DeviceTesterInterface::class);
        $this->assertTrue($reflection->hasMethod('supports'));

        $method = $reflection->getMethod('supports');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('deviceType', $parameters[0]->getName());
    }
}
