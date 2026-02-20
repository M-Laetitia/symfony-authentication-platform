<?php

namespace App\Enum;

enum ServiceProposalType: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
