<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    public string $id;
    public $records;

    public function __construct(string $id, $records)
    {
        $this->id = $id;
        $this->records = $records;
    }

    public function render(): View|\Closure|string
    {
        return view('components.table');
    }
}
