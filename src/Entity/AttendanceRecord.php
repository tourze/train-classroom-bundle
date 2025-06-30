<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 考勤记录实体
 * 记录学员的考勤信息，支持多种考勤方式和验证结果
 */
#[ORM\Entity]
#[ORM\Table(name: 'job_training_attendance_record', options: ['comment' => '表描述'])]
#[ORM\Index(name: 'idx_registration_id', columns: ['registration_id'])]
#[ORM\Index(name: 'idx_attendance_time', columns: ['attendance_time'])]
#[ORM\Index(name: 'idx_attendance_type', columns: ['attendance_type'])]
class AttendanceRecord implements Stringable
{
    use TimestampableAware;
    use SnowflakeKeyAware;

    /**
     * 关联的报班记录
     */
    #[ORM\ManyToOne(targetEntity: Registration::class, inversedBy: 'attendanceRecords')]
    #[ORM\JoinColumn(name: 'registration_id', referencedColumnName: 'id', nullable: false)]
    private Registration $registration;

#[ORM\Column(type: Types::STRING, length: 20, enumType: AttendanceType::class, options: ['comment' => '字段说明'])]
    private AttendanceType $attendanceType;

#[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '字段说明'])]
    private \DateTimeImmutable $attendanceTime;

#[ORM\Column(type: Types::STRING, length: 20, enumType: AttendanceMethod::class, options: ['comment' => '字段说明'])]
    private AttendanceMethod $attendanceMethod;

#[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '字段说明'])]
    private ?array $attendanceData = null;

#[ORM\Column(type: Types::BOOLEAN, options: ['default' => true, 'comment' => '是否有效'])]
    private bool $isValid = true;

#[ORM\Column(type: Types::STRING, length: 20, enumType: VerificationResult::class, options: ['comment' => '字段说明'])]
    private VerificationResult $verificationResult;

#[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $deviceId = null;

#[ORM\Column(type: Types::STRING, length: 200, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $deviceLocation = null;

#[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $latitude = null;

#[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $longitude = null;

#[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '字段说明'])]
    private ?string $remark = null;



    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    public function setRegistration(Registration $registration): self
    {
        $this->registration = $registration;
        return $this;
    }

    public function getAttendanceType(): AttendanceType
    {
        return $this->attendanceType;
    }

    public function setAttendanceType(AttendanceType $attendanceType): self
    {
        $this->attendanceType = $attendanceType;
        return $this;
    }

    public function getAttendanceTime(): \DateTimeImmutable
    {
        return $this->attendanceTime;
    }

    public function setAttendanceTime(\DateTimeImmutable $attendanceTime): self
    {
        $this->attendanceTime = $attendanceTime;
        return $this;
    }

    public function getAttendanceMethod(): AttendanceMethod
    {
        return $this->attendanceMethod;
    }

    public function setAttendanceMethod(AttendanceMethod $attendanceMethod): self
    {
        $this->attendanceMethod = $attendanceMethod;
        return $this;
    }

    public function getAttendanceData(): ?array
    {
        return $this->attendanceData;
    }

    public function setAttendanceData(?array $attendanceData): self
    {
        $this->attendanceData = $attendanceData;
        return $this;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): self
    {
        $this->isValid = $isValid;
        return $this;
    }

    public function getVerificationResult(): VerificationResult
    {
        return $this->verificationResult;
    }

    public function setVerificationResult(VerificationResult $verificationResult): self
    {
        $this->verificationResult = $verificationResult;
        return $this;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): self
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    public function getDeviceLocation(): ?string
    {
        return $this->deviceLocation;
    }

    public function setDeviceLocation(?string $deviceLocation): self
    {
        $this->deviceLocation = $deviceLocation;
        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;
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

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    /**
     * 获取考勤位置信息
     */
    public function getLocationInfo(): ?array
    {
        if ($this->latitude === null || $this->longitude === null) {
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
        return $this->verificationResult === VerificationResult::SUCCESS && $this->isValid;
    }

    /**
     * 获取考勤摘要信息
     */
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

    public function setType(AttendanceType $type): self
    {
        return $this->setAttendanceType($type);
    }

    public function getMethod(): AttendanceMethod
    {
        return $this->attendanceMethod;
    }

    public function setMethod(AttendanceMethod $method): self
    {
        return $this->setAttendanceMethod($method);
    }

    public function getRecordTime(): \DateTimeImmutable
    {
        return $this->attendanceTime;
    }

    public function setRecordTime(\DateTimeInterface $recordTime): self
    {
        $this->attendanceTime = \DateTimeImmutable::createFromInterface($recordTime);
        return $this;
    }

    public function getDeviceData(): ?array
    {
        return $this->attendanceData;
    }

    public function setDeviceData(?array $deviceData): self
    {
        return $this->setAttendanceData($deviceData);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
} 