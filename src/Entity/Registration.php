<?php

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserAgentBundle\Attribute\CreateUserAgentColumn;
use Tourze\DoctrineUserAgentBundle\Attribute\UpdateUserAgentColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 通过报班记录，可以知道每个人的学习情况
 * @implements ApiArrayInterface<string, mixed>
 * @implements AdminArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\Table(name: 'job_training_class_registration', options: ['comment' => '报班记录'])]
#[ORM\UniqueConstraint(name: 'job_training_class_registration_idx_uniq', columns: ['classroom_id', 'student_id'])]
class Registration implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;
    use IpTraceableAware;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Classroom $classroom;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: UserInterface::class, inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private UserInterface $student;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[Assert\Choice(callback: [TrainType::class, 'cases'])]
    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(length: 10, nullable: true, enumType: TrainType::class, options: ['comment' => '培训类型'])]
    private ?TrainType $trainType = null;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [OrderStatus::class, 'cases'])]
    #[ORM\Column(enumType: OrderStatus::class, options: ['comment' => '订单状态'])]
    private ?OrderStatus $status = OrderStatus::PENDING;

    #[Assert\NotNull]
    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开通时间'])]
    private \DateTimeInterface $beginTime;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    #[Assert\Type(type: '\DateTimeInterface')]
    private ?\DateTimeInterface $endTime = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '首次学习时间'])]
    private ?\DateTimeInterface $firstLearnTime = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '最后学习时间'])]
    private ?\DateTimeInterface $lastLearnTime = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    private ?Qrcode $qrcode = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '支付时间'])]
    private ?\DateTimeInterface $payTime = null;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '退款失败'])]
    private ?\DateTimeInterface $refundTime = null;

    #[Assert\Length(max: 22)]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: '价格格式错误')]
    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true, options: ['comment' => '扣款金额'])]
    private ?string $payPrice = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(options: ['comment' => '是否完成', 'default' => false])]
    private bool $finished = false;

    #[Assert\Type(type: '\DateTimeInterface')]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $finishTime = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(nullable: true, options: ['comment' => '是否已过期', 'default' => false])]
    private ?bool $expired = false;

    #[Assert\Range(min: 0, max: 150)]
    #[ORM\Column(nullable: true, options: ['comment' => '报名年龄'])]
    private ?int $age = null;

    /**
     * @var Collection<int, AttendanceRecord>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: AttendanceRecord::class, mappedBy: 'registration')]
    private Collection $attendanceRecords;

    #[Assert\Length(max: 2000)]
    #[CreateUserAgentColumn]
    private ?string $createdFromUa = null;

    #[Assert\Length(max: 2000)]
    #[UpdateUserAgentColumn]
    private ?string $updatedFromUa = null;

    public function __construct()
    {
        $this->attendanceRecords = new ArrayCollection();
        $this->beginTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return "{$this->getClassroom()->__toString()} 报名于 " . $this->getCreateTime()?->format('Y-m-d H:i:s');
    }

    public function setCreatedFromUa(?string $createdFromUa): void
    {
        $this->createdFromUa = $createdFromUa;
    }

    public function getCreatedFromUa(): ?string
    {
        return $this->createdFromUa;
    }

    public function setUpdatedFromUa(?string $updatedFromUa): void
    {
        $this->updatedFromUa = $updatedFromUa;
    }

    public function getUpdatedFromUa(): ?string
    {
        return $this->updatedFromUa;
    }

    public function getClassroom(): Classroom
    {
        return $this->classroom;
    }

    public function setClassroom(Classroom $classroom): void
    {
        $this->classroom = $classroom;
        $this->setCourse($classroom->getCourse());
    }

    public function getStudent(): UserInterface
    {
        return $this->student;
    }

    public function setStudent(UserInterface $student): void
    {
        $this->student = $student;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): void
    {
        $this->course = $course;
    }

    public function getFirstLearnTime(): ?\DateTimeInterface
    {
        return $this->firstLearnTime;
    }

    public function setFirstLearnTime(?\DateTimeInterface $firstLearnTime): void
    {
        $this->firstLearnTime = $firstLearnTime;
    }

    public function getLastLearnTime(): ?\DateTimeInterface
    {
        return $this->lastLearnTime;
    }

    public function setLastLearnTime(?\DateTimeInterface $lastLearnTime): void
    {
        $this->lastLearnTime = $lastLearnTime;
    }

    public function getBeginTime(): \DateTimeInterface
    {
        return $this->beginTime;
    }

    public function setBeginTime(\DateTimeInterface $beginTime): void
    {
        $this->beginTime = $beginTime;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getTrainType(): ?TrainType
    {
        return $this->trainType;
    }

    public function setTrainType(?TrainType $trainType): void
    {
        $this->trainType = $trainType;
    }

    public function getQrcode(): ?Qrcode
    {
        return $this->qrcode;
    }

    public function setQrcode(?Qrcode $qrcode): void
    {
        $this->qrcode = $qrcode;
    }

    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }

    public function setStatus(?OrderStatus $status): void
    {
        $this->status = $status;
    }

    public function getPayTime(): ?\DateTimeInterface
    {
        return $this->payTime;
    }

    public function setPayTime(?\DateTimeInterface $payTime): void
    {
        $this->payTime = $payTime;
    }

    public function getRefundTime(): ?\DateTimeInterface
    {
        return $this->refundTime;
    }

    public function setRefundTime(?\DateTimeInterface $refundTime): void
    {
        $this->refundTime = $refundTime;
    }

    public function getPayPrice(): ?string
    {
        return $this->payPrice;
    }

    public function setPayPrice(?string $payPrice): void
    {
        $this->payPrice = $payPrice;
    }

    /** @return array<string, mixed> */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'beginTime' => $this->getBeginTime()->format('Y-m-d H:i:s'),
            'endTime' => $this->getEndTime()?->format('Y-m-d H:i:s'),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'classroom' => $this->getClassroom()->retrieveApiArray(),
        ];
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
    }

    /**
     * 检查报名是否有效
     */
    public function isActive(): bool
    {
        // 如果已完成则不活跃
        if ($this->finished) {
            return false;
        }

        // 检查时间范围
        $now = new \DateTimeImmutable();
        if ($now < $this->beginTime) {
            return false;
        }
        if (null !== $this->endTime && $now > $this->endTime) {
            return false;
        }

        return true;
    }

    public function setFinished(bool $finished): void
    {
        $this->finished = $finished;
    }

    public function getFinishTime(): ?\DateTimeInterface
    {
        return $this->finishTime;
    }

    public function setFinishTime(?\DateTimeInterface $finishTime): void
    {
        $this->finishTime = $finishTime;
    }

    /** @return array<string, mixed> */
    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'trainType' => $this->getTrainType()?->value,
            'beginTime' => $this->getBeginTime()->format('Y-m-d H:i:s'),
            'endTime' => $this->getEndTime()?->format('Y-m-d H:i:s'),
            'status' => $this->getStatus()?->value,
        ];
    }

    public function isExpired(): ?bool
    {
        return $this->expired;
    }

    public function setExpired(?bool $expired): void
    {
        $this->expired = $expired;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): void
    {
        $this->age = $age;
    }

    /**
     * @return Collection<int, AttendanceRecord>
     */
    public function getAttendanceRecords(): Collection
    {
        return $this->attendanceRecords;
    }

    public function addAttendanceRecord(AttendanceRecord $attendanceRecord): void
    {
        if (!$this->attendanceRecords->contains($attendanceRecord)) {
            $this->attendanceRecords->add($attendanceRecord);
            $attendanceRecord->setRegistration($this);
        }
    }

    public function removeAttendanceRecord(AttendanceRecord $attendanceRecord): void
    {
        if ($this->attendanceRecords->removeElement($attendanceRecord)) {
            // 考勤记录被移除时，不需要设置关联为null，因为这可能破坏数据完整性
            // 如果确实需要解除关联，应该在AttendanceRecord中添加支持nullable的setter方法
        }
    }
}
