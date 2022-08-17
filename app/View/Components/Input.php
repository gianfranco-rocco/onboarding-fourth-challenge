<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public string $id, $type, $name, $placeholder, $value;

    public function __construct(string $id = '', string $type = 'text', string $name = '', string $placeholder = 'Placeholder', string $value = '')
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->value = $value;
    }

    public function render(): View|\Closure|string
    {
        return view('components.input');
    }
}
