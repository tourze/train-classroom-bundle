<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 培训类型枚举
 */
enum TrainType: string implements BadgeInterface, Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case ONLINE = 'online';
    case OFFLINE = 'offline';
    case HYBRID = 'hybrid';

    /**
     * 获取类型描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::ONLINE => '线上培训',
            self::OFFLINE => '线下培训',
            self::HYBRID => '混合培训',
        };
    }

    /**
     * 获取所有选项
     * @return array<string, string>
     */
    public static function getOptions(): array
    {
        return [
            self::ONLINE->value => self::ONLINE->getDescription(),
            self::OFFLINE->value => self::OFFLINE->getDescription(),
            self::HYBRID->value => self::HYBRID->getDescription(),
        ];
    }

    /**
     * 检查是否为线上培训
     */
    public function isOnline(): bool
    {
        return self::ONLINE === $this;
    }

    /**
     * 检查是否为线下培训
     */
    public function isOffline(): bool
    {
        return self::OFFLINE === $this;
    }

    /**
     * 检查是否为混合培训
     */
    public function isHybrid(): bool
    {
        return self::HYBRID === $this;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ONLINE => '线上培训',
            self::OFFLINE => '线下培训',
            self::HYBRID => '混合培训',
        };
    }

    /**
     * 获取徽章类型
     */
    public function getBadge(): string
    {
        return match ($this) {
            self::ONLINE => BadgeInterface::INFO,
            self::OFFLINE => BadgeInterface::PRIMARY,
            self::HYBRID => BadgeInterface::SUCCESS,
        };
    }

    /**
     * 获取徽章CSS类
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::ONLINE => 'badge-info',
            self::OFFLINE => 'badge-primary',
            self::HYBRID => 'badge-success',
        };
    }

    /**
     * 获取徽章显示标签
     */
    public function getBadgeLabel(): string
    {
        return $this->getLabel();
    }
}
