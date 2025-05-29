<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * 教室排课实体
 * 管理教室的课程安排和教师分配
 */
#[ORM\Entity]
#[ORM\Table(name: 'job_training_classroom_schedule')]
#[ORM\Index(columns: ['classroom_id'], name: 'idx_classroom_id')]
#[ORM\Index(columns: ['schedule_date'], name: 'idx_schedule_date')]
#[ORM\Index(columns: ['teacher_id'], name: 'idx_teacher_id')]
#[ORM\Index(columns: ['supplier_id'], name: 'idx_supplier_id')]
#[ORM\UniqueConstraint(name: 'uk_classroom_time', columns: ['classroom_id', 'start_time', 'end_time'])]
class ClassroomSchedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    /**
     * 关联的教室
     */
    #[ORM\ManyToOne(targetEntity: Classroom::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: 'classroom_id', referencedColumnName: 'id', nullable: false)]
    private Classroom $classroom;

    /**
     * 教师ID
     */
    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $teacherId;

    /**
     * 排课日期
     */
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $scheduleDate;

    /**
     * 开始时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $startTime;

    /**
     * 结束时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $endTime;

    /**
     * 排课类型
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: ScheduleType::class)]
    private ScheduleType $scheduleType;

    /**
     * 排课状态
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: ScheduleStatus::class)]
    private ScheduleStatus $scheduleStatus;

    /**
     * 排课配置（重复规则等）
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $scheduleConfig = null;

    /**
     * 课程内容
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $courseContent = null;

    /**
     * 预期学员数
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $expectedStudents = null;

    /**
     * 实际学员数
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $actualStudents = null;

    /**
     * 备注
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remark = null;

    /**
     * 创建时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[CreateTimeColumn]
    private ?\DateTimeImmutable $createTime = null;

    /**
     * 更新时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[UpdateTimeColumn]
    private ?\DateTimeImmutable $updateTime = null;

    /**
     * 创建人
     */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[CreatedByColumn]
    private ?string $createdBy = null;

    /**
     * 更新人
     */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[UpdatedByColumn]
    private ?string $updatedBy = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getClassroom(): Classroom
    {
        return $this->classroom;
    }

    public function setClassroom(Classroom $classroom): self
    {
        $this->classroom = $classroom;
        return $this;
    }

    public function getTeacherId(): string
    {
        return $this->teacherId;
    }

    public function setTeacherId(string $teacherId): self
    {
        $this->teacherId = $teacherId;
        return $this;
    }

    public function getScheduleDate(): \DateTimeImmutable
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(\DateTimeImmutable $scheduleDate): self
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): self
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getScheduleType(): ScheduleType
    {
        return $this->scheduleType;
    }

    public function setScheduleType(ScheduleType $scheduleType): self
    {
        $this->scheduleType = $scheduleType;
        return $this;
    }

    public function getScheduleStatus(): ScheduleStatus
    {
        return $this->scheduleStatus;
    }

    public function setScheduleStatus(ScheduleStatus $scheduleStatus): self
    {
        $this->scheduleStatus = $scheduleStatus;
        return $this;
    }

    public function getScheduleConfig(): ?array
    {
        return $this->scheduleConfig;
    }

    public function setScheduleConfig(?array $scheduleConfig): self
    {
        $this->scheduleConfig = $scheduleConfig;
        return $this;
    }

    public function getCourseContent(): ?string
    {
        return $this->courseContent;
    }

    public function setCourseContent(?string $courseContent): self
    {
        $this->courseContent = $courseContent;
        return $this;
    }

    public function getExpectedStudents(): ?int
    {
        return $this->expectedStudents;
    }

    public function setExpectedStudents(?int $expectedStudents): self
    {
        $this->expectedStudents = $expectedStudents;
        return $this;
    }

    public function getActualStudents(): ?int
    {
        return $this->actualStudents;
    }

    public function setActualStudents(?int $actualStudents): self
    {
        $this->actualStudents = $actualStudents;
        return $this;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): self
    {
        $this->remark = $remark;
        return $this;
    }

    public function getCreateTime(): \DateTimeImmutable
    {
        return $this->createTime;
    }

    public function getUpdateTime(): \DateTimeImmutable
    {
        return $this->updateTime;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    /**
     * 获取课程持续时间（分钟）
     */
    public function getDurationInMinutes(): int
    {
        return (int) (($this->endTime->getTimestamp() - $this->startTime->getTimestamp()) / 60);
    }

    /**
     * 检查时间是否冲突
     */
    public function hasTimeConflict(\DateTimeImmutable $startTime, \DateTimeImmutable $endTime): bool
    {
        return !($endTime <= $this->startTime || $startTime >= $this->endTime);
    }

    /**
     * 检查是否可以取消
     */
    public function canBeCancelled(): bool
    {
        return $this->scheduleStatus->isEditable() && $this->startTime > new \DateTimeImmutable();
    }

    /**
     * 检查是否正在进行
     */
    public function isOngoing(): bool
    {
        $now = new \DateTimeImmutable();
        return $this->startTime <= $now && $now <= $this->endTime;
    }

    /**
     * 获取排课摘要信息
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'classroom_name' => $this->classroom->getName(),
            'teacher_id' => $this->teacherId,
            'schedule_date' => $this->scheduleDate->format('Y-m-d'),
            'start_time' => $this->startTime->format('H:i'),
            'end_time' => $this->endTime->format('H:i'),
            'duration' => $this->getDurationInMinutes(),
            'type' => $this->scheduleType->value,
            'status' => $this->scheduleStatus->value,
            'expected_students' => $this->expectedStudents,
            'actual_students' => $this->actualStudents,
        ];
    }
} 