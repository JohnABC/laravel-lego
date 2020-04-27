<?php

namespace JA\Lego\Foundation\Carrier;

use JA\Lego\Foundation\QS;
use JA\Lego\Foundation\Query\Query;
use JA\Lego\Foundation\Store\Store;
use JA\Lego\Foundation\Carrier\Concern\HasEvents;
use JA\Lego\Foundation\Carrier\Concern\HasRender;
use JA\Lego\Foundation\Carrier\Concern\Contract\HasRender as HasRenderContract;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Traits\Macroable;

abstract class Carrier implements HasRenderContract
{
    use HasEvents,
        HasRender,
        Macroable;

    /**
     * @var string
     */
    public $id;

    protected $data;

    /**
     * @var \JA\Lego\Foundation\Store\Store
     */
    protected $store;

    /**
     * @var \JA\Lego\Foundation\Query\Query
     */
    protected $query;

    /**
     * @var bool
     */
    protected $processed = false;

    /**
     * @var \Illuminate\Support\MessageBag
     */
    protected $errors;

    /**
     * @var \Illuminate\Support\MessageBag
     */
    protected $messages;

    public function __construct($data, $id = null)
    {
        $this->id = $id ?: str_replace('.', '-', uniqid(strtolower(class_basename(static::class)) . '-', true));
        $this->data = $data;
        $this->errors = new MessageBag();
        $this->messages = new MessageBag();

        $this->initData();
    }

    protected function initData()
    {
        $data = $this->data;

        if ($data instanceof Store) {
            $this->data = $data->getData();
            $this->store = $data;
            $this->query = QS::transform2Query($this->data);
        } elseif ($data instanceof Query) {
            $this->data = $data->getData();
            $this->store = QS::transform2Store($this->data);
            $this->query = $data;
        } else {
            $this->data = $data;
            $this->query = QS::transform2Query($data);
            $this->store = QS::transform2Store($data);
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStore()
    {
        return $this->store;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function processOnce()
    {
        if (!$this->processed) {
            $this->process();

            $this->processed = true;
        }
    }

    abstract public function process();
}