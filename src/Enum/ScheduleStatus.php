<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Enum;

/**
 * 排课状态枚举
 * 定义排课的不同状态
 */
enum ScheduleStatus: string
{
    /**
     * 已排课
     */
    case SCHEDULED = 'SCHEDULED';

    /**
     * 进行中
     */
    case ONGOING = 'ONGOING';

    /**
     * 进行中（别名）
     */
    case IN_PROGRESS = 'IN_PROGRESS';

    /**
     * 已完成
     */
    case COMPLETED = 'COMPLETED';

    /**
     * 已取消
     */
    case CANCELLED = 'CANCELLED';

    /**
     * 已暂停
     */
    case SUSPENDED = 'SUSPENDED';

    /**
     * 已延期
     */
    case POSTPONED = 'POSTPONED';

    /**
     * 获取状态描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::SCHEDULED => '已排课',
            self::ONGOING, self::IN_PROGRESS => '进行中',
            self::COMPLETED => '已完成',
            self::CANCELLED => '已取消',
            self::SUSPENDED => '已暂停',
            self::POSTPONED => '已延期',
        };
    }

    /**
     * 获取状态颜色（用于前端显示）
     */
    public function getColor(): string
    {
        return match ($this) {
            self::SCHEDULED => 'primary',
            self::ONGOING, self::IN_PROGRESS => 'success',
            self::COMPLETED => 'info',
            self::CANCELLED => 'danger',
            self::SUSPENDED => 'warning',
            self::POSTPONED => 'secondary',
        };
    }

    /**
     * 获取所有状态选项
     */
    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getDescription();
        }
        return $options;
    }

    /**
     * 检查是否为活跃状态
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::SCHEDULED,
            self::ONGOING,
            self::IN_PROGRESS,
        ]);
    }

    /**
     * 检查是否为结束状态
     */
    public function isFinished(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::CANCELLED,
        ]);
    }

    /**
     * 检查是否可以修改
     */
    public function isEditable(): bool
    {
        return in_array($this, [
            self::SCHEDULED,
            self::SUSPENDED,
            self::POSTPONED,
        ]);
    }
} 