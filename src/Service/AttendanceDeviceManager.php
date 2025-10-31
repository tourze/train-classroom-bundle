<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;

/**
 * 考勤设备管理器
 *
 * 负责考勤相关的设备功能
 */
class AttendanceDeviceManager
{
    /** @param array<AttendanceVerifierInterface> $verifiers */
    public function __construct(
        private readonly array $verifiers,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $devices
     * @return array<int, AttendanceMethod>
     */
    public function getSupportedMethods(array $devices): array
    {
        $methods = $this->extractMethodsFromDevices($devices);

        // 总是支持手动考勤
        $methods[] = AttendanceMethod::MANUAL;

        return $this->deduplicateMethods($methods);
    }

    /**
     * @param array<string, mixed> $devices
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function performVerification(Classroom $classroom, array $devices, AttendanceMethod $method, array $data): array
    {
        $targetDevice = $this->findSupportingDevice($devices, $method);

        if (null === $targetDevice && AttendanceMethod::MANUAL !== $method) {
            return $this->createDeviceNotFoundResponse();
        }

        return $this->executeVerification($classroom, $method, $targetDevice ?? [], $data);
    }

    /**
     * @param array<string, mixed> $devices
     * @return array<int, AttendanceMethod>
     */
    private function extractMethodsFromDevices(array $devices): array
    {
        $methods = [];

        foreach ($devices as $device) {
            if (!is_array($device)) {
                continue;
            }

            $deviceType = is_string($device['type'] ?? null) ? $device['type'] : 'unknown';
            $method = $this->mapDeviceTypeToMethod($deviceType);
            if (null !== $method) {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    private function mapDeviceTypeToMethod(string $deviceType): ?AttendanceMethod
    {
        return match ($deviceType) {
            'face_recognition' => AttendanceMethod::FACE,
            'fingerprint' => AttendanceMethod::FINGERPRINT,
            'card_reader' => AttendanceMethod::CARD,
            'qr_scanner' => AttendanceMethod::QR_CODE,
            default => null,
        };
    }

    /**
     * @param array<int, AttendanceMethod> $methods
     * @return array<int, AttendanceMethod>
     */
    private function deduplicateMethods(array $methods): array
    {
        $uniqueMethods = [];
        foreach ($methods as $method) {
            $uniqueMethods[$method->value] = $method;
        }

        return array_values($uniqueMethods);
    }

    /**
     * @param array<string, mixed> $devices
     * @return array<string, mixed>|null
     */
    private function findSupportingDevice(array $devices, AttendanceMethod $method): ?array
    {
        foreach ($devices as $device) {
            if (is_array($device)) {
                /** @var array<string, mixed> $device */
                if ($this->deviceSupportsMethod($device, $method)) {
                    return $device;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function createDeviceNotFoundResponse(): array
    {
        return [
            'success' => false,
            'result' => VerificationResult::DEVICE_ERROR,
            'message' => '未找到支持该考勤方式的设备',
        ];
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function executeVerification(
        Classroom $classroom,
        AttendanceMethod $method,
        array $device,
        array $data,
    ): array {
        try {
            return $this->delegateToVerifier($method, $device, $data);
        } catch (\Throwable $e) {
            return $this->handleVerificationError($classroom, $method, $e);
        }
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function delegateToVerifier(AttendanceMethod $method, array $device, array $data): array
    {
        foreach ($this->verifiers as $verifier) {
            if ($verifier->supports($method)) {
                return $verifier->verify($device, $data);
            }
        }

        return [
            'success' => false,
            'result' => VerificationResult::FAILED,
            'message' => '不支持的考勤方式',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function handleVerificationError(Classroom $classroom, AttendanceMethod $method, \Throwable $e): array
    {
        $this->logger->error('考勤验证失败', [
            'classroom_id' => $classroom->getId(),
            'method' => $method->value,
            'error' => $e->getMessage(),
        ]);

        return [
            'success' => false,
            'result' => VerificationResult::DEVICE_ERROR,
            'message' => $e->getMessage(),
        ];
    }

    /**
     * @param array<string, mixed> $device
     */
    private function deviceSupportsMethod(array $device, AttendanceMethod $method): bool
    {
        $type = $device['type'] ?? 'unknown';

        return match ($method) {
            AttendanceMethod::FACE => 'face_recognition' === $type,
            AttendanceMethod::FINGERPRINT => 'fingerprint' === $type,
            AttendanceMethod::CARD => 'card_reader' === $type,
            AttendanceMethod::QR_CODE => 'qr_scanner' === $type,
            AttendanceMethod::MANUAL => true,
            default => false,
        };
    }
}
