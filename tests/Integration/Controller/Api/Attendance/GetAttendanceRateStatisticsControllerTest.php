<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Integration\Controller\Api\Attendance;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetAttendanceRateStatisticsController;

class GetAttendanceRateStatisticsControllerTest extends TestCase
{
    public function testControllerExists(): void
    {
        $this->assertTrue(class_exists(GetAttendanceRateStatisticsController::class));
    }
}