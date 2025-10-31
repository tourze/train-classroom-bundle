<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\ClassroomNotFoundException;

/**
 * @internal
 */
#[CoversClass(ClassroomNotFoundException::class)]
final class ClassroomNotFoundExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeInstantiated(): void
    {
        $exception = new ClassroomNotFoundException('Classroom not found');
        $this->assertEquals('Classroom not found', $exception->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(ClassroomNotFoundException::class);
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
    }
}
