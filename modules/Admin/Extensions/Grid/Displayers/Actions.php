<?php

namespace Modules\Admin\Extensions\Grid\Displayers;


class Actions extends \Encore\Admin\Grid\Displayers\Actions {

    protected $editText = '编辑';
    protected $editIcon = 'edit';

    protected $viewText = '查看';
    protected $viewIcon = 'eye';

    protected $deleteText = '删除';
    protected $deleteIcon = 'trash';

    public function setEditText($val) {
        $this->editText = $val;
    }

    public function setEditIcon($val) {
        $this->editIcon = $val;
    }

    public function setViewText($val) {
        $this->viewText = $val;
    }

    public function setViewIcon($val) {
        $this->viewIcon = $val;
    }

    public function setDeleteText($val) {
        $this->deleteText = $val;
    }

    public function setDeleteIcon($val) {
        $this->deleteIcon = $val;
    }

    /**
     * Render view action.
     *
     * @return string
     */
    protected function renderView() {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->grid->getGridRowName()}-view">
    <i class="fa fa-{$this->viewIcon}"></i> {$this->viewText}
</a>
EOT;
    }

    /**
     * Render edit action.
     *
     * @return string
     */
    protected function renderEdit() {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}/edit" class="{$this->grid->getGridRowName()}-edit">
    <i class="fa fa-{$this->editIcon}"></i> {$this->editText}
</a>
EOT;
    }

    /**
     * Render delete action.
     *
     * @return string
     */
    protected function renderDelete() {
        $this->setupDeleteScript();

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="{$this->grid->getGridRowName()}-delete">
    <i class="fa fa-{$this->deleteIcon}"></i> {$this->deleteText}
</a>
EOT;
    }

}
