<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\ScheduleException;

class ScheduleExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new ScheduleException('Test message');
        $this->assertInstanceOf(ScheduleException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new ScheduleException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}