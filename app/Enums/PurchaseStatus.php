<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case DRAFT_SALE = 'draft';
    case ON_HOLD = 'on_hold';
    case AWAITING_COLLECTION = 'awaiting_collection';
    case OUT_FOR_COLLECTION = 'out_for_collection';
    case COLLECTED = 'collected';
    case COMPLETE = 'complete';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT_SALE => 'Draft Sale',
            self::ON_HOLD => 'On Hold',
            self::AWAITING_COLLECTION => 'Awaiting Collection',
            self::OUT_FOR_COLLECTION => 'Out for Collection',
            self::COLLECTED => 'Collected',
            self::COMPLETE => 'Complete',
            self::CANCELLED => 'Cancelled',
        };
    }
}