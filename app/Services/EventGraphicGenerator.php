<?php

namespace App\Services;

use App\Models\Role;
use App\Services\designs\ModernDesign;
use App\Services\designs\MinimalDesign;
use Illuminate\Support\Collection;

class EventGraphicGenerator
{
    private AbstractEventDesign $design;

    public function __construct(Role $role, Collection $events, string $design = 'modern')
    {
        $this->design = $this->createDesign($design, $role, $events);
    }

    public function generate(): string
    {
        return $this->design->generate();
    }

    private function createDesign(string $design, Role $role, Collection $events): AbstractEventDesign
    {
        return match($design) {
            'modern' => new ModernDesign($role, $events),
            'minimal' => new MinimalDesign($role, $events),
            default => new ModernDesign($role, $events),
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
