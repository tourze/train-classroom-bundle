<?php

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainClassroomBundle\Repository\QrcodeRepository;

#[ORM\Entity(repositoryClass: QrcodeRepository::class)]
#[ORM\Table(name: 'job_training_reg_qrcode', options: ['comment' => '报名二维码'])]
#[ORM\UniqueConstraint(name: 'job_training_reg_qrcode_idx_uniq', columns: ['classroom_id', 'title'])]
class Qrcode implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[TrackColumn]
    #[Groups(groups: ['admin_curd', 'restful_read', 'restful_read', 'restful_write'])]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'qrcodes')]
    #[ORM\JoinColumn(nullable: false)]
    private Classroom $classroom;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(length: 120, options: ['comment' => '二维码名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 120)]
    private string $title;

    #[Groups(groups: ['admin_curd'])]
    #[ORM\Column(options: ['comment' => '限制人数'])]
    #[Assert\NotNull]
    #[Assert\Type(type: 'integer')]
    #[Assert\PositiveOrZero]
    private int $limitNumber;

    /**
     * @var Collection<int, Registration>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'qrcode')]
    private Collection $registrations;

    public function __construct()
    {
        $this->registrations = new ArrayCollection();
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getClassroom(): Classroom
    {
        return $this->classroom;
    }

    public function setClassroom(Classroom $classroom): void
    {
        $this->classroom = $classroom;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLimitNumber(): int
    {
        return $this->limitNumber;
    }

    public function setLimitNumber(int $limitNumber): void
    {
        $this->limitNumber = $limitNumber;
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
            $registration->setQrcode($this);
        }
    }

    public function removeRegistration(Registration $registration): void
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getQrcode() === $this) {
                $registration->setQrcode(null);
            }
        }
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
