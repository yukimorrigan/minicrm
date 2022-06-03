<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ModalInfo extends Component
{
    public $header;
    public $text;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($header, $text)
    {
        $this->header = $header;
        $this->text = $text;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.modal-info', (array) $this);
    }
}
