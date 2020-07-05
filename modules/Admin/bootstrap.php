<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 *
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Encore\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Encore\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */


Admin::css('/admin_assets/css/custom.css');
Admin::js("/admin_assets/js/custom.js");

Encore\Admin\Form::forget(['map', 'editor']);

Encore\Admin\Grid\Column::extend('customEditable', \Modules\Admin\Extensions\Grid\CustomEditable::class);
Encore\Admin\Grid\Column::extend('default', Modules\Admin\Extensions\Grid\DefaultValue::class);
Encore\Admin\Grid\Column::extend('fee', Modules\Admin\Extensions\Grid\Fee::class);
Encore\Admin\Form::extend('fee', Modules\Admin\Extensions\Form\Fee::class);
Encore\Admin\Form::extend('num', Modules\Admin\Extensions\Form\Num::class);
Encore\Admin\Form::extend('customTable', Modules\Admin\Extensions\Form\Table::class);
Encore\Admin\Grid\Column::extend('emp', Modules\Admin\Extensions\Grid\EmptyData::class);
Encore\Admin\Form::extend('scriptinjecter', Field\Interaction\ScriptInjecter::class);

\Encore\Admin\Admin::booted(function() {
    \Encore\Admin\Admin::js('/vendor/laravel-admin-ext/field-interaction/js/FieldHub.js');
    \Encore\Admin\Admin::js('/vendor/laravel-admin/AdminLTE/plugins/select2/i18n/zh-CN.js');
    \Encore\Admin\Admin::js('admin_assets/js/tim-js.js');
    \Encore\Admin\Admin::js('admin_assets/js/tim-usersig.min.js');
});
\Encore\Admin\Grid::init(function(\Encore\Admin\Grid $grid) {
    $grid->disableColumnSelector();
    $grid->disableRowSelector();
    $grid->disableExport();
    $grid->setActionClass(\Modules\Admin\Extensions\Grid\Displayers\Actions::class);
//    $grid->actions(function(\Modules\Admin\Extensions\Grid\Displayers\Actions $actions) {
//        $actions->disableView();
//    });
});
\Encore\Admin\Form::init(function(\Encore\Admin\Form $form) {
    $form->tools(function(\Encore\Admin\Form\Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
    });
    $form->disableViewCheck();
    $form->disableEditingCheck();
    $form->disableCreatingCheck();
});
\Encore\Admin\Show::init(function(\Encore\Admin\Show $show) {
    $show->panel()->tools(function(\Encore\Admin\Show\Tools $tools) {
        $tools->disableDelete();
        $tools->disableEdit();
    });
});
app('view')->prependNamespace('admin', resource_path('views/vendor/laravel-admin'));
