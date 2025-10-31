<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Classroom;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Service\ClassroomServiceInterface;

final class CreateClassroomController extends AbstractController
{
    public function __construct(
        private readonly ClassroomServiceInterface $classroomService,
    ) {
    }

    #[Route(path: '/api/classrooms', name: 'api_classroom_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return $this->json(['error' => '无效的JSON数据'], 400);
            }

            /** @var array<string, mixed> $data */
            $classroom = $this->classroomService->createClassroom($data);

            return $this->json([
                'success' => true,
                'data' => [
                    'id' => $classroom->getId(),
                    'name' => $classroom->getName(),
                    'type' => $classroom->getType(),
                    'status' => $classroom->getStatus(),
                    'capacity' => $classroom->getCapacity(),
                ],
                'message' => '教室创建成功',
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['error' => '创建教室失败'], 500);
        }
    }
}
