<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropdownItem extends Component
{
    public string $dropdownId, $id, $href, $label;

    public function __construct(string $dropdownId, string $id, string $href, string $label)
    {
        $this->dropdownId = $dropdownId;
        $this->id = $id;
        $this->href = $href;
        $this->label = $label;
    }

    public function render(): View|\Closure|string
    {
        return view('components.dropdown-item');
    }
}
