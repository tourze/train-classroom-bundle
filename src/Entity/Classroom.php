<?php

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Entity(repositoryClass: ClassroomRepository::class)]
#[ORM\Table(name: 'job_training_classroom', options: ['comment' => '班级信息'])]
class Classroom implements \Stringable, ApiArrayInterface
{
    use TimestampableAware;
    use SnowflakeKeyAware;
    use BlameableAware;

    // #[FormField(title: '所属分类', optionWhere: 'a.parent IS NULL')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Catalog $category;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(length: 150, options: ['comment' => '班级名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $title;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $startTime = null;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(inversedBy: 'classrooms')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    /**
     * @var Collection<int, Registration>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'classroom', orphanRemoval: true)]
    private Collection $registrations;

    /**
     * @var Collection<int, Qrcode>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Qrcode::class, mappedBy: 'classroom', orphanRemoval: true)]
    private Collection $qrcodes;

    /**
     * @var Collection<int, ClassroomSchedule>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: ClassroomSchedule::class, mappedBy: 'classroom', orphanRemoval: true)]
    private Collection $schedules;

    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '教室类型'])]
    #[Assert\Length(max: 20)]
    #[Assert\Choice(choices: ['PHYSICAL', 'VIRTUAL', 'HYBRID'], message: '教室类型必须是: PHYSICAL, VIRTUAL, HYBRID 之一')]
    private ?string $type = null;

    #[ORM\Column(length: 20, nullable: true, options: ['comment' => '教室状态'])]
    #[Assert\Length(max: 20)]
    #[Assert\Choice(choices: ['ACTIVE', 'INACTIVE', 'MAINTENANCE', 'RESERVED'], message: '教室状态必须是: ACTIVE, INACTIVE, MAINTENANCE, RESERVED 之一')]
    private ?string $status = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '容量'])]
    #[Assert\Type(type: 'integer')]
    #[Assert\PositiveOrZero]
    private ?int $capacity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '面积（平方米）'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 12)]
    private ?string $area = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '位置'])]
    #[Assert\Length(max: 255)]
    private ?string $location = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '描述'])]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '设备信息'])]
    #[Assert\Type(type: 'array')]
    private ?array $devices = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options: ['comment' => '供应商ID'])]
    #[Assert\Type(type: 'integer')]
    #[Assert\PositiveOrZero]
    private ?int $supplierId = null;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->qrcodes = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId()) {
            return '';
        }

        return $this->getTitle();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getArea(): ?float
    {
        return null !== $this->area ? (float) $this->area : null;
    }

    public function setArea(?float $area): void
    {
        $this->area = null !== $area ? (string) $area : null;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /** @return array<string, mixed>|null */
    public function getDevices(): ?array
    {
        return $this->devices;
    }

    /** @param array<string, mixed>|null $devices */
    public function setDevices(?array $devices): void
    {
        $this->devices = $devices;
    }

    public function getSupplierId(): ?int
    {
        return $this->supplierId;
    }

    public function setSupplierId(?int $supplierId): void
    {
        $this->supplierId = $supplierId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCategory(): Catalog
    {
        return $this->category;
    }

    public function setCategory(Catalog $category): void
    {
        $this->category = $category;
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): void
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setClassroom($this);
        }
    }

    public function removeRegistration(Registration $registration): void
    {
        $this->registrations->removeElement($registration);
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course): void
    {
        $this->course = $course;
    }

    /** @return array<string, mixed> */
    public function retrieveApiArray(): array
    {
        $category = $this->getCategory();

        return [
            'id' => $this->getId(),
            'startTime' => $this->getStartTime()?->format('Y-m-d H:i:s'),
            'endTime' => $this->getEndTime()?->format('Y-m-d H:i:s'),
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ],
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

    public function addQrcode(Qrcode $qrcode): void
    {
        if (!$this->qrcodes->contains($qrcode)) {
            $this->qrcodes->add($qrcode);
            $qrcode->setClassroom($this);
        }
    }

    public function removeQrcode(Qrcode $qrcode): void
    {
        $this->qrcodes->removeElement($qrcode);
    }

    /**
     * @return Collection<int, ClassroomSchedule>
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(ClassroomSchedule $schedule): void
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules->add($schedule);
            $schedule->setClassroom($this);
        }
    }

    public function removeSchedule(ClassroomSchedule $schedule): void
    {
        $this->schedules->removeElement($schedule);
    }

    /**
     * 获取教室名称（用于排课显示）
     */
    public function getName(): string
    {
        return $this->title;
    }
}
