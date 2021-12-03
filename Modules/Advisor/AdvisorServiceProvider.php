<?php

namespace Modules\Advisor;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class AdvisorServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
    	$this->loadRoutesFrom(__DIR__.'/routes/api.php');
        $this->loadViewsFrom(__DIR__.'/views', 'advisor');
    	
    	// $this->mergeConfigFrom(
     //        __DIR__.'/config/advisor_config.php', 'advisor'
     //    );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // $this->loadViewsFrom(__DIR__.'/Http/Controllers', 'advisor');
        
    }
}