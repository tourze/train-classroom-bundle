<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Integration\Controller\Api\Attendance;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\BatchImportAttendanceController;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

class BatchImportAttendanceControllerTest extends TestCase
{
    public function testControllerCanBeInstantiated(): void
    {
        $mockService = $this->createMock(AttendanceServiceInterface::class);
        $controller = new BatchImportAttendanceController($mockService);
        
        $this->assertInstanceOf(BatchImportAttendanceController::class, $controller);
    }
}