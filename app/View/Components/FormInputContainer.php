<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormInputContainer extends Component
{
    public string $compoundId;

    public function __construct(string $forForm, string $forInput)
    {
        $this->compoundId = "{$forForm}-{$forInput}";
    }

    public function render(): View|\Closure|string
    {
        return view('components.form-input-container');
    }
}
