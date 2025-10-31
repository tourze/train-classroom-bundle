<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * 教室排课实体
 * 管理教室的课程安排和教师分配
 */
#[ORM\Entity]
#[ORM\Table(name: 'job_training_classroom_schedule', options: ['comment' => '表描述'])]
#[ORM\UniqueConstraint(name: 'uk_classroom_time', columns: ['classroom_id', 'start_time', 'end_time'])]
class ClassroomSchedule implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    /**
     * 关联的教室
     */
    #[ORM\ManyToOne(targetEntity: Classroom::class, inversedBy: 'schedules')]
    #[ORM\JoinColumn(name: 'classroom_id', referencedColumnName: 'id', nullable: false)]
    private Classroom $classroom;

    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 100, options: ['comment' => '字段说明'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $teacherId;

    #[IndexColumn]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['comment' => '字段说明'])]
    #[Assert\NotNull]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private \DateTimeImmutable $scheduleDate;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    #[Assert\NotNull]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    #[Assert\NotNull]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private \DateTimeImmutable $endTime;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ScheduleType::class, options: ['comment' => '字段说明'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [ScheduleType::class, 'cases'])]
    private ScheduleType $scheduleType;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: ScheduleStatus::class, options: ['comment' => '字段说明'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [ScheduleStatus::class, 'cases'])]
    private ScheduleStatus $scheduleStatus;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Type(type: 'array')]
    private ?array $scheduleConfig = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $courseContent = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Type(type: 'integer')]
    #[Assert\PositiveOrZero]
    private ?int $expectedStudents = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Type(type: 'integer')]
    #[Assert\PositiveOrZero]
    private ?int $actualStudents = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    public function getClassroom(): Classroom
    {
        return $this->classroom;
    }

    public function setClassroom(Classroom $classroom): void
    {
        $this->classroom = $classroom;
    }

    public function getTeacherId(): string
    {
        return $this->teacherId;
    }

    public function setTeacherId(string $teacherId): void
    {
        $this->teacherId = $teacherId;
    }

    public function getScheduleDate(): \DateTimeImmutable
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(\DateTimeImmutable $scheduleDate): void
    {
        $this->scheduleDate = $scheduleDate;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getScheduleType(): ScheduleType
    {
        return $this->scheduleType;
    }

    public function setScheduleType(ScheduleType $scheduleType): void
    {
        $this->scheduleType = $scheduleType;
    }

    public function getScheduleStatus(): ScheduleStatus
    {
        return $this->scheduleStatus;
    }

    public function setScheduleStatus(ScheduleStatus $scheduleStatus): void
    {
        $this->scheduleStatus = $scheduleStatus;
    }

    /** @return array<string, mixed>|null */
    public function getScheduleConfig(): ?array
    {
        return $this->scheduleConfig;
    }

    /** @param array<string, mixed>|null $scheduleConfig */
    public function setScheduleConfig(?array $scheduleConfig): void
    {
        $this->scheduleConfig = $scheduleConfig;
    }

    public function getCourseContent(): ?string
    {
        return $this->courseContent;
    }

    public function setCourseContent(?string $courseContent): void
    {
        $this->courseContent = $courseContent;
    }

    public function getExpectedStudents(): ?int
    {
        return $this->expectedStudents;
    }

    public function setExpectedStudents(?int $expectedStudents): void
    {
        $this->expectedStudents = $expectedStudents;
    }

    public function getActualStudents(): ?int
    {
        return $this->actualStudents;
    }

    public function setActualStudents(?int $actualStudents): void
    {
        $this->actualStudents = $actualStudents;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
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
    /** @return array<string, mixed> */
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
