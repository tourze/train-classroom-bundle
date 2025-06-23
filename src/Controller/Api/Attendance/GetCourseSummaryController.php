<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

final class GetCourseSummaryController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService
    ) {
    }

    #[Route('/api/attendance/course-summary/{courseId}', name: 'api_attendance_course_summary', methods: ['GET'])]
    public function __invoke(int $courseId, Request $request): JsonResponse
    {
        try {
            $startDate = $request->query->get('start_date') 
                ? new \DateTimeImmutable($request->query->get('start_date'))
                : null;
            $endDate = $request->query->get('end_date')
                ? new \DateTimeImmutable($request->query->get('end_date'))
                : null;

            $summary = $this->attendanceService->getCourseAttendanceSummary(
                $courseId,
                $startDate,
                $endDate
            );

            return $this->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取汇总失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}