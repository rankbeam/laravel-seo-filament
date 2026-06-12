<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Models;

use Rankbeam\Seo\Traits\HasSEO;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSEO;

    protected $guarded = [];

    public function getUrlForSEO(): string
    {
        return 'https://example.test/blog/'.$this->slug.'?utm_source=admin';
    }
}
