<?php


namespace Modules\Admin\Extensions\Form;

use Encore\Admin\Admin;
use Encore\Admin\Form\Field;
use Encore\Admin\Form\NestedForm;

// 修复内嵌 Table 组件
class Table extends Field\Table
{
    protected function setupScriptForTableView($templateScript)
    {
        $removeClass = NestedForm::REMOVE_FLAG_CLASS;
        $defaultKey = NestedForm::DEFAULT_KEY_NAME;

        /**
         * When add a new sub form, replace all element key in new sub form.
         *
         * @example comments[new___key__][title]  => comments[new_{index}][title]
         *
         * {count} is increment number of current sub form count.
         */
        $script = <<<EOT
var index = 0;
$('#has-many-{$this->column}').on('click', '.add', function () {

    var tpl = $('template.{$this->column}-tpl');

    index++;

    var template = tpl.html().replace(/{$defaultKey}/g, index);
    $('.has-many-{$this->column}-forms').append(template);
    {$templateScript}
    return false;
});

$('#has-many-{$this->column}').on('click', '.remove', function () {
    var first_input_name = $(this).closest('.has-many-{$this->column}-form').find('input[name]:first').attr('name');
    if (first_input_name.match('{$this->column}\\\[new_')) {
        $(this).closest('.has-many-{$this->column}-form').remove();
    } else {
        $(this).closest('.has-many-{$this->column}-form').hide();
        $(this).closest('.has-many-{$this->column}-form').find('.$removeClass').val(1);
        $(this).closest('.has-many-{$this->column}-form').find('input').removeAttr('required');
    }
    return false;
});

EOT;

        Admin::script($script);
    }
}
