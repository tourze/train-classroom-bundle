<?php

namespace Tourze\TrainClassroomBundle\Enum;

/**
 * 考勤类型枚举
 * 定义学员考勤的不同类型
 */
enum AttendanceType: string
{
    case SIGN_IN = 'SIGN_IN';      // 签到
    case SIGN_OUT = 'SIGN_OUT';    // 签退
    case BREAK_OUT = 'BREAK_OUT';  // 休息外出
    case BREAK_IN = 'BREAK_IN';    // 休息返回

    /**
     * 获取中文描述
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::SIGN_IN => '签到',
            self::SIGN_OUT => '签退',
            self::BREAK_OUT => '休息外出',
            self::BREAK_IN => '休息返回',
        };
    }

    /**
     * 获取所有类型的选项数组
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
     * 判断是否为签到类型
     */
    public function isSignIn(): bool
    {
        return $this === self::SIGN_IN || $this === self::BREAK_IN;
    }

    /**
     * 判断是否为签退类型
     */
    public function isSignOut(): bool
    {
        return $this === self::SIGN_OUT || $this === self::BREAK_OUT;
    }
} 