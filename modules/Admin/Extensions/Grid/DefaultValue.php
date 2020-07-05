<?php

namespace Modules\Admin\Extensions\Grid;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class DefaultValue extends AbstractDisplayer {
    public function display($val = '') {
        return (isset($this->value) && !is_null($this->value) && $this->value !== '') ? $this->value : $val;
    }
}
