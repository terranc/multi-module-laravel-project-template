<?php
/**
 * Created by PhpStorm.
 * User: youxingxiang
 * Date: 2019/9/29
 * Time: 11:19 AM
 */

namespace App\Exceptions\Api;

use App\Exceptions\ApiException;

class ApiNotFoundException extends ApiException {

    public function render() {
        $this->noFound($this->getMessage());
        return parent::render();
    }
}
