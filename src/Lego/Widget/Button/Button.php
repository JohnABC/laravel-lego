<?php

namespace JA\Lego\Widget\Button;

use Collective\Html\HtmlFacade;
use JA\Lego\Foundation\Response;
use JA\Lego\Widget\Concern\HasHtmlAttributes;
use JA\Lego\Foundation\Carrier\Concern\HasRender;
use JA\Lego\Foundation\Carrier\Concern\Contract\HasRender as HasRenderContract;

class Button implements HasRenderContract
{
    use HasRender,
        HasHtmlAttributes;

    protected $id;
    protected $text;
    protected $link;
    protected $prevent = false;

    public function __construct($text, $link = null, $id = null)
    {
        $this->text = $text;
        $this->id = $id ?: md5('__ja_lego_button ' . $text);
        $this->link($link instanceof \Closure ? $this->registerResponse($link)->urlWithPriority() : $link);

        $this->setAttribute(config('ja-lego.button.default-attributes'));
    }

    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function link($link)
    {
        $this->link = $link;

        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function prevent($prevent)
    {
        $this->prevent = $prevent;

        return $this;
    }

    public function getPrevent()
    {
        return $this->prevent;
    }

    public function registerResponse($callback)
    {
        return Response::registerPriority(md5(__METHOD__ . ' ' . $this->getText()), $callback);
    }

    public function openInSelf()
    {
        return $this->setAttribute('target', '_self');
    }

    public function openInBlank()
    {
        return $this->setAttribute('target', '_blank');
    }

    public function render()
    {
        $this->attributes['id'] = $this->id;
        $attributes = $this->getFlattenAttributes();
        $attributes = HtmlFacade::attributes($attributes);

        view(config('ja-lego.button.default-view'), ['button' => $this, 'attributes' => $attributes])->render();
    }
}