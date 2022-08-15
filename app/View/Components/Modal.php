<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Modal extends Component
{
    public string $id, $title, $submitBtnLabel, $submitBtnOnclick, $closeBtnLabel, $closeBtnOnclick;
    
    public function __construct(string $id, string $title, string $submitBtnLabel, string $submitBtnOnclick = '', string $closeBtnLabel = 'Cancel', string $closeBtnOnclick = '')
    {
        $this->id = $id;
        $this->title = $title;
        $this->submitBtnLabel = $submitBtnLabel;
        $this->submitBtnOnclick = $submitBtnOnclick;
        $this->closeBtnLabel = $closeBtnLabel;
        $this->closeBtnOnclick = $closeBtnOnclick;
    }

    public function render(): View|\Closure|string
    {
        return view('components.modal');
    }
}
