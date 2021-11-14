<?php


namespace Modules\Admin\Extensions\Grid;

use Encore\Admin\Grid;
use Encore\Admin\Grid\Exporter;

class CustomGrid extends Grid {
    protected array $abstractTool = [];

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

    public function appendCustomButton($button): CustomGrid {
        $this->abstractTool[] = $button;
        return $this;
    }

    public function renderCustomButton(): string {
        $html = '';
        foreach ($this->abstractTool as $v) {
            $html .= $v->render();
        }
        return $html;
    }
}
