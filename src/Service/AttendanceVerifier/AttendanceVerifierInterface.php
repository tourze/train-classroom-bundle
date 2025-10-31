<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\AttendanceVerifier;

use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;

/**
 * 考勤验证器接口
 */
interface AttendanceVerifierInterface
{
    /**
     * 验证考勤数据
     *
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function verify(array $device, array $data): array;

    /**
     * 判断是否支持该考勤方式
     */
    public function supports(AttendanceMethod $method): bool;
}
