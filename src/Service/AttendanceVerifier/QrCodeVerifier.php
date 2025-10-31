<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\AttendanceVerifier;

use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 二维码验证器
 */
class QrCodeVerifier implements AttendanceVerifierInterface
{
    public function supports(AttendanceMethod $method): bool
    {
        return AttendanceMethod::QR_CODE === $method;
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function verify(array $device, array $data): array
    {
        // 模拟二维码验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'qr_code' => $data['qr_code'] ?? null,
        ];
    }
}
