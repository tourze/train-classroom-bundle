<?php

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 考勤方式枚举
 * 定义不同的考勤验证方式
 */
enum AttendanceMethod: string implements Itemable, Labelable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;
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
     * @return array<string, string>
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
        return self::FACE === $this || self::FINGERPRINT === $this;
    }

    /**
     * 判断是否为自动识别方式
     */
    public function isAutomatic(): bool
    {
        return self::MANUAL !== $this;
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

    /**
     * 获取Badge样式
     */
    public function getBadge(): string
    {
        return match ($this) {
            self::FACE => BadgeInterface::PRIMARY,
            self::CARD => BadgeInterface::INFO,
            self::FINGERPRINT => BadgeInterface::SUCCESS,
            self::QR_CODE => BadgeInterface::WARNING,
            self::MANUAL => BadgeInterface::SECONDARY,
            self::MOBILE => BadgeInterface::LIGHT,
        };
    }
}
