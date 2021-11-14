<?php

namespace Modules\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class Num extends Field\Text {

    public function render() {
        $this->defaultAttribute('style', 'width: 119px; text-align: right;')
            ->defaultAttribute('type', 'number')
            ->defaultAttribute('value', old($this->elementName ?: $this->column, $this->value()))
            ->defaultAttribute('class', 'form-control '.$this->getElementClassString())
            ->default('0');

        return parent::render();
    }

    public function min($value)
    {
        $this->attribute('min', $value);

        return $this;
    }

    public function max($value)
    {
        $this->attribute('max', $value);

        return $this;
    }
}
