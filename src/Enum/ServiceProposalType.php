<?php

namespace App\Enum;

enum ServiceProposalType: string
{
    case PENDING = 'pending';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
