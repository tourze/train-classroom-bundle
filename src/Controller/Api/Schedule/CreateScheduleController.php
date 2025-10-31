<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;
use Tourze\TrainClassroomBundle\Exception\ClassroomNotFoundException;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

final class CreateScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
        private readonly ClassroomRepository $classroomRepository,
    ) {
    }

    #[Route(path: '/api/schedule/create', name: 'api_schedule_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = $this->parseAndValidateRequest($request);
            $classroom = $this->findClassroomOrFail($data['classroom_id']);
            $options = $this->extractOptionalParameters($data);

            $courseId = $data['course_id'];
            $type = $data['type'];
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];

            if (!is_numeric($courseId)) {
                throw new \InvalidArgumentException('课程ID必须为数字');
            }

            if (!is_string($type) && !is_int($type)) {
                throw new \InvalidArgumentException('排课类型参数格式错误');
            }

            if (!is_string($startTime) || !is_string($endTime)) {
                throw new \InvalidArgumentException('日期参数必须为字符串格式');
            }

            $schedule = $this->scheduleService->createSchedule(
                $classroom,
                (int) $courseId,
                ScheduleType::from($type),
                new \DateTimeImmutable($startTime),
                new \DateTimeImmutable($endTime),
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
        } catch (ClassroomNotFoundException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
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

    /**
     * @return array<string, mixed>
     */
    private function parseAndValidateRequest(Request $request): array
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('无效的请求数据');
        }

        /** @var array<string, mixed> $validatedData */
        $validatedData = $data;
        $this->validateRequiredParameters($validatedData);
        $this->validateParameterTypes($validatedData);

        return $validatedData;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateRequiredParameters(array $data): void
    {
        if (!isset($data['classroom_id'], $data['course_id'], $data['type'], $data['start_time'], $data['end_time'])) {
            throw new \InvalidArgumentException('缺少必需参数：classroom_id, course_id, type, start_time, end_time');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateParameterTypes(array $data): void
    {
        if (!is_string($data['start_time']) || !is_string($data['end_time'])) {
            throw new \InvalidArgumentException('日期参数必须为字符串格式');
        }

        if (!is_numeric($data['course_id'])) {
            throw new \InvalidArgumentException('课程ID必须为数字');
        }

        if (!is_string($data['type']) && !is_int($data['type'])) {
            throw new \InvalidArgumentException('排课类型参数格式错误');
        }
    }

    private function findClassroomOrFail(mixed $classroomId): Classroom
    {
        $classroom = $this->classroomRepository->find($classroomId);

        if (null === $classroom) {
            throw new ClassroomNotFoundException('教室不存在');
        }

        return $classroom;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function extractOptionalParameters(array $data): array
    {
        $optionalKeys = ['instructor_id', 'max_students', 'location', 'description'];
        $options = [];

        foreach ($optionalKeys as $key) {
            if (isset($data[$key])) {
                $options[$key] = $data[$key];
            }
        }

        return $options;
    }
}
