<?php

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainCategoryBundle\Entity\Category;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainCourseBundle\Entity\Course;

#[ORM\Entity(repositoryClass: ClassroomRepository::class)]
#[ORM\Table(name: 'job_training_classroom', options: ['comment' => '班级信息'])]
class Classroom implements \Stringable, ApiArrayInterface
{
    use TimestampableAware;
    #[Groups(['restful_read', 'admin_curd', 'recursive_view', 'api_tree'])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    // #[FormField(title: '所属分类', optionWhere: 'a.parent IS NULL')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[Groups(['admin_curd'])]
    #[ORM\Column(length: 150, options: ['comment' => '班级名称'])]
    private string $title;

    #[Groups(['admin_curd'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[Groups(['admin_curd'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(inversedBy: 'classrooms')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'classroom', orphanRemoval: true)]
    private Collection $registrations;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: Qrcode::class, mappedBy: 'classroom', orphanRemoval: true)]
    private Collection $qrcodes;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: ClassroomSchedule::class, mappedBy: 'classroom', orphanRemoval: true)]
    private Collection $schedules;

    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '教室类型'])]
    private ?string $type = null;

    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '教室状态'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '容量'])]
    private ?int $capacity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '面积（平方米）'])]
    private ?string $area = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '位置'])]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '描述'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '设备信息'])]
    private ?array $devices = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '供应商ID'])]
    private ?int $supplierId = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '创建者'])]
    private ?string $createdBy = null;

    #[ORM\Column(length: 100, nullable: true, options: ['comment' => '更新者'])]
    private ?string $updatedBy = null;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->qrcodes = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (($this->getId() === null)) {
            return '';
        }

        return $this->getTitle();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): static
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function getArea(): ?float
    {
        return $this->area !== null ? (float) $this->area : null;
    }

    public function setArea(?float $area): static
    {
        $this->area = $area !== null ? (string) $area : null;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDevices(): ?array
    {
        return $this->devices;
    }

    public function setDevices(?array $devices): static
    {
        $this->devices = $devices;
        return $this;
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function setSupplierId(?int $supplierId): static
    {
        $this->supplierId = $supplierId;
        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): static
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setClassroom($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getClassroom() === $this) {
                $registration->setClassroom(null);
            }
        }

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

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

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'startTime' => $this->getStartTime()?->format('Y-m-d H:i:s'),
            'endTime' => $this->getEndTime()?->format('Y-m-d H:i:s'),
            'category' => $this->getCategory()->retrieveApiArray(),
            'topCategory' => $this->getCategory()->getTopCategory()->retrieveApiArray(),
            'title' => $this->getTitle(),
            'registrationCount' => $this->getRegistrations()->count(),
        ];
    }

    /**
     * @return Collection<int, Qrcode>
     */
    public function getQrcodes(): Collection
    {
        return $this->qrcodes;
    }

    public function addQrcode(Qrcode $qrcode): static
    {
        if (!$this->qrcodes->contains($qrcode)) {
            $this->qrcodes->add($qrcode);
            $qrcode->setClassroom($this);
        }

        return $this;
    }

    public function removeQrcode(Qrcode $qrcode): static
    {
        if ($this->qrcodes->removeElement($qrcode)) {
            // set the owning side to null (unless already changed)
            if ($qrcode->getClassroom() === $this) {
                $qrcode->setClassroom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ClassroomSchedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(ClassroomSchedule $schedule): static
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setClassroom($this);
        }

        return $this;
    }

    public function removeSchedule(ClassroomSchedule $schedule): static
    {
        if ($this->schedules->removeElement($schedule)) {
            // set the owning side to null (unless already changed)
            if ($schedule->getClassroom() === $this) {
                $schedule->setClassroom(null);
            }
        }

        return $this;
    }

    /**
     * 获取教室名称（用于排课显示）
     */
    public function getName(): string
    {
        return $this->title;
    }
}
