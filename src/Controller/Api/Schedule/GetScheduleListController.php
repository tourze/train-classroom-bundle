<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

final class GetScheduleListController extends AbstractController
{
    public function __construct(
        private readonly ClassroomScheduleRepository $scheduleRepository,
    ) {
    }

    #[Route(path: '/api/schedule/list', name: 'api_schedule_list', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $filters = $this->buildFilters($request);
            $pagination = $this->buildPagination($request);

            $qb = $this->buildQuery($filters);
            $total = $this->getTotal($qb);
            $schedules = $this->getSchedules($qb, $pagination);
            $data = $this->formatSchedules($schedules);

            return $this->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $total,
                    'page' => $pagination['page'],
                    'limit' => $pagination['limit'],
                    'pages' => (int) ceil($total / $pagination['limit']),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取排课列表失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /** @return array<string, mixed> */
    private function buildFilters(Request $request): array
    {
        $filters = [
            'classroom_id' => $request->query->get('classroom_id'),
            'course_id' => $request->query->get('course_id'),
            'instructor_id' => $request->query->get('instructor_id'),
            'status' => $request->query->get('status'),
            'type' => $request->query->get('type'),
            'start_date' => $request->query->get('start_date'),
            'end_date' => $request->query->get('end_date'),
        ];

        $filters = array_filter($filters, fn ($value) => null !== $value && '' !== $value);

        if (isset($filters['start_date'])) {
            $filters['start_date'] = new \DateTimeImmutable((string) $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $filters['end_date'] = new \DateTimeImmutable((string) $filters['end_date']);
        }

        return $filters;
    }

    /** @return array<string, int> */
    private function buildPagination(Request $request): array
    {
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
        ];
    }

    /** @param array<string, mixed> $filters */
    private function buildQuery(array $filters): QueryBuilder
    {
        $qb = $this->scheduleRepository->createQueryBuilder('s')
            ->leftJoin('s.classroom', 'c')
            ->addSelect('c')
        ;

        if (isset($filters['classroom_id'])) {
            $qb->andWhere('c.id = :classroom_id')
                ->setParameter('classroom_id', $filters['classroom_id'])
            ;
        }

        if (isset($filters['status'])) {
            $qb->andWhere('s.scheduleStatus = :status')
                ->setParameter('status', $filters['status'])
            ;
        }

        if (isset($filters['type'])) {
            $qb->andWhere('s.scheduleType = :type')
                ->setParameter('type', $filters['type'])
            ;
        }

        if (isset($filters['start_date'])) {
            $qb->andWhere('s.startTime >= :start_date')
                ->setParameter('start_date', $filters['start_date'])
            ;
        }

        if (isset($filters['end_date'])) {
            $qb->andWhere('s.endTime <= :end_date')
                ->setParameter('end_date', $filters['end_date'])
            ;
        }

        return $qb;
    }

    private function getTotal(QueryBuilder $qb): int
    {
        return (int) (clone $qb)->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param array<string, int> $pagination
     * @return array<int, ClassroomSchedule>
     */
    private function getSchedules(QueryBuilder $qb, array $pagination): array
    {
        $result = $qb->orderBy('s.startTime', 'ASC')
            ->setMaxResults($pagination['limit'])
            ->setFirstResult($pagination['offset'])
            ->getQuery()
            ->getResult()
        ;

        assert(is_array($result));

        /** @var array<int, ClassroomSchedule> */
        return $result;
    }

    /**
     * @param array<int, ClassroomSchedule> $schedules
     * @return array<int, array<string, mixed>>
     */
    private function formatSchedules(array $schedules): array
    {
        return array_map(function (ClassroomSchedule $schedule) {
            return [
                'id' => $schedule->getId(),
                'classroom' => [
                    'id' => $schedule->getClassroom()->getId(),
                    'name' => $schedule->getClassroom()->getName(),
                ],
                'type' => $schedule->getScheduleType()->value,
                'status' => $schedule->getScheduleStatus()->value,
                'start_time' => $schedule->getStartTime()->format('Y-m-d H:i:s'),
                'end_time' => $schedule->getEndTime()->format('Y-m-d H:i:s'),
            ];
        }, $schedules);
    }
}
