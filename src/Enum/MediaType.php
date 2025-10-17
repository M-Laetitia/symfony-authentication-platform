<?php

namespace App\Enum;

enum MediaType: string
{
    case COVER = 'cover';
    case GALLERY_IMAGE = 'gallery_image';
    case ARTICLE_IMAGE = 'article_image';
}