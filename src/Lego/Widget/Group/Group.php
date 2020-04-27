<?php

namespace JA\Lego\Widget\Group;

use JA\Lego\Widget\Field;

class Group
{
    protected $name;
    protected $widgetFields;
    protected $groupFields = [];
    protected $condition;

    public function __construct($name, $widgetFields)
    {
        $this->name = $name;
        $this->widgetFields = $widgetFields;
    }

    public function getWidgetFields()
    {
        return $this->widgetFields->filter(function (Field $field) {
            return isset($this->groupFields[$field->getName()]);
        });
    }

    public function getGroupFields()
    {
        return $this->groupFields;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function addField($field)
    {
        $fields = is_array($field) ? $field : (func_num_args() > 1 ? func_get_args() : [$field]);
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $this->groupFields[$field->getName()] = $field->getName();
            } else {
                $this->groupFields[$field] = $field;
            }
        }

        return $this;
    }

    public function readonly($condition = true)
    {
        return $condition ? $this->callFieldsMethod('readonly') : $this;
    }

    public function required($condition = true)
    {
        return $condition ? $this->callFieldsMethod('required') : $this;
    }

    protected function callFieldsMethod($method, $params = [])
    {
        $this->getWidgetFields()->each(function (Field $field) use ($method, $params) {
            call_user_func_array([$field, $method], $params);
        });

        return $this;
    }

    public function condition($field, $operator, $expected)
    {
        $field = $field instanceof Field ? $field : $this->fields[$field];
        $this->condition = new Condition($field, $operator, $expected);

        LegoAssets::js('field/condition-group.js');

        return $this;
    }

    public function __toString()
    {
        return view('ja-lego::default.group', ['group' => $this])->render();
    }
}