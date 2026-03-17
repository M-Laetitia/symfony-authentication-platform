<?php

namespace App\Enum;

enum InvoiceType: string
{
    case ISSUED = 'issued';
    case CANCELLED = 'cancelled';
}
