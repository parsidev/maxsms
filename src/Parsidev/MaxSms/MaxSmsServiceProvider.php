<?php

namespace Parsidev\MaxSms;

use Illuminate\Support\ServiceProvider;

class MaxSmsServiceProvider extends ServiceProvider {

    protected $defer = true;

    public function boot() {
        $this->publishes([
            __DIR__ . '/../../config/maxsms.php' => config_path('maxsms.php'),
        ]);
    }

    public function register() {
        $this->app->singleton('maxsms', function($app) {
            $config = config('maxsms');
            return new MaxSms($config);
        });
    }

    public function provides() {
        return ['maxsms'];
    }

}