<?php

namespace App\Providers;

use App\Events\TestEvent;
use App\Listeners\TestListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Laravel\Telescope\Telescope;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TestEvent::class => [
            TestListener::class,
        ],
    ];
    protected $subscribe = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        \Event::listen('laravels.received_request', function(\Illuminate\Http\Request $request, $app) {
            if ($this->app->isLocal() && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
                $reflection = new \ReflectionClass(Telescope::class);
                $handlingApprovedRequest = $reflection->getMethod('handlingApprovedRequest');
                $handlingApprovedRequest->setAccessible(true);
                $handlingApprovedRequest->invoke(NULL, $app) ? Telescope::startRecording() : Telescope::stopRecording();
            }
        });
        parent::boot();
    }
}
