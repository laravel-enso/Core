<?php

namespace LaravelEnso\Core;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use LaravelEnso\ActionLogger\App\Http\Middleware\ActionLogger;
use LaravelEnso\Core\App\Commands\AnnounceAppUpdate;
use LaravelEnso\Core\App\Commands\ClearPreferences;
use LaravelEnso\Core\App\Commands\ResetStorage;
use LaravelEnso\Core\App\Commands\UpdateGlobalPreferences;
use LaravelEnso\Core\App\Commands\Upgrade;
use LaravelEnso\Core\App\Http\Middleware\VerifyActiveState;
use LaravelEnso\Core\App\Models\User;
use LaravelEnso\Impersonate\App\Http\Middleware\Impersonate;
use LaravelEnso\Localisation\App\Http\Middleware\SetLanguage;
use LaravelEnso\Permissions\App\Http\Middleware\VerifyRouteAccess;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        JsonResource::withoutWrapping();

        $this->loadMiddleware()
            ->loadDependencies()
            ->publishDependencies()
            ->publishResources()
            ->mapMorphs()
            ->commands(
                AnnounceAppUpdate::class,
                ClearPreferences::class,
                ResetStorage::class,
                UpdateGlobalPreferences::class,
                Upgrade::class
            );
    }

    private function loadMiddleware()
    {
        $this->app['router']->aliasMiddleware(
            'verify-active-state',
            VerifyActiveState::class
        );

        $this->app['router']->middlewareGroup('core', [
            VerifyActiveState::class,
            ActionLogger::class,
            Impersonate::class,
            VerifyRouteAccess::class,
            SetLanguage::class,
        ]);

        return $this;
    }

    private function loadDependencies()
    {
        $this->mergeConfigFrom(__DIR__.'/config/inspiring.php', 'enso.inspiring');

        $this->mergeConfigFrom(__DIR__.'/config/config.php', 'enso.config');

        $this->mergeConfigFrom(__DIR__.'/config/auth.php', 'enso.auth');

        $this->loadRoutesFrom(__DIR__.'/routes/api.php');

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-enso/core');

        return $this;
    }

    private function publishDependencies()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], ['core-config', 'enso-config']);

        $this->publishes([
            __DIR__.'/resources/preferences.json' => resource_path('preferences.json'),
        ], ['core-preferences', 'enso-preferences']);

        $this->publishes([
            __DIR__.'/database/factories' => database_path('factories'),
        ], ['core-factories', 'enso-factories']);

        $this->publishes([
            __DIR__.'/database/seeds' => database_path('seeds'),
        ], ['core-seeders', 'enso-seeders']);

        return $this;
    }

    private function publishResources()
    {
        $this->publishes([
            __DIR__.'/storage' => storage_path('app'),
        ], 'core-storage');

        $this->publishes([
            __DIR__.'/resources/images' => resource_path('images'),
        ], 'core-images');

        $this->publishes([
            __DIR__.'/resources/views/mail' => resource_path('views/vendor/mail'),
        ], 'enso-email');

        return $this;
    }

    private function mapMorphs()
    {
        Relation::morphMap([
            User::morphMapKey() => User::class,
        ]);

        return $this;
    }
}
