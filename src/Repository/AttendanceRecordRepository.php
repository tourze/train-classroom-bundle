<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 考勤记录仓储类
 * 提供考勤记录的查询和统计功能
 *
 * @extends ServiceEntityRepository<AttendanceRecord>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: AttendanceRecord::class)]
final class AttendanceRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AttendanceRecord::class);
    }

    /**
     * 根据报班记录查找考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findByRegistration(Registration $registration): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->setParameter('registration', $registration)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定日期范围内的考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.attendanceTime BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定类型的考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findByType(AttendanceType $type): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.attendanceType = :type')
            ->setParameter('type', $type)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找成功的考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findSuccessfulRecords(): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.verificationResult = :result')
            ->andWhere('a.isValid = :valid')
            ->setParameter('result', VerificationResult::SUCCESS)
            ->setParameter('valid', true)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
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
            ->getSingleScalarResult()
        ;
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
            ->getSingleScalarResult()
        ;
    }

    /**
     * 查找最近的考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findLatestByRegistration(Registration $registration, int $limit = 10): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->setParameter('registration', $registration)
            ->orderBy('a.attendanceTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定设备的考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findByDevice(string $deviceId): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.deviceId = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 统计每日考勤数据
     *
     * @return array<array{attendanceType: string, count: int}>
     */
    public function getDailyAttendanceStats(\DateTimeInterface $date): array
    {
        $startOfDay = \DateTime::createFromInterface($date)->setTime(0, 0, 0);
        $endOfDay = \DateTime::createFromInterface($date)->setTime(23, 59, 59);

        /** @var list<array{attendanceType: string, count: int}> */
        return $this->createQueryBuilder('a')
            ->select('a.attendanceType, COUNT(a.id) as count')
            ->andWhere('a.attendanceTime BETWEEN :start AND :end')
            ->andWhere('a.isValid = :valid')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('valid', true)
            ->groupBy('a.attendanceType')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找异常考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findAnomalousRecords(): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.verificationResult != :success OR a.isValid = :invalid')
            ->setParameter('success', VerificationResult::SUCCESS)
            ->setParameter('invalid', false)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据供应商查找考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findBySupplier(string $supplierId): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.deviceId = :supplierId')
            ->setParameter('supplierId', $supplierId)
            ->orderBy('a.attendanceTime', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找过期的报名记录
     *
     * @return array<AttendanceRecord>
     */
    public function findExpiredRegistrations(\DateTimeInterface $expiryDate): array
    {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->join('a.registration', 'r')
            ->where('r.createTime < :expiryDate')
            ->setParameter('expiryDate', $expiryDate)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据报班记录和日期范围查找考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findByRegistrationAndDateRange(
        Registration $registration,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
    ): array {
        /** @var list<AttendanceRecord> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.registration = :registration')
            ->andWhere('a.attendanceTime BETWEEN :startDate AND :endDate')
            ->setParameter('registration', $registration)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.attendanceTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据报班记录、考勤类型和日期范围查找考勤记录
     *
     * @return array<AttendanceRecord>
     */
    public function findByRegistrationTypeAndDateRange(
        Registration $registration,
        AttendanceType $type,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
    ): array {
        /** @var list<AttendanceRecord> */
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
            ->getResult()
        ;
    }

    /**
     * 获取课程考勤汇总
     *
     * @return array<array{registration_id: int, total_records: int, sign_in_count: int, sign_out_count: int, unique_days: int, attendance_rate: float}>
     */
    public function getCourseAttendanceSummary(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        $qb = $this->createQueryBuilder('a')
            ->select([
                'r.id as registration_id',
                'COUNT(a.id) as total_records',
                'SUM(CASE WHEN a.attendanceType = :signIn THEN 1 ELSE 0 END) as sign_in_count',
                'SUM(CASE WHEN a.attendanceType = :signOut THEN 1 ELSE 0 END) as sign_out_count',
                'COUNT(DISTINCT SUBSTRING(a.attendanceTime, 1, 10)) as unique_days',
            ])
            ->join('a.registration', 'r')
            ->join('r.course', 'c')
            ->where('c.id = :courseId')
            ->groupBy('r.id')
            ->setParameter('courseId', $courseId)
            ->setParameter('signIn', AttendanceType::SIGN_IN)
            ->setParameter('signOut', AttendanceType::SIGN_OUT)
        ;

        if (null !== $startDate) {
            $qb->andWhere('a.attendanceTime >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (null !== $endDate) {
            $qb->andWhere('a.attendanceTime <= :endDate')
                ->setParameter('endDate', $endDate)
            ;
        }

        /** @var array<array{registration_id: int, total_records: int, sign_in_count: int, sign_out_count: int, unique_days: int}> $results */
        $results = $qb->getQuery()->getResult();

        // 计算考勤率
        /** @var array<array{registration_id: int, total_records: int, sign_in_count: int, sign_out_count: int, unique_days: int, attendance_rate: float}> $finalResults */
        $finalResults = [];
        foreach ($results as $result) {
            // 假设每个课程30天
            $totalDays = 30;
            $finalResults[] = [
                'registration_id' => $result['registration_id'],
                'total_records' => $result['total_records'],
                'sign_in_count' => $result['sign_in_count'],
                'sign_out_count' => $result['sign_out_count'],
                'unique_days' => $result['unique_days'],
                'attendance_rate' => round(($result['unique_days'] / $totalDays) * 100, 2),
            ];
        }

        return $finalResults;
    }

    /**
     * 获取考勤率统计
     */
    /**
     * @return array<array{registration_id: int, total_records: int, sign_in_count: int, sign_out_count: int, unique_days: int, attendance_rate: float}>
     */
    public function getAttendanceRateStatistics(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        return $this->getCourseAttendanceSummary($courseId, $startDate, $endDate);
    }

    public function save(AttendanceRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AttendanceRecord $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
