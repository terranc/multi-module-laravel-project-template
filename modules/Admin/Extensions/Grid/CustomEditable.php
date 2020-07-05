<?php

namespace Modules\Admin\Extensions\Grid;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\Editable as BaseEditable;

class CustomEditable extends BaseEditable {
    public function display() {
        $this->options['name'] = $column = $this->column->getName();

        $class = 'grid-editable-' . str_replace(['.', '#', '[', ']'], '-', $column);

        $this->buildEditableOptions(func_get_args());

        $options = json_encode($this->options);

        Admin::script("$('.$class').editable($.extend($options, {'success': function() { $.isFunction(window.customEditableCallback) && window.customEditableCallback()}}));");

        $this->value = htmlentities($this->value);

        $attributes = [
            'href'       => '#',
            'class'      => "$class",
            'data-type'  => $this->type,
            'data-pk'    => "{$this->getKey()}",
            'data-url'   => "{$this->grid->resource()}/{$this->getKey()}",
            'data-value' => "{$this->value}",
        ];

        if (!empty($this->attributes)) {
            $attributes = array_merge($attributes, $this->attributes);
        }

        $attributes = collect($attributes)->map(function($attribute, $name) {
            return "$name='$attribute'";
        })->implode(' ');

        $html = $this->type === 'select' ? '' : $this->value;

        return "<a $attributes>{$html}</a>";
    }
}
