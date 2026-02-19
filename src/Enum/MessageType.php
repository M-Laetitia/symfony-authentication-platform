<?php

namespace App\Enum;

enum MessageType: string
{
    case READ = 'read';
    case UNREAD = 'unread';
}
