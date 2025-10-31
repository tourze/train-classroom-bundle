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
 * 排课类型枚举
 * 定义不同的排课类型
 */
enum ScheduleType: string implements BadgeInterface, Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    /**
     * 常规课程
     */
    case REGULAR = 'REGULAR';

    /**
     * 补课
     */
    case MAKEUP = 'MAKEUP';

    /**
     * 考试
     */
    case EXAM = 'EXAM';

    /**
     * 会议
     */
    case MEETING = 'MEETING';

    /**
     * 实训
     */
    case PRACTICE = 'PRACTICE';

    /**
     * 讲座
     */
    case LECTURE = 'LECTURE';

    /**
     * 获取类型描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::REGULAR => '常规课程',
            self::MAKEUP => '补课',
            self::EXAM => '考试',
            self::MEETING => '会议',
            self::PRACTICE => '实训',
            self::LECTURE => '讲座',
        };
    }

    /**
     * 获取所有类型选项
     * @return array<string, string>
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
     * 检查是否为教学类型
     */
    public function isTeaching(): bool
    {
        return in_array($this, [
            self::REGULAR,
            self::MAKEUP,
            self::PRACTICE,
            self::LECTURE,
        ], true);
    }

    /**
     * 检查是否为评估类型
     */
    public function isAssessment(): bool
    {
        return self::EXAM === $this;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::REGULAR => '常规课程',
            self::MAKEUP => '补课',
            self::EXAM => '考试',
            self::MEETING => '会议',
            self::PRACTICE => '实训',
            self::LECTURE => '讲座',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::REGULAR => BadgeInterface::PRIMARY,
            self::MAKEUP => BadgeInterface::WARNING,
            self::EXAM => BadgeInterface::DANGER,
            self::MEETING => BadgeInterface::INFO,
            self::PRACTICE => BadgeInterface::SUCCESS,
            self::LECTURE => BadgeInterface::SECONDARY,
        };
    }
}
