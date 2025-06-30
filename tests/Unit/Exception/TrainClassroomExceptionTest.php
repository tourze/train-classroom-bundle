<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Exception\TrainClassroomException;

class TrainClassroomExceptionTest extends TestCase
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