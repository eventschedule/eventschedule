<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Event;
use App\Services\designs\GridDesign;
use App\Services\designs\ListDesign;
use Illuminate\Support\Collection;

class EventGraphicGenerator
{
    protected Role $role;
    protected Collection $events;
    protected string $layout;
    protected AbstractEventDesign $design;
    
    public function __construct(Role $role, Collection $events, string $layout = 'grid')
    {
        $this->role = $role;
        $this->events = $events;
        $this->layout = $layout;
        
        // Create the appropriate design based on layout
        $this->design = $this->createDesign();
    }
    
    protected function createDesign(): AbstractEventDesign
    {
        switch (strtolower($this->layout)) {
            case 'list':
                return new ListDesign($this->role, $this->events);
            case 'grid':
            default:
                return new GridDesign($this->role, $this->events);
        }
    }
    
    public function generate(): string
    {
        return $this->design->generate();
    }
    
    public function getWidth(): int
    {
        return $this->design->getWidth();
    }
    
    public function getHeight(): int
    {
        return $this->design->getHeight();
    }
    
    public function getEventCount(): int
    {
        return $this->design->getEventCount();
    }
    
    public function getLayout(): string
    {
        return $this->layout;
    }
    
    public function getDesign(): AbstractEventDesign
    {
        return $this->design;
    }
    
    // Grid-specific methods (delegated to GridDesign if available)
    public function getGridInfo(): ?array
    {
        if ($this->design instanceof GridDesign) {
            return $this->design->getGridInfo();
        }
        return null;
    }
    
    public function getCurrentGridLayout(): ?array
    {
        if ($this->design instanceof GridDesign) {
            return $this->design->getCurrentGridLayout();
        }
        return null;
    }
    
    public function getQRCodePositionForFlyer(int $row, int $col): ?array
    {
        if ($this->design instanceof GridDesign) {
            return $this->design->getQRCodePositionForFlyer($row, $col);
        }
        return null;
    }
    
    public function getDetailedQRCodeInfo(): ?array
    {
        if ($this->design instanceof GridDesign) {
            return $this->design->getDetailedQRCodeInfo();
        }
        return null;
    }
    
    // List-specific methods (delegated to ListDesign if available)
    public function getListInfo(): ?array
    {
        if ($this->design instanceof ListDesign) {
            return $this->design->getListInfo();
        }
        return null;
    }
    
    public function getCurrentListLayout(): ?array
    {
        if ($this->design instanceof ListDesign) {
            return $this->design->getCurrentListLayout();
        }
        return null;
    }
    
    // General design information
    public function getDesignInfo(): array
    {
        $info = [
            'layout' => $this->layout,
            'event_count' => $this->getEventCount(),
            'total_width' => $this->getWidth(),
            'total_height' => $this->getHeight(),
            'design_class' => get_class($this->design),
        ];
        
        // Add layout-specific information
        if ($this->design instanceof GridDesign) {
            $info['grid_info'] = $this->design->getGridInfo();
        } elseif ($this->design instanceof ListDesign) {
            $info['list_info'] = $this->design->getListInfo();
        }
        
        return $info;
    }
    
    /**
     * Check if the current design supports a specific feature
     */
    public function supportsFeature(string $feature): bool
    {
        switch ($feature) {
            case 'grid_layout':
                return $this->design instanceof GridDesign;
            case 'list_layout':
                return $this->design instanceof ListDesign;
            case 'qr_codes':
                return true; // Both designs support QR codes
            case 'rounded_corners':
                return true; // Both designs support rounded corners
            default:
                return false;
        }
    }
    
    /**
     * Get available layouts
     */
    public static function getAvailableLayouts(): array
    {
        return [
            'grid' => [
                'name' => 'Grid Layout',
                'description' => 'Display events in a grid format with flyer images',
                'features' => ['flyer_images', 'qr_codes', 'rounded_corners', 'dynamic_grid']
            ],
            'list' => [
                'name' => 'List Layout',
                'description' => 'Display events in a list format with details and flyer images',
                'features' => ['flyer_images', 'event_details', 'qr_codes', 'rounded_corners', 'separator_lines']
            ]
        ];
    }
    
    /**
     * Create a new instance with a different layout
     */
    public function withLayout(string $layout): self
    {
        return new self($this->role, $this->events, $layout);
    }
    
    /**
     * Get the underlying design object for advanced operations
     */
    public function getDesignObject(): AbstractEventDesign
    {
        return $this->design;
    }
    
    /**
     * Get font debugging information for troubleshooting
     */
    public function getFontDebugInfo(): array
    {
        return $this->design->getFontDebugInfo();
    }
}