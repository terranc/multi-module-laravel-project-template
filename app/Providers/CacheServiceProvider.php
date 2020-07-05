<?php

namespace App\Providers;

use App\Services\CacheService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider implements DeferrableProvider {
    public function register() {
        $this->app->singleton('cache.service', function() {
            return new CacheService();
        });
    }

    public function provides() {
        return [
            'cache.service',
        ];
    }
}
