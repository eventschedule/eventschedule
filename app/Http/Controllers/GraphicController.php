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
        $layout = $request->get('layout', 'grid');
        
        // Validate layout parameter
        if (!in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        return view('graphic.show', compact('role', 'layout'));
    }
    
    public function generateGraphicData(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $layout = $request->get('layout', 'grid');
        
        // Validate layout parameter
        if (!in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        // Get the next 10 events
        $events = Event::with('roles')
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)->where('is_accepted', true);
            })
            ->where('starts_at', '>=', now())
            ->where('flyer_image_url', '!=', null)
            ->whereNull('days_of_week')
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        if ($events->isEmpty()) {
            return response()->json(['error' => __('messages.no_events_found')], 404);
        }

        if (config('services.capturekit.key') && (! config('app.hosted') || $role->id == 19)) {
            $url = $role->getGuestUrl($role->subdomain) . '?embed=true&graphic=true';
            $url = 'https://api.capturekit.dev/capture?&access_key=' . config('services.capturekit.key') . '&viewport_width=950&full_page=true&url=' . urlencode($url);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60, // 60 second timeout
                CURLOPT_CONNECTTIMEOUT => 30, // 30 second connection timeout
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'EventSchedule/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: image/png,image/*,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                ]
            ]);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                \Log::error('CaptureKit cURL error: ' . $error);
                return redirect()->back()->with('error', 'Failed to generate graphic: ' . $error);
            }
            
            if ($httpCode !== 200) {
                \Log::error('CaptureKit HTTP error: ' . $httpCode);
                return redirect()->back()->with('error', 'Failed to generate graphic: HTTP ' . $httpCode);
            }
            
            if (empty($imageData)) {
                \Log::error('CaptureKit returned empty response');
                return redirect()->back()->with('error', 'Failed to generate graphic: Empty response');
            }
        } else {
            // Use the service to generate the graphic with the specified layout
            $generator = new EventGraphicGenerator($role, $events, $layout);
            $imageData = $generator->generate();
        }
         
        // Convert image data to base64 for display
        $imageBase64 = base64_encode($imageData);
        
        // Generate event text content
        $eventText = $this->generateEventText($role, $events);

        return response()->json([
            'image' => $imageBase64,
            'text' => $eventText,
            'download_url' => route('event.download_graphic', ['subdomain' => $role->subdomain, 'layout' => $layout])
        ]);
    }
    
    public function downloadGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $layout = $request->get('layout', 'grid');
        
        // Validate layout parameter
        if (!in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        // Get the next 10 events
        $events = Event::with('roles')
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)->where('is_accepted', true);
            })
            ->where('starts_at', '>=', now())
            ->where('flyer_image_url', '!=', null)
            ->whereNull('days_of_week')
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        if ($events->isEmpty()) {
            return redirect()->back()->with('error', __('messages.no_events_found'));
        }

        if (config('services.capturekit.key') && (! config('app.hosted') || $role->id == 19)) {
            $url = $role->getGuestUrl($role->subdomain) . '?embed=true&graphic=true';
            $url = 'https://api.capturekit.dev/capture?&access_key=' . config('services.capturekit.key') . '&viewport_width=950&full_page=true&url=' . urlencode($url);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60, // 60 second timeout
                CURLOPT_CONNECTTIMEOUT => 30, // 30 second connection timeout
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'EventSchedule/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: image/png,image/*,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                ]
            ]);
            
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                \Log::error('CaptureKit cURL error: ' . $error);
                return redirect()->back()->with('error', 'Failed to generate graphic: ' . $error);
            }
            
            if ($httpCode !== 200) {
                \Log::error('CaptureKit HTTP error: ' . $httpCode);
                return redirect()->back()->with('error', 'Failed to generate graphic: HTTP ' . $httpCode);
            }
            
            if (empty($imageData)) {
                \Log::error('CaptureKit returned empty response');
                return redirect()->back()->with('error', 'Failed to generate graphic: Empty response');
            }
        } else {
            // Use the service to generate the graphic with the specified layout
            $generator = new EventGraphicGenerator($role, $events, $layout);
            $imageData = $generator->generate();
        }

        // Generate filename based on layout
        $filename = $role->subdomain . '-upcoming-events.png';

        // Return the image as a response
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    
    private function generateEventText($role, $events)
    {
        $text = __('messages.upcoming_events') . ":\n\n";
        
        $currentDay = null;
        foreach ($events as $event) {
            $startDate = $event->getStartDateTime(null, true);
            $dayName = $startDate->format('l');
            $dateStr = $event->localStartsAt(true);
            
            // Group events by day
            if ($currentDay !== $dayName) {
                if ($currentDay !== null) {
                    $text .= "\n";
                }
                $currentDay = $dayName;
            }
            
            // Format time and event details
            if ($startDate->isToday()) {
                $text .= __('messages.tonight') . " - {$dateStr}\n";
            } else {
                $text .= "{$dateStr}\n";
            }
            
            $text .= "*{$event->translatedName()}*\n";
            
            if ($event->venue) {
                $text .= "{$event->venue->translatedName()}\n";
            }
            
            $text .= "{$event->getGuestUrl($role->subdomain, null, true)}\n";
            
            $text .= "\n";
        }
        
        return $text;
    }    
}