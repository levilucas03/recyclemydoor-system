<?php

namespace App\Enums;

enum SaleStatus: string
{
    case DRAFT_SALE = 'draft';
    case ON_HOLD = 'on_hold';
    case AWAITING_DELIVERY = 'awaiting_delivery';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case COMPLETE = 'complete';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT_SALE => 'Draft Sale',
            self::ON_HOLD => 'On Hold',
            self::AWAITING_DELIVERY => 'Awaiting Delivery',
            self::OUT_FOR_DELIVERY => 'Out for Delivery',
            self::DELIVERED => 'Collected',
            self::COMPLETE => 'Complete',
            self::CANCELLED => 'Cancelled',
        };
    }
}