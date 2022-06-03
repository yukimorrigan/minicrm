<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AdminButton extends Component
{
    /**
     * Constructor properties
     */
    public $table;
    public $entity;
    public $action;
    public $id;
    public $modal;

    /**
     * Generated properties
     */
    public $method;
    public $slot;
    public $icon = '';
    public $toggle = false;
    public $target = false;
    public $params = [];
    public $href;
    public $modalHeader;
    public $modalText;
    public $modalButton;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($table, $entity, $action, $id = null, $modal = false)
    {
        $this->table = $table;
        $this->entity = $entity;
        $this->action = $action;
        $this->id = $id;
        $this->modal = $modal;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $this->attributes = $this->attributes ?: $this->newAttributeBag();
        $this->initializeParams();
        $this->initializeAction();
        return view('components.admin-button', (array) $this);
    }

    private function initializeParams()
    {
        switch ($this->action)
        {
            case 'create':
                $this->method = 'GET';
                $this->slot = __('admin.add');
                $this->icon = 'plus';
                break;
            case 'edit':
                $this->method = 'GET';
                $this->slot = __('admin.edit');
                $this->icon = 'edit';
                break;
            case 'destroy':
                $this->method = 'DELETE';
                $this->slot = __('admin.delete');
                $this->icon = 'trash';
                $genitive = __('admin.'.$this->entity.'_genitive');
                $this->modalHeader = __('admin.delete') . ' ' . $genitive;
                $this->modalText = __('admin.delete_text', ['entity' => $genitive, 'id' => $this->id]);
                $this->modalButton = __('Yes');
                break;
        }
    }

    private function initializeAction()
    {
        if ($this->modal)
        {
            $this->toggle = 'modal';
            $this->target = "#$this->action-$this->entity";
            if (!empty($this->id)) {
                $this->target .= "-$this->id";
                $this->params[$this->entity] = $this->id;
            }
            $this->href = "javascript:void(0);";
        }
        else
        {
            if (!empty($this->id)) {
                $this->params[$this->entity] = $this->id;
            }
            $this->href = route("$this->table.$this->action", $this->params);
        }
    }
}
