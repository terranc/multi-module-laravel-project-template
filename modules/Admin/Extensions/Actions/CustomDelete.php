<?php

namespace Modules\Admin\Extensions\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class CustomDelete extends RowAction
{
    public $name = '<i class="fa fa-trash-o"></i> 删除 ';

    public function dialog() {
        $this->confirm('确认删除吗？');
    }

    public function handle(Model $model)
    {
        $model->delete();
        return $this->response()->success('删除')->refresh();
    }

}
