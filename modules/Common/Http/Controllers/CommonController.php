<?php

namespace Modules\Common\Http\Controllers;

use App\Exceptions\Api\ApiNotFoundException;

class CommonController extends Controller {
    public function ping() {
        throw new ApiNotFoundException('无可用记录');
        return $this->success('SUCCESS');
    }
}
