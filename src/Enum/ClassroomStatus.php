<?php

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 教室状态枚举
 * 定义教室的运行状态
 */
enum ClassroomStatus: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case ACTIVE = 'ACTIVE';           // 活跃
    case INACTIVE = 'INACTIVE';       // 非活跃
    case MAINTENANCE = 'MAINTENANCE'; // 维护中
    case RESERVED = 'RESERVED';       // 预留

    /**
     * 获取中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => '正常使用',
            self::INACTIVE => '暂停使用',
            self::MAINTENANCE => '维护中',
            self::RESERVED => '预留',
        };
    }

    /**
     * 获取所有状态的选项数组
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
     * 判断是否可用于培训
     */
    public function isAvailableForTraining(): bool
    {
        return self::ACTIVE === $this;
    }

    /**
     * 判断是否需要维护
     */
    public function needsMaintenance(): bool
    {
        return self::MAINTENANCE === $this;
    }

    /**
     * 获取状态颜色类名
     */
    public function getColorClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'text-success',
            self::INACTIVE => 'text-secondary',
            self::MAINTENANCE => 'text-warning',
            self::RESERVED => 'text-info',
        };
    }

    /**
     * 获取状态徽章类名
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'badge-success',
            self::INACTIVE => 'badge-secondary',
            self::MAINTENANCE => 'badge-warning',
            self::RESERVED => 'badge-info',
        };
    }

    /**
     * 获取图标类名
     */
    public function getIconClass(): string
    {
        return match ($this) {
            self::ACTIVE => 'fa-check-circle',
            self::INACTIVE => 'fa-pause-circle',
            self::MAINTENANCE => 'fa-tools',
            self::RESERVED => 'fa-bookmark',
        };
    }
}
