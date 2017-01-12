<?php

namespace HipsterJazzbo\Telegraph\Laravel;

use HipsterJazzbo\Telegraph\Push;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class TelegraphServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = function_exists('config_path') ? config_path('telegraph.php') : 'telegraph.php';

        $this->publishes([
            realpath(__DIR__ . '/../../config/telegraph.php') => $configPath
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Push::class, function (Application $app) {
            $config = $app['config']->get('telegraph');

            $serviceConfigs = array_get($config, 'services', []);
            $strict  = array_get($config, 'strict', false);

            return new Push($serviceConfigs, $strict);
        });
    }
}
