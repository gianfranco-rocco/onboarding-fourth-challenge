<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Label extends Component
{
    public string $id, $for;
    
    public function __construct(string $forForm, string $for)
    {
        $this->id = "{$forForm}-{$for}-label";
        $this->for = $for;
    }

    public function render(): View|\Closure|string
    {
        return view('components.label');
    }
}
