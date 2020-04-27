<?php

namespace JA\Lego\Widget\Concern;

use JA\Lego\Widget\Concern\Contract\HasMode as HasModeContract;

trait HasMode
{
    protected $mode = HasModeContract::MODE_EDITABLE;
    protected $modeModified = false;

    protected $escape = true;

    public function getMode()
    {
        return $this->mode;
    }

    protected function isMode($mode)
    {
        return $this->mode == $mode;
    }

    public function isReadonlyMode()
    {
        return $this->isMode(HasModeContract::MODE_READONLY);
    }

    public function isEditableMode()
    {
        return $this->isMode(HasModeContract::MODE_EDITABLE);
    }

    public function isDisabledMode()
    {
        return $this->isMode(HasModeContract::MODE_DISABLED);
    }

    public function isModeModified()
    {
        return $this->modeModified;
    }

    protected function mode($mode, $condition = true)
    {
        if (value($condition)) {
            $this->mode = $mode;
            $this->modeModified = true;
        }

        return $this;
    }

    public function readonly($condition = true)
    {
        return $this->mode(self::$modeReadonly, $condition);
    }

    public function editable($condition = true)
    {
        return $this->mode(self::$modeEditable, $condition);
    }

    public function disabled($condition = true)
    {
        return $this->mode(self::$modeDisabled, $condition);
    }

    protected function renderByMode()
    {
        return call_user_func_array([$this, 'render' . ucfirst($this->mode)], []);
    }

    public function enableEscape()
    {
        $this->escape = true;

        return $this;
    }

    public function disableEscape()
    {
        $this->escape = false;

        return $this;
    }
}