<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Integration\Controller\Api\Attendance;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\DetectAnomaliesController;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

class DetectAnomaliesControllerTest extends TestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $mockService = $this->createMock(AttendanceServiceInterface::class);
        $mockRepository = $this->createMock(RegistrationRepository::class);
        $controller = new DetectAnomaliesController($mockService, $mockRepository);
        
        $this->assertInstanceOf(DetectAnomaliesController::class, $controller);
    }
}