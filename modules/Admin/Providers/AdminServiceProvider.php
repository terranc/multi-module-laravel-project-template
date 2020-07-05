<?php

namespace Modules\Admin\Providers;

use Modules\Admin\Http\Middleware\Pjax;

class AdminServiceProvider extends \Encore\Admin\AdminServiceProvider {
    /**
     * Boot the application events.
     *
     * @return void
     */
    public function register() {
        $this->routeMiddleware['admin.pjax'] = Pjax::class;

        parent::register();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [];
    }
}
