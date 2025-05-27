<?php

namespace Tourze\TrainClassroomBundle\Entity;

use AppBundle\Entity\Supplier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ExamBundle\Entity\Bank;
use SenboTrainingBundle\Attribute\Column\SupplierColumn;
use SenboTrainingBundle\Entity\Classroom;
use SenboTrainingBundle\Entity\Course;
use SenboTrainingBundle\Entity\CreateTimeColumn;
use SenboTrainingBundle\Entity\FaceDetect;
use SenboTrainingBundle\Entity\LearnLog;
use SenboTrainingBundle\Entity\LearnSession;
use SenboTrainingBundle\Entity\Lesson;
use SenboTrainingBundle\Entity\Qrcode;
use SenboTrainingBundle\Entity\Student;
use SenboTrainingBundle\Entity\UpdateTimeColumn;
use SenboTrainingBundle\Repository\RegistrationRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineUserAgentBundle\Attribute\CreateUserAgentColumn;
use Tourze\DoctrineUserAgentBundle\Attribute\UpdateUserAgentColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\RegistrationLearnStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;

/**
 * 通过报班记录，可以知道每个人的学习情况
 */
#[AsPermission(title: '报班记录')]
#[Listable]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity(repositoryClass: RegistrationRepository::class)]
#[ORM\Table(name: 'job_training_class_registration', options: ['comment' => '报班记录'])]
#[ORM\UniqueConstraint(name: 'job_training_class_registration_idx_uniq', columns: ['classroom_id', 'student_id'])]
#[ORM\HasLifecycleCallbacks]
class Registration implements \Stringable, ApiArrayInterface, AdminArrayInterface
{
    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'restful_read'])]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[CreatedByColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[Groups(['restful_read'])]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(length: 128, nullable: true, options: ['comment' => '更新时IP'])]
    private ?string $updatedFromIp = null;

    #[CreateUserAgentColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '创建时UA'])]
    private ?string $createdFromUa = null;

    #[UpdateUserAgentColumn]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '更新时UA'])]
    private ?string $updatedFromUa = null;

    #[SupplierColumn]
    #[ORM\ManyToOne]
    private ?Supplier $supplier = null;

    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Classroom $classroom;

    #[Ignore]
    #[Keyword(inputWidth: 60, name: 'student.realName', label: '学生姓名')]
    #[Keyword(inputWidth: 60, name: 'student.idCardNumber', label: '证件号码')]
    #[ListColumn(title: '学员')]
    #[FormField(title: '学员')]
    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Student $student;

    #[Ignore]
    #[Keyword(inputWidth: 60, name: 'course.title', label: '课程')]
    #[ORM\ManyToOne(inversedBy: 'registrations')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[Ignore]
    #[Keyword(inputWidth: 60, name: 'bank.title', label: '题库')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Bank $bank;

    #[Groups(['admin_curd'])]
    #[ORM\Column(length: 10, nullable: true, enumType: TrainType::class, options: ['comment' => '培训类型'])]
    private ?TrainType $trainType = null;

    #[ListColumn]
    #[ORM\Column(length: 20, nullable: true, enumType: OrderStatus::class, options: ['comment' => '状态'])]
    private ?OrderStatus $status = OrderStatus::PENDING;

    #[Filterable]
    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[FormField(span: 8)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '开通时间'])]
    private ?\DateTimeInterface $beginTime;

    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[FormField(span: 8)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '首次学习时间'])]
    private ?\DateTimeInterface $firstLearnTime = null;

    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '最后学习时间'])]
    private ?\DateTimeInterface $lastLearnTime = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'registration', targetEntity: FaceDetect::class)]
    private Collection $faceDetects;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'registration', targetEntity: LearnSession::class, orphanRemoval: true)]
    private Collection $sessions;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'registrations')]
    private ?Qrcode $qrcode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '支付时间'])]
    private ?\DateTimeInterface $payTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '退款失败'])]
    private ?\DateTimeInterface $refundTime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 20, scale: 2, nullable: true, options: ['comment' => '扣款金额'])]
    private ?string $payPrice = null;

    #[BoolColumn]
    #[IndexColumn]
    #[ListColumn]
    #[ORM\Column(options: ['comment' => '是否完成'])]
    private bool $finished = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $finishTime = null;

    #[ORM\Column(nullable: true, options: ['comment' => '是否已过期', 'default' => false])]
    private ?bool $expired = false;

    #[ORM\Column(nullable: true, options: ['comment' => '报名年龄'])]
    private ?int $age = null;

    /**
     * @var Collection<int, LearnLog>
     */
    #[ORM\OneToMany(mappedBy: 'registration', targetEntity: LearnLog::class)]
    private Collection $learnLogs;

    /**
     * @var Collection<int, AttendanceRecord>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'registration', targetEntity: AttendanceRecord::class)]
    private Collection $attendanceRecords;

    public function __construct()
    {
        $this->faceDetects = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->learnLogs = new ArrayCollection();
        $this->attendanceRecords = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getClassroom()->__toString()} 报名于 " . $this->getCreateTime()->format('Y-m-d H:i:s');
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
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

    public function getSupplier(): ?Supplier
    {
        return $this->supplier;
    }

    public function setSupplier(?Supplier $supplier): static
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function getClassroom(): Classroom
    {
        return $this->classroom;
    }

    public function setClassroom(Classroom $classroom): static
    {
        $this->classroom = $classroom;
        $this->setCourse($classroom->getCourse());
        $this->setBank($classroom->getBank());

        return $this;
    }

    public function getStudent(): Student
    {
        return $this->student;
    }

    public function setStudent(Student $student): static
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

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function setBank(Bank $bank): static
    {
        $this->bank = $bank;

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

    /**
     * @return Collection<int, FaceDetect>
     */
    public function getFaceDetects(): Collection
    {
        return $this->faceDetects;
    }

    public function addFaceDetect(FaceDetect $faceDetect): static
    {
        if (!$this->faceDetects->contains($faceDetect)) {
            $this->faceDetects->add($faceDetect);
            $faceDetect->setRegistration($this);
        }

        return $this;
    }

    public function removeFaceDetect(FaceDetect $faceDetect): static
    {
        if ($this->faceDetects->removeElement($faceDetect)) {
            // set the owning side to null (unless already changed)
            if ($faceDetect->getRegistration() === $this) {
                $faceDetect->setRegistration(null);
            }
        }

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

    /**
     * @return Collection<int, LearnSession>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(LearnSession $record): static
    {
        if (!$this->sessions->contains($record)) {
            $this->sessions->add($record);
            $record->setRegistration($this);
        }

        return $this;
    }

    public function removeSession(LearnSession $record): static
    {
        if ($this->sessions->removeElement($record)) {
            // set the owning side to null (unless already changed)
            if ($record->getRegistration() === $this) {
                $record->setRegistration(null);
            }
        }

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
        $learnStatus = $this->getLearnStatus();

        return [
            'id' => $this->getId(),
            'beginTime' => $this->getBeginTime()?->format('Y-m-d H:i:s'),
            'endTime' => $this->getEndTime()?->format('Y-m-d H:i:s'),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'classroom' => $this->getClassroom()->retrieveApiArray(),
            'learnStatus' => [
                'value' => $learnStatus->value,
                'label' => $learnStatus->getLabel(),
            ],
        ];
    }

    public function getSessionByLesson(Lesson $lesson): ?LearnSession
    {
        foreach ($this->getSessions() as $session) {
            if ($session->getLesson()->getId() === $lesson->getId()) {
                return $session;
            }
        }

        return null;
    }

    public function getLearnStatus(): RegistrationLearnStatus
    {
        $finishCount = 0;
        foreach ($this->getCourse()->getChapters() as $chapter) {
            foreach ($chapter->getLessons() as $lesson) {
                if ($session = $this->getSessionByLesson($lesson)) {
                    if (!$session->isFinished()) {
                        return RegistrationLearnStatus::LEARNING;
                    }
                    ++$finishCount;
                }
            }
        }

        if ($finishCount === $this->getCourse()->getLessonCount()) {
            return RegistrationLearnStatus::FINISHED;
        }

        return RegistrationLearnStatus::PENDING; // 未开始学习
    }

    #[ORM\PrePersist]
    public function ensureFilePath(): void
    {
        if (!$this->getCourse()) {
            $this->setCourse($this->getClassroom()->getCourse());
        }
        if (!$this->getBank()) {
            $this->setBank($this->getClassroom()->getBank());
        }
        if (null === $this->getPayPrice()) {
            $price = $this->getCourse()->getPrice() + $this->getBank()->getPrice();
            $this->setPayPrice($price);
        }
    }

    public function isFinished(): ?bool
    {
        return $this->finished;
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
            'beginTime' => $this->getBeginTime()?->format('Y-m-d H:i:s'),
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
     * @return Collection<int, LearnLog>
     */
    public function getLearnLogs(): Collection
    {
        return $this->learnLogs;
    }

    public function addLearnLog(LearnLog $learnLog): static
    {
        if (!$this->learnLogs->contains($learnLog)) {
            $this->learnLogs->add($learnLog);
            $learnLog->setRegistration($this);
        }

        return $this;
    }

    public function removeLearnLog(LearnLog $learnLog): static
    {
        if ($this->learnLogs->removeElement($learnLog)) {
            // set the owning side to null (unless already changed)
            if ($learnLog->getRegistration() === $this) {
                $learnLog->setRegistration(null);
            }
        }

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
            // set the owning side to null (unless already changed)
            if ($attendanceRecord->getRegistration() === $this) {
                $attendanceRecord->setRegistration(null);
            }
        }

        return $this;
    }
}
