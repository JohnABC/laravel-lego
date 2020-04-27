<?php

namespace JA\Lego\Widget;

use JA\Lego\Field\Field;
use JA\Lego\Widget\Group\Group;
use JA\Lego\Widget\Concern\HasButtons;
use JA\Lego\Foundation\Response;
use JA\Lego\Foundation\Carrier\Carrier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;

abstract class Widget extends Carrier
{
    use HasButtons;

    const EVENT_ADD_FIELD_AFTER = 'add-field-after';

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $fields;

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $activeGroups = [];

    /**
     * @var array
     */
    protected $requestData = [];

    /**
     * @var \Illuminate\Contracts\View\View|string
     */
    protected $response;

    public function __construct($data, $id = null)
    {
        $this->fields = new Collection();

        $this->addFieldsMacro();

        parent::__construct($data, $id);
    }

    public function addFieldsMacro()
    {
        foreach (Field::registerFields() as $fieldName => $fieldClass) {
            static::macro('add' . $fieldName, function (...$args) use ($fieldClass) {
                return $this->addFieldByClassName($fieldClass, ...$args);
            });
        }
    }

    public function addField(Field $field)
    {
        $this->fields->put($field->getName(), $field);

        foreach ($this->getActiveGroups() as $group) {
            $group->addField($field->getName());
        }

        $this->fire(static::EVENT_ADD_FIELD_AFTER, [$field]);

        return $field;
    }

    protected function addFieldByClassName($class, $name, $description = null)
    {
        /* @var Field $field */
        $field = new $class($name, $description, $this->getStore());
        $field->setElementNamePrefix($this->getFieldElementNamePrefix());

        return $this->addField($field);
    }

    public function editableFields()
    {
        $ignored = [];
        foreach ($this->getGroups() as $group) {
            /* @var Group $group */
            if ($group->getCondition() && $group->getCondition()->fail()) {
                $ignored = array_merge($ignored, $group->getGroupFields());
            }
        }

        return $this->getFields()->filter(function (Field $field) use ($ignored) {
            return $field->isEditableMode() && !in_array($field->getName(), $ignored);
        });
    }

    protected function processFields()
    {
        $this->getFields()->each(function (Field $field) {
            $field->process();
        });
    }

    public function required($fields = [])
    {
        $fields = $fields ?: $this->getFields();
        foreach ($fields as $field) {
            /* @var Field $field */
            $field = is_string($field) ? $this->getField($field) : $field;
            $field->required();
        }

        return $this;
    }

    public function getField($name)
    {
        return $this->fields->get($name);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getFieldsValues($names = [])
    {
        $values = [];
        if (!$names) {
            foreach ($this->getFields() as $field) {
                $names[] = $field->getName();
            }
        } elseif (!is_array($names)) {
            $names = func_get_args();
        }

        foreach ($names as $name) {
            $values[] = $this->getField($name)->getRequestValue();
        }

        return $values;
    }

    protected function getFieldElementNamePrefix()
    {
        return '';
    }

    public function withRequestData($requestData)
    {
        $this->requestData = $requestData;

        return $this;
    }

    protected function getDefaultRequestData()
    {
        return Request::all();
    }

    public function getRequestData($key = null, $default = null)
    {
        $array = $this->requestData ?: $this->getDefaultRequestData();

        return $key ? $array[$key] ?? $default : $array;
    }

    public function getGroup($name)
    {
        if (!isset($this->groups[$name])) {
            $this->groups[$name] = new Group($name, $this->fields);
        }

        return $this->groups[$name];
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getActiveGroups()
    {
        return $this->activeGroups;
    }

    protected function startGroup($name)
    {
        if (isset($this->groups[$name]) && !isset($this->activeGroups[$name])) {
            $this->activeGroups[$name] = $this->groups[$name];
        }

        return $this;
    }

    protected function stopGroup($name = null)
    {
        if (is_null($name)) {
            array_pop($this->activeGroups);
        } else {
            unset($this->activeGroups[$name]);
        }

        return $this;
    }

    public function group($name, $callback = null)
    {
        $group = $this->getGroup($name);

        if ($callback) {
            $this->startGroup($name);
            call_user_func_array($callback, [$this, $group]);
            $this->stopGroup($name);

            return $group;
        }

        $this->startGroup($name);
        $this->events->once('after-add-field', __METHOD__ . $name, function () use ($name) {
            $this->stopGroup($name);
        });

        return $this;
    }

    public function when($field, $operator, $value, \Closure $closure)
    {
        $field = $field instanceof Field ? $field : $this->getField($field);

        $name = __METHOD__ . $field->getName() . $operator . md5(json_encode($value));

        $this->group($name, $closure);
        $this->getGroup($name)->condition($field, $operator, $value);

        return $this;
    }

    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->query->with($relations);

        return $this;
    }

    public function limit($limit)
    {
        $this->query->limit($limit);

        return $this;
    }

    public function orderBy($column, bool $desc = false)
    {
        $this->query->orderBy($column, $desc);

        return $this;
    }

    public function orderByDesc($column)
    {
        return $this->orderBy($column, true);
    }

    public function view(...$args)
    {
        return $this->response(View::make(...$args));
    }

    public function response($response)
    {
        $priorityResponse = Response::priorityResponse();
        if (!is_null($priorityResponse)) {
            return $priorityResponse;
        }

        $this->processOnce();

        if (!is_null($this->response)) {
            return value($this->response);
        }

        return $response;
    }
}