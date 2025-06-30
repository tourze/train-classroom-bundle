<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Integration\Controller\Api\Register;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Register\RegisterFormController;

class RegisterFormControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(RegisterFormController::class));
    }
}