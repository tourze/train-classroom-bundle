<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;

/**
 * @internal
 */
#[CoversClass(RuntimeException::class)]
final class RuntimeExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new RuntimeException('Test message');
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(RuntimeException::class);
        $this->assertTrue($reflection->isSubclassOf(\RuntimeException::class));
    }
}
