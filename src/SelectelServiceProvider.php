<?php

namespace Febalist\LaravelSelectel;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class SelectelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('selectel', function ($app, $config) {

        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
