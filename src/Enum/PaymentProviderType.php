<?php

namespace App\Enum;

enum PaymentProviderType: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
}
