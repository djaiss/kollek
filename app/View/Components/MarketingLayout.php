<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class MarketingLayout extends Component
{
    /**
     * The title, the description and the social card are all optional. A page
     * that gives none of them is described by App\ViewModels\MarketingSeo from
     * its route name, which is where the copy for the public pages lives, and
     * shares under the site wide card.
     */
    public function __construct(
        public array $breadcrumbItems = [],
        public ?string $title = null,
        public ?string $description = null,
        public ?string $image = null,
        public ?array $structuredData = null,
    ) {}

    public function render(): View
    {
        return view('layouts.marketing', [
            'breadcrumbItems' => $this->breadcrumbItems,
            'pageTitle' => $this->title,
            'pageDescription' => $this->description,
            'pageImage' => $this->image,
            'pageStructuredData' => $this->structuredData,
        ]);
    }
}
