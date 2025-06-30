<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;

class RuntimeExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new RuntimeException('Test message');
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new RuntimeException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }
}