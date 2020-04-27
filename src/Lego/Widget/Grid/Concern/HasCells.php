<?php

namespace JA\Lego\Widget\Grid\Concern;

use JA\Lego\Widget\Grid\Cell;
use JA\Lego\Exception\IllegalCellAfterException;

trait HasCells
{
    /**
     * @var \JA\Lego\Widget\Grid\Cell[]
     */
    protected $cells = [];

    /**
     * @var string
     */
    protected $cellAfter;

    /**
     * @var string
     */
    protected $cellAfterOnce;

    public function add($name, $description)
    {
        $cell = new Cell($name, $description);
        if ($after = $this->cellAfterOnce ?: $this->cellAfter) {
            $idx = array_search($after, array_keys($this->cells)) + 1;
            $this->cells = array_slice($this->cells, 0, $idx, true) + [$name => $cell] + array_slice($this->cells, $idx, count($this->cells) - $idx);
            $this->cellAfterOnce = null;
        } else {
            $this->cells[$name] = $cell;
        }

        return $cell;
    }

    public function after($name, $callback = null)
    {
        if (!isset($this->cells[$name])) {
            throw new IllegalCellAfterException("Can not found cell `{$name}`");
        }

        if ($callback) {
            $this->cellAfter = $name;
            call_user_func($callback, $this);
            $this->cellAfter = null;
        } else {
            $this->cellAfterOnce = $name;
        }

        return $this;
    }

    public function remove($names)
    {
        $names = is_array($names) ? $names : func_get_args();
        foreach ($names as $name) {
            unset($this->cells[$name]);
        }

        return $this;
    }

    public function cells()
    {
        return $this->cells;
    }

    public function cell($name)
    {
        return $this->cells[$name];
    }
}