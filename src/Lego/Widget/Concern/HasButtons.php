<?php

namespace JA\Lego\Widget\Concern;

use Illuminate\Support\Str;
use JA\Lego\Widget\Button\Button;

trait HasButtons
{
    protected $buttons = [];

    protected function getButtonLocations()
    {
        return [
            'right-top',
            'right-bottom',
            'left-top',
            'left-bottom',
        ];
    }

    protected function initHasButtons($macroCallback)
    {
        foreach ($this->getButtonLocations() as $location) {
            $this->buttons[$location] = [];

            call_user_func($macroCallback, 'add' . Str::ucfirst(Str::camel($location)) . 'Button', function (...$args) use ($location) {
                $this->addButton($location, ...$args);
            });
        }
    }

    public function getButton($location, $text)
    {
        return $this->getButtons($location)[$text];
    }

    public function getButtons($location)
    {
        return $this->buttons[$location];
    }

    public function addButton($location, $text, $url = null, $id = null)
    {
        $button = new Button($text, $url, $id);
        $this->buttons[$location][$text] = $button;

        return $button;
    }

    public function removeButton($location, $text)
    {
        unset($this->buttons[$location][$text]);
    }
}