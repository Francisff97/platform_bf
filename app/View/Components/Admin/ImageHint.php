<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class ImageHint extends Component
{
    public $model;
    public $field;
    public $icon;

    public function __construct($model = null, $field = null, $icon = 'photo')
    {
        $this->model = $model;
        $this->field = $field;
        $this->icon  = $icon;
    }

    public function render()
    {
        $hint = \App\Support\ImageHints::resolve($this->model, $this->field);

        return view('components.admin.image-hint', [
            'hint' => $hint,
            'icon' => $this->icon,
        ]);
    }
}