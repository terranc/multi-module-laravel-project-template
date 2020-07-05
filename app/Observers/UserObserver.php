<?php

namespace App\Observers;

use App\Events\UserActionEvent;
use App\Models\User;

class UserObserver {
    public function updated(User $user): void {
        // 若密码有修改时
        if ($user->isDirty('password')) {
            // 发条短信通知用户
            // event('xxxxx');
        }
    }
}
