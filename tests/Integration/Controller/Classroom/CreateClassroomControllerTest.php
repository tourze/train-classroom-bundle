<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Integration\Controller\Classroom;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Controller\Classroom\CreateClassroomController;

class CreateClassroomControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(CreateClassroomController::class));
    }
}