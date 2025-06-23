<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * 教室排课仓储类
 * 提供排课查询、冲突检测和统计功能
 */
class ClassroomScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClassroomSchedule::class);
    }

    /**
     * 根据教室查找排课记录
     */
    public function findByClassroom(Classroom $classroom): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->setParameter('classroom', $classroom)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定日期的排课记录
     */
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleDate = :date')
            ->setParameter('date', $date)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定日期范围内的排课记录
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据状态查找排课记录
     */
    public function findByStatus(ScheduleStatus $status): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleStatus = :status')
            ->setParameter('status', $status)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据类型查找排课记录
     */
    public function findByType(ScheduleType $type): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleType = :type')
            ->setParameter('type', $type)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定教师的排课记录
     */
    public function findByTeacher(string $teacherId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.teacherId = :teacherId')
            ->setParameter('teacherId', $teacherId)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 检查教室时间冲突
     */
    public function findConflictingSchedules(
        Classroom $classroom,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        ?string $excludeId = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->andWhere('NOT (s.endTime <= :startTime OR s.startTime >= :endTime)')
            ->setParameter('classroom', $classroom)
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        if ($excludeId !== null) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 检查教师时间冲突
     */
    public function findTeacherConflictingSchedules(
        string $teacherId,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        ?string $excludeId = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.teacherId = :teacherId')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->andWhere('NOT (s.endTime <= :startTime OR s.startTime >= :endTime)')
            ->setParameter('teacherId', $teacherId)
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        if ($excludeId !== null) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 查找活跃状态的排课记录
     */
    public function findActiveSchedules(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找正在进行的排课记录
     */
    public function findOngoingSchedules(): array
    {
        $now = new \DateTimeImmutable();
        
        return $this->createQueryBuilder('s')
            ->andWhere('s.startTime <= :now')
            ->andWhere('s.endTime >= :now')
            ->andWhere('s.scheduleStatus = :status')
            ->setParameter('now', $now)
            ->setParameter('status', ScheduleStatus::ONGOING)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找即将开始的排课记录
     */
    public function findUpcomingSchedules(int $minutesBefore = 30): array
    {
        $now = new \DateTimeImmutable();
        $upcoming = $now->modify("+{$minutesBefore} minutes");
        
        return $this->createQueryBuilder('s')
            ->andWhere('s.startTime BETWEEN :now AND :upcoming')
            ->andWhere('s.scheduleStatus = :status')
            ->setParameter('now', $now)
            ->setParameter('upcoming', $upcoming)
            ->setParameter('status', ScheduleStatus::SCHEDULED)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
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
            ->getSingleScalarResult();
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
            ->getSingleScalarResult();
    }

    /**
     * 获取教室使用率统计
     */
    public function getClassroomUsageStats(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
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
            ->getResult();
    }

    /**
     * 根据供应商查找排课记录
     */
    public function findBySupplier(string $supplierId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.supplierId = :supplierId')
            ->setParameter('supplierId', $supplierId)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找需要更新状态的排课记录
     */
    public function findSchedulesToUpdateStatus(): array
    {
        $now = new \DateTimeImmutable();
        
        return $this->createQueryBuilder('s')
            ->andWhere('(s.scheduleStatus = :scheduled AND s.startTime <= :now AND s.endTime > :now)')
            ->orWhere('(s.scheduleStatus = :ongoing AND s.endTime <= :now)')
            ->setParameter('scheduled', ScheduleStatus::SCHEDULED)
            ->setParameter('ongoing', ScheduleStatus::ONGOING)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 查找活跃的排课记录（按教室）
     */
    public function findActiveSchedulesByClassroom(Classroom $classroom): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleStatus IN (:activeStatuses)')
            ->setParameter('classroom', $classroom)
            ->setParameter('activeStatuses', [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING])
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 获取教室使用率
     */
    public function getClassroomUtilizationRate(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $totalHours = $this->createQueryBuilder('s')
            ->select('SUM(TIMESTAMPDIFF(HOUR, s.startTime, s.endTime)) as total_hours')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->andWhere('s.scheduleStatus != :cancelled')
            ->setParameter('classroom', $classroom)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('cancelled', ScheduleStatus::CANCELLED)
            ->getQuery()
            ->getSingleScalarResult();
            
        $totalDays = $startDate->diff($endDate)->days + 1;
        $workHoursPerDay = 8; // 假设每天工作8小时
        $totalAvailableHours = $totalDays * $workHoursPerDay;
        
        return [
            'total_hours' => (float)$totalHours,
            'available_hours' => $totalAvailableHours,
            'utilization_rate' => $totalAvailableHours > 0 ? round(((float)$totalHours / $totalAvailableHours) * 100, 2) : 0,
        ];
    }
    
    /**
     * 查找指定日期范围内的排课
     */
    public function findSchedulesInDateRange(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $classroomIds = []
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);
            
        if (!empty($classroomIds)) {
            $qb->andWhere('s.classroom IN (:classroomIds)')
                ->setParameter('classroomIds', $classroomIds);
        }
        
        return $qb->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 根据教室和日期范围查找排课记录
     */
    public function findSchedulesByClassroomAndDateRange(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->createQueryBuilder('s')
            ->andWhere('s.classroom = :classroom')
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('classroom', $classroom)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 获取排课统计报告
     */
    public function getScheduleStatisticsReport(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $filters = []
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->select([
                'COUNT(DISTINCT s.id) as total_schedules',
                'COUNT(DISTINCT s.classroom) as total_classrooms',
                'COUNT(DISTINCT s.teacherId) as total_teachers',
                'COUNT(CASE WHEN s.scheduleStatus = :completed THEN 1 END) as completed_schedules',
                'COUNT(CASE WHEN s.scheduleStatus = :cancelled THEN 1 END) as cancelled_schedules',
                'SUM(s.expectedStudents) as total_expected_students',
                'SUM(s.actualStudents) as total_actual_students'
            ])
            ->andWhere('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('completed', ScheduleStatus::COMPLETED)
            ->setParameter('cancelled', ScheduleStatus::CANCELLED);
            
        if (!empty($filters['classroom_id'])) {
            $qb->andWhere('s.classroom = :classroom_id')
                ->setParameter('classroom_id', $filters['classroom_id']);
        }
        
        if (!empty($filters['teacher_id'])) {
            $qb->andWhere('s.teacherId = :teacher_id')
                ->setParameter('teacher_id', $filters['teacher_id']);
        }
        
        return $qb->getQuery()->getSingleResult();
    }
} 