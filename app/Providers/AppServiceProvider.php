<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\ActivityConfig;
use App\Models\ActivityExtend;
use App\Models\ActivityStage;
use App\Models\DirtyWords;
use App\Models\GoodWords;
use App\Models\LiveProduct;
use App\Models\Merchant;
use App\Models\Redpacket;
use App\Models\Room;
use App\Models\SettlementLog;
use App\Models\Staff;
use App\Models\StaffMoneyLog;
use App\Models\Url;
use App\Models\User;
use App\Models\UserMoneyLog;
use App\Observers\ActivityConfigObserver;
use App\Observers\ActivityExtendObserver;
use App\Observers\ActivityObserver;
use App\Observers\AdminConfigObserver;
use App\Observers\DirtyWordsObserver;
use App\Observers\GoodWordsObserver;
use App\Observers\LiveProductObserver;
use App\Observers\MerchantObserver;
use App\Observers\RedpacketObserver;
use App\Observers\RoomObserver;
use App\Observers\SettlementLogObserver;
use App\Observers\StaffMoneyLogObserver;
use App\Observers\StaffObserver;
use App\Observers\StageObserver;
use App\Observers\UrlObserver;
use App\Observers\UserMoneyLogObserver;
use App\Observers\UserObserver;
use Encore\Admin\Config\ConfigModel;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        if ($this->app->isLocal() && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        bcscale(2);
        Blade::withoutComponentTags();
        Redis::enableEvents();
        JsonResource::withoutWrapping();
        User::observe(UserObserver::class);
    }
}
