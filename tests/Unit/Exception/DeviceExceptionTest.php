<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\DeviceException;

class DeviceExceptionTest extends TestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new DeviceException('Test message');
        $this->assertInstanceOf(DeviceException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new DeviceException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }
}