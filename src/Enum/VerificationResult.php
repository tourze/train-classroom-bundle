<?php

namespace Tourze\TrainClassroomBundle\Enum;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 验证结果枚举
 * 定义考勤验证的结果状态
 */
enum VerificationResult: string
 implements Itemable, Labelable, Selectable{
    
    use ItemTrait;
    use SelectTrait;
case SUCCESS = 'SUCCESS';     // 验证成功
    case FAILED = 'FAILED';       // 验证失败
    case PENDING = 'PENDING';     // 待验证
    case TIMEOUT = 'TIMEOUT';     // 验证超时
    case ERROR = 'ERROR';         // 验证错误
    case DEVICE_ERROR = 'DEVICE_ERROR'; // 设备错误

    /**
     * 获取中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::SUCCESS => '验证成功',
            self::FAILED => '验证失败',
            self::PENDING => '待验证',
            self::TIMEOUT => '验证超时',
            self::ERROR => '验证错误',
            self::DEVICE_ERROR => '设备错误',
        };
    }

    /**
     * 获取所有结果的选项数组
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
     * 判断是否为成功状态
     */
    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }

    /**
     * 判断是否为失败状态
     */
    public function isFailure(): bool
    {
        return $this === self::FAILED || $this === self::TIMEOUT || $this === self::ERROR || $this === self::DEVICE_ERROR;
    }

    /**
     * 判断是否为待处理状态
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * 获取状态颜色类名
     */
    public function getColorClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'text-success',
            self::FAILED => 'text-danger',
            self::PENDING => 'text-warning',
            self::TIMEOUT => 'text-secondary',
            self::ERROR => 'text-danger',
            self::DEVICE_ERROR => 'text-danger',
        };
    }

    /**
     * 获取图标类名
     */
    public function getIconClass(): string
    {
        return match ($this) {
            self::SUCCESS => 'fa-check-circle',
            self::FAILED => 'fa-times-circle',
            self::PENDING => 'fa-clock',
            self::TIMEOUT => 'fa-hourglass-end',
            self::ERROR => 'fa-exclamation-triangle',
            self::DEVICE_ERROR => 'fa-cog',
        };
    }
} 