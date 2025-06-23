<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

final class BatchCreateScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
        private readonly ClassroomRepository $classroomRepository
    ) {
    }

    #[Route('/api/schedule/batch-create', name: 'api_schedule_batch_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // 验证必需参数
            if (!isset($data['classroom_id'], $data['course_id'], $data['start_date'], $data['end_date'])) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少必需参数：classroom_id, course_id, start_date, end_date',
                ], Response::HTTP_BAD_REQUEST);
            }

            // 获取教室
            $classroom = $this->classroomRepository->find($data['classroom_id']);
            
            if ($classroom === null) {
                return $this->json([
                    'success' => false,
                    'message' => '教室不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            // 准备批量创建的数据
            $scheduleData = [];
            $startDate = new \DateTimeImmutable($data['start_date']);
            $endDate = new \DateTimeImmutable($data['end_date']);
            
            // 这里应该根据业务需求生成具体的排课数据
            // 示例：每天创建一个排课
            $currentDate = $startDate;
            while ($currentDate <= $endDate) {
                $scheduleData[] = [
                    'classroom' => $classroom,
                    'course_id' => (int) $data['course_id'],
                    'type' => $data['type'] ?? 'REGULAR',
                    'start_time' => $currentDate->setTime(9, 0),
                    'end_time' => $currentDate->setTime(17, 0),
                    'instructor_id' => $data['instructor_id'] ?? null,
                    'max_students' => $data['max_students'] ?? null,
                ];
                $currentDate = $currentDate->modify('+1 day');
            }

            $skipConflicts = $data['skip_conflicts'] ?? true;
            $schedules = $this->scheduleService->batchCreateSchedules($scheduleData, $skipConflicts);

            return $this->json([
                'success' => true,
                'message' => sprintf('成功创建 %d 个排课', count($schedules)),
                'data' => [
                    'count' => count($schedules),
                    'schedules' => array_map(function ($schedule) {
                        return [
                            'id' => $schedule->getId(),
                            'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
                            'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
                        ];
                    }, $schedules),
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
                'message' => '批量创建排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}