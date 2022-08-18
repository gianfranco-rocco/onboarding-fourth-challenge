<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Dropdown extends Component
{
    public string $id, $name, $onchange;
    
    public function __construct(string $id, string $name = '', string $onchange = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->onchange = $onchange;
    }

    public function render(): View|\Closure|string
    {
        return view('components.dropdown');
    }
}
