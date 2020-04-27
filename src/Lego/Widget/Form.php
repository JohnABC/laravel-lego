<?php

namespace JA\Lego\Widget;

use JA\Lego\Widget\Concern\HasMode;
use JA\Lego\Widget\Concern\Contract\HasMode as HasModeContract;

class Form extends Widget implements HasModeContract
{
    use HasMode;

    public function process()
    {

    }

    public function renderReadonly()
    {
        // TODO: Implement renderReadonly() method.
    }

    public function renderEditable()
    {
        // TODO: Implement renderEditable() method.
    }

    public function renderDisabled()
    {
        // TODO: Implement renderDisabled() method.
    }
}