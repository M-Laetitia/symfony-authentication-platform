<?php

namespace App\Enum;

enum ArticleType: string
{
    case DRAFT= 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case UNSAVED = 'unsaved';

}
