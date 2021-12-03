<?php

namespace Modules\Client;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ClientServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
    	$this->loadRoutesFrom(__DIR__.'/routes/api.php');
        $this->loadViewsFrom(__DIR__.'/views', 'client');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        
    }
}