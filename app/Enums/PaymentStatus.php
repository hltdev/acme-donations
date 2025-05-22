<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case INITIATED = 'initiated';
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
