<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Schedule;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

final class GetScheduleListController extends AbstractController
{
    public function __construct(
        private readonly ClassroomScheduleRepository $scheduleRepository
    ) {
    }

    #[Route(path: '/api/schedule/list', name: 'api_schedule_list', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $filters = [
                'classroom_id' => $request->query->get('classroom_id'),
                'course_id' => $request->query->get('course_id'),
                'instructor_id' => $request->query->get('instructor_id'),
                'status' => $request->query->get('status'),
                'type' => $request->query->get('type'),
                'start_date' => $request->query->get('start_date'),
                'end_date' => $request->query->get('end_date'),
            ];

            // 过滤空值
            $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');

            // 转换日期
            if (isset($filters['start_date'])) {
                $filters['start_date'] = new \DateTimeImmutable($filters['start_date']);
            }
            if (isset($filters['end_date'])) {
                $filters['end_date'] = new \DateTimeImmutable($filters['end_date']);
            }

            $page = (int) ($request->query->get('page', 1));
            $limit = (int) ($request->query->get('limit', 20));
            $offset = ($page - 1) * $limit;

            // 使用 repository 查询
            $qb = $this->scheduleRepository->createQueryBuilder('s')
                ->leftJoin('s.classroom', 'c')
                ->addSelect('c');

            // 应用过滤条件
            if (isset($filters['classroom_id'])) {
                $qb->andWhere('c.id = :classroom_id')
                   ->setParameter('classroom_id', $filters['classroom_id']);
            }
            
            if (isset($filters['status'])) {
                $qb->andWhere('s.scheduleStatus = :status')
                   ->setParameter('status', $filters['status']);
            }
            
            if (isset($filters['type'])) {
                $qb->andWhere('s.scheduleType = :type')
                   ->setParameter('type', $filters['type']);
            }
            
            if (isset($filters['start_date'])) {
                $qb->andWhere('s.startTime >= :start_date')
                   ->setParameter('start_date', $filters['start_date']);
            }
            
            if (isset($filters['end_date'])) {
                $qb->andWhere('s.endTime <= :end_date')
                   ->setParameter('end_date', $filters['end_date']);
            }

            // 获取总数
            $total = (int) (clone $qb)->select('COUNT(s.id)')
                ->getQuery()
                ->getSingleScalarResult();

            // 获取数据
            $schedules = $qb->orderBy('s.startTime', 'ASC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->getQuery()
                ->getResult();

            // 格式化数据
            $data = array_map(function ($schedule) {
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

            return $this->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => (int) ceil($total / $limit),
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'message' => '获取排课列表失败：' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}