<?php

namespace App\Enum;

enum CancellationReasonType: string
{
    case CLIENT_REQUEST = 'client_request';
    case PHOTOGRAPHER_REQUEST = 'photographer_request';
    case PAYMENT_FAILED = 'payment_failed';
    case ADMIN = 'admin';
}
