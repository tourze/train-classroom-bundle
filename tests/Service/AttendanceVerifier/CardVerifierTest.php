<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\AttendanceVerifier;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\CardVerifier;

/**
 * CardVerifier测试类
 *
 * 测试刷卡验证器的功能
 *
 * @internal
 */
#[CoversClass(CardVerifier::class)]
final class CardVerifierTest extends TestCase
{
    private CardVerifier $verifier;

    protected function setUp(): void
    {
        $this->verifier = new CardVerifier();
    }

    /**
     * 测试验证器实现了接口
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(AttendanceVerifierInterface::class, $this->verifier);
    }

    /**
     * 测试支持刷卡方式
     */
    public function testSupportsCardMethod(): void
    {
        $this->assertTrue($this->verifier->supports(AttendanceMethod::CARD));
    }

    /**
     * 测试不支持其他考勤方式
     */
    public function testDoesNotSupportOtherMethods(): void
    {
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FACE));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::FINGERPRINT));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::QR_CODE));
        $this->assertFalse($this->verifier->supports(AttendanceMethod::MANUAL));
    }

    /**
     * 测试验证方法返回成功结果
     */
    public function testVerifyReturnsSuccessResult(): void
    {
        $device = ['id' => 1, 'type' => 'card_reader'];
        $data = ['card_id' => '12345'];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(VerificationResult::SUCCESS, $result['result']);
        $this->assertEquals('12345', $result['card_id']);
    }

    /**
     * 测试验证方法处理缺失的card_id
     */
    public function testVerifyHandlesMissingCardId(): void
    {
        $device = ['id' => 1, 'type' => 'card_reader'];
        $data = [];

        $result = $this->verifier->verify($device, $data);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertNull($result['card_id']);
    }
}
