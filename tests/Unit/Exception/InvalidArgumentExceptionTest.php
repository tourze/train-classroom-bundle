<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new InvalidArgumentException('Test message');
        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(InvalidArgumentException::class);
        $this->assertTrue($reflection->isSubclassOf(\Tourze\TrainClassroomBundle\Exception\TrainClassroomException::class));
    }
}