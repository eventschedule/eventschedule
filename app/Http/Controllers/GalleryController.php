<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\GalleryImage;
use App\Models\Role;
use App\Utils\GalleryUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * The organizer gallery editor's upload endpoints (resources/views/partials/gallery-editor).
 *
 * Every photo is stored the moment it is added, as a DRAFT row tagged with the token the page
 * minted and owned by the uploader. Nothing here publishes anything: the edit form's Save commits
 * the drafts it lists (GalleryUtils::sync()), and app:prune-gallery-drafts removes the rest.
 *
 * The target says which gallery the photo is for, because a new event's drafts and a schedule's
 * both have no event_id yet: `schedule`, `event` (with its hash) or `new_event`.
 */
class GalleryController extends Controller
{
    public function upload(Request $request, string $subdomain): JsonResponse
    {
        $context = $this->resolveContext($request, $subdomain);

        if ($context instanceof JsonResponse) {
            return $context;
        }

        [$owner, $event, $token] = $context;

        $file = $request->file('photo');

        // A body over post_max_size arrives with no file at all, and one over
        // upload_max_filesize as a file carrying UPLOAD_ERR_INI_SIZE: both are "too large".
        if (! $file || ($file instanceof UploadedFile && in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true))) {
            return $this->error(__('messages.gallery_too_large', ['size' => $this->maxSizeLabel()]), 422);
        }

        // photo[] posts an array of files, which is not something this endpoint takes.
        if (! $file instanceof UploadedFile) {
            return $this->error(__('messages.gallery_invalid_type'), 422);
        }

        if ($limit = $this->draftLimitResponse($request, $token)) {
            return $limit;
        }

        try {
            $stored = GalleryUtils::storeUpload($file);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return $this->error(__('messages.gallery_upload_failed'), 500);
        } catch (BusinessException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Exception $e) {
            $message = str_contains($e->getMessage(), 'too large')
                ? __('messages.gallery_too_large', ['size' => $this->maxSizeLabel()])
                : __('messages.gallery_invalid_type');

            return $this->error($message, 422);
        }

        $image = $this->createDraft($request, $owner, $event, $token, $stored);

        return response()->json(['success' => true, 'image' => $image->toEditorArray()]);
    }

    /**
     * Copy approved fan photos of an event into its gallery, as drafts like any upload. A copy,
     * not a reference, so rejecting or deleting the fan photo later cannot break the gallery.
     */
    public function fromFanPhotos(Request $request, string $subdomain): JsonResponse
    {
        $context = $this->resolveContext($request, $subdomain);

        if ($context instanceof JsonResponse) {
            return $context;
        }

        [$owner, $event, $token] = $context;

        if (! $event) {
            return $this->error(__('messages.not_authorized'), 403);
        }

        $ids = collect((array) $request->input('photo_ids', []))
            ->filter(fn ($hash) => is_string($hash) && $hash !== '')
            ->map(fn ($hash) => UrlUtils::decodeId($hash))
            ->filter()
            ->unique()
            ->take(GalleryUtils::maxImages())
            ->all();

        $photos = EventPhoto::where('event_id', $event->id)
            ->where('is_approved', true)
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (EventPhoto $photo) => array_search($photo->id, $ids, true));

        $images = [];

        foreach ($photos as $photo) {
            if ($this->draftLimitResponse($request, $token)) {
                break;
            }

            try {
                $stored = GalleryUtils::copyFanPhoto($photo);
            } catch (\Throwable $e) {
                report($e);
                $stored = null;
            }

            if (! $stored) {
                continue;
            }

            // Credited to whoever took it. Never submitterName()'s generic fallback ("User"), and
            // never an email: the credit is shown to guests.
            $credit = trim((string) ($photo->user?->name ?: $photo->guest_name));
            $image = $this->createDraft($request, $owner, $event, $token, $stored, $credit !== '' ? $credit : null);
            $images[] = $image->toEditorArray();
        }

        return response()->json(['success' => true, 'images' => $images]);
    }

    /**
     * @return array{0: Role, 1: ?Event, 2: string}|JsonResponse
     */
    private function resolveContext(Request $request, string $subdomain): array|JsonResponse
    {
        if (is_demo_mode()) {
            return $this->error(__('messages.demo_mode_restriction'), 403);
        }

        $user = $request->user();

        if (! $user || ! $user->isEditor($subdomain)) {
            return $this->error(__('messages.not_authorized'), 403);
        }

        $token = $request->input('draft_token');

        if (! GalleryUtils::isValidToken($token)) {
            return $this->error(__('messages.invalid_request'), 422);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $event = null;

        switch ($request->input('target')) {
            case 'event':
                $eventHash = $request->input('event');
                $event = is_string($eventHash) && $eventHash !== '' ? Event::find(UrlUtils::decodeId($eventHash)) : null;

                if (! $event || $user->cannot('update', $event)) {
                    return $this->error(__('messages.not_authorized'), 403);
                }

                // The event's OWNING schedule, whose plan decides whether guests see the gallery,
                // not whichever schedule the organizer reached the form through.
                $owner = $event->ticketingRole() ?? $role;
                break;
            case 'new_event':
            case 'schedule':
                $owner = $role;
                break;
            default:
                return $this->error(__('messages.invalid_request'), 422);
        }

        if (! $owner->isPro()) {
            return $this->error(__('messages.gallery_requires_pro'), 403);
        }

        return [$owner, $event, $token];
    }

    /**
     * Uploads are cheap to make and cost storage until pruned, so the drafts one user can have
     * outstanding are capped, and so are the ones behind one form (a gallery can never commit more
     * than maxImages() of them anyway).
     */
    private function draftLimitResponse(Request $request, string $token): ?JsonResponse
    {
        $userDrafts = GalleryImage::whereNotNull('draft_token')->where('user_id', $request->user()->id)->count();
        $tokenDrafts = GalleryImage::where('draft_token', $token)->where('user_id', $request->user()->id)->count();

        if ($userDrafts >= GalleryUtils::MAX_DRAFTS_PER_USER || $tokenDrafts >= GalleryUtils::maxImages() * 2) {
            return $this->error(__('messages.gallery_limit_reached', ['max' => GalleryUtils::maxImages()]), 422);
        }

        return null;
    }

    /**
     * @param  array{filename: string, width: ?int, height: ?int, color: ?string}  $stored
     */
    private function createDraft(Request $request, Role $owner, ?Event $event, string $token, array $stored, ?string $credit = null): GalleryImage
    {
        return GalleryImage::create([
            'role_id' => $owner->id,
            'event_id' => $event?->id,
            'user_id' => $request->user()->id,
            'filename' => $stored['filename'],
            'width' => $stored['width'],
            'height' => $stored['height'],
            'color' => $stored['color'],
            'credit' => $credit ? mb_substr($credit, 0, 100) : null,
            'sort_order' => 0,
            'draft_token' => $token,
        ]);
    }

    private function maxSizeLabel(): string
    {
        return round(GalleryUtils::maxUploadBytes() / 1048576, 1).' MB';
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $message], $status);
    }
}
