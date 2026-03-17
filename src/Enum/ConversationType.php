<?php

namespace App\Enum;

enum ConversationType: string
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';
    case DEFAULT = 'default'; 
}
