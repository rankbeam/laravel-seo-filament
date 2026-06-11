<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests\Fixtures\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    protected $guarded = [];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
