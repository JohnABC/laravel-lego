<?php

namespace JA\Lego\Widget;

use Mobile_Detect;
use JA\Lego\Widget\Concern\HasPagination;
use JA\Lego\Widget\Grid\Concern\HasCells;
use JA\Lego\Widget\Grid\Concerns\HasBatch;

class Grid extends Widget
{
    use HasCells,
        HasBatch,
        HasPagination;

    /**
     * @var \JA\Lego\Widget\Filter
     */
    protected $filter;

    /**
     * @var bool
     */
    protected $responsive = true;

    public function __construct($data, $id = null)
    {
        parent::__construct($data, $id);

        $this->setResponsive(config('ja-lego.widgets.grid.responsive'));
    }

    protected function initData()
    {
        if ($this->data instanceof Filter) {
            $this->filter = $this->data;
            $this->filter->processOnce();

            $this->data = $this->filter->getQuery();
        }

        parent::initData();
    }

    public function getResponsive()
    {
        return $this->responsive;
    }

    public function setResponsive($condition = true)
    {
        $this->responsive = boolval($condition);

        return $this;
    }

    public function render()
    {
        $template = $this->responsive && app(Mobile_Detect::class)->isMobile() ? '-mobile' : '';

        return view(config('ja-lego.widgets.grid.default-view' . $template))->with('grid', $this)->render();
    }

    public function process()
    {
        // TODO: Implement process() method.
    }
}