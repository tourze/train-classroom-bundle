<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\AttendanceVerifier;

use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 刷卡验证器
 */
class CardVerifier implements AttendanceVerifierInterface
{
    public function supports(AttendanceMethod $method): bool
    {
        return AttendanceMethod::CARD === $method;
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function verify(array $device, array $data): array
    {
        // 模拟刷卡验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'card_id' => $data['card_id'] ?? null,
        ];
    }
}
