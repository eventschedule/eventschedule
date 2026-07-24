<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventComment;
use App\Models\EventFeedback;
use App\Models\EventPhoto;
use App\Models\EventVideo;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Read endpoints for post-event feedback and fan content (issue #108).
 *
 * Both are scoped to schedules the caller owns or administers, so an integration can pull
 * ratings and approved submissions for its own site without exposing anything a schedule
 * owner cannot already see in the admin portal.
 */
class ApiFeedbackController extends Controller
{
    protected const MAX_PER_PAGE = 500;

    protected const DEFAULT_PER_PAGE = 100;

    /**
     * Schedule ids the API key's owner may read from.
     */
    private function managedRoleIds()
    {
        return auth()->user()->roles()
            ->wherePivotIn('level', ['owner', 'admin'])
            ->pluck('roles.id');
    }

    private function perPage(Request $request): int
    {
        return min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );
    }

    private function paginatedResponse($paginator, Request $request)
    {
        return response()->json([
            'data' => $paginator->map(fn ($row) => $row->toApiData())->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'path' => $request->url(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * GET /api/feedback - post-event star ratings and comments.
     *
     * Pro only, matching FeedbackController: collecting feedback is a Pro feature, so
     * reading it back is too.
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'nullable|string',
                'subdomain' => 'nullable|string',
                'event_date' => 'nullable|date_format:Y-m-d',
                'min_rating' => 'nullable|integer|min:1|max:5',
                'from' => 'nullable|date_format:Y-m-d',
                'to' => 'nullable|date_format:Y-m-d',
                'per_page' => 'nullable|integer|min:1|max:500',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $roleIds = $this->managedRoleIds();

        $query = EventFeedback::with(['event', 'sale'])
            ->whereHas('event.roles', fn ($q) => $q->whereIn('roles.id', $roleIds))
            ->whereHas('event.roles', fn ($q) => $q->wherePro())
            // A deleted or unpaid sale is not a real attendee, and the guest page hides
            // those too.
            ->whereHas('sale', fn ($q) => $q->where('is_deleted', false)->where('status', 'paid'));

        if ($request->filled('event_id')) {
            $query->where('event_id', UrlUtils::decodeId($request->event_id));
        }

        if ($request->filled('subdomain')) {
            $query->whereHas('sale', fn ($q) => $q->where('subdomain', $request->subdomain));
        }

        if ($request->filled('event_date')) {
            $query->where('event_date', $request->event_date);
        }

        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (int) $request->min_rating);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $feedback = $query->orderBy('created_at', 'desc')->paginate($this->perPage($request));

        return $this->paginatedResponse($feedback, $request);
    }

    /**
     * GET /api/fan-content - fan comments, photos and videos in one feed.
     *
     * Defaults to approved rows only, which is what an external site wants to display.
     * Pass is_approved=0 to review the moderation queue instead.
     */
    public function fanContent(Request $request)
    {
        try {
            $request->validate([
                'type' => 'nullable|string|in:comment,photo,video',
                'event_id' => 'nullable|string',
                'subdomain' => 'nullable|string',
                'event_date' => 'nullable|date_format:Y-m-d',
                'is_approved' => 'nullable|boolean',
                'per_page' => 'nullable|integer|min:1|max:500',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $roleIds = $this->managedRoleIds();
        $isApproved = $request->has('is_approved') ? $request->boolean('is_approved') : true;

        $models = [
            'comment' => EventComment::class,
            'photo' => EventPhoto::class,
            'video' => EventVideo::class,
        ];

        if ($request->filled('type')) {
            $models = [$request->type => $models[$request->type]];
        }

        // Three tables with no shared parent, so each is queried and the results merged.
        // Ordered newest first overall, then paginated in PHP.
        $rows = collect();

        foreach ($models as $model) {
            $query = $model::with(['event'])
                ->where('is_approved', $isApproved)
                ->whereHas('event.roles', fn ($q) => $q->whereIn('roles.id', $roleIds));

            if ($request->filled('event_id')) {
                $query->where('event_id', UrlUtils::decodeId($request->event_id));
            }

            if ($request->filled('subdomain')) {
                $query->whereHas('event.roles', fn ($q) => $q->where('roles.subdomain', $request->subdomain));
            }

            if ($request->filled('event_date')) {
                $query->where('event_date', $request->event_date);
            }

            $rows = $rows->concat($query->orderBy('created_at', 'desc')->get());
        }

        $rows = $rows->sortByDesc('created_at')->values();

        $perPage = $this->perPage($request);
        $page = max(1, (int) $request->input('page', 1));

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return $this->paginatedResponse($paginator, $request);
    }
}
