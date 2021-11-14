<?php

namespace Modules\Api\Providers;

use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ApiServiceProvider extends ServiceProvider {
    /**
     * Boot the application events.
     *
     * @return void
     */
//    public function boot() {
        //        $this->registerTranslations();
        //        $this->registerConfig();
        //        $this->registerViews();
        //        $this->registerFactories();
        //        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
//    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        Route::middleware('api')->namespace('Modules\Api\Http\Controllers')->group(__DIR__ . '/../Routes/api.php');
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
//    public function provides() {
//        return [];
//    }
}
