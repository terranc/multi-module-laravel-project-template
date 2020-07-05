<?php

namespace Modules\Admin\Http\Middleware;


use App\Exceptions\SwooleExitException;
use Encore\Admin\Middleware\Pjax as AdminPjax;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Pjax extends AdminPjax {

    public static function respond(Response $response) {
        $next = function() use ($response) {
            return $response;
        };

        throw new SwooleExitException((new static())->handle(Request::capture(), $next));
    }
}
