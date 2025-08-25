<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Services\EventGraphicGenerator; // <-- Import the new service
use Illuminate\Http\Request;

class GraphicController extends Controller
{
    public function generateGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        // Get the next 10 events
        $events = Event::with('roles')
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)->where('is_accepted', true);
            })
            ->where('flyer_image_url', '!=', null)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        if ($events->isEmpty()) {
            abort(404, 'No upcoming events found to generate a graphic.');
        }

        // Use the service to generate the graphic
        $generator = new EventGraphicGenerator($role, $events);
        $imageData = $generator->generate();

        $filename = $role->subdomain . '-upcoming-events.png';

        // Return the image as a response
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'public, max-age=3600');
    }
}