<?php

namespace JA\Lego;

use JA\Lego\Foundation\Asset;
use JA\Lego\Foundation\Cache;
use JA\Lego\Foundation\Session;
use Illuminate\Support\ServiceProvider;

class LegoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->publishConfigs();
        $this->publishAssets();

        $this->loadViewsFrom($this->path('resources/views'), 'ja-lego');

        $this->app->singleton('ja-lego-asset', Asset::class);
        $this->app->singleton('ja-lego-cache', Cache::class);
        $this->app->singleton('ja-lego-session', Session::class);

        parent::register();
    }

    protected function path($path)
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }

    protected function publishConfigs()
    {
        $config = $this->path('config/ja-lego.php');

        $this->publishes([$config => config_path('ja-lego.php')], 'ja-lego-config');

        $this->mergeConfigFrom($config, 'ja-lego');
    }

    protected function publishAssets()
    {
        $this->publishes(
            [
                $this->path('public/') => public_path(Asset::PATH),
            ],
            'ja-lego-assets'
        );
    }
}