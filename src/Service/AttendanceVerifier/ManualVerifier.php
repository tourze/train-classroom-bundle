<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\AttendanceVerifier;

use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 手动验证器
 */
class ManualVerifier implements AttendanceVerifierInterface
{
    public function supports(AttendanceMethod $method): bool
    {
        return AttendanceMethod::MANUAL === $method;
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function verify(array $device, array $data): array
    {
        // 手动验证总是成功
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'user_id' => $data['user_id'] ?? null,
        ];
    }
}
