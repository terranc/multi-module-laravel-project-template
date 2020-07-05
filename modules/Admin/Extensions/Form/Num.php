<?php

namespace Modules\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

// 自定义数字数据框
class Num extends Field\Text {

    public function render() {
        $this->defaultAttribute('style', 'width: 119px; text-align: right;')
            ->defaultAttribute('type', 'number')
            ->default('0');

        return parent::render();
    }
}
