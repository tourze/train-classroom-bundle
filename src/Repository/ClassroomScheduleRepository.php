<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * 教室排课仓储类
 * 提供排课查询、冲突检测和统计功能
 *
 * @extends ServiceEntityRepository<ClassroomSchedule>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: ClassroomSchedule::class)]
final class ClassroomScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassroomSchedule::class);
    }

    /**
     * 根据教室查找排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findByClassroom(Classroom $classroom): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->setParameter('classroom', $classroom)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findByDate(\DateTimeInterface $date): array
    {
        // 确保只比较日期部分，不比较时间
        $dateOnly = $date instanceof \DateTimeImmutable ? $date : new \DateTimeImmutable($date->format('Y-m-d'));

        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleDate = :date')
            ->setParameter('date', $dateOnly, Types::DATE_IMMUTABLE)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期范围内的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据状态查找排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findByStatus(ScheduleStatus $status): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleStatus = :status')
            ->setParameter('status', $status)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据类型查找排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findByType(ScheduleType $type): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleType = :type')
            ->setParameter('type', $type)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定教师的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findByTeacher(string $teacherId): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.teacherId = :teacherId')
            ->setParameter('teacherId', $teacherId)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 检查教室时间冲突
     *
     * @return array<ClassroomSchedule>
     */
    public function findConflictingSchedules(
        Classroom $classroom,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        ?string $excludeId = null,
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->andWhere('NOT (s.endTime <= :startTime OR s.startTime >= :endTime)')
            ->setParameter('classroom', $classroom)
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
        ;

        if (null !== $excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId)
            ;
        }

        /** @var list<ClassroomSchedule> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 检查教师时间冲突
     *
     * @return array<ClassroomSchedule>
     */
    public function findTeacherConflictingSchedules(
        string $teacherId,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        ?string $excludeId = null,
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.teacherId = :teacherId')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->andWhere('NOT (s.endTime <= :startTime OR s.startTime >= :endTime)')
            ->setParameter('teacherId', $teacherId)
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
        ;

        if (null !== $excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId)
            ;
        }

        /** @var list<ClassroomSchedule> */
        return $qb->getQuery()->getResult();
    }

    /**
     * 查找活跃状态的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findActiveSchedules(): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找正在进行的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findOngoingSchedules(): array
    {
        $now = new \DateTimeImmutable();

        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.startTime <= :now')
            ->andWhere('s.endTime >= :now')
            ->andWhere('s.scheduleStatus = :status')
            ->setParameter('now', $now)
            ->setParameter('status', ScheduleStatus::ONGOING)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找即将开始的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findUpcomingSchedules(int $minutesBefore = 30): array
    {
        $now = new \DateTimeImmutable();
        $upcoming = $now->modify("+{$minutesBefore} minutes");

        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.startTime BETWEEN :now AND :upcoming')
            ->andWhere('s.scheduleStatus = :status')
            ->setParameter('now', $now)
            ->setParameter('upcoming', $upcoming)
            ->setParameter('status', ScheduleStatus::SCHEDULED)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 统计指定类型的排课数量
     */
    public function countByType(ScheduleType $type): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.scheduleType = :type')
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 统计指定教室的排课数量
     */
    public function countByClassroom(Classroom $classroom): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.classroom = :classroom')
            ->setParameter('classroom', $classroom)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 获取教室使用率统计
     *
     * @return array<array{classroom_id: int, classroom_name: string, schedule_count: int}>
     */
    public function getClassroomUsageStats(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var list<array{classroom_id: int, classroom_name: string, schedule_count: int}> */
        return $this->createQueryBuilder('s')
            ->select('c.id as classroom_id, c.title as classroom_name, COUNT(s.id) as schedule_count')
            ->join('s.classroom', 'c')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->andWhere('s.scheduleStatus != :cancelled')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('cancelled', ScheduleStatus::CANCELLED)
            ->groupBy('c.id')
            ->orderBy('schedule_count', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据供应商查找排课记录
     * 注意：当前实体不包含 supplierId 字段，此方法返回空数组
     *
     * @return array<ClassroomSchedule>
     */
    public function findBySupplier(string $supplierId): array
    {
        // ClassroomSchedule 实体暂不支持 supplierId
        // 如需此功能，请先在实体中添加相应字段
        return [];
    }

    /**
     * 查找需要更新状态的排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findSchedulesToUpdateStatus(): array
    {
        $now = new \DateTimeImmutable();

        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('(s.scheduleStatus = :scheduled AND s.startTime <= :now AND s.endTime > :now)')
            ->orWhere('(s.scheduleStatus = :ongoing AND s.endTime <= :now)')
            ->setParameter('scheduled', ScheduleStatus::SCHEDULED)
            ->setParameter('ongoing', ScheduleStatus::ONGOING)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找活跃的排课记录（按教室）
     *
     * @return array<ClassroomSchedule>
     */
    public function findActiveSchedulesByClassroom(Classroom $classroom): array
    {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->setParameter('classroom', $classroom)
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取教室使用率
     *
     * @return array{total_hours: float, available_hours: float, utilization_rate: float}
     */
    public function getClassroomUtilizationRate(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
    ): array {
        // 获取时间范围内的所有排课
        /** @var list<ClassroomSchedule> $schedules */
        $schedules = $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->andWhere('s.scheduleStatus != :cancelled')
            ->setParameter('classroom', $classroom)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('cancelled', ScheduleStatus::CANCELLED)
            ->getQuery()
            ->getResult()
        ;

        // 在 PHP 中计算总小时数
        $totalHours = 0.0;
        foreach ($schedules as $schedule) {
            $diff = $schedule->getEndTime()->diff($schedule->getStartTime());
            $totalHours += $diff->h + ($diff->i / 60) + ($diff->s / 3600);
        }

        $daysProperty = $startDate->diff($endDate)->days;
        $totalDays = (false === $daysProperty ? 0 : $daysProperty) + 1;
        $workHoursPerDay = 8; // 假设每天工作8小时
        $totalAvailableHours = $totalDays * $workHoursPerDay;

        return [
            'total_hours' => $totalHours,
            'available_hours' => $totalAvailableHours,
            'utilization_rate' => $totalAvailableHours > 0 ? round(($totalHours / $totalAvailableHours) * 100, 2) : 0,
        ];
    }

    /**
     * 查找指定日期范围内的排课
     *
     * @param array<string> $classroomIds
     * @return array<ClassroomSchedule>
     */
    public function findSchedulesInDateRange(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $classroomIds = [],
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
        ;

        if ([] !== $classroomIds) {
            $qb->andWhere('s.classroom IN (:classroomIds)')
                ->setParameter('classroomIds', $classroomIds)
            ;
        }

        /** @var list<ClassroomSchedule> */
        return $qb->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据教室和日期范围查找排课记录
     *
     * @return array<ClassroomSchedule>
     */
    public function findSchedulesByClassroomAndDateRange(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
    ): array {
        /** @var list<ClassroomSchedule> */
        return $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('classroom', $classroom)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 获取排课统计报告
     *
     * @param array<string, mixed> $filters
     * @return array{total_schedules: int, total_classrooms: int, total_teachers: int, completed_schedules: int, cancelled_schedules: int, total_expected_students: int, total_actual_students: int}
     */
    public function getScheduleStatisticsReport(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $filters = [],
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->select([
                'COUNT(DISTINCT s.id) as total_schedules',
                'COUNT(DISTINCT s.classroom) as total_classrooms',
                'COUNT(DISTINCT s.teacherId) as total_teachers',
                'SUM(CASE WHEN s.scheduleStatus = :completed THEN 1 ELSE 0 END) as completed_schedules',
                'SUM(CASE WHEN s.scheduleStatus = :cancelled THEN 1 ELSE 0 END) as cancelled_schedules',
                'SUM(s.expectedStudents) as total_expected_students',
                'SUM(s.actualStudents) as total_actual_students',
            ])
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('completed', ScheduleStatus::COMPLETED)
            ->setParameter('cancelled', ScheduleStatus::CANCELLED)
        ;

        if (isset($filters['classroom_id']) && '' !== $filters['classroom_id']) {
            $qb->andWhere('s.classroom = :classroom_id')
                ->setParameter('classroom_id', $filters['classroom_id'])
            ;
        }

        if (isset($filters['teacher_id']) && '' !== $filters['teacher_id']) {
            $qb->andWhere('s.teacherId = :teacher_id')
                ->setParameter('teacher_id', $filters['teacher_id'])
            ;
        }

        $result = $qb->getQuery()->getSingleResult();

        assert(is_array($result));

        /** @var array{total_schedules: int, total_classrooms: int, total_teachers: int, completed_schedules: int, cancelled_schedules: int, total_expected_students: int, total_actual_students: int} */
        return $result;
    }

    public function save(ClassroomSchedule $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ClassroomSchedule $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
