<?php

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
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
 */
#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\Table(name: 'job_training_class_registration', options: ['comment' => '报班记录'])]
#[ORM\UniqueConstraint(name: 'job_training_class_registration_idx_uniq', columns: ['classroom_id', 'student_id'])]
class Registration implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Classroom $classroom;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private UserInterface $student;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(length: 10, nullable: true, enumType: TrainType::class, options: ['comment' => '培训类型'])]
    private ?TrainType $trainType = null;

    private ?OrderStatus $status = OrderStatus::PENDING;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开通时间'])]
    private \DateTimeInterface $beginTime;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[Groups(groups: ['admin_curd'])]
    private ?\DateTimeInterface $firstLearnTime = null;

    #[Groups(groups: ['admin_curd'])]
    private ?\DateTimeInterface $lastLearnTime = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    private ?Qrcode $qrcode = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '支付时间'])]
    private ?\DateTimeInterface $payTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '退款失败'])]
    private ?\DateTimeInterface $refundTime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true, options: ['comment' => '扣款金额'])]
    private ?string $payPrice = null;

    private bool $finished = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $finishTime = null;

    #[ORM\Column(nullable: true, options: ['comment' => '是否已过期', 'default' => false])]
    private ?bool $expired = false;

    #[ORM\Column(nullable: true, options: ['comment' => '报名年龄'])]
    private ?int $age = null;

    /**
     * @var Collection<int, AttendanceRecord>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: AttendanceRecord::class, mappedBy: 'registration')]
    private Collection $attendanceRecords;


    #[CreateIpColumn]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    private ?string $updatedFromIp = null;

    #[CreateUserAgentColumn]
    private ?string $createdFromUa = null;

    #[UpdateUserAgentColumn]
    private ?string $updatedFromUa = null;

    public function __construct()
    {
        $this->attendanceRecords = new ArrayCollection();
        $this->beginTime = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        if (($this->getId() === null)) {
            return '';
        }

        return "{$this->getClassroom()->__toString()} 报名于 " . $this->getCreateTime()->format('Y-m-d H:i:s');
    }



    public function setCreatedFromIp(?string $createdFromIp): self
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): self
    {
        $this->updatedFromIp = $updatedFromIp;

        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
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

    public function setClassroom(Classroom $classroom): static
    {
        $this->classroom = $classroom;
        $this->setCourse($classroom->getCourse());

        return $this;
    }

    public function getStudent(): UserInterface
    {
        return $this->student;
    }

    public function setStudent(UserInterface $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getFirstLearnTime(): ?\DateTimeInterface
    {
        return $this->firstLearnTime;
    }

    public function setFirstLearnTime(?\DateTimeInterface $firstLearnTime): static
    {
        $this->firstLearnTime = $firstLearnTime;

        return $this;
    }

    public function getLastLearnTime(): ?\DateTimeInterface
    {
        return $this->lastLearnTime;
    }

    public function setLastLearnTime(?\DateTimeInterface $lastLearnTime): static
    {
        $this->lastLearnTime = $lastLearnTime;

        return $this;
    }

    public function getBeginTime(): \DateTimeInterface
    {
        return $this->beginTime;
    }

    public function setBeginTime(\DateTimeInterface $beginTime): static
    {
        $this->beginTime = $beginTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getTrainType(): ?TrainType
    {
        return $this->trainType;
    }

    public function setTrainType(?TrainType $trainType): static
    {
        $this->trainType = $trainType;

        return $this;
    }

    public function getQrcode(): ?Qrcode
    {
        return $this->qrcode;
    }

    public function setQrcode(?Qrcode $qrcode): static
    {
        $this->qrcode = $qrcode;

        return $this;
    }

    public function getStatus(): ?OrderStatus
    {
        return $this->status;
    }

    public function setStatus(?OrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPayTime(): ?\DateTimeInterface
    {
        return $this->payTime;
    }

    public function setPayTime(?\DateTimeInterface $payTime): static
    {
        $this->payTime = $payTime;

        return $this;
    }

    public function getRefundTime(): ?\DateTimeInterface
    {
        return $this->refundTime;
    }

    public function setRefundTime(?\DateTimeInterface $refundTime): static
    {
        $this->refundTime = $refundTime;

        return $this;
    }

    public function getPayPrice(): ?string
    {
        return $this->payPrice;
    }

    public function setPayPrice(?string $payPrice): static
    {
        $this->payPrice = $payPrice;

        return $this;
    }

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
        if ($this->endTime !== null && $now > $this->endTime) {
            return false;
        }
        
        return true;
    }

    public function setFinished(?bool $finished): static
    {
        $this->finished = $finished;

        return $this;
    }

    public function getFinishTime(): ?\DateTimeInterface
    {
        return $this->finishTime;
    }

    public function setFinishTime(?\DateTimeInterface $finishTime): static
    {
        $this->finishTime = $finishTime;

        return $this;
    }

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

    public function setExpired(?bool $expired): static
    {
        $this->expired = $expired;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    /**
     * @return Collection<int, AttendanceRecord>
     */
    public function getAttendanceRecords(): Collection
    {
        return $this->attendanceRecords;
    }

    public function addAttendanceRecord(AttendanceRecord $attendanceRecord): static
    {
        if (!$this->attendanceRecords->contains($attendanceRecord)) {
            $this->attendanceRecords->add($attendanceRecord);
            $attendanceRecord->setRegistration($this);
        }

        return $this;
    }

    public function removeAttendanceRecord(AttendanceRecord $attendanceRecord): static
    {
        if ($this->attendanceRecords->removeElement($attendanceRecord)) {
            // 考勤记录被移除时，不需要设置关联为null，因为这可能破坏数据完整性
            // 如果确实需要解除关联，应该在AttendanceRecord中添加支持nullable的setter方法
        }

        return $this;
    }}
