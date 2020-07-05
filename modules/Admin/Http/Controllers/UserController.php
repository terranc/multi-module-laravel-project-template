<?php

namespace Modules\Admin\Controllers;

use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Modules\Admin\Extensions\Actions\CustomDelete;
use Modules\Admin\Extensions\Grid\Displayers\Actions;

class UserController extends AdminController {

    protected $title = '用户列表';

    protected function grid()
    {
        $grid = new Grid(new User);

        $grid->column('id', __('ID'));
        $grid->column('email', __('Email'));
        $grid->column('name', __('名字'));
        $grid->column('created_at', __('创建时间'));

        $grid->actions(function(Actions $actions) {
            $actions->disableDelete();
            // 添加自定义操作
            $actions->append((new CustomDelete())->setGrid($actions->getGrid())->setRow($actions->row)->render());
        });


        return $grid;
    }

    protected function form()
    {
        $form = new Form(new User());
        $form->text('email', '邮箱')->rules(['required', 'unique:users,email,{{id}}']);
        $form->text('name', '名字')->rules(['required', 'unique:users,name,{{id}}']);
        $form->password('password', '密码')
            ->attribute(['value' => ''])->creationRules(['required'])->updateRules('sometimes');

        $form->saving(function($form) {
            if ($form->password) {
                $form->password = bcrypt($form->password);
            } else {
                $form->password = $form->model()->password;
            }
        });

        return $form;
    }
}
