<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\ClassroomException;

class ClassroomExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new ClassroomException('Test message');
        $this->assertInstanceOf(ClassroomException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new ClassroomException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}