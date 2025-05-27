<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Enum;

/**
 * 订单状态枚举
 */
enum OrderStatus: string
{

    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * 获取状态描述
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::PENDING => '待支付',
            self::PAID => '已支付',
            self::CANCELLED => '已取消',
            self::REFUNDED => '已退款',
        };
    }

    /**
     * 获取所有选项
     */
    public static function getOptions(): array
    {
        return [
            self::PENDING->value => self::PENDING->getDescription(),
            self::PAID->value => self::PAID->getDescription(),
            self::CANCELLED->value => self::CANCELLED->getDescription(),
            self::REFUNDED->value => self::REFUNDED->getDescription(),
        ];
    }

    /**
     * 检查是否为已支付状态
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    /**
     * 检查是否为待支付状态
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * 检查是否为已取消状态
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    /**
     * 检查是否为已退款状态
     */
    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }
} 