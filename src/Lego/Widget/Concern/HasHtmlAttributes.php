<?php

namespace JA\Lego\Widget\Concern;

use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

trait HasHtmlAttributes
{
    protected $attributes = [];

    public function getAttribute($attribute, $default = null)
    {
        return Arr::get($this->attributes, $attribute, $default);
    }

    public function getAttributeString($attribute, $default = '')
    {
        $values = $this->getAttribute($attribute);

        return is_null($values) ? $default : join(' ', (array) $values);
    }

    public function setAttribute($attribute, $value = null)
    {
        if (is_array($attribute)) {
            $this->attributes = array_merge_recursive($this->attributes, $attribute);

            return $this;
        }

        if (is_array($value)) {
            $this->attributes[$attribute] = array_merge((array) $this->getAttribute($attribute, []), $value);
        } else {
            $this->attributes[$attribute] = $value;
        }

        return $this;
    }

    public function removeAttribute()
    {
        foreach (func_get_args() as $attr) {
            unset($this->attributes[$attr]);
        }

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getFlattenAttributes()
    {
        $attributes = [];
        foreach ($this->attributes as $name => $values) {
            $attributes[$name] = is_array($values) ? implode(' ', $values) : $values;
        }

        return $attributes;
    }

    public function getAttributesString()
    {
        $attributes = $this->getFlattenAttributes();

        $html = '';
        foreach ($attributes as $key => $value) {
            $html .= " {$key}=\"{$value}\"";
        }

        return new HtmlString(trim($html));
    }

    public function addClass($class)
    {
        return $this->setAttribute('class', is_array($class) ? $class : explode(' ', trim($class)));
    }

    public function removeClass($class)
    {
        $classes = $this->getAttribute('class');
        if (!$classes) {
            return;
        }

        if (($index = array_search($class, $classes)) !== false) {
            unset($this->attributes['class'][$index]);
        }
    }

    public function removeWildcardClass($wildcard)
    {
        $classes = $this->getAttribute('class');
        foreach ($classes as $index => $class) {
            if (strpos($class, $wildcard) === 0) {
                unset($this->attributes['class'][$index]);
            }
        }
    }
}