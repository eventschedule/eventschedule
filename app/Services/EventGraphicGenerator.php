<?php

namespace App\Services;

use App\Models\Role;
use App\Services\designs\ModernDesign;
use App\Services\designs\MinimalDesign;
use Illuminate\Support\Collection;

class EventGraphicGenerator
{
    private AbstractEventDesign $design;

    public function __construct(Role $role, Collection $events, string $lang = 'en', string $design = 'modern')
    {
        $this->design = $this->createDesign($design, $role, $events, $lang);
    }

    public function generate(): string
    {
        return $this->design->generate();
    }

    private function createDesign(string $design, Role $role, Collection $events, string $lang): AbstractEventDesign
    {
        return new MinimalDesign($role, $events, $lang);

        return match($design) {
            'modern' => new ModernDesign($role, $events, $lang),
            'minimal' => new MinimalDesign($role, $events, $lang),
            default => new ModernDesign($role, $events, $lang),
        };
    }

    public function getWidth(): int
    {
        return $this->design->getWidth();
    }

    public function getHeight(): int
    {
        return $this->design->getHeight();
    }
}
