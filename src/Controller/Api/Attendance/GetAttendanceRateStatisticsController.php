<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

final class GetAttendanceRateStatisticsController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService,
    ) {
    }

    #[Route(path: '/api/attendance/rate-statistics/{courseId}', name: 'api_attendance_rate_statistics', methods: ['GET'])]
    public function __invoke(int $courseId, Request $request): JsonResponse
    {
        try {
            $startDateParam = $request->query->get('start_date');
            $startDate = null !== $startDateParam && '' !== $startDateParam
                ? new \DateTimeImmutable((string) $startDateParam)
                : null;
            $endDateParam = $request->query->get('end_date');
            $endDate = null !== $endDateParam && '' !== $endDateParam
                ? new \DateTimeImmutable((string) $endDateParam)
                : null;

            $statistics = $this->attendanceService->getAttendanceRateStatistics(
                $courseId,
                $startDate,
                $endDate
            );

            return $this->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取考勤率统计失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
