<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Repos\EventRepo;
use App\Services\EventbriteService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventbriteController extends Controller
{
    protected EventRepo $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function show(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            abort(403, __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isPro()) {
            abort(403, __('messages.not_authorized'));
        }

        return view('event.import-eventbrite', [
            'role' => $role,
        ]);
    }

    public function connect(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'token' => 'required|string|max:500',
        ]);

        try {
            $service = new EventbriteService($request->token);

            $me = $service->getMe();
            $userName = $me['name'] ?? ($me['first_name'] ?? '').' '.($me['last_name'] ?? '');
            $userName = trim($userName);

            if (empty($userName)) {
                $userName = $me['emails'][0]['email'] ?? __('messages.eventbrite_user');
            }

            $orgId = $me['organization_id'] ?? null;

            if (! $orgId && ! empty($me['id'])) {
                $orgs = $service->getOrganizations($me['id']);
                if (! empty($orgs)) {
                    $orgId = $orgs[0]['id'] ?? null;
                }
            }

            if (! $orgId) {
                return response()->json([
                    'error' => __('messages.eventbrite_no_organization'),
                ], 422);
            }

            $rawEvents = $service->getOrganizationEvents($orgId);

            if (empty($rawEvents)) {
                return response()->json([
                    'error' => __('messages.eventbrite_no_events'),
                ], 422);
            }

            $timezone = auth()->user()->timezone ?: 'UTC';
            $events = [];

            foreach ($rawEvents as $raw) {
                $startUtc = $raw['start']['utc'] ?? null;
                $endUtc = $raw['end']['utc'] ?? null;

                $startLocal = null;
                $duration = null;

                if ($startUtc) {
                    $startCarbon = Carbon::parse($startUtc)->setTimezone($timezone);
                    $startLocal = $startCarbon->format('Y-m-d H:i:s');

                    if ($endUtc) {
                        $endCarbon = Carbon::parse($endUtc)->setTimezone($timezone);
                        $diffMinutes = $startCarbon->diffInMinutes($endCarbon);
                        $duration = round($diffMinutes / 60, 2);
                    }
                }

                $isUpcoming = false;
                if ($startUtc) {
                    $isUpcoming = Carbon::parse($startUtc)->isFuture();
                    if (!$isUpcoming && $duration && $duration >= 24) {
                        $isUpcoming = Carbon::parse($startUtc)->addHours($duration)->isFuture();
                    }
                }

                $tickets = [];
                foreach ($raw['ticket_classes'] ?? [] as $tc) {
                    $price = 0;
                    if (! ($tc['free'] ?? false) && isset($tc['cost']['major_value'])) {
                        $price = (float) $tc['cost']['major_value'];
                    }

                    $tickets[] = [
                        'type' => $tc['name'] ?? 'General Admission',
                        'quantity' => $tc['quantity_total'] ?? null,
                        'price' => $price,
                        'description' => $tc['description'] ?? null,
                    ];
                }

                $venue = null;
                if (! empty($raw['venue'])) {
                    $v = $raw['venue'];
                    $address = $v['address'] ?? [];
                    $venue = [
                        'name' => $v['name'] ?? null,
                        'address1' => $address['address_1'] ?? null,
                        'address2' => $address['address_2'] ?? null,
                        'city' => $address['city'] ?? null,
                        'state' => $address['region'] ?? null,
                        'postal_code' => $address['postal_code'] ?? null,
                        'country_code' => isset($address['country']) ? strtolower($address['country']) : null,
                    ];
                }

                $imageUrl = null;
                if (! empty($raw['logo']['original']['url'])) {
                    $imageUrl = $raw['logo']['original']['url'];
                } elseif (! empty($raw['logo']['url'])) {
                    $imageUrl = $raw['logo']['url'];
                }

                $currency = $raw['currency'] ?? null;
                if ($currency) {
                    $currency = strtoupper($currency);
                }

                $status = $raw['status'] ?? 'draft';

                $events[] = [
                    'eventbrite_id' => $raw['id'],
                    'name' => $raw['name']['text'] ?? 'Untitled Event',
                    'start_utc' => $startUtc,
                    'start_local' => $startLocal,
                    'duration' => $duration,
                    'status' => $status,
                    'is_upcoming' => $isUpcoming,
                    'image_url' => $imageUrl,
                    'category_id' => EventbriteService::mapCategory($raw['category_id'] ?? null),
                    'currency' => $currency,
                    'tickets' => $tickets,
                    'venue' => $venue,
                    'is_online' => $raw['online_event'] ?? false,
                ];
            }

            return response()->json([
                'user_name' => $userName,
                'events' => $events,
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => __('messages.eventbrite_connection_failed'),
            ], 422);
        }
    }

    public function import(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'token' => 'required|string|max:500',
            'eventbrite_id' => 'required|string|max:100',
            'name' => 'required|string|max:1000',
            'start_local' => 'nullable|string|max:30',
            'duration' => 'nullable|numeric|min:0|max:10080',
            'category_id' => 'nullable|integer|min:1|max:12',
            'currency' => 'nullable|string|max:3',
            'image_url' => 'nullable|url|max:2048',
            'is_online' => 'nullable|boolean',
            'tickets' => 'nullable|array|max:50',
            'tickets.*.type' => 'required|string|max:255',
            'tickets.*.quantity' => 'nullable|integer|min:0',
            'tickets.*.price' => 'required|numeric|min:0',
            'venue' => 'nullable|array',
            'venue.name' => 'nullable|string|max:255',
            'venue.address1' => 'nullable|string|max:255',
            'venue.address2' => 'nullable|string|max:255',
            'venue.city' => 'nullable|string|max:100',
            'venue.state' => 'nullable|string|max:100',
            'venue.postal_code' => 'nullable|string|max:20',
            'venue.country_code' => 'nullable|string|max:2',
            'venue_id' => 'nullable|string|max:20',
        ]);

        try {
            $service = new EventbriteService($request->token);

            // Fetch full description
            $htmlDescription = $service->getEventDescription($request->eventbrite_id);
            $description = $this->htmlToMarkdown($htmlDescription);

            // Download image if provided
            $imageFilename = null;
            if ($request->image_url) {
                $imageFilename = $service->downloadImage($request->image_url);
            }

            // Build event request data
            $eventData = [
                'name' => $request->name,
                'description' => $description,
                'starts_at' => $request->start_local,
                'duration' => $request->duration,
                'category_id' => $request->category_id,
                'tickets_enabled' => ! empty($request->tickets),
                'ticket_currency_code' => $request->currency,
                'schedule_type' => 'once',
            ];

            // Handle venue
            if ($request->venue_id) {
                $eventData['venue_id'] = $request->venue_id;
            } elseif ($request->venue) {
                $venueData = $request->venue;
                $eventData['venue_name'] = $venueData['name'] ?? null;
                $eventData['venue_address1'] = $venueData['address1'] ?? null;
                $eventData['venue_address2'] = $venueData['address2'] ?? null;
                $eventData['venue_city'] = $venueData['city'] ?? null;
                $eventData['venue_state'] = $venueData['state'] ?? null;
                $eventData['venue_postal_code'] = $venueData['postal_code'] ?? null;
                $eventData['venue_country_code'] = $venueData['country_code'] ?? null;
            }

            // Handle online events
            if ($request->is_online) {
                $eventData['registration_url'] = 'https://www.eventbrite.com/e/'.$request->eventbrite_id;
            }

            // Handle tickets
            if (! empty($request->tickets)) {
                $eventData['tickets'] = $request->tickets;
            }

            $eventRequest = new Request($eventData);
            $eventRequest->setUserResolver(function () {
                return auth()->user();
            });

            $event = $this->eventRepo->saveEvent($role, $eventRequest, null, false);

            // Store downloaded image
            if ($imageFilename) {
                $tempDir = storage_path('app/temp');
                $imagePath = $tempDir.'/'.$imageFilename;
                $realPath = realpath($imagePath);
                if ($realPath && str_starts_with($realPath, realpath($tempDir).DIRECTORY_SEPARATOR) && file_exists($realPath)) {
                    try {
                        $file = new \Illuminate\Http\UploadedFile($realPath, basename($realPath));
                        $filename = strtolower('flyer_'.Str::random(32).'.'.$file->getClientOriginalExtension());
                        $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                        $event->flyer_image_url = $filename;
                        $event->save();
                    } finally {
                        @unlink($realPath);
                    }
                }
            }

            $role->autoCurateEvent($event);

            // Get venue data for deduplication
            $venueData = null;
            $venue = $event->venue;
            if ($venue) {
                $venueData = $venue->toData();
            }

            return response()->json([
                'success' => true,
                'event' => [
                    'view_url' => $event->getGuestUrl($subdomain),
                    'edit_url' => route('event.edit', ['subdomain' => $subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
                ],
                'venue' => $venueData,
            ]);
        } catch (\Exception $e) {
            if ($imageFilename) {
                @unlink(storage_path('app/temp').'/'.$imageFilename);
            }

            report($e);

            return response()->json([
                'error' => __('messages.eventbrite_import_failed'),
            ], 500);
        }
    }

    /**
     * Convert HTML to markdown (simple conversion for event descriptions)
     */
    protected function htmlToMarkdown(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $text = $html;

        // Convert headings
        $text = preg_replace('/<h1[^>]*>(.*?)<\/h1>/si', "# $1\n\n", $text);
        $text = preg_replace('/<h2[^>]*>(.*?)<\/h2>/si', "## $1\n\n", $text);
        $text = preg_replace('/<h3[^>]*>(.*?)<\/h3>/si', "### $1\n\n", $text);
        $text = preg_replace('/<h4[^>]*>(.*?)<\/h4>/si', "#### $1\n\n", $text);

        // Convert bold and italic
        $text = preg_replace('/<(strong|b)[^>]*>(.*?)<\/(strong|b)>/si', '**$2**', $text);
        $text = preg_replace('/<(em|i)[^>]*>(.*?)<\/(em|i)>/si', '*$2*', $text);

        // Convert links
        $text = preg_replace('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/si', '[$2]($1)', $text);

        // Convert list items
        $text = preg_replace('/<li[^>]*>(.*?)<\/li>/si', "- $1\n", $text);
        $text = preg_replace('/<\/?[ou]l[^>]*>/si', "\n", $text);

        // Convert line breaks and paragraphs
        $text = preg_replace('/<br\s*\/?>/si', "\n", $text);
        $text = preg_replace('/<\/p>/si', "\n\n", $text);
        $text = preg_replace('/<p[^>]*>/si', '', $text);

        // Convert images
        $text = preg_replace('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/si', '![]($1)', $text);

        // Strip remaining HTML tags
        $text = strip_tags($text);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Clean up excessive whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        return $text;
    }
}
