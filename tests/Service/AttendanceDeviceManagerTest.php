<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceDeviceManager;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;

/**
 * @internal
 */
#[CoversClass(AttendanceDeviceManager::class)]
final class AttendanceDeviceManagerTest extends TestCase
{
    private AttendanceDeviceManager $deviceManager;
    private LoggerInterface $logger;
    /** @var AttendanceVerifierInterface&MockObject */
    private AttendanceVerifierInterface $verifier;

    protected function setUp(): void
    {
        /** @var LoggerInterface&MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $this->logger = $logger;

        /** @var AttendanceVerifierInterface&MockObject $verifier */
        $verifier = $this->createMock(AttendanceVerifierInterface::class);
        $this->verifier = $verifier;

        $this->deviceManager = new AttendanceDeviceManager([$this->verifier], $this->logger);
    }

    public function testGetSupportedMethods(): void
    {
        /** @var array<string, mixed> $devices */
        $devices = [
            'device1' => ['type' => 'face_recognition', 'name' => 'Face Device'],
            'device2' => ['type' => 'qr_scanner', 'name' => 'QR Scanner'],
        ];

        $methods = $this->deviceManager->getSupportedMethods($devices);

        $this->assertContains(AttendanceMethod::FACE, $methods);
        $this->assertContains(AttendanceMethod::QR_CODE, $methods);
        $this->assertContains(AttendanceMethod::MANUAL, $methods); // 总是支持手动考勤
    }

    public function testPerformVerification(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('Test Classroom');

        /** @var array<string, mixed> $devices */
        $devices = [
            'device1' => ['type' => 'face_recognition', 'name' => 'Face Device'],
        ];

        $this->verifier->expects($this->once())
            ->method('supports')
            ->with(AttendanceMethod::FACE)
            ->willReturn(true);

        $this->verifier->expects($this->once())
            ->method('verify')
            ->willReturn([
                'success' => true,
                'result' => VerificationResult::SUCCESS,
                'message' => '验证成功',
            ]);

        $result = $this->deviceManager->performVerification(
            $classroom,
            $devices,
            AttendanceMethod::FACE,
            ['user_id' => 123]
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(VerificationResult::SUCCESS, $result['result']);
    }

    public function testPerformVerificationWithUnsupportedMethod(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('Test Classroom');

        /** @var array<string, mixed> $devices */
        $devices = [
            'device1' => ['type' => 'face_recognition', 'name' => 'Face Device'],
        ];

        // 尝试使用指纹验证，但设备不支持
        $result = $this->deviceManager->performVerification(
            $classroom,
            $devices,
            AttendanceMethod::FINGERPRINT,
            ['user_id' => 123]
        );

        $this->assertFalse($result['success']);
        $this->assertEquals(VerificationResult::DEVICE_ERROR, $result['result']);
        $this->assertIsString($result['message']);
        $this->assertStringContainsString('未找到支持该考勤方式的设备', $result['message']);
    }

    public function testPerformVerificationManualMethod(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('Test Classroom');

        /** @var array<string, mixed> $devices */
        $devices = [];

        $this->verifier->expects($this->once())
            ->method('supports')
            ->with(AttendanceMethod::MANUAL)
            ->willReturn(true);

        $this->verifier->expects($this->once())
            ->method('verify')
            ->willReturn([
                'success' => true,
                'result' => VerificationResult::SUCCESS,
                'message' => '手动验证成功',
            ]);

        $result = $this->deviceManager->performVerification(
            $classroom,
            $devices,
            AttendanceMethod::MANUAL,
            ['user_id' => 123]
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(VerificationResult::SUCCESS, $result['result']);
    }
}
