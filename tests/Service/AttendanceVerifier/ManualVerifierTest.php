<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\AttendanceVerifier;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\ManualVerifier;

/**
 * ManualVerifier测试类
 *
 * 测试手动验证器的功能
 *
 * @internal
 */
#[CoversClass(ManualVerifier::class)]
#[RunTestsInSeparateProcesses]
final class ManualVerifierTest extends AbstractIntegrationTestCase
{
    private ManualVerifier $verifier;

    protected function onSetUp(): void
    {
        $this->verifier = self::getService(ManualVerifier::class);
    }

    /**
     * 测试验证器实现了接口
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(AttendanceVerifierInterface::class, $this->verifier);
    }

    /**
     * 测试支持手动验证方式
     */
    public function testSupportsManualMethod(): void
    {
        $this->assertTrue($this->verifier->supports(AttendanceMethod::MANUAL));
    }

    /**
     * 测试不支持其他考勤方式
     */
    public function testDoesNotSupportOtherMethods(): void
    {
        $this->assertFalse($this->verifier->supports(AttendanceMethod::CARD));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FACE));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FINGERPRINT));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::QR_CODE));
    }

    /**
     * 测试验证方法总是返回成功结果
     */
    public function testVerifyAlwaysReturnsSuccess(): void
    {
        $device = ['id' => 1, 'type' => 'manual'];
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
        $device = ['id' => 1, 'type' => 'manual'];
        $data = [];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertNull($result['user_id']);
    }
}
