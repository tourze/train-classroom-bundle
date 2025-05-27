<?php

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 报班的学习状态
 */
enum RegistrationLearnStatus: string implements Labelable, Itemable, Selectable
{
    use ItemTrait;
    use SelectTrait;

    case PENDING = 'pending';
    case LEARNING = 'learning';
    case FINISHED = 'finished';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => '未开始',
            self::LEARNING => '学习中',
            self::FINISHED => '已完成',
        };
    }
}
