<?php

namespace JA\Lego\Foundation\Carrier\Concern;

trait HasEvents
{
    protected $events = [];

    public function listen($event, $callback, $listener = null)
    {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }

        if ($listener) {
            $this->events[$event][$listener] = $callback;
        } else {
            $this->events[$event][] = $callback;
        }
    }

    public function fire($event, $params = [])
    {
        if (!isset($this->events[$event])) {
            return;
        }

        foreach ($this->events[$event] as $listener => $callback) {
            call_user_func_array($callback, $params);
        }
    }
}