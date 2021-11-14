<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware(['accept-language'])->group(function(\Illuminate\Routing\Router $router) {
    $router->get('projects/{id}/faq', [\Modules\Api\Http\Controllers\FaqController::class, 'listByProject']);
    $router->get('categories', [\Modules\Api\Http\Controllers\CategoryController::class, 'lists']);
    $router->get('categories/{id}/projects', [\Modules\Api\Http\Controllers\ProjectController::class, 'listsByCategory']);
    $router->get('projects', [\Modules\Api\Http\Controllers\ProjectController::class, 'lists']);
    $router->get('projects/{id}', [\Modules\Api\Http\Controllers\ProjectController::class, 'show']);
    $router->any('notify', [\Modules\Api\Http\Controllers\Controller::class, 'notify']);
    $router->post('account/auth', [\Modules\Api\Http\Controllers\AccountController::class, 'auth']);
    $router->post('account/login', [\Modules\Api\Http\Controllers\AccountController::class, 'login']);
    $router->get('configs', [\Modules\Api\Http\Controllers\Controller::class, 'configs']);
    $router->get('projects/{id}/faq', [\Modules\Api\Http\Controllers\ProjectController::class, 'faq']);

    $router->middleware('check.login')->group(function(\Illuminate\Routing\Router $router) {
        $router->post('projects', [\Modules\Api\Http\Controllers\ProjectController::class, 'apply']);
        $router->get('orders', [\Modules\Api\Http\Controllers\OrderController::class, 'lists']);
    });
});
