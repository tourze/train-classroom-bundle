<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

/**
 * 考勤API控制器
 */
#[Route('/api/attendance', name: 'api_attendance_')]
class AttendanceController extends AbstractController
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * 记录考勤
     */
    #[Route('/record', name: 'record', methods: ['POST'])]
    public function record(Request $request): JsonResponse
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
            $registration = $this->entityManager->getRepository(Registration::class)
                ->find($data['registration_id']);

            if (!$registration) {
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '考勤记录失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 批量导入考勤记录
     */
    #[Route('/batch-import', name: 'batch_import', methods: ['POST'])]
    public function batchImport(Request $request): JsonResponse
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '批量导入失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 获取学员考勤统计
     */
    #[Route('/statistics/{registrationId}', name: 'statistics', methods: ['GET'])]
    public function getStatistics(int $registrationId): JsonResponse
    {
        try {
            $registration = $this->entityManager->getRepository(Registration::class)
                ->find($registrationId);

            if (!$registration) {
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '获取统计失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 获取课程考勤汇总
     */
    #[Route('/course-summary/{courseId}', name: 'course_summary', methods: ['GET'])]
    public function getCourseSummary(int $courseId, Request $request): JsonResponse
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '获取汇总失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 检测考勤异常
     */
    #[Route('/anomalies/{registrationId}', name: 'anomalies', methods: ['GET'])]
    public function detectAnomalies(int $registrationId, Request $request): JsonResponse
    {
        try {
            $registration = $this->entityManager->getRepository(Registration::class)
                ->find($registrationId);

            if (!$registration) {
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '检测异常失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 补录考勤记录
     */
    #[Route('/makeup', name: 'makeup', methods: ['POST'])]
    public function makeupAttendance(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // 验证必需参数
            if (!isset($data['registration_id'], $data['type'], $data['record_time'], $data['reason'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：registration_id, type, record_time, reason',
                ], Response::HTTP_BAD_REQUEST);
            }

            // 获取报名记录
            $registration = $this->entityManager->getRepository(Registration::class)
                ->find($data['registration_id']);

            if (!$registration) {
                return $this->json([
                    'success' => false,
                    'message' => '报名记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            // 补录考勤
            $attendance = $this->attendanceService->makeUpAttendance(
                $registration,
                AttendanceType::from($data['type']),
                new \DateTimeImmutable($data['record_time']),
                $data['reason']
            );

            return $this->json([
                'success' => true,
                'message' => '补录考勤成功',
                'data' => [
                    'id' => $attendance->getId(),
                    'type' => $attendance->getType()->value,
                    'record_time' => $attendance->getRecordTime()->format('Y-m-d H:i:s'),
                    'remark' => $attendance->getRemark(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '补录考勤失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 获取考勤率统计
     */
    #[Route('/rate-statistics/{courseId}', name: 'rate_statistics', methods: ['GET'])]
    public function getAttendanceRateStatistics(int $courseId, Request $request): JsonResponse
    {
        try {
            $startDate = $request->query->get('start_date')
                ? new \DateTimeImmutable($request->query->get('start_date'))
                : null;
            $endDate = $request->query->get('end_date')
                ? new \DateTimeImmutable($request->query->get('end_date'))
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
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => '获取考勤率统计失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 