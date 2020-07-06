<?php

namespace Modules\Common\Http\Controllers;

use App\Exceptions\Api\ApiNotFoundException;
use App\Exceptions\DontReportApiException;
use App\Models\User;
use Illuminate\Http\Request;

class CommonController extends Controller {
    public function ping(Request $request) {
        if ($request->error) {
            throw new ApiNotFoundException('无数据');
        }
        return $this->success('SUCCESS', User::first());
    }
}
