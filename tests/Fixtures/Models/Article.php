<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An entity whose canonical SEO lives on a RELATED model. Article itself does
 * NOT use the core HasSEO trait — so a test can prove the SEO section writes to
 * the related {@see PublicPage} and never to the article.
 */
class Article extends Model
{
    protected $guarded = [];

    /**
     * The related model that carries this article's SEO.
     */
    public function publicPage(): HasOne
    {
        return $this->hasOne(PublicPage::class);
    }
}
