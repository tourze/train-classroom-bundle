<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIpBundle\Attribute\Ip;
use Tourze\DoctrineSnowflakeBundle\Attribute\SnowflakeId;
use Tourze\DoctrineTimestampBundle\Attribute\Timestamp;
use Tourze\DoctrineUserAgentBundle\Attribute\UserAgent;
use Tourze\DoctrineUserBundle\Attribute\User;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 考勤记录实体
 * 记录学员的考勤信息，支持多种考勤方式和验证结果
 */
#[ORM\Entity]
#[ORM\Table(name: 'job_training_attendance_record')]
#[ORM\Index(columns: ['registration_id'], name: 'idx_registration_id')]
#[ORM\Index(columns: ['attendance_time'], name: 'idx_attendance_time')]
#[ORM\Index(columns: ['attendance_type'], name: 'idx_attendance_type')]
#[ORM\Index(columns: ['supplier_id'], name: 'idx_supplier_id')]
class AttendanceRecord
{
    /**
     * 主键ID
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::BIGINT)]
    #[SnowflakeId]
    private readonly string $id;

    /**
     * 关联的报班记录
     */
    #[ORM\ManyToOne(targetEntity: Registration::class, inversedBy: 'attendanceRecords')]
    #[ORM\JoinColumn(name: 'registration_id', referencedColumnName: 'id', nullable: false)]
    private Registration $registration;

    /**
     * 考勤类型
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: AttendanceType::class)]
    private AttendanceType $attendanceType;

    /**
     * 考勤时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $attendanceTime;

    /**
     * 考勤方式
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: AttendanceMethod::class)]
    private AttendanceMethod $attendanceMethod;

    /**
     * 考勤数据（JSON格式存储人脸特征、指纹数据等）
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $attendanceData = null;

    /**
     * 是否有效
     */
    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isValid = true;

    /**
     * 验证结果
     */
    #[ORM\Column(type: Types::STRING, length: 20, enumType: VerificationResult::class)]
    private VerificationResult $verificationResult;

    /**
     * 设备ID
     */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $deviceId = null;

    /**
     * 设备位置
     */
    #[ORM\Column(type: Types::STRING, length: 200, nullable: true)]
    private ?string $deviceLocation = null;

    /**
     * 纬度
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 8, nullable: true)]
    private ?string $latitude = null;

    /**
     * 经度
     */
    #[ORM\Column(type: Types::DECIMAL, precision: 11, scale: 8, nullable: true)]
    private ?string $longitude = null;

    /**
     * 备注
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $remark = null;

    /**
     * 创建时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Timestamp(on: ['create'])]
    private readonly \DateTimeImmutable $createTime;

    /**
     * 更新时间
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Timestamp(on: ['create', 'update'])]
    private \DateTimeImmutable $updateTime;

    /**
     * 创建人
     */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[User(on: ['create'])]
    private readonly ?string $createdBy;

    /**
     * 更新人
     */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    #[User(on: ['create', 'update'])]
    private ?string $updatedBy;

    /**
     * 创建时IP
     */
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    #[Ip(on: ['create'])]
    private readonly ?string $createdFromIp;

    /**
     * 更新时IP
     */
    #[ORM\Column(type: Types::STRING, length: 128, nullable: true)]
    #[Ip(on: ['create', 'update'])]
    private ?string $updatedFromIp;

    /**
     * 供应商ID（多租户支持）
     */
    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $supplierId = null;

    public function getId(): string
    {
        return $this->id;
    }

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
    }

    public function getCreateTime(): \DateTimeImmutable
    {
        return $this->createTime;
    }

    public function getUpdateTime(): \DateTimeImmutable
    {
        return $this->updateTime;
    }

    public function getCreatedBy(): ?string
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

    public function getSupplierId(): ?string
    {
        return $this->supplierId;
    }

    public function setSupplierId(?string $supplierId): self
    {
        $this->supplierId = $supplierId;
        return $this;
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
} 