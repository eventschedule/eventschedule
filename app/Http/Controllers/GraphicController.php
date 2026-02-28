<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Services\EventGraphicGenerator;
use App\Services\GraphicEmailService;
use App\Utils\EventTextGenerator;
use App\Utils\GeminiUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GraphicController extends Controller
{
    public function generateGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $layout = $request->get('layout', 'grid');

        // Validate layout parameter
        if (! in_array($layout, ['grid', 'list', 'row'])) {
            $layout = 'grid';
        }

        $isPro = $role->isPro();
        $isEnterprise = $role->isEnterprise();
        $graphicSettings = $role->graphic_settings;
        $hasRecurringEvents = $role->events()->whereNotNull('days_of_week')->exists();

        $headerImagePreviewUrl = null;
        if (! empty($graphicSettings['header_image_url'])) {
            if (config('filesystems.default') == 'local') {
                $headerImagePreviewUrl = url('/storage/'.$graphicSettings['header_image_url']);
            } else {
                $headerImagePreviewUrl = Storage::url($graphicSettings['header_image_url']);
            }
        }

        return view('graphic.show', compact('role', 'layout', 'isPro', 'isEnterprise', 'graphicSettings', 'hasRecurringEvents', 'headerImagePreviewUrl'));
    }

    public function getSettings($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        return response()->json([
            'settings' => $role->graphic_settings,
            'is_pro' => $role->isPro(),
            'is_enterprise' => $role->isEnterprise(),
        ]);
    }

    public function saveSettings(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $validated = $request->validate([
            'enabled' => 'boolean',
            'frequency' => 'in:daily,weekly,monthly',
            'ai_prompt' => 'nullable|string|max:500',
            'text_template' => 'nullable|string|max:2000',
            'layout' => 'in:grid,list,row',
            'send_day' => 'integer|min:0|max:31',
            'send_days' => 'array',
            'send_days.*' => 'integer|min:0|max:6',
            'send_hour' => 'integer|min:0|max:23',

            'recipient_emails' => 'nullable|string|max:1000',
            'exclude_recurring' => 'boolean',
            'date_position' => 'nullable|in:overlay,above',
            'event_count' => 'nullable|integer|min:1',
            'max_per_row' => 'nullable|integer|min:1|max:20',
            'overlay_text' => 'nullable|string|max:200',
            'url_include_https' => 'boolean',
            'url_include_id' => 'boolean',
        ]);

        // Merge with existing settings to preserve defaults
        $currentSettings = $role->graphic_settings ?? [];
        $newSettings = array_merge($currentSettings, $validated);

        // Require recipient_emails when enabled is true
        $isEnabled = $newSettings['enabled'] ?? false;
        $recipientEmails = trim($newSettings['recipient_emails'] ?? '');

        if ($isEnabled) {
            if (empty($recipientEmails)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_required'),
                    'errors' => ['recipient_emails' => [__('messages.email_required')]],
                ], 422);
            }

            // Validate that at least one email is valid
            $emailList = array_map('trim', explode(',', $recipientEmails));
            $validEmails = array_filter($emailList, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

            if (empty($validEmails)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.email_required'),
                    'errors' => ['recipient_emails' => [__('messages.email_required')]],
                ], 422);
            }
        }

        $role->graphic_settings = $newSettings;
        $role->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_saved'),
            'settings' => $role->graphic_settings,
        ]);
    }

    public function sendTestEmail(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        // Require Enterprise plan for sending test emails
        if (! $role->isEnterprise()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.enterprise_feature_email_scheduling'),
            ], 403);
        }

        // Get recipient emails from graphic settings
        $settings = $role->graphic_settings ?? [];
        $recipientEmails = trim($settings['recipient_emails'] ?? '');

        if (empty($recipientEmails)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.email_required'),
            ], 400);
        }

        // Validate that at least one email is valid
        $emailList = array_map('trim', explode(',', $recipientEmails));
        $validEmails = array_filter($emailList, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        if (empty($validEmails)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.email_required'),
            ], 400);
        }

        try {
            $service = new GraphicEmailService;
            // Send to all recipients in a single email (service handles parsing)
            $result = $service->sendGraphicEmail($role, $recipientEmails);

            if (! $result) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.no_events_found'),
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => __('messages.test_email_sent'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send graphic test email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.email_failed').': '.$e->getMessage(),
            ], 500);
        }
    }

    public function generateGraphicData(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $graphicSettings = $role->graphic_settings ?? [];

        // Use request parameters if provided, otherwise fall back to saved settings
        $layout = $request->get('layout', $graphicSettings['layout'] ?? 'grid');
        $directRegistration = $role->direct_registration;

        // Validate layout parameter
        if (! in_array($layout, ['grid', 'list', 'row'])) {
            $layout = 'grid';
        }

        // Get date_position from request or settings (applies to grid and row layouts)
        $datePosition = $request->get('date_position', $graphicSettings['date_position'] ?? null);
        if (! in_array($layout, ['grid', 'row'])) {
            $datePosition = null; // Date position only applies to grid and row layouts
        }

        // Get max_per_row from request or settings (applies to row layout only)
        $maxPerRow = $request->get('max_per_row', $graphicSettings['max_per_row'] ?? null);
        if ($layout !== 'row') {
            $maxPerRow = null; // Max per row only applies to row layout
        }

        // Get overlay_text from request or settings (for custom text on flyers)
        $overlayText = $request->get('overlay_text', $graphicSettings['overlay_text'] ?? '');

        // Get event_count from request or settings
        $eventCountSetting = $request->get('event_count', $graphicSettings['event_count'] ?? null);
        $eventLimit = $eventCountSetting ? (int) $eventCountSetting : 20; // Default max is 20

        // Build the events query
        // Only include events that have their own flyer image (not relying on role image)
        $query = Event::with(['roles', 'tickets', 'venue'])
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)->where('is_accepted', true);
            })
            ->where('starts_at', '>=', now())
            ->whereNotNull('flyer_image_url')
            ->where('flyer_image_url', '!=', '')
            ->where('is_private', false)
            ->whereNull('event_password');

        // Only exclude recurring events if setting is true
        if ($request->boolean('exclude_recurring', false)) {
            $query->whereNull('days_of_week');
        }

        $events = $query->orderBy('starts_at')
            ->limit($eventLimit)
            ->get();

        if ($events->isEmpty()) {
            return response()->json(['error' => __('messages.no_events_found')]);
        }

        // Build options array for the generator
        $options = [
            'date_position' => $datePosition,
            'max_per_row' => $maxPerRow,
            'overlay_text' => $overlayText,
            'header_image_url' => $graphicSettings['header_image_url'] ?? null,
        ];

        // Allow fetching text and image independently to avoid timeouts
        $type = $request->get('type'); // 'text', 'image', or null (both)

        $response = [];

        // Generate image unless type=text
        if ($type !== 'text') {
            $generator = new EventGraphicGenerator($role, $events, $layout, $directRegistration, $options);
            $imageData = $generator->generate();
            $response['image'] = base64_encode($imageData);
        }

        // Generate text unless type=image
        if ($type !== 'image') {
            // Get text template - use request parameter if provided, otherwise fall back to saved settings
            $textTemplate = $request->has('text_template')
                ? $request->get('text_template', '')
                : ($graphicSettings['text_template'] ?? '');

            // Get URL formatting settings
            $urlSettings = [
                'url_include_https' => $request->has('url_include_https')
                    ? $request->boolean('url_include_https')
                    : ($graphicSettings['url_include_https'] ?? false),
                'url_include_id' => $request->has('url_include_id')
                    ? $request->boolean('url_include_id')
                    : ($graphicSettings['url_include_id'] ?? false),
            ];

            // Generate event text content
            $eventText = EventTextGenerator::generate($role, $events, $directRegistration, $textTemplate, $urlSettings);

            // Process text through AI if ai_prompt is set (Enterprise feature)
            // Use request parameter if provided, otherwise fall back to saved settings
            $aiPrompt = $request->has('ai_prompt')
                ? trim($request->get('ai_prompt', ''))
                : trim($graphicSettings['ai_prompt'] ?? '');
            if ($role->isEnterprise() && ! empty($aiPrompt) && config('services.google.gemini_key')) {
                $aiProcessedText = $this->processTextWithAI($eventText, $aiPrompt);
                if ($aiProcessedText) {
                    $eventText = $aiProcessedText;
                }
            }

            $response['text'] = $eventText;
            $response['download_url'] = route('event.download_graphic', ['subdomain' => $role->subdomain, 'layout' => $layout]);
        }

        return response()->json($response);
    }

    public function downloadGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $graphicSettings = $role->graphic_settings ?? [];

        $layout = $request->get('layout', $graphicSettings['layout'] ?? 'grid');
        $directRegistration = $role->direct_registration;

        // Validate layout parameter
        if (! in_array($layout, ['grid', 'list', 'row'])) {
            $layout = 'grid';
        }

        // Get date_position from settings (applies to grid and row layouts)
        $datePosition = $graphicSettings['date_position'] ?? null;
        if (! in_array($layout, ['grid', 'row'])) {
            $datePosition = null;
        }

        // Get max_per_row from settings (applies to row layout only)
        $maxPerRow = $graphicSettings['max_per_row'] ?? null;
        if ($layout !== 'row') {
            $maxPerRow = null;
        }

        // Get overlay_text from settings (for custom text on flyers)
        $overlayText = $graphicSettings['overlay_text'] ?? '';

        // Get event_count from settings
        $eventCountSetting = $graphicSettings['event_count'] ?? null;
        $eventLimit = $eventCountSetting ? (int) $eventCountSetting : 20;

        $excludeRecurring = $graphicSettings['exclude_recurring'] ?? false;

        // Only include events that have their own flyer image (not relying on role image)
        $query = Event::with(['roles', 'tickets', 'venue'])
            ->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)->where('is_accepted', true);
            })
            ->where('starts_at', '>=', now())
            ->whereNotNull('flyer_image_url')
            ->where('flyer_image_url', '!=', '')
            ->where('is_private', false)
            ->whereNull('event_password');

        // Only exclude recurring events if setting is true
        if ($excludeRecurring) {
            $query->whereNull('days_of_week');
        }

        $events = $query->orderBy('starts_at')
            ->limit($eventLimit)
            ->get();

        if ($events->isEmpty()) {
            return redirect()->back()->with('error', __('messages.no_events_found'));
        }

        // Build options array for the generator
        $options = [
            'date_position' => $datePosition,
            'max_per_row' => $maxPerRow,
            'overlay_text' => $overlayText,
            'header_image_url' => $graphicSettings['header_image_url'] ?? null,
        ];

        // Use the service to generate the graphic with the specified layout and options
        $generator = new EventGraphicGenerator($role, $events, $layout, $directRegistration, $options);
        $imageData = $generator->generate();

        // Generate filename based on layout
        $filename = $role->subdomain.'-upcoming-events.png';

        // Return the image as a response
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function processTextWithAI($text, $aiPrompt)
    {
        try {
            $prompt = "Transform the following event list text according to this instruction: \"{$aiPrompt}\"\n\nEvent List:\n{$text}\n\nReturn only the transformed text as a JSON string with a single key 'text'.";

            $response = GeminiUtils::sendPrompt($prompt);

            if ($response && isset($response[0]['text'])) {
                return $response[0]['text'];
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to process text with AI: '.$e->getMessage());

            return null;
        }
    }

    public function uploadHeaderImage(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $request->hasFile('header_image')) {
            return response()->json(['error' => __('messages.no_file_uploaded')], 400);
        }

        $file = $request->file('header_image');
        $extension = strtolower($file->getClientOriginalExtension());

        // Validate file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (! in_array($extension, $allowedExtensions)) {
            return response()->json(['error' => __('messages.invalid_file_type')], 400);
        }

        // Validate MIME type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array($file->getMimeType(), $allowedMimeTypes)) {
            return response()->json(['error' => __('messages.invalid_file_type')], 400);
        }

        // Get existing settings
        $settings = $role->graphic_settings ?? [];

        // Delete existing header image if present
        if (! empty($settings['header_image_url'])) {
            $path = $settings['header_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/'.$path;
            }
            Storage::delete($path);
        }

        // Store new image
        $filename = strtolower('graphic_header_'.Str::random(32).'.'.$extension);
        $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

        // Update graphic settings
        $settings['header_image_url'] = $filename;
        $role->graphic_settings = $settings;
        $role->save();

        // Build URL for preview
        if (config('filesystems.default') == 'local') {
            $url = url('/storage/'.$filename);
        } else {
            $url = Storage::url($filename);
        }

        return response()->json([
            'success' => true,
            'url' => $url,
            'filename' => $filename,
        ]);
    }

    public function deleteHeaderImage(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        // Get existing settings
        $settings = $role->graphic_settings ?? [];

        // Delete the header image if present
        if (! empty($settings['header_image_url'])) {
            $path = $settings['header_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/'.$path;
            }
            Storage::delete($path);

            // Clear from settings
            unset($settings['header_image_url']);
            $role->graphic_settings = $settings;
            $role->save();
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
