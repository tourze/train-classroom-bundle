<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\AttendanceVerifier;

use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 人脸识别验证器
 */
class FaceRecognitionVerifier implements AttendanceVerifierInterface
{
    public function supports(AttendanceMethod $method): bool
    {
        return AttendanceMethod::FACE === $method;
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function verify(array $device, array $data): array
    {
        // 模拟人脸识别验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'confidence' => 0.95,
            'user_id' => $data['user_id'] ?? null,
        ];
    }
}
