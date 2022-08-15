<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormInputContainer extends Component
{
    public string $name, $label, $inputType, $placeholder, $value;

    public function __construct(
        string $name, 
        string $label, 
        string $inputType = "text", 
        string $placeholder = "", 
        string $value = ""
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->inputType = $inputType;
        $this->placeholder = $placeholder;
        $this->value = $value;
    }

    public function render(): View|\Closure|string
    {
        return view('components.form-input-container');
    }
}
