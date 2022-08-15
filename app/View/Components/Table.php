<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    public $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function render(): View|\Closure|string
    {
        return view('components.table');
    }
}
