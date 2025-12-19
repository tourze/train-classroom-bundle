<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\AttendanceVerifier;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\QrCodeVerifier;

/**
 * QrCodeVerifier测试类
 *
 * 测试二维码验证器的功能
 *
 * @internal
 */
#[CoversClass(QrCodeVerifier::class)]
#[RunTestsInSeparateProcesses]
final class QrCodeVerifierTest extends AbstractIntegrationTestCase
{
    private QrCodeVerifier $verifier;

    protected function onSetUp(): void
    {
        $this->verifier = self::getService(QrCodeVerifier::class);
    }

    /**
     * 测试验证器实现了接口
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(AttendanceVerifierInterface::class, $this->verifier);
    }

    /**
     * 测试支持二维码验证方式
     */
    public function testSupportsQrCodeMethod(): void
    {
        $this->assertTrue($this->verifier->supports(AttendanceMethod::QR_CODE));
    }

    /**
     * 测试不支持其他考勤方式
     */
    public function testDoesNotSupportOtherMethods(): void
    {
        $this->assertFalse($this->verifier->supports(AttendanceMethod::CARD));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FACE));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FINGERPRINT));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::MANUAL));
    }

    /**
     * 测试验证方法返回成功结果
     */
    public function testVerifyReturnsSuccessResult(): void
    {
        $device = ['id' => 1, 'type' => 'qr_scanner'];
        $data = ['qr_code' => 'QR123456'];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(VerificationResult::SUCCESS, $result['result']);
        $this->assertEquals('QR123456', $result['qr_code']);
    }

    /**
     * 测试验证方法处理缺失的qr_code
     */
    public function testVerifyHandlesMissingQrCode(): void
    {
        $device = ['id' => 1, 'type' => 'qr_scanner'];
        $data = [];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertNull($result['qr_code']);
    }
}
