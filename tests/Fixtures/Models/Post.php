<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rankbeam\Seo\Traits\HasSEO;

class Post extends Model
{
    use HasSEO;

    protected $guarded = [];

    public function getUrlForSEO(): string
    {
        return 'https://example.test/blog/'.$this->slug.'?utm_source=admin';
    }

    /**
     * Ancestor relation walked by BreadcrumbSchema::fromModelAncestors().
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
