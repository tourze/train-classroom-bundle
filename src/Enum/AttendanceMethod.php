<?php

namespace Tourze\TrainClassroomBundle\Enum;

/**
 * 考勤方式枚举
 * 定义不同的考勤验证方式
 */
enum AttendanceMethod: string
{
    case FACE = 'FACE';                    // 人脸识别
    case CARD = 'CARD';                    // 刷卡
    case FINGERPRINT = 'FINGERPRINT';      // 指纹
    case QR_CODE = 'QR_CODE';             // 二维码
    case MANUAL = 'MANUAL';               // 手动
    case MOBILE = 'MOBILE';               // 移动端

    /**
     * 获取中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::FACE => '人脸识别',
            self::CARD => '刷卡',
            self::FINGERPRINT => '指纹识别',
            self::QR_CODE => '二维码',
            self::MANUAL => '手动录入',
            self::MOBILE => '移动端',
        };
    }

    /**
     * 获取所有方式的选项数组
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }

    /**
     * 判断是否需要生物识别
     */
    public function requiresBiometric(): bool
    {
        return $this === self::FACE || $this === self::FINGERPRINT;
    }

    /**
     * 判断是否为自动识别方式
     */
    public function isAutomatic(): bool
    {
        return $this !== self::MANUAL;
    }

    /**
     * 获取图标类名
     */
    public function getIconClass(): string
    {
        return match ($this) {
            self::FACE => 'fa-user-circle',
            self::CARD => 'fa-id-card',
            self::FINGERPRINT => 'fa-fingerprint',
            self::QR_CODE => 'fa-qrcode',
            self::MANUAL => 'fa-edit',
            self::MOBILE => 'fa-mobile-alt',
        };
    }
} 