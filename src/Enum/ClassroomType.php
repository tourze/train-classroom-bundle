<?php

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 教室类型枚举
 * 定义不同类型的培训教室
 */
enum ClassroomType: string implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    case PHYSICAL = 'PHYSICAL';   // 物理教室
    case VIRTUAL = 'VIRTUAL';     // 虚拟教室
    case HYBRID = 'HYBRID';       // 混合教室

    /**
     * 获取中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PHYSICAL => '物理教室',
            self::VIRTUAL => '虚拟教室',
            self::HYBRID => '混合教室',
        };
    }

    /**
     * 获取所有类型的选项数组
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
     * 判断是否需要物理空间
     */
    public function requiresPhysicalSpace(): bool
    {
        return self::PHYSICAL === $this || self::HYBRID === $this;
    }

    /**
     * 判断是否支持在线功能
     */
    public function supportsOnline(): bool
    {
        return self::VIRTUAL === $this || self::HYBRID === $this;
    }

    /**
     * 获取图标类名
     */
    public function getIconClass(): string
    {
        return match ($this) {
            self::PHYSICAL => 'fa-building',
            self::VIRTUAL => 'fa-desktop',
            self::HYBRID => 'fa-laptop',
        };
    }

    /**
     * 获取描述信息
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::PHYSICAL => '传统线下培训教室，需要学员到场参与',
            self::VIRTUAL => '在线虚拟教室，支持远程培训',
            self::HYBRID => '混合式教室，支持线上线下同时进行',
        };
    }
}
