<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Traits\IpTraceableAware;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 考勤记录实体
 * 记录学员的考勤信息，支持多种考勤方式和验证结果
 */
#[ORM\Entity]
#[ORM\Table(name: 'job_training_attendance_record', options: ['comment' => '表描述'])]
class AttendanceRecord implements \Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;
    use BlameableAware;
    use IpTraceableAware;

    /**
     * 关联的报班记录
     */
    #[ORM\ManyToOne(targetEntity: Registration::class, inversedBy: 'attendanceRecords')]
    #[ORM\JoinColumn(name: 'registration_id', referencedColumnName: 'id', nullable: false)]
    private Registration $registration;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [AttendanceType::class, 'cases'])]
    #[IndexColumn]
    #[ORM\Column(type: Types::STRING, length: 20, enumType: AttendanceType::class, options: ['comment' => '字段说明'])]
    private AttendanceType $attendanceType;

    #[Assert\NotNull]
    #[IndexColumn]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    private \DateTimeImmutable $attendanceTime;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [AttendanceMethod::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 20, enumType: AttendanceMethod::class, options: ['comment' => '字段说明'])]
    private AttendanceMethod $attendanceMethod;

    /**
     * @var array<string, mixed>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '字段说明'])]
    private ?array $attendanceData = null;

    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否有效'])]
    private bool $isValid = true;

    #[Assert\NotNull]
    #[Assert\Choice(callback: [VerificationResult::class, 'cases'])]
    #[ORM\Column(type: Types::STRING, length: 20, enumType: VerificationResult::class, options: ['comment' => '字段说明'])]
    private VerificationResult $verificationResult;

    #[Assert\Length(max: 100)]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $deviceId = null;

    #[Assert\Length(max: 200)]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $deviceLocation = null;

    #[Assert\Length(max: 12)]
    #[Assert\Range(min: -90, max: 90)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $latitude = null;

    #[Assert\Length(max: 13)]
    #[Assert\Range(min: -180, max: 180)]
    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $longitude = null;

    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $remark = null;

    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    public function setRegistration(Registration $registration): void
    {
        $this->registration = $registration;
    }

    public function getAttendanceType(): AttendanceType
    {
        return $this->attendanceType;
    }

    public function setAttendanceType(AttendanceType $attendanceType): void
    {
        $this->attendanceType = $attendanceType;
    }

    public function getAttendanceTime(): \DateTimeImmutable
    {
        return $this->attendanceTime;
    }

    public function setAttendanceTime(\DateTimeImmutable $attendanceTime): void
    {
        $this->attendanceTime = $attendanceTime;
    }

    public function getAttendanceMethod(): AttendanceMethod
    {
        return $this->attendanceMethod;
    }

    public function setAttendanceMethod(AttendanceMethod $attendanceMethod): void
    {
        $this->attendanceMethod = $attendanceMethod;
    }

    /** @return array<string, mixed>|null */
    public function getAttendanceData(): ?array
    {
        return $this->attendanceData;
    }

    /** @param array<string, mixed>|null $attendanceData */
    public function setAttendanceData(?array $attendanceData): void
    {
        $this->attendanceData = $attendanceData;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): void
    {
        $this->isValid = $isValid;
    }

    /**
     * 标准getter方法，用于测试框架兼容性
     */
    public function getIsValid(): bool
    {
        return $this->isValid;
    }

    public function getVerificationResult(): VerificationResult
    {
        return $this->verificationResult;
    }

    public function setVerificationResult(VerificationResult $verificationResult): void
    {
        $this->verificationResult = $verificationResult;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getDeviceLocation(): ?string
    {
        return $this->deviceLocation;
    }

    public function setDeviceLocation(?string $deviceLocation): void
    {
        $this->deviceLocation = $deviceLocation;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): void
    {
        $this->longitude = $longitude;
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
     * 获取考勤位置信息
     */
    /** @return array<string, string|null>|null */
    public function getLocationInfo(): ?array
    {
        if (null === $this->latitude || null === $this->longitude) {
            return null;
        }

        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'device_location' => $this->deviceLocation,
        ];
    }

    /**
     * 检查考勤是否成功
     */
    public function isSuccessful(): bool
    {
        return VerificationResult::SUCCESS === $this->verificationResult && $this->isValid;
    }

    /**
     * 获取考勤摘要信息
     */
    /** @return array<string, mixed> */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->attendanceType->value,
            'method' => $this->attendanceMethod->value,
            'time' => $this->attendanceTime->format('Y-m-d H:i:s'),
            'result' => $this->verificationResult->value,
            'is_valid' => $this->isValid,
            'device_id' => $this->deviceId,
        ];
    }

    // 别名方法，与服务层保持一致
    public function getType(): AttendanceType
    {
        return $this->attendanceType;
    }

    public function setType(AttendanceType $type): void
    {
        $this->setAttendanceType($type);
    }

    public function getMethod(): AttendanceMethod
    {
        return $this->attendanceMethod;
    }

    public function setMethod(AttendanceMethod $method): void
    {
        $this->setAttendanceMethod($method);
    }

    public function getRecordTime(): \DateTimeImmutable
    {
        return $this->attendanceTime;
    }

    public function setRecordTime(\DateTimeInterface $recordTime): void
    {
        $this->attendanceTime = \DateTimeImmutable::createFromInterface($recordTime);
    }

    /** @return array<string, mixed>|null */
    public function getDeviceData(): ?array
    {
        return $this->attendanceData;
    }

    /** @param array<string, mixed>|null $deviceData */
    public function setDeviceData(?array $deviceData): void
    {
        $this->setAttendanceData($deviceData);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
