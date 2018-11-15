<?php

namespace Febalist\LaravelSelectel;

use ArgentCrusade\Selectel\CloudStorage\CloudStorage;
use ArgentCrusade\Selectel\CloudStorage\Container;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;

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
            $api = new ApiClient($config['username'], $config['password'], $config['logs'] ?? false);

            $api->authenticate();

            $storage = new CloudStorage($api);

            /** @var Container $container */
            $container = $storage->getContainer($config['container']);

            if ($domain = $config['domain']) {
                $protocol = $config['ssl'] ? 'https' : 'http';
                $container->setUrl("$protocol://$domain");
            }

            return new Filesystem(new SelectelAdapter($container));
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
