<?php

namespace JA\Lego\Foundation\Carrier\Concern;

use Illuminate\Support\HtmlString;

trait HasRender
{
    public function __toString()
    {
        return (string) $this->render();
    }

    final public function toHtmlString()
    {
        return new HtmlString($this->__toString());
    }
}