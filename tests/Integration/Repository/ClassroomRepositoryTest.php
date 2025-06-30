<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;

class ClassroomRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(ClassroomRepository::class));
    }
}