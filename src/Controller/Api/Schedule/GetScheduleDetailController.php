<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

final class GetScheduleDetailController extends AbstractController
{
    public function __construct(
        private readonly ClassroomScheduleRepository $scheduleRepository,
    ) {
    }

    #[Route(path: '/api/schedule/detail/{id}', name: 'api_schedule_detail', methods: ['GET'])]
    public function __invoke(int|string $id): JsonResponse
    {
        try {
            // 验证ID参数
            if (!is_numeric($id)) {
                return $this->json([
                    'success' => false,
                    'message' => '无效的排课ID',
                ], Response::HTTP_BAD_REQUEST);
            }

            $id = (int) $id;
            $schedule = $this->scheduleRepository->find($id);

            if (null === $schedule) {
                return $this->json([
                    'success' => false,
                    'message' => '排课记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $schedule->getId(),
                    'classroom' => [
                        'id' => $schedule->getClassroom()->getId(),
                        'name' => $schedule->getClassroom()->getName(),
                        'type' => $schedule->getClassroom()->getType(),
                    ],
                    'teacher_id' => $schedule->getTeacherId(),
                    'type' => $schedule->getScheduleType()->value,
                    'status' => $schedule->getScheduleStatus()->value,
                    'schedule_date' => $schedule->getScheduleDate()->format('Y-m-d'),
                    'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
                    'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
                    'expected_students' => $schedule->getExpectedStudents(),
                    'actual_students' => $schedule->getActualStudents(),
                    'course_content' => $schedule->getCourseContent(),
                    'remark' => $schedule->getRemark(),
                    'created_at' => $schedule->getCreateTime()?->format('Y-m-d H:i:s'),
                    'updated_at' => $schedule->getUpdateTime()?->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取排课详情失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
