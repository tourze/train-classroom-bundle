<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 培训类型枚举
 */
enum TrainType: string
 implements Itemable, Labelable, Selectable{
    
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
        return $this === self::ONLINE;
    }

    /**
     * 检查是否为线下培训
     */
    public function isOffline(): bool
    {
        return $this === self::OFFLINE;
    }

    /**
     * 检查是否为混合培训
     */
    public function isHybrid(): bool
    {
        return $this === self::HYBRID;
    }

    public function getLabel(): string
    {
        return match($this) {
            // TODO: 添加具体的标签映射
            default => $this->name,
        };
    }
} 