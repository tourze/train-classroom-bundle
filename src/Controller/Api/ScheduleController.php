<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

/**
 * 排课API控制器
 */
#[Route('/api/schedule', name: 'api_schedule_')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * 创建排课
     */
    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // 验证必需参数
            if (!isset($data['classroom_id'], $data['course_id'], $data['type'], $data['start_time'], $data['end_time'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：classroom_id, course_id, type, start_time, end_time',
                ], Response::HTTP_BAD_REQUEST);
            }

            // 获取教室
            $classroom = $this->entityManager->getRepository(Classroom::class)
                ->find($data['classroom_id']);

            if (!$classroom) {
                return $this->json([
                    'success' => false,
                    'message' => '教室不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            // 创建排课
            $schedule = $this->scheduleService->createSchedule(
                $classroom,
                $data['course_id'],
                ScheduleType::from($data['type']),
                new \DateTimeImmutable($data['start_time']),
                new \DateTimeImmutable($data['end_time']),
                $data['options'] ?? []
            );

            return $this->json([
                'success' => true,
                'message' => '排课创建成功',
                'data' => [
                    'id' => $schedule->getId(),
                    'classroom_id' => $schedule->getClassroom()->getId(),
                    'course_id' => $schedule->getCourseId(),
                    'type' => $schedule->getType()->value,
                    'status' => $schedule->getStatus()->value,
                    'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
                    'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
                    'duration_minutes' => $schedule->getDurationInMinutes(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '排课创建失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 检测排课冲突
     */
    #[Route('/conflicts', name: 'conflicts', methods: ['POST'])]
    public function detectConflicts(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['classroom_id'], $data['start_time'], $data['end_time'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：classroom_id, start_time, end_time',
                ], Response::HTTP_BAD_REQUEST);
            }

            $classroom = $this->entityManager->getRepository(Classroom::class)
                ->find($data['classroom_id']);

            if (!$classroom) {
                return $this->json([
                    'success' => false,
                    'message' => '教室不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $conflicts = $this->scheduleService->detectScheduleConflicts(
                $classroom,
                new \DateTimeImmutable($data['start_time']),
                new \DateTimeImmutable($data['end_time']),
                $data['exclude_schedule_id'] ?? null
            );

            return $this->json([
                'success' => true,
                'data' => [
                    'has_conflicts' => !empty($conflicts),
                    'conflict_count' => count($conflicts),
                    'conflicts' => array_map(function ($schedule) {
                        return [
                            'id' => $schedule->getId(),
                            'title' => $schedule->getTitle(),
                            'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
                            'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
                            'status' => $schedule->getStatus()->value,
                        ];
                    }, $conflicts),
                ],
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '冲突检测失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 更新排课状态
     */
    #[Route('/{id}/status', name: 'update_status', methods: ['PUT'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $schedule = $this->entityManager->getRepository(ClassroomSchedule::class)
                ->find($id);

            if (!$schedule) {
                return $this->json([
                    'success' => false,
                    'message' => '排课记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['status'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：status',
                ], Response::HTTP_BAD_REQUEST);
            }

            $updatedSchedule = $this->scheduleService->updateScheduleStatus(
                $schedule,
                ScheduleStatus::from($data['status']),
                $data['reason'] ?? null
            );

            return $this->json([
                'success' => true,
                'message' => '状态更新成功',
                'data' => [
                    'id' => $updatedSchedule->getId(),
                    'status' => $updatedSchedule->getStatus()->value,
                    'remark' => $updatedSchedule->getRemark(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '状态更新失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 查找可用教室
     */
    #[Route('/available-classrooms', name: 'available_classrooms', methods: ['GET'])]
    public function findAvailableClassrooms(Request $request): JsonResponse
    {
        try {
            $startTime = $request->query->get('start_time');
            $endTime = $request->query->get('end_time');

            if (!$startTime || !$endTime) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：start_time, end_time',
                ], Response::HTTP_BAD_REQUEST);
            }

            $minCapacity = $request->query->get('min_capacity') 
                ? (int) $request->query->get('min_capacity') 
                : null;
            $requiredFeatures = $request->query->get('required_features')
                ? explode(',', $request->query->get('required_features'))
                : [];

            $availableClassrooms = $this->scheduleService->findAvailableClassrooms(
                new \DateTimeImmutable($startTime),
                new \DateTimeImmutable($endTime),
                $minCapacity,
                $requiredFeatures
            );

            return $this->json([
                'success' => true,
                'data' => [
                    'available_count' => count($availableClassrooms),
                    'classrooms' => $availableClassrooms,
                ],
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '查找可用教室失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 批量创建排课
     */
    #[Route('/batch-create', name: 'batch_create', methods: ['POST'])]
    public function batchCreate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['schedule_data']) || !is_array($data['schedule_data'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少排课数据数组',
                ], Response::HTTP_BAD_REQUEST);
            }

            $skipConflicts = $data['skip_conflicts'] ?? false;

            $results = $this->scheduleService->batchCreateSchedules(
                $data['schedule_data'],
                $skipConflicts
            );

            return $this->json([
                'success' => true,
                'message' => '批量排课完成',
                'data' => $results,
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '批量排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 取消排课
     */
    #[Route('/{id}/cancel', name: 'cancel', methods: ['PUT'])]
    public function cancel(int $id, Request $request): JsonResponse
    {
        try {
            $schedule = $this->entityManager->getRepository(ClassroomSchedule::class)
                ->find($id);

            if (!$schedule) {
                return $this->json([
                    'success' => false,
                    'message' => '排课记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            $reason = $data['reason'] ?? '未提供取消原因';

            $cancelledSchedule = $this->scheduleService->cancelSchedule($schedule, $reason);

            return $this->json([
                'success' => true,
                'message' => '排课取消成功',
                'data' => [
                    'id' => $cancelledSchedule->getId(),
                    'status' => $cancelledSchedule->getStatus()->value,
                    'remark' => $cancelledSchedule->getRemark(),
                ],
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '取消排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 延期排课
     */
    #[Route('/{id}/postpone', name: 'postpone', methods: ['PUT'])]
    public function postpone(int $id, Request $request): JsonResponse
    {
        try {
            $schedule = $this->entityManager->getRepository(ClassroomSchedule::class)
                ->find($id);

            if (!$schedule) {
                return $this->json([
                    'success' => false,
                    'message' => '排课记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['new_start_time'], $data['new_end_time'], $data['reason'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：new_start_time, new_end_time, reason',
                ], Response::HTTP_BAD_REQUEST);
            }

            $postponedSchedule = $this->scheduleService->postponeSchedule(
                $schedule,
                new \DateTimeImmutable($data['new_start_time']),
                new \DateTimeImmutable($data['new_end_time']),
                $data['reason']
            );

            return $this->json([
                'success' => true,
                'message' => '排课延期成功',
                'data' => [
                    'id' => $postponedSchedule->getId(),
                    'status' => $postponedSchedule->getStatus()->value,
                    'start_time' => $postponedSchedule->getStartTime()->format('Y-m-d H:i:s'),
                    'end_time' => $postponedSchedule->getEndTime()->format('Y-m-d H:i:s'),
                    'remark' => $postponedSchedule->getRemark(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '延期排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 获取排课日历
     */
    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function getCalendar(Request $request): JsonResponse
    {
        try {
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');

            if (!$startDate || !$endDate) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：start_date, end_date',
                ], Response::HTTP_BAD_REQUEST);
            }

            $classroomIds = $request->query->get('classroom_ids')
                ? array_map('intval', explode(',', $request->query->get('classroom_ids')))
                : [];

            $calendar = $this->scheduleService->getScheduleCalendar(
                new \DateTimeImmutable($startDate),
                new \DateTimeImmutable($endDate),
                $classroomIds
            );

            return $this->json([
                'success' => true,
                'data' => $calendar,
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取日历失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 获取教室使用率统计
     */
    #[Route('/utilization/{classroomId}', name: 'utilization', methods: ['GET'])]
    public function getUtilization(int $classroomId, Request $request): JsonResponse
    {
        try {
            $classroom = $this->entityManager->getRepository(Classroom::class)
                ->find($classroomId);

            if (!$classroom) {
                return $this->json([
                    'success' => false,
                    'message' => '教室不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');

            if (!$startDate || !$endDate) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：start_date, end_date',
                ], Response::HTTP_BAD_REQUEST);
            }

            $utilization = $this->scheduleService->getClassroomUtilizationRate(
                $classroom,
                new \DateTimeImmutable($startDate),
                new \DateTimeImmutable($endDate)
            );

            return $this->json([
                'success' => true,
                'data' => $utilization,
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取使用率统计失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 获取排课统计报表
     */
    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $startDate = $request->query->get('start_date');
            $endDate = $request->query->get('end_date');

            if (!$startDate || !$endDate) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：start_date, end_date',
                ], Response::HTTP_BAD_REQUEST);
            }

            $filters = [];
            if ($request->query->get('classroom_ids')) {
                $filters['classroom_ids'] = array_map('intval', explode(',', $request->query->get('classroom_ids')));
            }
            if ($request->query->get('course_ids')) {
                $filters['course_ids'] = array_map('intval', explode(',', $request->query->get('course_ids')));
            }
            if ($request->query->get('status')) {
                $filters['status'] = $request->query->get('status');
            }

            $statistics = $this->scheduleService->getScheduleStatisticsReport(
                new \DateTimeImmutable($startDate),
                new \DateTimeImmutable($endDate),
                $filters
            );

            return $this->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch  (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取统计报表失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 