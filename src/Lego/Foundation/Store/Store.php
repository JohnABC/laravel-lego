<?php

namespace JA\Lego\Foundation\Store;

use Illuminate\Contracts\Support\Arrayable;

abstract class Store implements Arrayable, \ArrayAccess
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;

        $this->init();
    }

    protected function init()
    {
        //
    }

    public function getData()
    {
        return $this->data;
    }

    public function getKeyName()
    {
        return 'id';
    }

    public function getKey()
    {
        return $this->get($this->getKeyName());
    }

    abstract public static function parse($data);

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->get($name);
    }

    public function __unset($name)
    {
        $this->set($name, null);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->original, $name], $arguments);
    }

    public function offsetExists($offset)
    {
        return $this->get($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }

    abstract public function get($attribute, $default = null);

    abstract public function set($attribute, $value);

    abstract public function getAssociated($attribute);

    abstract public function associate($attribute, $id);

    abstract public function dissociate($attribute);

    abstract public function getAttached($attribute);

    abstract public function attach($attribute, array $ids, array $attributes = []);

    abstract public function detach($attribute, array $ids);

    abstract public function save($options = []);
}