<?php

namespace Modules\Admin\Http\Controllers;

use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;

class CommonController extends AdminController {
    public function upload(Request $request) {
        $file = $request->file('file');
        $disk = \Storage::disk(config('filesystems.default'));
        $ret = $disk->put('post/' . date('Ym'), $file);
        if ($ret) {
            return response()->json([
                'success'   => true,
                'msg'       => '上传成功!',
                'file_path' => $ret,
            ]);
        } else {
            return response()->json([
                'success'   => false,
                'msg'       => '上传失败!',
                'file_path' => NULL,
            ]);
        }
    }
}
