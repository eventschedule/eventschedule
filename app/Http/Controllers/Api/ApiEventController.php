<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Image;
use App\Models\Role;
use App\Models\RoleUser;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiEventController extends Controller
{
    protected const MAX_PER_PAGE = 1000;
    protected const DEFAULT_PER_PAGE = 100;

    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function resources(Request $request)
    {
        $roles = Role::where('user_id', auth()->id())
            ->whereIn('type', ['venue', 'curator', 'talent'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'venues' => $roles->where('type', 'venue')->map->toApiData()->values(),
                'curators' => $roles->where('type', 'curator')->map->toApiData()->values(),
                'talent' => $roles->where('type', 'talent')->map->toApiData()->values(),
            ],
            'meta' => [
                'total_roles' => $roles->count(),
                'path' => $request->url(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function index(Request $request)
    {
        /*
        $schedule = Role::findOrFail($scheduleId);
        
        if ($schedule->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        */

        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $events = Event::with('roles')
                    ->where('user_id', auth()->id())
                    ->paginate($perPage);

        /*
        $events = Event::whereHas('roles', function($query) use ($scheduleId) {
            $query->where('role_id', $scheduleId);
        })->paginate($perPage);
        */

        return response()->json([
            'data' => $events->map(function($event) {
                return $event->toApiData();
            })->values(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'from' => $events->firstItem(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'to' => $events->lastItem(),
                'total' => $events->total(),
                'path' => $request->url(),
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request, $subdomain)
    {
        $role = Role::with('groups')->subdomain($subdomain)->firstOrFail();
        $encodedRoleId = UrlUtils::encodeId($role->id);
        
        if ($role->isVenue()) {
            $request->merge(['venue_id' => $encodedRoleId]);
        } else if ($role->isTalent()) {
            $request->merge(['members' => [$encodedRoleId => ['name' => $role->name]]]);
        }   

        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'starts_at' => 'required|date_format:Y-m-d H:i:s',
            'venue_id' => 'required_without_all:venue_address1,event_url',
            'venue_address1' => 'required_without_all:venue_id,event_url',
            'event_url' => 'required_without_all:venue_id,venue_address1',
            'members' => 'array',
            'schedule' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
        ]);

        // Handle group name to group_id conversion
        if ($request->has('schedule')) {
            $groupSlug = $request->schedule;
            $group = $role->groups()->where('slug', $groupSlug)->first();
            if ($group) {
                $request->merge(['current_role_group_id' => UrlUtils::encodeId($group->id)]);
            } else {
                return response()->json(['error' => 'Schedule not found: ' . $request->schedule], 422);
            }
        }

        // Handle category name to category_id conversion
        if ($request->has('category_name') && ! $request->has('category_id')) {
            $categoryName = trim((string) $request->category_name);
            $categorySlug = Str::slug($categoryName);
            $categoryId = null;

            try {
                $eventTypes = EventType::ordered();
            } catch (\Throwable $exception) {
                $eventTypes = collect();
            }

            if ($eventTypes->isNotEmpty()) {
                foreach ($eventTypes as $eventType) {
                    if (Str::slug($eventType->name) === $categorySlug || Str::slug($eventType->translatedName('en')) === $categorySlug) {
                        $categoryId = $eventType->id;
                        break;
                    }

                    foreach (($eventType->translations ?? []) as $translation) {
                        if (! is_string($translation)) {
                            continue;
                        }

                        if (Str::slug($translation) === $categorySlug) {
                            $categoryId = $eventType->id;
                            break 2;
                        }
                    }
                }
            }

            if (! $categoryId) {
                $fallbackCategories = config('app.event_categories', []);

                foreach ($fallbackCategories as $id => $name) {
                    if (Str::slug($name) === $categorySlug) {
                        $categoryId = $id;
                        break;
                    }
                }
            }

            if ($categoryId) {
                $request->merge(['category_id' => $categoryId]);
            } else {
                return response()->json(['error' => 'Category not found: ' . $request->category_name], 422);
            }
        }

        $roleIds = RoleUser::where('user_id', auth()->user()->id)
                        ->whereIn('level', ['owner', 'follower'])
                        ->orderBy('id')->pluck('role_id')->toArray();

        if ($role->isVenue()) {
            $request->merge(['venue_id' => $encodedRoleId]);
        } else if ($request->has('venue_address1') && $request->has('venue_name')) {
            $venue = Role::where('name', $request->venue_name)
                ->where('address1', $request->venue_address1)
                ->where('type', 'venue')
                ->where('is_deleted', false)
                ->whereIn('id', $roleIds)
                ->orderBy('id')
                ->first();

            if ($venue) {
                $request->merge(['venue_id' => UrlUtils::encodeId($venue->id)]);    
            }
        }

        if ($role->isTalent()) {
            $request->merge(['members' => [$encodedRoleId => ['name' => $role->name]]]);
        } else if ($request->has('members')) {

            $processedMembers = [];
            foreach ($request->members as $memberId => $memberData) {

                $talent = Role::where('is_deleted', false)
                    ->where(function($query) use ($memberData) {
                        $query->when(isset($memberData['name']), function($q) use ($memberData) {
                                $q->where('name', $memberData['name']);
                            })
                            ->when(isset($memberData['email']), function($q) use ($memberData) {
                                $q->orWhere('email', $memberData['email']); 
                            });
                    })
                    ->where('type', 'talent')
                    ->whereIn('id', $roleIds)
                    ->orderBy('id')
                    ->first();

                if ($talent) {
                    $processedMembers[UrlUtils::encodeId($talent->id)] = $memberData;
                } else {
                    $processedMembers[] = $memberData;
                }
            }

            $request->merge(['members' => $processedMembers]);
        }

        if ($role->isCurator()) {
            $request->merge(['curators' => [$encodedRoleId]]);
        }

        $event = $this->eventRepo->saveEvent($role, $request, null);
                
        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Event created successfully'
            ]
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function flyer(Request $request, $event_id)
    {
        $event = Event::findOrFail(UrlUtils::decodeId($event_id));

        if ($event->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->has('flyer_image_id')) {
            $request->validate([
                'flyer_image_id' => ['nullable', 'integer', 'exists:images,id'],
            ]);

            $imageId = $request->input('flyer_image_id');

            if ($imageId) {
                $image = Image::find($imageId);

                if ($image) {
                    $event->flyer_image_id = $image->id;
                    $event->flyer_image_url = $image->path;
                    $event->save();
                }
            } else {
                $event->flyer_image_id = null;
                $event->flyer_image_url = null;
                $event->save();
            }
        }

        if ($request->hasFile('flyer_image')) {
            $disk = storage_public_disk();

            if (! $event->flyer_image_id && $event->flyer_image_url) {
                $path = storage_normalize_path($event->getAttributes()['flyer_image_url']);
                if ($path !== '') {
                    Storage::disk($disk)->delete($path);
                }
            }

            $file = $request->file('flyer_image');
            $filename = strtolower('flyer_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = storage_put_file_as_public($disk, $file, $filename);
            $dimensions = @getimagesize($file->getRealPath());

            $image = Image::create([
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'width' => $dimensions[0] ?? null,
                'height' => $dimensions[1] ?? null,
                'user_id' => $event->user_id,
            ]);

            $event->flyer_image_id = $image->id;
            $event->flyer_image_url = $image->path;
            $event->save();
        }
        
        return response()->json([
            'data' => $event->toApiData(),
            'meta' => [
                'message' => 'Flyer uploaded successfully'
            ]
        ], 200, [], JSON_PRETTY_PRINT);
    }
}