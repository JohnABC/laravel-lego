<?php

namespace JA\Lego\Widget\Grid;

use JA\Lego\Foundation\QS;
use JA\Lego\Foundation\Arr;
use JA\Lego\Foundation\Store\Store;
use Illuminate\Support\HtmlString;

class Cell
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \JA\Lego\Foundation\Store\Store
     */
    protected $store;

    /**
     * @var mixed
     */
    protected $defaultValue;

    protected $pipes = [];

    public function __construct($name, $description)
    {
        $pipes = explode('|', $name);
        $this->name = $pipes[0];
        $this->description = $description;

        foreach (array_slice($pipes, 1) as $pipe) {
            $this->pipe($pipe);
        }
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }

    public function default($value)
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function value()
    {
        return $this->getPipedValue();
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getOriginalValue()
    {
        return $this->store->get($this->name);
    }

    public function getPipedValue()
    {
        $value = Arr::notNullValue($this->getOriginalValue(), $this->getDefaultValue());
        foreach ($this->pipes as $pipe) {
            $value = $pipe->handle($value, $this->data, $this);
        }

        return new HtmlString((string) $value);
    }

    public function getPipedPlainValue()
    {
        return strip_tags($this->getPipedValue()->toHtml());
    }

    public function pipe($pipe)
    {
        $this->pipes[] = new PipeHandler($pipe, array_slice(func_get_args(), 1));

        return $this;
    }

    public function cell($callable)
    {
        return $this->pipe($callable);
    }

    public function copy()
    {
        return clone $this;
    }

    public function fill($data)
    {
        if ($data instanceof Store) {
            $this->data = $data->getData();
            $this->store = $data;
        } else {
            $this->data = $data;
            $this->store = QS::transform2Store($data);
        }

        return $this;
    }

    public function store()
    {
        return $this->store;
    }

    public function __toString()
    {
        return $this->value()->toHtml();
    }
}
