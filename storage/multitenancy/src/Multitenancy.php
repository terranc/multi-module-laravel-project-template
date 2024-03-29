<?php

namespace Encore\Admin\Multitenancy;

use Encore\Admin\Controllers\AuthController;
use Encore\Admin\Extension;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;

class Multitenancy extends Extension {
    /**
     * @var string
     */
    public $name = 'multitenancy';

    /**
     * @var string
     */
    protected static $middleware = '';

    /**
     * Register a laravel-admin tenancy.
     *
     * @param string $name
     * @param array  $config
     */
    public static function register($name, array $config) {
        static::$middleware = "multitenancy:$name";

        static::registerAuthRoutes($config);

        static::loadTenancyRoutes($config);
    }

    /**
     * Register auth routes for tenancy.
     *
     * @param array $config
     */
    public static function registerAuthRoutes(array $config) {
        $attributes = [
            'domain'     => Arr::get($config, 'route.domain'),
            'prefix'     => Arr::get($config, 'route.prefix'),
            'middleware' => static::prependMiddleware(Arr::get($config, 'route.middleware')),
        ];
        app('router')->group($attributes, function(Router $router) use ($config) {
            $router->namespace('\Encore\Admin\Controllers')->group(function(Router $router) use ($config) {
                $router->resource('auth/users', 'UserController');
                $router->resource('auth/roles', 'RoleController');
                $router->resource('auth/permissions', 'PermissionController');
                $router->resource('auth/menu', 'MenuController', ['except' => ['create']]);
                $router->resource('auth/logs', 'LogController', ['only' => ['index', 'destroy']]);

//                $router->post('_handle_form_', 'HandleController@handleForm')->name('admin.handle-form');
//                $router->post('_handle_action_', 'HandleController@handleAction')->name('admin.handle-action');
//                $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('admin.handle-selectable');
//                $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('admin.handle-renderable');
                $router->group([
                    'as' => Arr::get($config, 'route.prefix') . '.',
                ], function(Router $router) {
                    $router->post('_handle_form_', 'HandleController@handleForm')->name('handle-form');
                    $router->post('_handle_action_', 'HandleController@handleAction')->name('handle-action');
                    $router->get('_handle_selectable_', 'HandleController@handleSelectable')->name('handle-selectable');
                    $router->get('_handle_renderable_', 'HandleController@handleRenderable')->name('handle-renderable');
                });
            });

            $authController = Arr::get($config, 'auth.controller', AuthController::class);
            $router->get('auth/login', $authController . '@getLogin');
            $router->post('auth/login', $authController . '@postLogin');
            $router->get('auth/logout', $authController . '@getLogout');
            $router->get('auth/setting', $authController . '@getSetting');
            $router->put('auth/setting', $authController . '@putSetting');
        });
    }

    /**
     * Load tenancy routes.
     *
     * @param array $config
     */
    protected static function loadTenancyRoutes(array $config) {
        $routePath = ucfirst($config['directory']) . DIRECTORY_SEPARATOR . 'routes.php';

        if (file_exists($routePath)) {
            if (!app()->routesAreCached()) {

                $attributes = $config['route'];
                $attributes['middleware'] = static::prependMiddleware($attributes['middleware']);

                app('router')->group($attributes, function() use ($routePath) {
                    require $routePath;
                });
            }
        }
    }

    /**
     * Prepend a multitenancy middleware.
     *
     * @param array $middleware
     *
     * @return array
     */
    protected static function prependMiddleware(array $middleware): array {
        array_unshift($middleware, static::$middleware);

        return $middleware;
    }
}
