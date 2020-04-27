<?php

namespace JA\Lego\Widget\Concern\Contract;

interface HasMode
{
    const MODE_EDITABLE = 'editable';
    const MODE_READONLY = 'readonly';
    const MODE_DISABLED = 'disabled';

    public function renderEditable();

    public function renderReadonly();

    public function renderDisabled();
}