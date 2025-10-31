<?php

namespace Tourze\TrainClassroomBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TrainClassroomBundle\Exception\TrainClassroomException;

/**
 * @internal
 */
#[CoversClass(TrainClassroomException::class)]
final class TrainClassroomExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionIsAbstract(): void
    {
        $reflection = new \ReflectionClass(TrainClassroomException::class);
        $this->assertTrue($reflection->isAbstract());
    }

    public function testExceptionInheritance(): void
    {
        $reflection = new \ReflectionClass(TrainClassroomException::class);
        $this->assertTrue($reflection->isSubclassOf(\RuntimeException::class));
    }
}
