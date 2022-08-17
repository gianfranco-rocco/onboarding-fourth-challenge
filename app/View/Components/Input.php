<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    public string $id, $type, $name, $placeholder, $value;

    public function __construct(
        string $name, 
        string $id = '', 
        string $type = 'text', 
        string $placeholder = '', 
        string $value = '',
        string $forForm = ''
    ) {
        $this->id = $id;
        if (!empty($forForm) && empty($id)) {
            $this->id = "{$forForm}-{$name}";
        }

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
