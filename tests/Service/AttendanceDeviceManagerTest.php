<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceDeviceManager;

/**
 * @internal
 */
#[CoversClass(AttendanceDeviceManager::class)]
#[RunTestsInSeparateProcesses]
final class AttendanceDeviceManagerTest extends AbstractIntegrationTestCase
{
    private AttendanceDeviceManager $deviceManager;

    protected function onSetUp(): void
    {
        $this->deviceManager = self::getService(AttendanceDeviceManager::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(AttendanceDeviceManager::class, $this->deviceManager);
    }

    public function testPerformVerificationWithManualMethod(): void
    {
        $classroom = new Classroom();
        $devices = [];
        $method = AttendanceMethod::MANUAL;
        $data = ['user_id' => 123];

        $result = $this->deviceManager->performVerification($classroom, $devices, $method, $data);

        $this->assertIsArray($result);
    }

    public function testPerformVerificationWithUnsupportedDevice(): void
    {
        $classroom = new Classroom();
        $devices = [['type' => 'unknown_device']];
        $method = AttendanceMethod::FACE;
        $data = ['face_data' => 'base64_image'];

        $result = $this->deviceManager->performVerification($classroom, $devices, $method, $data);

        $this->assertFalse($result['success']);
        $this->assertEquals(VerificationResult::DEVICE_ERROR, $result['result']);
        $this->assertEquals('未找到支持该考勤方式的设备', $result['message']);
    }
}