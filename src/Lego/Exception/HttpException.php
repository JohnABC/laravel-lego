<?php

namespace JA\Lego\Exception;

use Throwable;
use JA\Lego\Foundation\Response;
use Illuminate\Support\Facades\Request;

class HttpException extends Exception
{
    protected $responseData = [];
    protected $responseStatus = 200;
    protected $responseBackUrl = null;
    protected $responseView = '';
    protected $responseJson = false;

    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->responseData = Response::errReturn($code, $message);
        $this->responseView = config('ja-lego.views.message.default-view');
    }

    public function setResponseData($responseData)
    {
        $this->responseData = $responseData;

        return $this;
    }

    public function setResponseStatus($responseStatus)
    {
        $this->responseStatus = $responseStatus;

        return $this;
    }

    public function setResponseBackUrl($responseBackUrl)
    {
        $this->responseBackUrl = $responseBackUrl;

        return $this;
    }

    public function setResponseView($responseView)
    {
        $this->responseView = $responseView;

        return $this;
    }

    public function setResponseJson($responseJson = true)
    {
        $this->responseJson = $responseJson;

        return $this;
    }

    public function render()
    {
        if ($this->responseJson || Request::expectsJson()) {
            return response()->json($this->responseData, $this->responseStatus);
        } else {
            return response()->view($this->responseView, $this->responseData, $this->responseStatus);
        }
    }
}