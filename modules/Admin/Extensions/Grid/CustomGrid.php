<?php


namespace Modules\Admin\Extensions\Grid;

use Closure;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Exporter;
use Encore\Admin\Grid\Model;
use Illuminate\Database\Eloquent\Model as Eloquent;

class CustomGrid extends Grid {
    public function __construct(Eloquent $model, Closure $builder = NULL) {
        $this->model = new Model($model, $this);
        $this->keyName = $model->getKeyName();
        $this->builder = $builder;

        $this->initialize();

        // $this->handleExportRequest();

        $this->callInitCallbacks();
    }

    public function handleCustomExportRequest() {
        $scope = request(Exporter::$queryName);

        // clear output buffer.
        if (ob_get_length()) {
            ob_end_clean();
        }

        $this->model()->usePaginate(false);

        if ($this->builder) {
            call_user_func($this->builder, $this);

            return $this->getExporter($scope)->export();
        }

        return $this->getExporter($scope)->export();
    }
}
