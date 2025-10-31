<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\ClassroomException;

/**
 * @internal
 */
#[CoversClass(ClassroomException::class)]
final class ClassroomExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCanBeExtended(): void
    {
        // 创建一个具体的实现来测试抽象类
        $concreteException = new class ('Test message') extends ClassroomException {};
        $this->assertEquals('Test message', $concreteException->getMessage());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(ClassroomException::class);
        $this->assertTrue($reflection->isSubclassOf(\Exception::class));
    }
}
