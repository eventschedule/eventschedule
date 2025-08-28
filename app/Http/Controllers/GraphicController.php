<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Services\EventGraphicGenerator;
use Illuminate\Http\Request;

class GraphicController extends Controller
{
    public function generateGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        //$layout = $request->get('layout', 'grid');
        $layout = $request->get('layout', 'list');
        
        // Validate layout parameter
        if (!in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        // Get the next 10 events
        $events = Event::with('roles')
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)->where('is_accepted', true);
            })
            ->where('flyer_image_url', '!=', null)
            /*
            ->where(function ($query) {
                $query->where('starts_at', '>=', now())
                    ->orWhereNotNull('days_of_week');
            })
            */
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        if ($events->isEmpty()) {
            return redirect()->back()->with('error', __('messages.no_events_found'));
        }

        // Use the service to generate the graphic with the specified layout
        $generator = new EventGraphicGenerator($role, $events, $layout);
        $imageData = $generator->generate();

        // Generate filename based on layout
        $layoutSuffix = $layout === 'list' ? '-list' : '';
        $filename = $role->subdomain . '-upcoming-events' . $layoutSuffix . '.png';

        // Return the image as a response
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'public, max-age=3600');
    }
}