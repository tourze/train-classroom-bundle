<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

final class RecordAttendanceController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService,
        private readonly RegistrationRepository $registrationRepository
    ) {
    }

    #[Route('/api/attendance/record', name: 'api_attendance_record', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // 验证必需参数
            if (!isset($data['registration_id'], $data['type'], $data['method'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：registration_id, type, method',
                ], Response::HTTP_BAD_REQUEST);
            }

            // 获取报名记录
            $registration = $this->registrationRepository->find($data['registration_id']);

            if ($registration === null) {
                return $this->json([
                    'success' => false,
                    'message' => '报名记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            // 记录考勤
            $attendance = $this->attendanceService->recordAttendance(
                $registration,
                AttendanceType::from($data['type']),
                AttendanceMethod::from($data['method']),
                $data['device_data'] ?? [],
                $data['remark'] ?? null
            );

            return $this->json([
                'success' => true,
                'message' => '考勤记录成功',
                'data' => [
                    'id' => $attendance->getId(),
                    'type' => $attendance->getType()->value,
                    'method' => $attendance->getMethod()->value,
                    'record_time' => $attendance->getRecordTime()->format('Y-m-d H:i:s'),
                    'verification_result' => $attendance->getVerificationResult()->value,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '考勤记录失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}