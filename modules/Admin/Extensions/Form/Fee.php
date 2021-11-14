<?php

namespace Modules\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class Fee extends Field\Currency {
    public function inputmask($options) {
        $options['onBeforeMask'] = <<<EOT
function(value, opts) {
    return (value / 100).toPrecision(12);
}
EOT;
        $options['onBeforePaste'] = <<<EOT
function(value, opts) {
    return value;
}
EOT;
        $options['onUnMask'] = <<<EOT
function(maskedValue, unmaskedValue, opts) {
    return parseInt((unmaskedValue * 100).toPrecision(12));
}
EOT;
        $options['groupSeparator'] = '';    // 临时解决带逗号数值转换的问题
        $options = json_encode_options($options);

        $this->script = "$('{$this->getElementClassSelector()}').inputmask($options);";

        return $this;
    }

    public function render() {
        $this->defaultAttribute('style', 'width: 130px')
            ->symbol('¥')
            ->defaultAttribute('value', old($this->elementName ?: $this->column, $this->value()));

        return parent::render();
    }
}
