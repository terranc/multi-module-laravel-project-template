<?php

namespace Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Layout\Content;

class HomeController extends Controller {
    public function index(Content $content) {
        return redirect(config('admin.route.prefix') . '/users');
    }

    public function telescope() {
        return redirect('/telescope');
    }
}
