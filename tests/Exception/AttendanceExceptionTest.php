<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\AttendanceException;

/**
 * @internal
 */
#[CoversClass(AttendanceException::class)]
final class AttendanceExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new AttendanceException('Test message');
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(AttendanceException::class);
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
    }
}
