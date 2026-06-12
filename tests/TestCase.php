<?php

declare(strict_types=1);

namespace Rankbeam\Seo\Filament\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Rankbeam\Seo\SEOServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\Facades\Filament;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Rankbeam\Seo\Filament\SeoFilamentServiceProvider;
use Rankbeam\Seo\Filament\Tests\Fixtures\AdminPanelProvider;
use Rankbeam\Seo\Filament\Tests\Fixtures\Models\User;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
        ]));

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function getPackageProviders($app): array
    {
        return [
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            // Filament's SupportServiceProvider must register before Livewire:
            // it rebinds Livewire's DataStore (bind() drops any existing
            // instance), and only Livewire's later mechanism registration
            // pins the resolved override as the shared instance — the same
            // order package discovery produces in a real app.
            SupportServiceProvider::class,
            LivewireServiceProvider::class,
            SchemasServiceProvider::class,
            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            SEOServiceProvider::class,
            SeoFilamentServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('filament.default_filesystem_disk', 'public');

        $app['config']->set('seo.site_name', 'Test Site');
        $app['config']->set('seo.title_suffix', ' | Test Site');
        $app['config']->set('seo.default_robots', 'index,follow');
        $app['config']->set('seo.default_og_image', null);
        $app['config']->set('seo.cache.store', 'array');
        $app['config']->set('seo.routes.enabled', false);
    }
}
