<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\AttendanceVerifier;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;

/**
 * AttendanceVerifierInterface测试类
 *
 * 测试考勤验证器接口的定义
 *
 * @internal
 */
#[CoversClass(AttendanceVerifierInterface::class)]
final class AttendanceVerifierInterfaceTest extends TestCase
{
    /**
     * 测试接口存在
     */
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(AttendanceVerifierInterface::class));
    }

    /**
     * 测试verify方法存在
     */
    public function testVerifyMethodExists(): void
    {
        $reflection = new \ReflectionClass(AttendanceVerifierInterface::class);
        $this->assertTrue($reflection->hasMethod('verify'));

        $method = $reflection->getMethod('verify');
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('device', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());
    }

    /**
     * 测试supports方法存在
     */
    public function testSupportsMethodExists(): void
    {
        $reflection = new \ReflectionClass(AttendanceVerifierInterface::class);
        $this->assertTrue($reflection->hasMethod('supports'));

        $method = $reflection->getMethod('supports');
        $this->assertTrue($method->isPublic());
        $this->assertCount(1, $method->getParameters());

        $parameters = $method->getParameters();
        $this->assertEquals('method', $parameters[0]->getName());
    }
}
