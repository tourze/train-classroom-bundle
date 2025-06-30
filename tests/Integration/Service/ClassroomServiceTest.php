<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\ClassroomService;

class ClassroomServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(ClassroomService::class));
    }
}