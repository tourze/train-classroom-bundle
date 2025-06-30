<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

final class DetectAnomaliesController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService,
        private readonly RegistrationRepository $registrationRepository
    ) {
    }

    #[Route(path: '/api/attendance/anomalies/{registrationId}', name: 'api_attendance_anomalies', methods: ['GET'])]
    public function __invoke(int $registrationId, Request $request): JsonResponse
    {
        try {
            $registration = $this->registrationRepository->find($registrationId);

            if ($registration === null) {
                return $this->json([
                    'success' => false,
                    'message' => '报名记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $date = $request->query->get('date')
                ? new \DateTimeImmutable($request->query->get('date'))
                : null;

            $anomalies = $this->attendanceService->detectAttendanceAnomalies($registration, $date);

            return $this->json([
                'success' => true,
                'data' => [
                    'anomalies' => $anomalies,
                    'count' => count($anomalies),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '检测异常失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}