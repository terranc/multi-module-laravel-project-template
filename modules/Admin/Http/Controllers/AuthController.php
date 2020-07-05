<?php

namespace Modules\Admin\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Form;

class AuthController extends BaseAuthController {
    public function getLogin() {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view('admin.login');
    }

    protected function settingForm() {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        //        $form->image('avatar', trans('admin.avatar'))->uniqueName();
        $form->password('password', trans('admin.password'))
            ->placeholder('不修改请留空')
            ->rules('confirmed|sometimes')
            ->attribute(['value' => '']);
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('sometimes')->default('');

        $form->setAction(admin_base_path('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function(Form $form) {
            if ($form->password) {
                if ($form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            } else {
                $form->password = $form->model()->password;
            }
        });

        $form->saved(function(Form $form) {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_base_path('auth/setting'));
        });

        return $form;
    }
}
