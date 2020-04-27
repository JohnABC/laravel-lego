<?php

namespace JA\Lego\Foundation\Query;

use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use JA\Lego\Foundation\QS;
use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

abstract class Query implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    protected $data;

    /**
     * @var \Illuminate\Pagination\AbstractPaginator
     */
    protected $paginator;

    public function __construct($data)
    {
        $this->data = $data;

        $this->init();
    }

    protected function init()
    {

    }

    public function getData()
    {
        return $this->data;
    }

    abstract public static function parse($data);

    public function with(array $relations)
    {
        return $this;
    }

    abstract protected function select(array $columns);

    abstract public function whereEquals($attribute, $value);

    abstract public function whereIn($attribute, array $values);

    abstract public function whereGt($attribute, $value, bool $equals = false);

    public function whereGte($attribute, $value)
    {
        return $this->whereGt($attribute, $value, true);
    }

    abstract public function whereLt($attribute, $value, bool $equals = false);

    public function whereLte($attribute, $value)
    {
        return $this->whereLt($attribute, $value, true);
    }

    abstract public function whereContains($attribute, string $value);

    abstract public function whereStartsWith($attribute, string $value);

    abstract public function whereEndsWith($attribute, string $value);

    abstract public function whereBetween($attribute, $min, $max);

    abstract public function whereScope($scope, $value);

    abstract public function suggest($attribute, string $keyword, string $valueColumn = null, int $limit = 20);

    abstract public function limit($limit);

    abstract public function orderBy($attribute, bool $desc = false);

    public function orderByDesc($attribute)
    {
        return $this->orderBy($attribute, true);
    }

    abstract protected function createLengthAwarePaginator($perPage, $columns, $pageName, $page);

    abstract protected function createLengthNotAwarePaginator($perPage, $columns, $pageName, $page);

    public function paginate($perPage = null, $columns = null, $pageName = null, $page = null, bool $lengthAware = true) {
        $perPage = is_null($perPage) ? config('ja-lego.paginator.per-page') : $perPage;
        $pageName = is_null($pageName) ? config('ja-lego.paginator.page-name') : $pageName;
        $columns = is_null($columns) ? ['*'] : $columns;
        $page = $page ?: Request::query($pageName, 1);

        if ($lengthAware) {
            $this->paginator = $this->createLengthAwarePaginator($perPage, $columns, $pageName, $page);
        } else {
            $this->paginator = $this->createLengthNotAwarePaginator($perPage, $columns, $pageName, $page);
        }

        $this->paginator->setCollection(
            $this->paginator->getCollection()->map(function ($row) {
                return QS::transform2Store($row);
            })
        );

        return $this->paginator;
    }

    public function paginator()
    {
        if (!$this->paginator) {
            $this->paginate();
        }

        return $this->paginator;
    }

    public function get($columns = ['*'])
    {
        return $this->select($columns)->map(function ($row) {
            return QS::transform2Store($row);
        });
    }

    public function toArray()
    {
        return $this->paginator()->toArray();
    }

    public function getIterator()
    {
        return $this->paginator()->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->paginator()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->paginator()->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->paginator()->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->paginator()->offsetUnset($offset);
    }

    public function toJson($options = 0)
    {
        return $this->paginator()->toJson(
            $options === 0 ? JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE : $options
        );
    }

    public function count()
    {
        return $this->paginator()->count();
    }

    public function jsonSerialize()
    {
        return $this->paginator()->jsonSerialize();
    }
}