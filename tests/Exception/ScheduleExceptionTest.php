<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\ScheduleException;

/**
 * @internal
 */
#[CoversClass(ScheduleException::class)]
final class ScheduleExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new ScheduleException('Test message');
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(ScheduleException::class);
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
    }
}
