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
 * 订单状态枚举
 */
enum OrderStatus: string implements BadgeInterface, Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
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
     * @return array<string, string>
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
        return self::PAID === $this;
    }

    /**
     * 检查是否为待支付状态
     */
    public function isPending(): bool
    {
        return self::PENDING === $this;
    }

    /**
     * 检查是否为已取消状态
     */
    public function isCancelled(): bool
    {
        return self::CANCELLED === $this;
    }

    /**
     * 检查是否为已退款状态
     */
    public function isRefunded(): bool
    {
        return self::REFUNDED === $this;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => '待支付',
            self::PAID => '已支付',
            self::CANCELLED => '已取消',
            self::REFUNDED => '已退款',
        };
    }

    /**
     * 获取徽章类型
     */
    public function getBadge(): string
    {
        return match ($this) {
            self::PENDING => BadgeInterface::WARNING,
            self::PAID => BadgeInterface::SUCCESS,
            self::CANCELLED => BadgeInterface::DANGER,
            self::REFUNDED => BadgeInterface::SECONDARY,
        };
    }

    /**
     * 获取徽章CSS类
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::PAID => 'badge-success',
            self::CANCELLED => 'badge-danger',
            self::REFUNDED => 'badge-secondary',
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
