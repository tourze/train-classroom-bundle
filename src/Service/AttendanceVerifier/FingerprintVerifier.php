<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\AttendanceVerifier;

use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 指纹验证器
 */
class FingerprintVerifier implements AttendanceVerifierInterface
{
    public function supports(AttendanceMethod $method): bool
    {
        return AttendanceMethod::FINGERPRINT === $method;
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function verify(array $device, array $data): array
    {
        // 模拟指纹验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'user_id' => $data['user_id'] ?? null,
        ];
    }
}
