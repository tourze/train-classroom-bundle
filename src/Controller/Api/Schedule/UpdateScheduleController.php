<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

final class UpdateScheduleController extends AbstractController
{
    public function __construct(
        private readonly ClassroomScheduleRepository $scheduleRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/api/schedule/update/{id}', name: 'api_schedule_update', methods: ['PUT', 'PATCH'])]
    public function __invoke(int $id, Request $request): JsonResponse
    {
        try {
            $schedule = $this->scheduleRepository->find($id);

            if (null === $schedule) {
                return $this->json([
                    'success' => false,
                    'message' => '排课记录不存在',
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'message' => '无效的请求数据',
                ], Response::HTTP_BAD_REQUEST);
            }

            /** @var array<string, mixed> $validatedData */
            $validatedData = $data;
            $this->updateScheduleFields($schedule, $validatedData);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => '更新排课成功',
                'data' => $this->formatScheduleResponse($schedule),
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

    /**
     * @param array<string, mixed> $data
     */
    private function updateScheduleFields(ClassroomSchedule $schedule, array $data): void
    {
        $this->updateTimeFields($schedule, $data);
        $this->updateTeacherField($schedule, $data);
        $this->updateStudentFields($schedule, $data);
        $this->updateContentFields($schedule, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateTimeFields(ClassroomSchedule $schedule, array $data): void
    {
        if (isset($data['start_time']) && is_string($data['start_time'])) {
            $schedule->setStartTime(new \DateTimeImmutable($data['start_time']));
        }

        if (isset($data['end_time']) && is_string($data['end_time'])) {
            $schedule->setEndTime(new \DateTimeImmutable($data['end_time']));
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateTeacherField(ClassroomSchedule $schedule, array $data): void
    {
        if (isset($data['teacher_id']) && is_string($data['teacher_id'])) {
            $schedule->setTeacherId($data['teacher_id']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateStudentFields(ClassroomSchedule $schedule, array $data): void
    {
        if (isset($data['expected_students']) && is_numeric($data['expected_students'])) {
            $schedule->setExpectedStudents((int) $data['expected_students']);
        }

        if (isset($data['actual_students']) && is_numeric($data['actual_students'])) {
            $schedule->setActualStudents((int) $data['actual_students']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateContentFields(ClassroomSchedule $schedule, array $data): void
    {
        if (isset($data['course_content'])) {
            $courseContent = $data['course_content'];
            $schedule->setCourseContent(is_string($courseContent) ? $courseContent : null);
        }

        if (isset($data['remark'])) {
            $remark = $data['remark'];
            $schedule->setRemark(is_string($remark) ? $remark : null);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formatScheduleResponse(ClassroomSchedule $schedule): array
    {
        return [
            'id' => $schedule->getId(),
            'status' => $schedule->getScheduleStatus()->value,
            'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
            'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
        ];
    }
}
