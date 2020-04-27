<?php

namespace JA\Lego\Widget\Grid\Concerns;

use Closure;
use JA\Lego\Facades\Session;
use JA\Lego\Widget\Grid\Batch;
use Illuminate\Support\Facades\Redirect;

trait HasBatch
{
    /**
     * @var \JA\Lego\Widget\Grid\Batch[]
     */
    protected $batches = [];
    protected $batchModeUrl;
    protected $batchModeSessionKey = 'ja-lego.batch-mode';
    protected $batchIdName = 'id';

    public function addBatch($name, Closure $callback = null, $primaryKey = null)
    {
        $batch = new Batch($name, $this->getQuery(), $primaryKey ?: $this->batchIdName);
        $this->batches[$name] = $batch;

        if ($callback) {
            $batch->each($callback);
        }

        if ($this->enteredBatchMode()) {
            $this->addLeftTopButton('退出批处理', function () {
                $this->quitBatchMode();

                return Redirect::back();
            });
        } else {
            $this->addLeftTopButton('批处理模式', function () {
                $this->enterBatchMode();

                return Redirect::back();
            });
        }

        return $batch;
    }

    public function getBatch($name)
    {
        return $this->batches[$name];
    }

    public function getBatches()
    {
        return $this->batches;
    }

    public function getBatchesAsArray()
    {
        $array = [];
        foreach ($this->batches as $batch) {
            $array[$batch->getName()] = $batch->toArray();
        }

        return $array;
    }

    public function enterBatchMode()
    {
        if (count($this->getBatches()) === 0) {
            return;
        }

        Session::put($this->batchModeSessionKey, true);
    }

    public function quitBatchMode()
    {
        Session::forget($this->batchModeSessionKey);
    }

    public function enteredBatchMode()
    {
        return count($this->getBatches()) && Session::get($this->batchModeSessionKey, false);
    }

    public function setBatchIdName($keyName)
    {
        $this->batchIdName = $keyName;

        return $this;
    }

    public function getBatchIdName()
    {
        return $this->batchIdName;
    }

    public function pluckBatchIds(): array
    {
        $ids = [];

        foreach ($this->paginator() as $store) {
            /** @var \JA\Lego\Foundation\Store\Store $store */
            $ids[] = $store->get($this->batchIdName);
        }

        return $ids;
    }
}
