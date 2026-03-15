<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarpoolOfferRequest;
use App\Http\Requests\CarpoolReportRequest;
use App\Http\Requests\CarpoolReviewRequest;
use App\Jobs\SendQueuedEmail;
use App\Mail\CarpoolNotification;
use App\Models\CarpoolOffer;
use App\Models\CarpoolReport;
use App\Models\CarpoolRequest;
use App\Models\CarpoolReview;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarpoolController extends Controller
{
    protected function resolveEventAndRole($subdomain, $event_hash)
    {
        $role = Role::where('subdomain', $subdomain)->firstOrFail();

        if (! $role->isPro() || ! $role->carpool_enabled) {
            abort(404);
        }

        $event_id = UrlUtils::decodeId($event_hash);
        $event = Event::with('roles')->findOrFail($event_id);

        if (! $event->roles()->wherePivot('role_id', $role->id)->wherePivot('is_accepted', true)->exists()) {
            abort(404);
        }

        return [$role, $event];
    }

    public function index(Request $request, $subdomain, $event_hash, $date = null)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();

        $isRecurring = (bool) $event->days_of_week;

        if ($isRecurring && ! $date) {
            abort(404);
        }

        if (! $isRecurring && $date) {
            abort(404);
        }

        if ($isRecurring && ! $event->matchesDate(\Carbon\Carbon::parse($date))) {
            abort(404);
        }

        $query = CarpoolOffer::where('event_id', $event->id)
            ->where('status', 'active')
            ->with(['user', 'approvedRequests.user', 'pendingRequests.user']);

        if ($isRecurring) {
            $query->where('event_date', $date);
        }

        $offers = $query->orderBy('created_at', 'desc')->get();

        $myOffers = collect();
        $myRequests = collect();
        if ($user) {
            $myOffers = $offers->where('user_id', $user->id);

            $offerIds = $offers->pluck('id');
            $myRequests = CarpoolRequest::where('user_id', $user->id)
                ->whereIn('carpool_offer_id', $offerIds)
                ->whereNot('status', 'cancelled')
                ->get()
                ->keyBy('carpool_offer_id');
        }

        $eventEnded = false;
        $endDateTime = $event->getEndDateTime($date);
        if ($endDateTime && $endDateTime->isPast()) {
            $eventEnded = true;
        }

        $myReviews = collect();
        if ($user && $eventEnded) {
            $myReviews = CarpoolReview::where('reviewer_user_id', $user->id)
                ->whereIn('carpool_offer_id', $offers->pluck('id'))
                ->get();
        }

        // Batch-load carpool ratings to avoid N+1 queries
        $allUserIds = $offers->pluck('user_id');
        foreach ($offers as $offer) {
            $allUserIds = $allUserIds->merge($offer->pendingRequests->pluck('user_id'));
            $allUserIds = $allUserIds->merge($offer->approvedRequests->pluck('user_id'));
        }
        $allUserIds = $allUserIds->unique()->values();

        $carpoolRatings = [];
        if ($allUserIds->isNotEmpty()) {
            $ratingsData = CarpoolReview::selectRaw('reviewed_user_id, AVG(rating) as avg_rating, COUNT(*) as review_count')
                ->whereIn('reviewed_user_id', $allUserIds)
                ->groupBy('reviewed_user_id')
                ->get()
                ->keyBy('reviewed_user_id');

            foreach ($ratingsData as $userId => $data) {
                $carpoolRatings[$userId] = [
                    'rating' => $data->avg_rating,
                    'count' => $data->review_count,
                ];
            }
        }

        $fonts = array_unique(array_filter($event->roles->pluck('font_family')->toArray()));

        return view('carpool.index', compact(
            'role', 'event', 'date', 'offers', 'user', 'fonts',
            'myOffers', 'myRequests', 'isRecurring', 'eventEnded', 'myReviews', 'carpoolRatings'
        ));
    }

    public function agreeDisclaimer(Request $request, $subdomain, $event_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $request->validate([
            'agree' => ['required', 'accepted'],
        ]);

        $user->carpool_agreed_at = now();
        $user->save();

        return redirect()->back()->with('success', __('messages.carpool_disclaimer_agreed'));
    }

    public function storeOffer(CarpoolOfferRequest $request, $subdomain, $event_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if (! $user->hasAgreedToCarpool()) {
            return redirect()->back()->with('error', __('messages.carpool_must_agree'));
        }

        $isRecurring = (bool) $event->days_of_week;
        $eventDate = $request->input('event_date');

        if ($isRecurring && ! $eventDate) {
            return redirect()->back()->with('error', __('messages.carpool_date_required'));
        }

        if ($isRecurring && $eventDate && ! $event->matchesDate(\Carbon\Carbon::parse($eventDate))) {
            return redirect()->back()->with('error', __('messages.carpool_invalid_date'));
        }

        $direction = $request->input('direction');

        // Check timing
        $startDateTime = $event->getStartDateTime($eventDate);
        $endDateTime = $event->getEndDateTime($eventDate);

        if (in_array($direction, ['to_event', 'round_trip']) && $startDateTime && $startDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_started'));
        }

        if ($direction === 'from_event' && $endDateTime && $endDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_ended'));
        }

        try {
            $result = DB::transaction(function () use ($user, $event, $direction, $isRecurring, $eventDate, $request, $role) {
                $directionsToCheck = match ($direction) {
                    'round_trip' => ['round_trip', 'to_event', 'from_event'],
                    'to_event' => ['to_event', 'round_trip'],
                    'from_event' => ['from_event', 'round_trip'],
                };

                $existingOffer = CarpoolOffer::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->whereIn('direction', $directionsToCheck)
                    ->where('status', 'active')
                    ->when($isRecurring, fn ($q) => $q->where('event_date', $eventDate))
                    ->when(! $isRecurring, fn ($q) => $q->whereNull('event_date'))
                    ->lockForUpdate()
                    ->exists();

                if ($existingOffer) {
                    return null;
                }

                return CarpoolOffer::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'event_date' => $isRecurring ? $eventDate : null,
                    'direction' => $direction,
                    'city' => $request->input('city'),
                    'departure_time' => $request->input('departure_time'),
                    'meeting_point' => $request->input('meeting_point'),
                    'total_spots' => $request->input('total_spots'),
                    'note' => $request->input('note'),
                ]);
            });

            if (! $result) {
                return redirect()->back()->with('error', __('messages.carpool_duplicate_offer'));
            }
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return redirect()->back()->with('error', __('messages.error'));
        }

        session(['has_carpool_activity' => true]);

        return redirect()->back()->with('success', __('messages.carpool_offer_created'));
    }

    public function cancelOffer(Request $request, $subdomain, $event_hash, $offer_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $offer = CarpoolOffer::findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        if ($offer->user_id !== $user->id) {
            abort(403);
        }

        $endDateTime = $event->getEndDateTime($offer->event_date?->format('Y-m-d'));
        if ($endDateTime && $endDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_ended'));
        }

        $approvedRequests = collect();
        $pendingRequests = collect();

        $result = DB::transaction(function () use ($offer, &$approvedRequests, &$pendingRequests) {
            $locked = CarpoolOffer::lockForUpdate()->find($offer->id);

            if ($locked->status !== 'active') {
                return false;
            }

            $locked->status = 'cancelled';
            $locked->save();

            // Collect pending requests before cancelling them
            $pendingRequests = $locked->pendingRequests()->with('user')->get();
            $locked->requests()->where('status', 'pending')->update(['status' => 'cancelled']);

            // Cancel approved requests
            $approvedRequests = $locked->approvedRequests()->with('user')->get();
            foreach ($approvedRequests as $carpoolRequest) {
                $carpoolRequest->status = 'cancelled';
                $carpoolRequest->save();
            }

            return true;
        });

        if (! $result) {
            return redirect()->back()->with('error', __('messages.carpool_offer_not_available'));
        }

        // Notify approved riders (outside transaction)
        foreach ($approvedRequests as $carpoolRequest) {
            $this->sendCarpoolEmail(
                $carpoolRequest->user,
                $role,
                'carpool_offer_cancelled',
                $event,
                $offer,
                $carpoolRequest
            );
        }

        // Notify pending riders (outside transaction)
        foreach ($pendingRequests as $carpoolRequest) {
            $this->sendCarpoolEmail(
                $carpoolRequest->user,
                $role,
                'carpool_offer_cancelled',
                $event,
                $offer,
                $carpoolRequest
            );
        }

        return redirect()->back()->with('success', __('messages.carpool_offer_cancelled'));
    }

    public function updateSpots(Request $request, $subdomain, $event_hash, $offer_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $offer = CarpoolOffer::findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        if ($offer->user_id !== $user->id) {
            abort(403);
        }

        $endDateTime = $event->getEndDateTime($offer->event_date?->format('Y-m-d'));
        if ($endDateTime && $endDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_ended'));
        }

        if ($offer->status !== 'active') {
            return redirect()->back()->with('error', __('messages.carpool_offer_not_available'));
        }

        $request->validate([
            'total_spots' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $newSpots = (int) $request->input('total_spots');

        $result = DB::transaction(function () use ($offer, $newSpots) {
            $locked = CarpoolOffer::lockForUpdate()->find($offer->id);

            if ($locked->status !== 'active') {
                return 'not_active';
            }

            $approvedCount = $locked->approvedRequests()->count();

            if ($newSpots < $approvedCount) {
                return 'below_approved';
            }

            $locked->total_spots = $newSpots;
            $locked->save();

            return 'ok';
        });

        if ($result === 'not_active') {
            return redirect()->back()->with('error', __('messages.carpool_offer_not_available'));
        }

        if ($result === 'below_approved') {
            return redirect()->back()->with('error', __('messages.carpool_spots_below_approved'));
        }

        return redirect()->back()->with('success', __('messages.carpool_spots_updated'));
    }

    public function requestRide(Request $request, $subdomain, $event_hash, $offer_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if (! $user->hasAgreedToCarpool()) {
            return redirect()->back()->with('error', __('messages.carpool_must_agree'));
        }

        $offer = CarpoolOffer::with('approvedRequests')->findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        if ($offer->user_id === $user->id) {
            return redirect()->back()->with('error', __('messages.carpool_cannot_request_own'));
        }

        if ($offer->status !== 'active') {
            return redirect()->back()->with('error', __('messages.carpool_offer_not_available'));
        }

        $direction = $offer->direction;

        if (in_array($direction, ['to_event', 'round_trip'])) {
            $startDateTime = $event->getStartDateTime($offer->event_date?->format('Y-m-d'));
            if ($startDateTime && $startDateTime->isPast()) {
                return redirect()->back()->with('error', __('messages.carpool_event_started'));
            }
        } else {
            $endDateTime = $event->getEndDateTime($offer->event_date?->format('Y-m-d'));
            if ($endDateTime && $endDateTime->isPast()) {
                return redirect()->back()->with('error', __('messages.carpool_event_ended'));
            }
        }

        $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $carpoolRequest = DB::transaction(function () use ($offer, $user, $request) {
                $lockedOffer = CarpoolOffer::lockForUpdate()->find($offer->id);

                if ($lockedOffer->status !== 'active') {
                    return 'not_active';
                }

                // Check fullness atomically inside transaction
                $approvedCount = CarpoolRequest::where('carpool_offer_id', $offer->id)
                    ->where('status', 'approved')
                    ->count();
                if ($approvedCount >= $lockedOffer->total_spots) {
                    return 'full';
                }

                // Check for existing cancelled/declined request and reuse it
                $existing = CarpoolRequest::lockForUpdate()
                    ->where('carpool_offer_id', $offer->id)
                    ->where('user_id', $user->id)
                    ->whereIn('status', ['cancelled', 'declined'])
                    ->first();

                if ($existing) {
                    $existing->status = 'pending';
                    $existing->message = $request->input('message');
                    $existing->reminder_sent_at = null;
                    $existing->save();

                    return $existing;
                }

                return CarpoolRequest::create([
                    'carpool_offer_id' => $offer->id,
                    'user_id' => $user->id,
                    'message' => $request->input('message'),
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()->with('error', __('messages.carpool_already_requested'));
            }

            return redirect()->back()->with('error', __('messages.error'));
        }

        if ($carpoolRequest === 'not_active') {
            return redirect()->back()->with('error', __('messages.carpool_offer_not_available'));
        }

        if ($carpoolRequest === 'full') {
            return redirect()->back()->with('error', __('messages.carpool_offer_full'));
        }

        // Notify driver
        $this->sendCarpoolEmail(
            $offer->user,
            $role,
            'carpool_ride_requested',
            $event,
            $offer,
            $carpoolRequest
        );

        session(['has_carpool_activity' => true]);

        return redirect()->back()->with('success', __('messages.carpool_request_sent'));
    }

    public function cancelRequest(Request $request, $subdomain, $event_hash, $request_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $carpoolRequest = CarpoolRequest::with('offer')->findOrFail(UrlUtils::decodeId($request_hash));

        if ($carpoolRequest->offer->event_id !== $event->id) {
            abort(404);
        }

        if ($carpoolRequest->user_id !== $user->id) {
            abort(403);
        }

        $endDateTime = $event->getEndDateTime($carpoolRequest->offer->event_date?->format('Y-m-d'));
        if ($endDateTime && $endDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_ended'));
        }

        $result = DB::transaction(function () use ($carpoolRequest) {
            $locked = CarpoolRequest::lockForUpdate()->find($carpoolRequest->id);
            if (! in_array($locked->status, ['pending', 'approved'])) {
                return null;
            }
            $wasApproved = $locked->status === 'approved';
            $locked->status = 'cancelled';
            $locked->save();

            return $wasApproved;
        });

        if ($result === null) {
            return redirect()->back()->with('error', __('messages.carpool_request_already_cancelled'));
        }

        // Notify driver if rider was already approved
        if ($result) {
            $offer = $carpoolRequest->offer;
            $this->sendCarpoolEmail(
                $offer->user,
                $role,
                'carpool_request_cancelled',
                $event,
                $offer,
                $carpoolRequest
            );
        }

        return redirect()->back()->with('success', __('messages.carpool_request_cancelled'));
    }

    public function approveRequest(Request $request, $subdomain, $event_hash, $offer_hash, $request_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $offer = CarpoolOffer::findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        if ($offer->user_id !== $user->id) {
            abort(403);
        }

        $endDateTime = $event->getEndDateTime($offer->event_date?->format('Y-m-d'));
        if ($endDateTime && $endDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_ended'));
        }

        $carpoolRequest = CarpoolRequest::findOrFail(UrlUtils::decodeId($request_hash));

        if ($carpoolRequest->carpool_offer_id !== $offer->id) {
            abort(404);
        }

        // Atomically check status, spots and approve
        $result = DB::transaction(function () use ($offer, $carpoolRequest) {
            $lockedOffer = CarpoolOffer::lockForUpdate()->find($offer->id);
            $lockedRequest = CarpoolRequest::lockForUpdate()->find($carpoolRequest->id);

            if ($lockedOffer->status !== 'active') {
                return 'cancelled';
            }

            if ($lockedRequest->status !== 'pending') {
                return 'not_pending';
            }

            $approvedCount = CarpoolRequest::where('carpool_offer_id', $lockedOffer->id)
                ->where('status', 'approved')
                ->count();

            if ($approvedCount >= $lockedOffer->total_spots) {
                return 'full';
            }

            $lockedRequest->status = 'approved';
            $lockedRequest->save();

            return 'approved';
        });

        if ($result === 'cancelled') {
            return redirect()->back()->with('error', __('messages.carpool_offer_cancelled'));
        }

        if ($result === 'not_pending') {
            return redirect()->back()->with('error', __('messages.carpool_request_not_pending'));
        }

        if ($result === 'full') {
            return redirect()->back()->with('error', __('messages.carpool_offer_full'));
        }

        // Notify rider with contact info
        $this->sendCarpoolEmail(
            $carpoolRequest->user,
            $role,
            'carpool_request_approved',
            $event,
            $offer,
            $carpoolRequest
        );

        return redirect()->back()->with('success', __('messages.carpool_request_approved'));
    }

    public function declineRequest(Request $request, $subdomain, $event_hash, $offer_hash, $request_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $offer = CarpoolOffer::findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        if ($offer->user_id !== $user->id) {
            abort(403);
        }

        $endDateTime = $event->getEndDateTime($offer->event_date?->format('Y-m-d'));
        if ($endDateTime && $endDateTime->isPast()) {
            return redirect()->back()->with('error', __('messages.carpool_event_ended'));
        }

        $carpoolRequest = CarpoolRequest::findOrFail(UrlUtils::decodeId($request_hash));

        if ($carpoolRequest->carpool_offer_id !== $offer->id) {
            abort(404);
        }

        $declined = DB::transaction(function () use ($carpoolRequest) {
            $locked = CarpoolRequest::lockForUpdate()->find($carpoolRequest->id);
            if ($locked->status !== 'pending') {
                return false;
            }
            $locked->status = 'declined';
            $locked->save();

            return true;
        });

        if (! $declined) {
            return redirect()->back()->with('error', __('messages.carpool_request_not_pending'));
        }

        // Notify rider
        $this->sendCarpoolEmail(
            $carpoolRequest->user,
            $role,
            'carpool_request_declined',
            $event,
            $offer,
            $carpoolRequest
        );

        return redirect()->back()->with('success', __('messages.carpool_request_declined'));
    }

    public function storeReview(CarpoolReviewRequest $request, $subdomain, $event_hash, $offer_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $offer = CarpoolOffer::with('approvedRequests')->findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        // Check event has ended
        $endDateTime = $event->getEndDateTime($offer->event_date?->format('Y-m-d'));
        if (! $endDateTime || $endDateTime->isFuture()) {
            return redirect()->back()->with('error', __('messages.carpool_review_not_yet'));
        }

        // User must be a participant (driver or approved rider)
        $isDriver = $offer->user_id === $user->id;
        $isApprovedRider = $offer->approvedRequests->contains('user_id', $user->id);

        if (! $isDriver && ! $isApprovedRider) {
            abort(403);
        }

        // Determine who is being reviewed
        $reviewedUserId = UrlUtils::decodeId($request->input('reviewed_user_id'));

        if (! $reviewedUserId) {
            abort(404);
        }

        // Validate reviewed user is a participant
        if ($isDriver) {
            // Driver can review approved riders
            if (! $offer->approvedRequests->contains('user_id', $reviewedUserId)) {
                abort(403);
            }
        } else {
            // Rider can review the driver
            if ($reviewedUserId !== $offer->user_id) {
                abort(403);
            }
        }

        try {
            CarpoolReview::create([
                'carpool_offer_id' => $offer->id,
                'reviewer_user_id' => $user->id,
                'reviewed_user_id' => $reviewedUserId,
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()->with('error', __('messages.carpool_already_reviewed'));
            }

            return redirect()->back()->with('error', __('messages.error'));
        }

        return redirect()->back()->with('success', __('messages.carpool_review_submitted'));
    }

    public function report(CarpoolReportRequest $request, $subdomain, $event_hash, $offer_hash, $user_hash)
    {
        [$role, $event] = $this->resolveEventAndRole($subdomain, $event_hash);

        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $offer = CarpoolOffer::with('approvedRequests')->findOrFail(UrlUtils::decodeId($offer_hash));

        if ($offer->event_id !== $event->id) {
            abort(404);
        }

        $reporterIsDriver = $offer->user_id === $user->id;
        $reporterIsApprovedRider = $offer->approvedRequests->contains('user_id', $user->id);
        if (! $reporterIsDriver && ! $reporterIsApprovedRider) {
            abort(403);
        }

        $reportedUserId = UrlUtils::decodeId($user_hash);

        if (! $reportedUserId) {
            abort(404);
        }

        if ($reportedUserId === $user->id) {
            abort(403);
        }

        $isDriver = $offer->user_id === $reportedUserId;
        $isApprovedRider = $offer->approvedRequests->contains('user_id', $reportedUserId);
        if (! $isDriver && ! $isApprovedRider) {
            abort(403);
        }

        try {
            CarpoolReport::create([
                'reporter_user_id' => $user->id,
                'reported_user_id' => $reportedUserId,
                'carpool_offer_id' => $offer->id,
                'reason' => $request->input('reason'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()->with('error', __('messages.carpool_already_reported'));
            }

            return redirect()->back()->with('error', __('messages.error'));
        }

        return redirect()->back()->with('success', __('messages.carpool_report_submitted'));
    }

    public function adminRemoveOffer(Request $request, $subdomain, $offer_hash)
    {
        $offer = CarpoolOffer::with(['event', 'role'])->findOrFail(UrlUtils::decodeId($offer_hash));

        if ($request->user()->cannot('update', $offer->event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $offer->role;

        $approvedRequests = collect();
        $pendingRequests = collect();

        DB::transaction(function () use ($offer, &$approvedRequests, &$pendingRequests) {
            $locked = CarpoolOffer::lockForUpdate()->find($offer->id);
            $locked->update(['status' => 'cancelled']);

            $pendingRequests = $locked->pendingRequests()->with('user')->get();
            $approvedRequests = $locked->approvedRequests()->with('user')->get();
            $locked->requests()->whereIn('status', ['pending', 'approved'])->update(['status' => 'cancelled']);
        });

        // Notify riders after successful cancellation
        if ($role) {
            foreach ($approvedRequests as $carpoolRequest) {
                $this->sendCarpoolEmail($carpoolRequest->user, $role, 'carpool_offer_cancelled', $offer->event, $offer, $carpoolRequest);
            }
            foreach ($pendingRequests as $carpoolRequest) {
                $this->sendCarpoolEmail($carpoolRequest->user, $role, 'carpool_offer_cancelled', $offer->event, $offer, $carpoolRequest);
            }
        }

        return redirect()->back()->with('message', __('messages.carpool_offer_removed'));
    }

    public function adminDismissReport(Request $request, $subdomain, $report_hash)
    {
        $report = CarpoolReport::with('offer.event')->findOrFail(UrlUtils::decodeId($report_hash));

        if ($request->user()->cannot('update', $report->offer->event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $report->delete();

        return redirect()->back()->with('message', __('messages.carpool_report_dismissed'));
    }

    public function myCarpools(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $myOffers = CarpoolOffer::where('user_id', $user->id)
            ->with(['event.roles', 'role', 'approvedRequests', 'pendingRequests'])
            ->orderBy('created_at', 'desc')
            ->get();

        $myRequests = CarpoolRequest::where('user_id', $user->id)
            ->with(['offer.event.roles', 'offer.role', 'offer.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('carpool.my-carpools', compact('user', 'myOffers', 'myRequests'));
    }

    protected function sendCarpoolEmail($recipient, $role, $type, $event, $offer, $carpoolRequest = null)
    {
        if (is_demo_role($role)) {
            return;
        }

        if (! $recipient->carpool_notifications_enabled || $recipient->is_subscribed === false) {
            return;
        }

        // Check if email sending is possible
        if (config('app.hosted')) {
            if (! $role->hasEmailSettings()) {
                return;
            }
        } else {
            $mailer = config('mail.default');
            if (in_array($mailer, ['log', 'array'])) {
                return;
            }
        }

        try {
            $mailable = new CarpoolNotification($type, $event, $offer, $carpoolRequest, $role, $recipient);

            SendQueuedEmail::dispatch(
                $mailable,
                $recipient->email,
                $role->id,
                $recipient->language_code ?? app()->getLocale()
            );
        } catch (\Exception $e) {
            report($e);
            Log::error('Failed to send carpool notification: '.$e->getMessage(), [
                'type' => $type,
                'offer_id' => $offer->id,
                'recipient_id' => $recipient->id,
            ]);
        }
    }
}
