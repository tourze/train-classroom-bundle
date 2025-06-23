<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 考勤记录仓储类
 * 提供考勤记录的查询和统计功能
 */
class AttendanceRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceRecord::class);
    }

    /**
     * 根据报班记录查找考勤记录
     */
    public function findByRegistration(Registration $registration): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->setParameter('registration', $registration)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定日期范围内的考勤记录
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.attendanceTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定类型的考勤记录
     */
    public function findByType(AttendanceType $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.attendanceType = :type')
            ->setParameter('type', $type)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找成功的考勤记录
     */
    public function findSuccessfulRecords(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.verificationResult = :result')
            ->andWhere('a.isValid = :valid')
            ->setParameter('result', VerificationResult::SUCCESS)
            ->setParameter('valid', true)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计指定报班记录的考勤次数
     */
    public function countByRegistration(Registration $registration): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.registration = :registration')
            ->setParameter('registration', $registration)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 统计指定报班记录的成功考勤次数
     */
    public function countSuccessfulByRegistration(Registration $registration): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.registration = :registration')
            ->andWhere('a.verificationResult = :result')
            ->andWhere('a.isValid = :valid')
            ->setParameter('registration', $registration)
            ->setParameter('result', VerificationResult::SUCCESS)
            ->setParameter('valid', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 查找最近的考勤记录
     */
    public function findLatestByRegistration(Registration $registration, int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->setParameter('registration', $registration)
            ->orderBy('a.attendanceTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定设备的考勤记录
     */
    public function findByDevice(string $deviceId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.deviceId = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计每日考勤数据
     */
    public function getDailyAttendanceStats(\DateTimeInterface $date): array
    {
        $startOfDay = \DateTime::createFromInterface($date)->setTime(0, 0, 0);
        $endOfDay = \DateTime::createFromInterface($date)->setTime(23, 59, 59);

        return $this->createQueryBuilder('a')
            ->select('a.attendanceType, COUNT(a.id) as count')
            ->andWhere('a.attendanceTime BETWEEN :start AND :end')
            ->andWhere('a.isValid = :valid')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('valid', true)
            ->groupBy('a.attendanceType')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找异常考勤记录
     */
    public function findAnomalousRecords(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.verificationResult != :success OR a.isValid = :invalid')
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('invalid', false)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据供应商查找考勤记录
     */
    public function findBySupplier(string $supplierId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.supplierId = :supplierId')
            ->setParameter('supplierId', $supplierId)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找过期的报名记录
     *
     * @param \DateTimeInterface $expiryDate
     * @return array
     */
    public function findExpiredRegistrations(\DateTimeInterface $expiryDate): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.status = :status')
            ->andWhere('r.createdAt < :expiryDate')
            ->setParameter('status', 'pending')
            ->setParameter('expiryDate', $expiryDate)
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 根据报班记录和日期范围查找考勤记录
     */
    public function findByRegistrationAndDateRange(
        Registration $registration,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->andWhere('a.attendanceTime BETWEEN :startDate AND :endDate')
            ->setParameter('registration', $registration)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.attendanceTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 根据报班记录、考勤类型和日期范围查找考勤记录
     */
    public function findByRegistrationTypeAndDateRange(
        Registration $registration,
        AttendanceType $type,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->andWhere('a.attendanceType = :type')
            ->andWhere('a.attendanceTime BETWEEN :startDate AND :endDate')
            ->setParameter('registration', $registration)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.attendanceTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * 获取课程考勤汇总
     */
    public function getCourseAttendanceSummary(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->select([
                'r.id as registration_id',
                'COUNT(a.id) as total_records',
                'COUNT(CASE WHEN a.attendanceType = :signIn THEN 1 END) as sign_in_count',
                'COUNT(CASE WHEN a.attendanceType = :signOut THEN 1 END) as sign_out_count',
                'COUNT(DISTINCT DATE(a.attendanceTime)) as unique_days'
            ])
            ->join('a.registration', 'r')
            ->join('r.course', 'c')
            ->where('c.id = :courseId')
            ->groupBy('r.id')
            ->setParameter('courseId', $courseId)
            ->setParameter('signIn', AttendanceType::SIGN_IN)
            ->setParameter('signOut', AttendanceType::SIGN_OUT);
            
        if ($startDate !== null) {
            $qb->andWhere('a.attendanceTime >= :startDate')
               ->setParameter('startDate', $startDate);
        }
        
        if ($endDate !== null) {
            $qb->andWhere('a.attendanceTime <= :endDate')
               ->setParameter('endDate', $endDate);
        }
        
        $results = $qb->getQuery()->getResult();
        
        // 计算考勤率
        foreach ($results as &$result) {
            // 假设每个课程30天
            $totalDays = 30;
            $result['attendance_rate'] = round(($result['unique_days'] / $totalDays) * 100, 2);
        }
        
        return $results;
    }
    
    /**
     * 获取考勤率统计
     */
    public function getAttendanceRateStatistics(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
        return $this->getCourseAttendanceSummary($courseId, $startDate, $endDate);
    }
} 