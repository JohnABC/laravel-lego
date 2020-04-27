<?php

namespace JA\Lego\Widget\Concern;

use JA\Lego\Field\Field;
use Illuminate\Support\Collection;

trait HasFields
{
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    protected function initHasFields()
    {
        $this->fields = new Collection();
    }

    public function registerFieldsMacro($macroCallback)
    {
        foreach (Field::registerFields() as $fieldName => $fieldClass) {
            call_user_func($macroCallback, 'add' . $fieldName, function (...$args) use ($fieldClass) {
                return $this->addFieldByClassName($fieldClass, ...$args);
            });
        }
    }

    public function addField(Field $field)
    {
        $this->fields->put($field->getName(), $field);

        return $field;
    }

    private function addFieldByClassName($class, $name, $description = null)
    {
        /** @var Field $field */
        $field = new $class($name, $description, $this->getStore());
        $field->setElementNamePrefix($this->getFieldElementNamePrefix());

        return $this->addField($field);
    }

    /**
     * 为避免同一页面有多个控件时的.
     */
    abstract protected function getFieldElementNamePrefix();

    /**
     * all fields.
     *
     * @return Collection|Field[]|Fields
     */
    public function fields()
    {
        return $this->fields->fields();
    }

    public function values($fields = [])
    {
        $values = [];
        if (!$fields) {
            foreach ($this->fields() as $field) {
                $values[] = $field->getNewValue();
            }

            return $values;
        }

        $fields = is_array($fields) ? $fields : func_get_args();
        foreach ($fields as $field) {
            $values[] = $this->field($field)->getNewValue();
        }

        return $values;
    }

    /**
     * only editable fields.
     *
     * @return Collection|Field[]
     */
    public function editableFields()
    {
        $ignored = [];
        foreach ($this->groups() as $group) {
            if ($group->getCondition() && $group->getCondition()->fail()) {
                $ignored = array_merge($ignored, $group->fieldNames());
            }
        }

        return $this->fields()->filter(function (Field $field) use ($ignored) {
            return $field->isEditable() && !in_array($field->name(), $ignored);
        });
    }

    /**
     * 根据 name 获取指定 Field.
     *
     * @param $fieldName
     *
     * @return Field|null
     */
    public function field($fieldName)
    {
        return $this->fields->field($fieldName);
    }

    protected function processFields()
    {
        $this->fields()->each(function (Field $field) {
            $field->process();
        });
    }

    /**
     * Mark Fields as Required.
     *
     * @param array[]|Field[] $fields
     *
     * @return $this
     */
    public function required($fields = [])
    {
        $fields = $fields ?: $this->fields();

        foreach ($fields as $field) {
            if (is_string($field)) {
                $this->field($field)->required();
                continue;
            }

            /* @var Field $field */
            $field->required();
        }

        return $this;
    }
}
