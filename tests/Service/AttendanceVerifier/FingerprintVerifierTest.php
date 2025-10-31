<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\AttendanceVerifier;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\FingerprintVerifier;

/**
 * FingerprintVerifier测试类
 *
 * 测试指纹验证器的功能
 *
 * @internal
 */
#[CoversClass(FingerprintVerifier::class)]
final class FingerprintVerifierTest extends TestCase
{
    private FingerprintVerifier $verifier;

    protected function setUp(): void
    {
        $this->verifier = new FingerprintVerifier();
    }

    /**
     * 测试验证器实现了接口
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(AttendanceVerifierInterface::class, $this->verifier);
    }

    /**
     * 测试支持指纹识别方式
     */
    public function testSupportsFingerprintMethod(): void
    {
        $this->assertTrue($this->verifier->supports(AttendanceMethod::FINGERPRINT));
    }

    /**
     * 测试不支持其他考勤方式
     */
    public function testDoesNotSupportOtherMethods(): void
    {
        $this->assertFalse($this->verifier->supports(AttendanceMethod::CARD));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FACE));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::QR_CODE));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::MANUAL));
    }

    /**
     * 测试验证方法返回成功结果
     */
    public function testVerifyReturnsSuccessResult(): void
    {
        $device = ['id' => 1, 'type' => 'fingerprint_scanner'];
        $data = ['user_id' => 100];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(VerificationResult::SUCCESS, $result['result']);
        $this->assertEquals(100, $result['user_id']);
    }

    /**
     * 测试验证方法处理缺失的user_id
     */
    public function testVerifyHandlesMissingUserId(): void
    {
        $device = ['id' => 1, 'type' => 'fingerprint_scanner'];
        $data = [];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertNull($result['user_id']);
    }
}
