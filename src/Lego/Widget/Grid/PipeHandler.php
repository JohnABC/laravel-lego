<?php

namespace JA\Lego\Widget\Grid;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use JA\Lego\Exception\IllegalCellPipeException;

class PipeHandler
{
    protected $pipeClass;
    protected $pipeMethod;
    protected $pipeArguments = [];
    protected $pipeClosure;

    protected static $registered = [];

    public function __construct($pipe, $pipeArguments = [])
    {
        if ($pipe instanceof Closure) {
            $this->pipe = $pipe;

            return;
        }

        if ($pipeArguments) {
            $this->pipeArguments = $pipeArguments;
        } elseif (Str::contains($pipe, ':')) {
            list($pipe, $pipeArguments) = explode(':', $pipe, 2);
            $this->pipeArguments = explode(',', $pipeArguments);
        }

        if (is_callable($pipe)) {
            $this->pipe = $pipe;
        } elseif ($collect = $this->getPipeClassAndMethod($pipe)) {
            list($this->pipeClass, $this->pipeMethod) = $collect;
        } else {
            throw new IllegalCellPipeException("Illegal pipe $pipe");
        }
    }

    protected function getPipeClassAndMethod($pipe)
    {
        if (empty(static::$registered)) {
            foreach (config('ja-lego.widgets.grid.pipes', []) as $pipesClass) {
                foreach ((new \ReflectionClass($pipesClass))->getMethods() as $method) {
                    if (Str::startsWith($method->name, 'handle')) {
                        $name = substr($method->name, 6);
                        static::$registered[Str::snake($name, '-')] = [$pipesClass, $method->name];
                    }
                }
            }
        }

        return Arr::get(static::$registered, $pipe);
    }

    public static function forgetRegistered()
    {
        static::$registered = [];
    }

    public function handle(...$arguments)
    {
        if ($this->pipe) {
            // if pipe is build in global function , pass single param only.
            if (is_callable($this->pipe) && !$this->pipe instanceof \Closure) {
                $arguments = array_slice($arguments, 0, 1);
            }

            return call_user_func_array($this->pipe, $arguments);
        }

        $class = $this->pipeClass;
        $instance = new $class(...$arguments);

        return call_user_func_array([$instance, $this->pipeMethod], $this->pipeArguments);
    }
}
