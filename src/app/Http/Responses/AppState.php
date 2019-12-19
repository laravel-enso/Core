<?php

namespace LaravelEnso\Core\app\Http\Responses;

use Route;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use LaravelEnso\Core\app\Enums\Themes;
use LaravelEnso\Core\app\Services\Inspiring;
use LaravelEnso\Core\app\Services\LocalState;
use LaravelEnso\Enums\app\Facades\Enums;
use LaravelEnso\Enums\app\Services\Enum;
use LaravelEnso\Helpers\app\Classes\JsonParser;
use LaravelEnso\Localisation\app\Models\Language;
use LaravelEnso\Menus\app\Services\TreeBuilder;
use LaravelEnso\Roles\app\Enums\Roles;

class AppState implements Responsable
{
    public function toResponse($request)
    {
        return $this->state();
    }

    private function state()
    {
        $response = $this->response();

        unset(Auth::user()->role);

        return $response;
    }

    private function response()
    {
        Enum::localisation(false);

        $languages = Language::active()
            ->get(['name', 'flag', 'is_rtl']);

        $langs = $languages->pluck('flag', 'name');

        return [
            'user' => Auth::user()->load(['person', 'avatar']),
            'preferences' => Auth::user()->preferences(),
            'i18n' => $this->i18n($langs),
            'languages' => $langs,
            'rtl' => $this->rtl($languages),
            'themes' => Themes::all(),
            'routes' => $this->routes(),
            'implicitRoute' => Auth::user()->role->menu->permission->name,
            'menus' => (new TreeBuilder())->handle(),
            'impersonating' => session()->has('impersonating'),
            'websockets' => [
                'pusher' => [
                    'key' => config('broadcasting.connections.pusher.key'),
                    'options' => config('broadcasting.connections.pusher.options'),
                ],
                'channels' => [
                    'privateChannel' => $this->privateChannel(),
                    'ioChannel' => $this->ioChannel(),
                    'appUpdates' => 'app-updates',
                ],
            ],
            'meta' => $this->meta(),
            'enums' => Enums::all(),
            'local' => App::make(LocalState::class)->build(),
        ];
    }

    private function i18n($languages)
    {
        return $languages->keys()
            ->reject(fn($lang) => $lang === 'en')
            ->reduce(function ($i18n, $lang) {
                $i18n[$lang] = (new JsonParser(
                    resource_path('lang'.DIRECTORY_SEPARATOR.$lang.'.json')
                ))->object();

                return $i18n;
            }, []);
    }

    private function rtl($languages)
    {
        return $languages->filter(fn($lang) => $lang->is_rtl)
            ->pluck('name');
    }

    private function meta()
    {
        return [
            'appName' => config('app.name'),
            'appUrl' => url('/').'/',
            'version' => config('enso.config.version'),
            'quote' => Inspiring::quote(),
            'env' => app()->environment(),
            'dateFormat' => config('enso.config.dateFormat'),
            'dateTimeFormat' => config('enso.config.dateFormat').' H:i:s',
            'extendedDocumentTitle' => config('enso.config.extendedDocumentTitle'),
            'csrfToken' => csrf_token(),
            'ravenKey' => config('enso.config.ravenKey'),
        ];
    }

    private function routes()
    {
        return Auth::user()->role
            ->permissions()
            ->pluck('name')
            ->reduce(function ($collection, $permission) {
                $route = Route::getRoutes()->getByName($permission);

                $collection[$permission] = $route
                    ? collect($route)->only(['uri', 'methods'])
                        ->put('domain', $route->domain())
                    : null;

                return $collection;
            }, []);
    }

    private function privateChannel()
    {
        return collect(
            explode('\\', config('auth.providers.users.model'))
        )->push(Auth::user()->id)->implode('.');
    }

    private function ioChannel()
    {
        $roles = App::make(Roles::class);

        return collect([$roles::Admin, $roles::Supervisor])
            ->contains(Auth::user()->role_id)
            ? 'operations'
            : 'operations'.Auth::user()->id;
    }
}
