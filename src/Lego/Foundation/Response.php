<?php

namespace JA\Lego\Foundation;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use JA\Lego\Exception\HttpException;

class Response
{
    const QUERY_NAME_PRIORITY_RESPONSE = '__ja_lego';

    protected $id;

    protected static $tree = [];
    protected static $current;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function registerPriority($name, $callback)
    {
        Register::set(Register::TYPE_PRIORITY_RESPONSE, $callback, $name);

        return new static(md5($name));
    }

    public function urlWithPriority(array $query = [])
    {
        if ($path = trim(Request::query(self::QUERY_NAME_PRIORITY_RESPONSE))) {
            if (!Str::contains($path, $this->id)) {
                $query[self::QUERY_NAME_PRIORITY_RESPONSE] = "{$path}+{$this->id}";
            }
        } else {
            $query[self::QUERY_NAME_PRIORITY_RESPONSE] = $this->id;
        }

        return Request::fullUrlWithQuery($query);
    }

    public static function priorityResponse($path = null)
    {
        $path = $path ?: Request::get(self::QUERY_NAME_PRIORITY_RESPONSE);
        if (!$path) {
            return null;
        }

        Arr::set(self::$tree, str_replace('+', '.', $path), []);

        $step = Arr::first(array_keys(Arr::get(self::$tree, self::$current)));
        if (!($callback = Register::get(Register::TYPE_PRIORITY_RESPONSE, $step))) {
            return null;
        }

        self::$current = self::$current ? (self::$current . '.' . $step) : $step;

        return call_user_func($callback);
    }

    public static function clearPriority()
    {
        return Request::fullUrlWithQuery([self::QUERY_NAME_PRIORITY_RESPONSE => null]);
    }

    public static function getReturn()
    {
        $keys = config('ja-lego.response.keys');

        return [
            $keys['code'] => config('ja-lego.response.code-success'),
            $keys['msg'] => '',
            $keys['data'] => new \stdClass(),
        ];
    }

    public static function corReturn($data = null, $msg = '')
    {
        $keys = config('ja-lego.response.keys');

        $rtn = static::getReturn();
        $rtn[$keys['data']] = $data;
        $rtn[$keys['msg']] = $msg;

        return $rtn;
    }

    public static function errReturn($code, $msg = '', $data = null)
    {
        $keys = config('ja-lego.response.keys');

        $rtn = static::getReturn();
        $rtn[$keys['code']] = $code;
        $rtn[$keys['msg']] = $msg;
        $rtn[$keys['data']] = $data;

        return $rtn;
    }

    public static function isCorrect($res)
    {
        $code = is_array($res) ? $res[config('ja-lego.response.keys.code')] : $res;

        return $code == config('ja-lego.response.code-success');
    }

    public static function errMsg($res)
    {
        return is_array($res) ? $res[config('ja-lego.response.keys.msg')] : '';
    }

    public static function corAjax($data = null, $msg = '')
    {
        return static::onAjax(static::corReturn($data, $msg));
    }

    public static function errAjax($code, $msg = '', $data = null)
    {
        return static::onAjax(static::errReturn($code, $msg, $data));
    }

    public static function onAjax($res)
    {
        $keys = config('ja-lego.response.keys');
        list($codeKey, $msgKey, $dataKey) = [$keys['code'], $keys['msg'], $keys['data']];

        if (empty($res[$dataKey])) {
            $res[$dataKey] = new \stdClass();
        }

        $isCorrect = $res[$codeKey] == config('ja-lego.response.code-success');
        if (!$isCorrect && empty($res[$msgKey])) {
            $res[$msgKey] = config('ja-lego.response.msg-failed');
        }

        if (!$isCorrect) {
            throw (new HttpException())->setResponseData($res)->setResponseJson(true);
        }

        return response()->json($res);
    }

    public static function err($msg = '', $code = null)
    {
        return new HttpException($msg, $code);
    }
}