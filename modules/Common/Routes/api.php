<?php
Route::prefix('api/common/v1')->group(function ($router) {
    $router->get('/ping', 'CommonController@ping');
});
