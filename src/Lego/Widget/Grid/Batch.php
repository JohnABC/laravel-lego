<?php

namespace JA\Lego\Widget\Grid;

use Closure;
use JA\Lego\Facades\Asset;
use JA\Lego\Facades\Cache;
use JA\Lego\Facades\Session;
use JA\Lego\Foundation\Response;
use JA\Lego\Foundation\Query\Query;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Contracts\Support\Arrayable;

class Batch implements Arrayable
{
    const BATCH_QUERY_PARAM_IDS = '__ja_lego_batch_ids';
    const BATCH_OPEN_TARGET_SELF = '_self';
    const BATCH_OPEN_TARGET_BLANK = '_blank';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \JA\Lego\Foundation\Query\Query
     */
    protected $query;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string|Closure
     */
    protected $message;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var Closure
     */
    protected $form;

    /**
     * @var Closure
     */
    protected $each;

    /**
     * @var Closure
     */
    protected $handle;

    /**
     * @var string
     */
    protected $openTarget;

    public function __construct($name, Query $query, $primaryKey = 'id')
    {
        $this->name = $name;
        $this->query = $query;
        $this->primaryKey = $primaryKey;
        $this->openTarget = config('ja-lego.widgets.grid.batch.default-target');
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function primaryKey($key)
    {
        $this->primaryKey = $key;

        return $this;
    }

    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function openInSelf($condition = true)
    {
        if ($condition) {
            $this->openTarget = static::BATCH_OPEN_TARGET_SELF;
        }

        return $this;
    }

    public function openInBlank($condition = true)
    {
        if ($condition) {
            $this->openTarget = static::BATCH_OPEN_TARGET_BLANK;
        }

        return $this;
    }

    public function openInPopup($width = 500, $height = 500, $condition = true)
    {
        if ($condition) {
            $this->openTarget = compact('height', 'width');
        }

        return $this;
    }

    public function resetOpenTarget()
    {
        $this->openTarget = config('ja-lego.widgets.grid.batch.default-target');

        return $this;
    }

    public function form(Closure $builder)
    {
        $this->form = $builder;

        return $this;
    }

    public function each(Closure $closure)
    {
        $this->each = $closure;

        $this->registerResponse();

        return $this;
    }

    public function handle(Closure $closure)
    {
        $this->handle = $closure;

        $this->registerResponse();

        return $this;
    }

    protected function registerResponse()
    {
        $this->url = Response::registerPriority(__METHOD__ . $this->getName(), function () {
            return $this->response();
        })->url();
    }

    protected function response()
    {
        Asset::reset();

        if (!$this->getIds()) {
            return $this->saveIdsResponse();
        }

        if ($this->form) {
            return $this->formResponse();
        }

        if ($this->message) {
            $message = $this->message instanceof Closure
                ? call_user_func($this->message, $this->getDataCollection())
                : $this->message;

            return Lego::confirm($message, function ($sure) {
                return $sure ? $this->callHandleClosure() : redirect($this->exit());
            });
        }

        return $this->callHandleClosure();
    }

    protected function getIds()
    {
        if (!($key = Request::get(self::BATCH_QUERY_PARAM_IDS))) {
            return [];
        }

        $ids = Cache::get(self::BATCH_QUERY_PARAM_IDS . $key);

        return is_array($ids) ? $ids : [];
    }

    protected function saveIdsResponse()
    {
        if (!$ids = Request::input('ids')) {
            return view('lego::message', ['message' => '尚未选中任何记录！', 'level' => 'warning']);
        }

        $ids = array_unique(is_array($ids) ? $ids : explode(',', $ids));
        $hash = md5(Session::getId() . microtime());
        Cache::put(self::BATCH_QUERY_PARAM_IDS . $hash, $ids, 10);

        return Redirect::to(Request::fullUrlWithQuery([self::BATCH_QUERY_PARAM_IDS => $hash]));
    }

    protected function formResponse()
    {
        $form = Lego::form();
        call_user_func($this->form, $form);
        $form->onSubmit(function ($form) {
            return $this->callHandleClosure($form);
        });

        return $form->view('lego::grid.action.form', ['form' => $form, 'action' => $this]);
    }

    protected function callHandleClosure()
    {
        $params = func_get_args();
        $collection = $this->getDataCollection();
        if ($this->each) {
            array_unshift($params, null);
            $collection->each(function (Store $store) use ($params) {
                $params[0] = $store->getOriginalData();
                call_user_func_array($this->each, $params);
            });

            return redirect($this->exit());
        } elseif ($this->handle) {
            $collection = $collection->map(function (Store $store) {
                return $store->getOriginalData();
            });
            $response = call_user_func($this->handle, $collection, ...$params);

            return $response ?: redirect($this->exit());
        } else {
            throw new LegoException(__CLASS__ . ' does not set `handle` or `each`.');
        }
    }

    protected function getDataCollection()
    {
        return $this->query->whereIn($this->primaryKey, $this->getIds())->get();
    }

    protected function exit()
    {
        return Request::fullUrlWithQuery(
            array_merge(Request::query(), [
                self::BATCH_QUERY_PARAM_IDS                => null,
                HighPriorityResponse::REQUEST_PARAM => null,
                Confirm::CONFIRM_QUERY_NAME         => null,
                Confirm::FROM_QUERY_NAME            => null,
            ])
        );
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'open_target' => $this->openTarget,
        ];
    }
}
