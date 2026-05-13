<?php

namespace App\Enum;

enum PricingPlanType: string
{
    case BASIC = 'basic';
    case STANDARD = 'standard';
    case PREMIUM = 'premium';
}
