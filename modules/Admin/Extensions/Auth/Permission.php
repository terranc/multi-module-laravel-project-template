<?php

namespace Modules\Admin\Extensions\Auth;

use Encore\Admin\Facades\Admin;
use Modules\Admin\Http\Middleware\Pjax;

class Permission extends \Encore\Admin\Auth\Permission
{

    /**
     * Send error response page.
     */
    public static function error()
    {
        $response = response(Admin::content()->withError(trans('admin.deny')));

        if (!request()->pjax() && request()->ajax()) {
            abort(403, trans('admin.deny'));
        }

        Pjax::respond($response);
    }
}
