<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Attendance;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

final class BatchImportAttendanceController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService
    ) {
    }

    #[Route(path: '/api/attendance/batch-import', name: 'api_attendance_batch_import', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['attendance_data']) || !is_array($data['attendance_data'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少考勤数据数组',
                ], Response::HTTP_BAD_REQUEST);
            }

            $results = $this->attendanceService->batchImportAttendance($data['attendance_data']);

            return $this->json([
                'success' => true,
                'message' => '批量导入完成',
                'data' => $results,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '批量导入失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}