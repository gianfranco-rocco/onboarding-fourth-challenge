<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public string $id, $submitBtnLabel;
    
    public function __construct(string $id, string $submitBtnLabel)
    {
        $this->id = $id;
        $this->submitBtnLabel = $submitBtnLabel;
    }

    public function render(): View|\Closure|string
    {
        return view('components.modal');
    }
}
