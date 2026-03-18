<?php

namespace App\Enum;

enum MediaType: string
{
    case ARTICLE_COVER = 'article_cover';
    case ARTICLE_IMAGE = 'article_image';

    case AVATAR = 'avatar';

    case PORTFOLIO_COVER = 'portfolio_cover';        
    case PORTFOLIO_FEATURED = 'portfolio_featured';  
    case GALLERY_SERIES = 'gallery_series';

    case DEFAULT = 'default'; 
}
