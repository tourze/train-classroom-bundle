<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

final class UpdateScheduleController extends AbstractController
{
    public function __construct(
        private readonly ClassroomScheduleRepository $scheduleRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/schedule/update/{id}', name: 'api_schedule_update', methods: ['PUT', 'PATCH'])]
    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            // 获取排课记录
            $schedule = $this->scheduleRepository->find($id);
            
            if ($schedule === null) {
                return $this->json([
                    'success' => false,
                    'message' => '排课记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            // 更新字段
            if (isset($data['start_time'])) {
                $schedule->setStartTime(new \DateTimeImmutable($data['start_time']));
            }
            
            if (isset($data['end_time'])) {
                $schedule->setEndTime(new \DateTimeImmutable($data['end_time']));
            }
            
            if (isset($data['teacher_id'])) {
                $schedule->setTeacherId($data['teacher_id']);
            }
            
            if (isset($data['expected_students'])) {
                $schedule->setExpectedStudents((int) $data['expected_students']);
            }
            
            if (isset($data['actual_students'])) {
                $schedule->setActualStudents((int) $data['actual_students']);
            }
            
            if (isset($data['course_content'])) {
                $schedule->setCourseContent($data['course_content']);
            }
            
            if (isset($data['remark'])) {
                $schedule->setRemark($data['remark']);
            }

            // 保存更改
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => '更新排课成功',
                'data' => [
                    'id' => $schedule->getId(),
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
                'message' => '更新排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}