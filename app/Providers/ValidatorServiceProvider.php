<?php
/**
 * Created by PhpStorm.
 * User: terranc
 * Date: 2018/9/25
 * Time: 11:21
 */

namespace App\Providers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider {

    public function boot(Request $request) {
        // 添加时间戳验证规则
        Validator::extend('timestamp', function($attribute, $value, $parameters, $validator) {
            return ((string)(int)$value === $value) && ($value <= PHP_INT_MAX) && ($value >= ~PHP_INT_MAX);
        });

    }

    public function register() {
        //
    }
}
