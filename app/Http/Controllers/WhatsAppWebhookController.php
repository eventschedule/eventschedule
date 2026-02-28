<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Repos\EventRepo;
use App\Services\WhatsAppService;
use App\Utils\GeminiUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppWebhookController extends Controller
{
    private EventRepo $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function handle(Request $request)
    {
        $twiml = '<Response></Response>';

        if (! WhatsAppService::verifySignature($request)) {
            return response($twiml, 200)->header('Content-Type', 'text/xml');
        }

        $phone = $request->input('From', '');
        $phone = str_replace('whatsapp:', '', $phone);

        $user = User::where('phone', $phone)
            ->whereNotNull('phone_verified_at')
            ->first();

        if (! $user) {
            WhatsAppService::sendMessage($phone, __('messages.whatsapp_user_not_found'));

            return response($twiml, 200)->header('Content-Type', 'text/xml');
        }

        $role = null;
        if ($user->default_role_id) {
            $role = Role::where('id', $user->default_role_id)
                ->where('is_deleted', false)
                ->first();
        }

        if (! $role) {
            // Fall back to single editor role
            $editorRoles = $user->editor()->get();
            if ($editorRoles->count() === 1) {
                $role = $editorRoles->first();
            }
        }

        if (! $role || ! $user->isEditor($role->subdomain)) {
            WhatsAppService::sendMessage($phone, __('messages.whatsapp_no_default_schedule'));

            return response($twiml, 200)->header('Content-Type', 'text/xml');
        }

        $body = trim($request->input('Body', ''));
        $numMedia = (int) $request->input('NumMedia', 0);
        $file = null;
        $tempFiles = [];

        try {
            // Download image if present
            if ($numMedia > 0) {
                $mediaContentType = $request->input('MediaContentType0', '');
                $mediaUrl = $request->input('MediaUrl0', '');

                if (str_starts_with($mediaContentType, 'image/') && $mediaUrl) {
                    $imageData = WhatsAppService::downloadMedia($mediaUrl);
                    if ($imageData) {
                        $extension = match (true) {
                            str_contains($mediaContentType, 'png') => 'png',
                            str_contains($mediaContentType, 'gif') => 'gif',
                            str_contains($mediaContentType, 'webp') => 'webp',
                            default => 'jpg',
                        };
                        $tempPath = storage_path('app/temp/whatsapp_'.Str::random(16).'.'.$extension);
                        if (! is_dir(dirname($tempPath))) {
                            mkdir(dirname($tempPath), 0755, true);
                        }
                        file_put_contents($tempPath, $imageData);
                        $tempFiles[] = $tempPath;
                        $file = new \Illuminate\Http\UploadedFile($tempPath, 'event_image.'.$extension, $mediaContentType, null, true);
                    }
                }
            }

            if (empty($body) && ! $file) {
                WhatsAppService::sendMessage($phone, __('messages.whatsapp_empty_message'));

                return response($twiml, 200)->header('Content-Type', 'text/xml');
            }

            // Set user for auth context (needed by GeminiUtils and EventRepo)
            Auth::setUser($user);

            $parsed = GeminiUtils::parseEvent($role, $body ?: '', $file);

            if (empty($parsed) || empty($parsed[0])) {
                WhatsAppService::sendMessage($phone, __('messages.whatsapp_parse_failed'));

                return response($twiml, 200)->header('Content-Type', 'text/xml');
            }

            $eventData = $parsed[0];

            // Check for duplicate
            if (! empty($eventData['event_url'])) {
                WhatsAppService::sendMessage($phone, __('messages.whatsapp_duplicate_event', ['url' => $eventData['event_url']]));

                return response($twiml, 200)->header('Content-Type', 'text/xml');
            }

            $fakeRequest = $this->buildSaveRequest($eventData, $role, $user);
            $event = $this->eventRepo->saveEvent($role, $fakeRequest, null, false);

            // Handle flyer image
            if (! empty($eventData['social_image'])) {
                $tempDir = storage_path('app/temp');
                $imagePath = $tempDir.'/'.basename($eventData['social_image']);
                $realPath = realpath($imagePath);
                if ($realPath && str_starts_with($realPath, $tempDir.DIRECTORY_SEPARATOR) && file_exists($realPath)) {
                    $imageFile = new \Illuminate\Http\UploadedFile($realPath, basename($realPath));
                    $filename = strtolower('flyer_'.Str::random(32).'.'.$imageFile->getClientOriginalExtension());
                    $imageFile->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                    $event->flyer_image_url = $filename;
                    $event->save();
                }
            }

            // Auto-curate into default curator schedules
            $role->autoCurateEvent($event);

            // Build confirmation message
            $timezone = $role->timezone ?: $user->timezone ?: 'UTC';
            $eventUrl = $event->getGuestUrl($role->subdomain);
            $message = __('messages.whatsapp_event_created', [
                'name' => $event->name,
                'url' => $eventUrl,
            ]);

            if ($event->starts_at) {
                $date = Carbon::parse($event->starts_at)->setTimezone($timezone);
                $message = __('messages.whatsapp_event_created', [
                    'name' => $event->name,
                    'url' => $eventUrl,
                ])."\n".$date->format('M j, Y g:ia');
            }

            WhatsAppService::sendMessage($phone, $message);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            WhatsAppService::sendMessage($phone, __('messages.whatsapp_error'));
        } finally {
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    private function buildSaveRequest(array $eventData, Role $role, User $user): Request
    {
        $data = [];

        // Event name
        $data['name'] = $eventData['event_name'] ?? '';
        $data['name_en'] = $eventData['event_name_en'] ?? '';

        // Short name (for slug generation)
        $data['short_name'] = $eventData['event_short_name'] ?? '';
        $data['short_name_en'] = $eventData['event_short_name_en'] ?? '';

        // Date/time - pass in local time; saveEvent() handles timezone conversion
        if (! empty($eventData['event_date_time'])) {
            $dateTime = $eventData['event_date_time'];
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $dateTime)) {
                $dateTime .= ':00';
            }
            $data['starts_at'] = $dateTime;
        }

        // Duration
        $data['duration'] = $eventData['event_duration'] ?? '';

        // Descriptions
        $data['short_description'] = $eventData['short_description'] ?? '';
        $data['short_description_en'] = $eventData['short_description_en'] ?? '';
        $data['description'] = $eventData['event_details'] ?? '';

        // Venue fields
        $data['venue_name'] = $eventData['venue_name'] ?? '';
        $data['venue_name_en'] = $eventData['venue_name_en'] ?? '';
        $data['venue_email'] = $eventData['venue_email'] ?? '';
        $data['venue_website'] = $eventData['venue_website'] ?? '';
        $data['venue_address1'] = $eventData['event_address'] ?? '';
        $data['venue_address1_en'] = $eventData['event_address_en'] ?? '';
        $data['venue_city'] = $eventData['event_city'] ?? '';
        $data['venue_city_en'] = $eventData['event_city_en'] ?? '';
        $data['venue_state'] = $eventData['event_state'] ?? '';
        $data['venue_state_en'] = $eventData['event_state_en'] ?? '';
        $data['venue_postal_code'] = $eventData['event_postal_code'] ?? '';
        $data['venue_country_code'] = $eventData['event_country_code'] ?? $role->country_code ?? '';
        $data['venue_id'] = $eventData['venue_id'] ?? '';
        $data['venue_language_code'] = $role->language_code ?? 'en';

        // Social image
        $data['social_image'] = $eventData['social_image'] ?? '';

        // Registration URL
        $data['registration_url'] = $eventData['registration_url'] ?? '';

        // Category
        $data['category_id'] = $eventData['category_id'] ?? '';

        // Custom field values
        if (! empty($eventData['custom_field_values'])) {
            $data['custom_field_values'] = $eventData['custom_field_values'];
        }

        // Performers -> members
        if (! empty($eventData['performers'])) {
            $members = [];
            foreach ($eventData['performers'] as $i => $performer) {
                if (! empty($performer['talent_id'])) {
                    $members[$performer['talent_id']] = $performer['talent_id'];
                } else {
                    $key = 'new_talent_'.$i;
                    $members[$key] = [
                        'name' => $performer['name'] ?? '',
                        'name_en' => $performer['name_en'] ?? '',
                        'email' => $performer['email'] ?? '',
                        'website' => $performer['website'] ?? '',
                    ];
                }
            }
            $data['members'] = $members;
        }

        // Talent ID (if talent schedule)
        if (! empty($eventData['talent_id'])) {
            $data['members'] = [$eventData['talent_id'] => $eventData['talent_id']];
        }

        $request = new Request($data);
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
