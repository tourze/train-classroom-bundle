<?php

namespace Tourze\TrainClassroomBundle\Entity;

use AppBundle\Entity\Supplier;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ExamBundle\Entity\Bank;
use ExamBundle\Entity\Category;
use SenboTrainingBundle\Attribute\Column\SupplierColumn;
use SenboTrainingBundle\Entity\Course;
use SenboTrainingBundle\Entity\CreateTimeColumn;
use SenboTrainingBundle\Entity\IndexColumn;
use SenboTrainingBundle\Entity\Qrcode;
use SenboTrainingBundle\Entity\Registration;
use SenboTrainingBundle\Entity\UpdateTimeColumn;
use SenboTrainingBundle\Repository\ClassroomRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '班级信息')]
#[Listable]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity(repositoryClass: ClassroomRepository::class)]
#[ORM\Table(name: 'job_training_classroom', options: ['comment' => '班级信息'])]
class Classroom implements \Stringable, ApiArrayInterface
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

    #[SupplierColumn]
    #[ORM\ManyToOne]
    private ?Supplier $supplier = null;

    #[Filterable(label: '所属分类', inputWidth: 400)]
    #[ListColumn(title: '所属分类')]
    // #[FormField(title: '所属分类', optionWhere: 'a.parent IS NULL')]
    #[FormField(title: '所属分类')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Category $category;

    #[Groups(['admin_curd'])]
    #[Keyword]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 150, options: ['comment' => '班级名称'])]
    private string $title;

    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[FormField(span: 6)]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    private ?\DateTimeInterface $startTime = null;

    #[Groups(['admin_curd'])]
    #[ListColumn]
    #[FormField(span: 6)]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    private ?\DateTimeInterface $endTime = null;

    #[ListColumn(title: '关联课程')]
    #[FormField(title: '关联课程')]
    #[ORM\ManyToOne(inversedBy: 'classrooms')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ListColumn(title: '关联题库')]
    #[FormField(title: '关联题库')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bank $bank = null;

    #[Ignore]
    #[CurdAction(label: '报班学员', drawerWidth: 1200)]
    #[ORM\OneToMany(mappedBy: 'classroom', targetEntity: Registration::class, orphanRemoval: true)]
    private Collection $registrations;

    #[Ignore]
    #[CurdAction(label: '报班二维码', drawerWidth: 1200)]
    #[ORM\OneToMany(mappedBy: 'classroom', targetEntity: Qrcode::class, orphanRemoval: true)]
    private Collection $qrcodes;

    #[Ignore]
    #[CurdAction(label: '排课记录', drawerWidth: 1200)]
    #[ORM\OneToMany(mappedBy: 'classroom', targetEntity: ClassroomSchedule::class, orphanRemoval: true)]
    private Collection $schedules;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
        $this->qrcodes = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (!$this->getId()) {
            return '';
        }

        return $this->getTitle();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    public function getTitle(): string
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

    public function getBank(): ?Bank
    {
        return $this->bank;
    }

    public function setBank(?Bank $bank): static
    {
        $this->bank = $bank;

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
