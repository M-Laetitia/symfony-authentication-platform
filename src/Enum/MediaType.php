<?php

namespace App\Enum;

enum MediaType: string
{
    case ARTICLE_COVER = 'aticle_cover';
    case GALLERY_IMAGE = 'gallery_image';
    case ARTICLE_IMAGE = 'article_image';
    case DEFAULT = 'default'; 
}
