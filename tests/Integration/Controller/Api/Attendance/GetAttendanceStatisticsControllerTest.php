<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Integration\Controller\Api\Attendance;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetAttendanceStatisticsController;

class GetAttendanceStatisticsControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(GetAttendanceStatisticsController::class));
    }
}