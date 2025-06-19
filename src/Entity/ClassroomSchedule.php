<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * 教室排课实体
 * 管理教室的课程安排和教师分配
 */
#[ORM\Entity]
#[ORM\Table(name: 'job_training_classroom_schedule', options: ['comment' => '表描述'])]
#[ORM\Index(columns: ['classroom_id'], name: 'idx_classroom_id')]
#[ORM\Index(columns: ['schedule_date'], name: 'idx_schedule_date')]
#[ORM\Index(columns: ['teacher_id'], name: 'idx_teacher_id')]
#[ORM\UniqueConstraint(name: 'uk_classroom_time', columns: ['classroom_id', 'start_time', 'end_time'])]
class ClassroomSchedule implements Stringable
{
    use TimestampableAware;
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

#[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '字段说明'])]
    private string $teacherId;

#[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '字段说明'])]
    private \DateTimeImmutable $scheduleDate;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    private \DateTimeImmutable $startTime;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    private \DateTimeImmutable $endTime;

#[ORM\Column(type: Types::STRING, length: 20, enumType: ScheduleType::class, options: ['comment' => '字段说明'])]
    private ScheduleType $scheduleType;

#[ORM\Column(type: Types::STRING, length: 20, enumType: ScheduleStatus::class, options: ['comment' => '字段说明'])]
    private ScheduleStatus $scheduleStatus;

#[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '字段说明'])]
    private ?array $scheduleConfig = null;

#[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $courseContent = null;

#[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '字段说明'])]
    private ?int $expectedStudents = null;

#[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '字段说明'])]
    private ?int $actualStudents = null;

#[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $remark = null;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    private ?\DateTimeImmutable $createTime = null;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    private ?\DateTimeImmutable $updateTime = null;

#[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $createdBy = null;

#[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '字段说明'])]
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
    }public function getCreatedBy(): ?string
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

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 