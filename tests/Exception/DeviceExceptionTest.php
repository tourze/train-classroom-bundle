<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\DeviceException;

/**
 * @internal
 */
#[CoversClass(DeviceException::class)]
final class DeviceExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new DeviceException('Test message');
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(DeviceException::class);
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
    }
}
