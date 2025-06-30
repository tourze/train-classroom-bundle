<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\AttendanceException;

class AttendanceExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new AttendanceException('Test message');
        $this->assertInstanceOf(AttendanceException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new AttendanceException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}