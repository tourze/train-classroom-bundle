<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

final class CreateScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
        private readonly ClassroomRepository $classroomRepository
    ) {
    }

    #[Route(path: '/api/schedule/create', name: 'api_schedule_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
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
            $classroom = $this->classroomRepository->find($data['classroom_id']);
            
            if ($classroom === null) {
                return $this->json([
                    'success' => false,
                    'message' => '教室不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            // 创建排课
            $options = [];
            if (isset($data['instructor_id'])) {
                $options['instructor_id'] = $data['instructor_id'];
            }
            if (isset($data['max_students'])) {
                $options['max_students'] = $data['max_students'];
            }
            if (isset($data['location'])) {
                $options['location'] = $data['location'];
            }
            if (isset($data['description'])) {
                $options['description'] = $data['description'];
            }
            
            $schedule = $this->scheduleService->createSchedule(
                $classroom,
                (int) $data['course_id'],
                ScheduleType::from($data['type']),
                new \DateTimeImmutable($data['start_time']),
                new \DateTimeImmutable($data['end_time']),
                $options
            );

            return $this->json([
                'success' => true,
                'message' => '创建排课成功',
                'data' => [
                    'id' => $schedule->getId(),
                    'type' => $schedule->getScheduleType()->value,
                    'status' => $schedule->getScheduleStatus()->value,
                    'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
                    'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
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
                'message' => '创建排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}