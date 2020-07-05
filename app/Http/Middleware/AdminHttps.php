<?php
/**
 * Created by PhpStorm.
 * User: liugang
 * Date: 2020-01-14
 * Time: 13:52
 */

namespace App\Http\Middleware;


use Closure;

class AdminHttps {
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (config('admin.https') || config('admin.secure')) {
            url()->forceScheme('https');
            $request->server->set('HTTPS', true);
        }
        return $next($request);
    }
}
