<?php
namespace App\View\Components;

use App\Models\Partner;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PartnersSlider extends Component
{
    public array $partners;

    public function __construct(
        public int $limit = 30, // safety
    ) {
        $this->partners = Partner::query()
            ->where('status','published')
            ->orderBy('order')
            ->limit($this->limit)
            ->get()
            ->all();
    }

    public function render(): View|Closure|string
    {
        return view('components.partners-slider');
    }
}
