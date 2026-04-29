<?php

namespace App\Enum;

enum ConversationReportType: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case DISMISSED = 'dismissed';
    case RESOLVED = 'resolved';
}
