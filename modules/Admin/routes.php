<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
    'as'         => config('admin.route.prefix') . '.',
], function(Router $router) {
    $router->post('common/upload', 'CommonController@upload');
    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/telescope', 'HomeController@telescope');
    $router->resource('users', 'UserController');
});


