<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

final class CancelScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
        private readonly ClassroomScheduleRepository $scheduleRepository
    ) {
    }

    #[Route('/api/schedule/cancel/{id}', name: 'api_schedule_cancel', methods: ['POST'])]
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
            $reason = $data['reason'] ?? '未说明原因';

            // 取消排课
            $schedule = $this->scheduleService->cancelSchedule($schedule, $reason);

            return $this->json([
                'success' => true,
                'message' => '取消排课成功',
                'data' => [
                    'id' => $schedule->getId(),
                    'status' => $schedule->getScheduleStatus()->value,
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
                'message' => '取消排课失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}