<?php

namespace Modules\Admin\Extensions\Grid;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class Fee extends AbstractDisplayer {
    public function display() {
        return '￥' . money_formatter($this->value);
    }
}
