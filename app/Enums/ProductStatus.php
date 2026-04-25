<?php

namespace App\Enums;

enum ProductStatus: string
{
    case PENDING = 'pending';
    case RECEIVED = 'received';
    case STORED = 'stored';
    case LISTED = 'listed';
    case SOLD = 'sold';
    case CANCELLED = 'cancelled';
    case DISCARDED = 'discarded';

    public function label(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }
}