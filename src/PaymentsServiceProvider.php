<?php

namespace Bcismariu\Laravel\Payments;

use Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->loadMigrationsFrom($this->packagePath('migrations'));
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }


    protected function publishConfig()
    {
        $path = $this->packagePath('config/payments.php');
        $this->publishes([
            $path => config_path('payments.php')
        ], 'config');
    }

    protected function packagePath($path)
    {
        return __DIR__ . "/../$path";
    }
}