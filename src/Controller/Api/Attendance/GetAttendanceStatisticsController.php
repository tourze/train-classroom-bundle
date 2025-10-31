<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

final class GetAttendanceStatisticsController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService,
        private readonly RegistrationRepository $registrationRepository,
    ) {
    }

    #[Route(path: '/api/attendance/statistics/{registrationId}', name: 'api_attendance_statistics', methods: ['GET'])]
    public function __invoke(int $registrationId): JsonResponse
    {
        try {
            $registration = $this->registrationRepository->find($registrationId);

            if (null === $registration) {
                return $this->json([
                    'success' => false,
                    'message' => '报名记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $statistics = $this->attendanceService->getAttendanceStatistics($registration);

            return $this->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取统计失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
