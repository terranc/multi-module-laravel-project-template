<?php

namespace Modules\Admin\Extensions\Grid;

use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class EmptyData extends AbstractDisplayer {
    public function display() {
        return $this->value ?: '-';
    }
}
