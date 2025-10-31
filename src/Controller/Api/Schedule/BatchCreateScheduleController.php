<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Exception\ClassroomNotFoundException;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

final class BatchCreateScheduleController extends AbstractController
{
    public function __construct(
        private readonly ScheduleServiceInterface $scheduleService,
        private readonly ClassroomRepository $classroomRepository,
    ) {
    }

    #[Route(path: '/api/schedule/batch-create', name: 'api_schedule_batch_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            $data = $this->parseAndValidateRequest($request);
            $classroom = $this->findClassroomOrFail($data['classroom_id']);
            $scheduleData = $this->generateScheduleData($data, $classroom);
            $skipConflicts = $this->getSkipConflictsOption($data);

            $result = $this->scheduleService->batchCreateSchedules($scheduleData, $skipConflicts);

            return $this->json([
                'success' => true,
                'message' => '批量创建排课完成',
                'data' => $result,
            ]);
        } catch (ClassroomNotFoundException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'success' => false,
                'message' => '访问被拒绝，请先登录',
            ], Response::HTTP_FORBIDDEN);
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
        $this->validateDateParameters($validatedData);

        return $validatedData;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateRequiredParameters(array $data): void
    {
        if (!isset($data['classroom_id'], $data['course_id'], $data['start_date'], $data['end_date'])) {
            throw new \InvalidArgumentException('缺少必需参数：classroom_id, course_id, start_date, end_date');
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateDateParameters(array $data): void
    {
        if (!is_string($data['start_date']) || !is_string($data['end_date'])) {
            throw new \InvalidArgumentException('日期参数必须为字符串格式');
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
     * @return array<int, array<string, mixed>>
     */
    private function generateScheduleData(array $data, Classroom $classroom): array
    {
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];

        if (!is_string($startDate) || !is_string($endDate)) {
            throw new \InvalidArgumentException('日期参数必须为字符串格式');
        }

        $startDateTime = new \DateTimeImmutable($startDate);
        $endDateTime = new \DateTimeImmutable($endDate);
        $params = $this->extractScheduleParameters($data);

        $scheduleData = [];
        $currentDate = $startDateTime;

        while ($currentDate <= $endDateTime) {
            $scheduleData[] = [
                'classroom' => $classroom,
                'course_id' => $params['course_id'],
                'type' => $params['type'],
                'start_time' => $currentDate->setTime(9, 0),
                'end_time' => $currentDate->setTime(17, 0),
                'instructor_id' => $params['instructor_id'],
                'max_students' => $params['max_students'],
            ];
            $currentDate = $currentDate->modify('+1 day');
        }

        return $scheduleData;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function extractScheduleParameters(array $data): array
    {
        return [
            'course_id' => is_numeric($data['course_id']) ? (int) $data['course_id'] : 0,
            'type' => is_string($data['type'] ?? null) ? $data['type'] : 'REGULAR',
            'instructor_id' => isset($data['instructor_id']) && is_numeric($data['instructor_id']) ? (int) $data['instructor_id'] : null,
            'max_students' => isset($data['max_students']) && is_numeric($data['max_students']) ? (int) $data['max_students'] : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getSkipConflictsOption(array $data): bool
    {
        return isset($data['skip_conflicts']) && is_bool($data['skip_conflicts']) ? $data['skip_conflicts'] : true;
    }
}
