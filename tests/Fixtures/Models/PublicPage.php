<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rankbeam\Seo\Traits\HasSEO;

/**
 * The model that actually carries the SEO metadata. An {@see Article} edits
 * its SEO through a related PublicPage (the T4 target-abstraction scenario).
 */
class PublicPage extends Model
{
    use HasSEO;

    protected $guarded = [];

    public function getUrlForSEO(): string
    {
        return 'https://example.test/'.$this->path.'?utm_source=admin';
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Ancestor relation walked by BreadcrumbSchema::fromModelAncestors().
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
