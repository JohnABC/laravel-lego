<?php

namespace JA\Lego\Widget;

use JA\Lego\Field\Field;
use JA\Lego\Widget\Concern\HasPagination;

class Filter extends Widget
{
    use HasPagination;

    /**
     * @var \JA\Lego\Widget\Grid
     */
    protected $grid;

    public function getButtonLocations()
    {
        return array_merge(parent::getButtonLocations(), ['']);
    }

    public function getGrid($syncFields = false)
    {
        $this->grid = $this->grid ?: new Grid($this);

        if ($syncFields) {
            $this->getFields()->each(
                function (Field $field) {
                    $this->grid->add($field->getName(), $field->getLabel());
                }
            );
        }

        return $this->grid;
    }

    public function getResult()
    {
        $this->processOnce();

        return $this->getQuery()->toArray();
    }

    public function process()
    {
        $this->processFields();

        $this->editableFields()->each(function (Field $field) {
            $field->placeholder($field->getLabel());
            $field->setRequestValue($this->getRequestData($field->getElementName()));

            if ($field->validRequestValue()) {
                $field->filter($this->query);
            }
        });

        if ($this->paginatorEnabled) {
            $this->paginator();
        }
    }

    public function render()
    {
        return view(config('ja-lego.widgets.filter.default-view'), ['filter' => $this]);
    }
}