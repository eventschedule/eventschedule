<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Toggle extends Component
{
    public $name;

    public $label;

    public $checked;

    public $help;

    public $id;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $label = '', $checked = false, $help = '', $id = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->checked = $checked;
        $this->help = $help;
        $this->id = $id ?? $name;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.toggle');
    }
}
