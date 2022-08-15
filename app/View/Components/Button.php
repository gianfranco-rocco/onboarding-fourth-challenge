<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $id, $type, $onclick;

    public function __construct(string $id = '', string $type = 'button', string $onclick = '')
    {
        $this->id = $id;
        $this->type = $type;
        $this->onclick = $onclick;
    }

    public function render(): View|\Closure|string
    {
        return view('components.button');
    }
}
