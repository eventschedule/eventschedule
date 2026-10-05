<?php

namespace App\Http\Controllers;

use App\Exceptions\LinkImportException;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use App\Services\GoogleCalendarService;
use App\Services\LinkImportService;
use App\Utils\GoogleImportUtils;
use App\Utils\IcsImportUtils;
use App\Utils\UrlUtils;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Exception as GoogleServiceException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Safely calculate expires_in seconds from google_token_expires_at
     */
    private function calculateExpiresIn($expiresAt): int
    {
        if (! $expiresAt) {
            return 3600; // Default to 1 hour
        }

        if (is_string($expiresAt)) {
            $expiresAt = \Carbon\Carbon::parse($expiresAt);
        }

        return now()->diffInSeconds($expiresAt);
    }

    /** Where a connection may be started from, other than account settings. */
    private const RETURN_TARGETS = ['import', 'settings'];

    /**
     * Redirect to Google OAuth.
     *
     * From the import page (?from=import&subdomain=...) the request is for read access only, and
     * the person comes back to that page. From a schedule's settings (?from=settings) it is the
     * full request, which is how a read-only connection gains the right to send events to
     * Google. What is remembered for the way back is the schedule's id and one of two words,
     * never an address: callback() builds the address itself.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $user = Auth::user();

        session()->forget('google_oauth_return');
        $from = null;
        if (in_array($request->query('from'), self::RETURN_TARGETS, true)) {
            $role = Role::subdomain((string) $request->query('subdomain'))->first();
            // The owner's alone: a schedule's Google connection is its owner's.
            if ($role && (int) $role->user_id === (int) $user->id) {
                $from = $request->query('from');
                session(['google_oauth_return' => ['role_id' => $role->id, 'from' => $from, 'at' => now()->getTimestamp()]]);
            }
        }

        // The Google account already connected, if there is one. Google is told which it is, so
        // that with several accounts signed in it does not offer another by default.
        $account = $user->google_token && $user->google_id ? (string) $user->google_id : null;

        if ($from === 'import') {
            // Another account would take over the token every synced schedule of this person
            // runs on, so the choice is only offered while nothing is synced.
            $chooseAccount = $request->boolean('choose') && ! $this->hasSyncedSchedule($user);

            return redirect($this->googleCalendarService->getAuthUrl(true, $chooseAccount, $account));
        }

        // If user has tokens but no refresh token, force re-authorization
        if ($user->google_token && ! $user->google_refresh_token) {
            $authUrl = $this->googleCalendarService->getAuthUrlWithForce();
        } else {
            $authUrl = $this->googleCalendarService->getAuthUrl(false, false, $account);
        }

        return redirect($authUrl);
    }

    private function hasSyncedSchedule($user): bool
    {
        return Role::where('user_id', $user->id)->whereNotNull('sync_direction')->exists();
    }

    /**
     * Where the person started, as [address, target]. Read once: the session entry is single use.
     * Anything stale, unknown or about a schedule that is not theirs lands in account settings,
     * where a connection has always landed.
     *
     * @return array{0: string, 1: ?string}
     */
    private function returnTarget(): array
    {
        $return = session()->pull('google_oauth_return');
        $settings = [route('profile.edit').'#section-google-calendar', null];

        if (! is_array($return) || ($return['at'] ?? 0) < now()->subMinutes(30)->getTimestamp()) {
            return $settings;
        }

        $role = Role::find($return['role_id'] ?? 0);
        if (! $role || (int) $role->user_id !== (int) Auth::id()) {
            return $settings;
        }

        return match ($return['from'] ?? null) {
            'import' => [route('event.show_import_ai', ['subdomain' => $role->subdomain, 'source' => 'google']), 'import'],
            'settings' => [route('role.edit', ['subdomain' => $role->subdomain]).'#section-integrations', 'settings'],
            default => $settings,
        };
    }

    /**
     * Back to where the connection was started, with what happened. The import page shows a
     * failure in place, beside the button that failed, so it gets its own flash key and no toast.
     */
    private function backWith(array $target, string $key, string $message): RedirectResponse
    {
        [$url, $from] = $target;

        if ($from === 'import') {
            return $key === 'error'
                ? redirect()->to($url)->with('google_import_error', $message)
                : redirect()->to($url);
        }

        return redirect()->to($url)->with($key, $message);
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(Request $request): RedirectResponse
    {
        $target = $this->returnTarget();

        try {
            $code = $request->get('code');

            if (! $code) {
                // No code is the person pressing Cancel on Google's screen, or Google refusing.
                return $this->backWith($target, 'error', __('messages.google_connect_cancelled'));
            }

            $expectedState = session()->pull('google_oauth_state');
            $providedState = (string) $request->get('state');

            if (! $expectedState || ! hash_equals($expectedState, $providedState)) {
                Log::warning('Google OAuth state mismatch', ['user_id' => Auth::id()]);

                return $this->backWith($target, 'error', __('messages.google_connect_failed'));
            }

            $token = $this->googleCalendarService->getAccessToken($code);

            if (isset($token['error'])) {
                // Google's own description stays in the log: it is written for a developer.
                Log::error('Google OAuth error', ['error' => $token['error'], 'description' => $token['error_description'] ?? null]);

                return $this->backWith($target, 'error', __('messages.google_connect_failed'));
            }

            // Google lets a person untick a permission on its screen. Without the one this
            // connection was started for there is nothing it can do, and nothing to replace a
            // working one with.
            $granted = isset($token['scope']) ? (string) $token['scope'] : null;
            if ($granted !== null && ! $this->grantCovers($granted, $target[1])) {
                return $this->backWith($target, 'error', __('messages.google_connect_no_calendar_access'));
            }

            // Store tokens in user record
            $user = Auth::user();
            $user->update([
                'google_id' => ! empty($token['id_token']) ? $this->extractGoogleId($token['id_token']) : null,
                'google_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? null,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);
            // What was granted, so the app knows whether it may write to the calendar
            // (User::googleCanWrite()). Not mass-assignable. Skipped for the moment of a deploy
            // in which the column is not there yet: the connection then reads as one made
            // before grants were recorded, which is how every existing one reads.
            if (User::googleScopesColumnReady()) {
                $user->forceFill(['google_token_scopes' => $granted])->save();
            }

            AuditService::log(AuditService::GOOGLE_CALENDAR_CONNECT, $user->id, 'User', $user->id);

            return $this->backWith($target, 'message', __('messages.google_connect_done'));

        } catch (\Exception $e) {
            Log::error('Google Calendar OAuth callback error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return $this->backWith($target, 'error', __('messages.google_connect_failed'));
        }
    }

    /**
     * Re-authorize Google Calendar (for users missing refresh token)
     */
    public function reauthorize(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->with('error', 'Google Calendar not connected. Please connect first.');
        }

        // Force re-authorization to get refresh token
        $authUrl = $this->googleCalendarService->getAuthUrlWithForce();

        return redirect($authUrl);
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(): RedirectResponse
    {
        $user = Auth::user();

        // Stops the webhooks, revokes the grant at Google and forgets every token, cursor and sync
        // record (GoogleCalendarService::forgetAuthorization).
        try {
            $this->googleCalendarService->forgetAuthorization($user, true);
        } catch (\Exception $e) {
            Log::warning('Failed to clean up during Google Calendar disconnect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $user->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ]);

        AuditService::log(AuditService::GOOGLE_CALENDAR_DISCONNECT, $user->id, 'User', $user->id);

        return redirect()->to(route('profile.edit').'#section-google-calendar')
            ->with('message', 'Google Calendar disconnected successfully.');
    }

    /**
     * Whether what Google granted is enough for where the connection was started. Whole scope
     * names are compared: "calendar.events" contains "calendar" and is a different permission.
     *
     * The import page starts by listing the person's calendars, which takes calendar.readonly
     * (or the whole calendar scope). The full request asks for that and for "edit events", and
     * Google lets a person tick either one alone: with only "edit events" the list answers 403.
     * Anywhere else a connection that can do one of the two is kept, as it always was.
     */
    private function grantCovers(string $granted, ?string $from): bool
    {
        $scopes = preg_split('/\s+/', trim($granted)) ?: [];
        $listsCalendars = [GoogleCalendar::CALENDAR_READONLY, GoogleCalendar::CALENDAR];

        return (bool) array_intersect($scopes, $from === 'import'
            ? $listsCalendars
            : array_merge($listsCalendars, [GoogleCalendar::CALENDAR_EVENTS]));
    }

    /**
     * What a refusal from Google's API means to the person, as [reason, message]. Three of them
     * are not "try again in a moment": a grant that was withdrawn (401), one that does not
     * cover what was asked (403, insufficientPermissions), and a calendar that is gone or no
     * longer shared with this account (404, 410, or 403 for the calendar itself).
     *
     * @return array{0: string, 1: string}
     */
    private function googleRefusal(GoogleServiceException $e): array
    {
        $reasons = array_column($e->getErrors() ?: [], 'reason');

        if ($e->getCode() === 401) {
            return ['reconnect', __('messages.google_import_reconnect')];
        }
        if ($e->getCode() === 403 && in_array('insufficientPermissions', $reasons, true)) {
            return ['reconnect', __('messages.google_connect_no_calendar_access')];
        }
        if (in_array($e->getCode(), [404, 410], true) || ($e->getCode() === 403 && in_array('forbidden', $reasons, true))) {
            return ['calendar_gone', __('messages.google_import_calendar_gone')];
        }

        return ['failed', __('messages.google_import_load_failed')];
    }

    /**
     * The schedule an import is for, when the person asking owns it. A schedule's Google
     * connection is its owner's, and the demo's shared account must not connect anything.
     */
    private function importSchedule(string $subdomain): ?Role
    {
        $role = Role::subdomain($subdomain)->first();

        if (! $role || (int) $role->user_id !== (int) Auth::id() || is_demo_mode()) {
            return null;
        }

        return $role;
    }

    /**
     * The calendars to choose from on the import page, and whose they are. A connection that is
     * missing or no longer works answers "not connected" so the page offers the button; Google
     * failing answers with an error, so it is never mistaken for "you have no calendars".
     */
    public function importCalendars(Request $request, string $subdomain)
    {
        if (! $this->importSchedule($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['connected' => false]);
        }

        try {
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['connected' => false, 'error' => __('messages.google_import_reconnect')]);
            }

            $calendars = $this->googleCalendarService->listImportCalendars();

            return response()->json([
                'connected' => true,
                'account' => GoogleImportUtils::accountOf($calendars),
                'calendars' => GoogleImportUtils::calendarChoices($calendars),
                'can_switch_account' => ! $this->hasSyncedSchedule($user),
            ]);
        } catch (GoogleServiceException $e) {
            [$reason, $message] = $this->googleRefusal($e);
            // A grant that is gone, or never covered the calendars, gets the button back.
            if ($reason === 'reconnect') {
                return response()->json(['connected' => false, 'error' => $message]);
            }
            report($e);

            return response()->json(['connected' => true, 'error' => __('messages.google_import_load_failed')], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['connected' => true, 'error' => __('messages.google_import_load_failed')], 422);
        }
    }

    /**
     * One calendar's upcoming events, as the same preview a pasted calendar link gives. Read
     * with the owner's own token, so the calendar is one Google lets them see; nothing is
     * changed there, and nothing is added here until they choose.
     */
    public function importEvents(Request $request, string $subdomain)
    {
        $role = $this->importSchedule($subdomain);
        if (! $role) {
            return response()->json(['error' => __('messages.not_authorized'), 'reason' => 'forbidden'], 403);
        }

        $request->validate(['calendar_id' => ['required', 'string', 'max:255']]);
        $user = Auth::user();

        // The same allowance as reading a link: both are a preview that costs a round trip.
        $limiter = 'link-import:'.$user->id;
        if (RateLimiter::tooManyAttempts($limiter, 10)) {
            return response()->json(['error' => __('messages.link_import_too_many'), 'reason' => 'too_many'], 429);
        }
        RateLimiter::hit($limiter, 60);

        if (! $user->google_token || ! $this->googleCalendarService->ensureValidToken($user)) {
            return response()->json(['error' => __('messages.google_import_reconnect'), 'reason' => 'reconnect'], 422);
        }

        try {
            $from = now($role->captureTimezone())->startOfDay();
            $listed = $this->googleCalendarService->listUpcomingEvents(
                (string) $request->input('calendar_id'),
                $from,
                $from->copy()->addDays(IcsImportUtils::WINDOW_DAYS)
            );

            // Cut short with nothing read is not "no upcoming events": it is not known.
            if (empty($listed['events']) && ! empty($listed['truncated'])) {
                return response()->json(['error' => __('messages.google_import_load_failed'), 'reason' => 'failed'], 422);
            }

            $preview = app(LinkImportService::class)->previewCalendar(
                $role,
                GoogleImportUtils::toCalendarText($listed['events'], $listed['timezone']),
                Event::IMPORT_GOOGLE,
                (string) ($listed['name'] ?? ''),
                ! empty($listed['truncated'])
            );

            return response()->json($preview);
        } catch (LinkImportException $e) {
            return response()->json(['error' => $e->getMessage(), 'reason' => $e->reason()], 422);
        } catch (GoogleServiceException $e) {
            [$reason, $message] = $this->googleRefusal($e);
            if ($reason === 'failed') {
                report($e);
            }

            return response()->json(['error' => $message, 'reason' => $reason], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => __('messages.google_import_load_failed'), 'reason' => 'failed'], 422);
        }
    }

    /**
     * Get user's Google Calendars
     */
    public function getCalendars(Request $request)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            // Ensure user has valid token before getting calendars
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $calendars = $this->googleCalendarService->getCalendars();

            return response()->json(['calendars' => $calendars]);

        } catch (\Exception $e) {
            Log::error('Failed to get Google Calendars', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to fetch calendars'], 500);
        }
    }

    /**
     * Sync a specific event to Google Calendar
     */
    public function syncEvent(Request $request, $subdomain, $eventId)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        // A read-only connection cannot add anything to a Google calendar: say so rather than
        // answer "synced" for an event the job behind this will not send.
        if (! $user->googleCanWrite()) {
            return response()->json(['error' => __('messages.google_needs_edit_access')], 422);
        }

        try {
            $event = \App\Models\Event::findOrFail(UrlUtils::decodeId($eventId));

            // Check if user has permission to sync this event
            if (! $event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Ensure user has valid token before syncing
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            // Get the role from the subdomain in the request
            $subdomain = request()->subdomain;
            $role = \App\Models\Role::subdomain($subdomain)->first();

            if (! $role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $sync = \App\Models\CalendarSync::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            if ($sync?->google_event_id) {
                $calendarId = $sync->google_calendar_id ?: $role->getGoogleCalendarId();
                $googleEvent = $this->googleCalendarService->updateEvent($event, $sync->google_event_id, $role, $calendarId);
            } else {
                $calendarId = $role->getGoogleCalendarId();
                $googleEvent = $this->googleCalendarService->createEvent($event, $role, $calendarId);
                if ($googleEvent) {
                    \App\Models\CalendarSync::updateOrCreate(
                        ['user_id' => $user->id, 'event_id' => $event->id, 'role_id' => $role->id],
                        ['google_event_id' => $googleEvent->getId(), 'google_calendar_id' => $calendarId]
                    );
                }
            }

            if ($googleEvent) {
                return response()->json([
                    'message' => 'Event synced successfully',
                    'google_event_id' => $googleEvent->getId(),
                ]);
            } else {
                return response()->json(['error' => 'Failed to sync event'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync event to Google Calendar', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync event'], 500);
        }
    }

    /**
     * Remove event from Google Calendar
     */
    public function unsyncEvent(Request $request, $subdomain, $eventId)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail(UrlUtils::decodeId($eventId));

            // Check if user has permission to unsync this event
            if (! $event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get the role from the subdomain in the request
            $subdomain = request()->subdomain;
            $role = \App\Models\Role::subdomain($subdomain)->first();

            if (! $role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $sync = \App\Models\CalendarSync::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            if ($sync?->google_event_id) {
                // Ensure user has valid token before deleting
                if (! $this->googleCalendarService->ensureValidToken($user)) {
                    return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
                }

                $calendarId = $sync->google_calendar_id ?: $role->getGoogleCalendarId();
                $this->googleCalendarService->deleteEvent($sync->google_event_id, $calendarId, $role->id);
                $sync->delete();
            }

            return response()->json(['message' => 'Event removed from Google Calendar']);

        } catch (\Exception $e) {
            Log::error('Failed to remove event from Google Calendar', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to remove event'], 500);
        }
    }

    /**
     * Unified sync method that handles 'to', 'from', and 'both' directions
     */
    public function sync(Request $request, $subdomain)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();

            // Owner only. This endpoint changes the schedule's sync direction and pulls with the
            // CALLER's token, and the calendar id it pulls ('primary' by default) resolves against
            // that token's account. Role::users() includes followers, so the old membership check
            // let anyone following the schedule publish their own calendar onto it. The standing
            // sync (google:sync, the webhook) runs as the owner; a member's own calendar goes
            // through memberSync() instead.
            if ((int) $user->id !== (int) $role->user_id) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            // Get sync direction from request, default to role's current setting
            $syncDirection = $request->input('sync_direction', $role->sync_direction);

            // Validate sync direction
            if (! in_array($syncDirection, ['to', 'from', 'both'])) {
                return response()->json(['error' => 'Invalid sync direction. Must be "to", "from", or "both"'], 400);
            }

            // Sending events to Google needs more than a read-only connection holds.
            if (in_array($syncDirection, ['to', 'both'], true) && ! $user->googleCanWrite()) {
                return response()->json(['error' => __('messages.google_needs_edit_access')], 422);
            }

            // Update the role's sync_direction if provided
            if ($request->has('sync_direction')) {
                $this->updateRoleSyncDirection($user, $syncDirection, $role);
            }

            // Persist the deletion policy if provided (shared across providers; also saved via the
            // main schedule form). Validated to the allowed set before writing.
            if ($request->has('delete_action') && in_array($request->input('delete_action'), ['ignore', 'cancel', 'delete'], true)) {
                $role->update(['calendar_delete_action' => $request->input('delete_action')]);
            }

            // Ensure user has valid token before syncing
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $results = [];

            // Handle different sync directions
            switch ($syncDirection) {
                case 'to':
                    $results['to'] = $this->googleCalendarService->syncUserEvents($user, $role);
                    break;

                case 'from':
                    $calendarId = $role->getGoogleCalendarId();
                    $results['from'] = $this->googleCalendarService->syncFromGoogleCalendar($user, $role, $calendarId);
                    break;

                case 'both':
                    // Sync to Google Calendar first
                    $results['to'] = $this->googleCalendarService->syncUserEvents($user, $role);

                    // Then sync from Google Calendar
                    $calendarId = $role->getGoogleCalendarId();
                    $results['from'] = $this->googleCalendarService->syncFromGoogleCalendar($user, $role, $calendarId);
                    break;
            }

            AuditService::log(AuditService::GOOGLE_CALENDAR_SYNC, $user->id, 'Role', $role->id, null, null, $syncDirection);

            return response()->json([
                'message' => __('messages.events_synced'),
                'sync_direction' => $syncDirection,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync events', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'sync_direction' => $syncDirection ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync events'], 500);
        }
    }

    /**
     * Clear owner outbound Google sync mappings for this schedule, then push all events to Google again (Event Schedule to Google only; never imports from Google).
     */
    public function forceSyncToGoogle(Request $request, string $subdomain)
    {
        $user = Auth::user();

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();

            if (! $role->users->contains($user)) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            if ($user->id !== $role->user_id) {
                return response()->json(['error' => __('messages.google_force_resync_owner_only')], 403);
            }

            $owner = $role->user;
            if (! $owner || ! $owner->google_token) {
                return response()->json(['error' => __('messages.google_calendar_not_connected')], 400);
            }

            if (! $role->syncsToGoogle()) {
                return response()->json(['error' => __('messages.google_force_resync_requires_push')], 400);
            }

            if (! $this->googleCalendarService->ensureValidToken($owner)) {
                return response()->json(['error' => __('messages.google_calendar_token_refresh_failed')], 401);
            }

            // Run the actual sync on the queue — pushing every event to Google
            // can take minutes for large schedules and would otherwise time out
            // at the gateway.
            \App\Jobs\ForceResyncGoogleCalendar::dispatch($owner, $role);

            AuditService::log(AuditService::GOOGLE_CALENDAR_SYNC, $user->id, 'Role', $role->id, null, null, 'force_to');

            return response()->json([
                'message' => __('messages.google_force_resync_queued'),
            ], 202);
        } catch (QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.sync_error')], 500);
        } catch (\Throwable $e) {
            report($e);
            Log::error('Failed to force sync events to Google Calendar', [
                'user_id' => $user?->id,
                'subdomain' => $subdomain,
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);

            $payload = ['error' => __('messages.sync_error')];
            if (config('app.debug')) {
                $payload['debug'] = get_class($e).': '.$e->getMessage();
            }

            return response()->json($payload, 500);
        }
    }

    /**
     * Enable or disable member calendar sync for the current user
     */
    public function memberSync(Request $request, $subdomain)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => __('messages.google_calendar_not_connected')], 400);
        }

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();

            // Must be a member but not the owner
            if ($role->user_id == $user->id) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            $roleUser = \App\Models\RoleUser::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->first();

            if (! $roleUser) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            $googleCalendarId = $request->input('google_calendar_id');

            if ($googleCalendarId) {
                // Their calendar is written to from here on, which a read-only connection
                // cannot do. Turning it off (below) stays possible either way.
                if (! $user->googleCanWrite()) {
                    return response()->json(['error' => __('messages.google_needs_edit_access')], 422);
                }

                // Enable sync
                $roleUser->update(['google_calendar_id' => $googleCalendarId]);

                AuditService::log(AuditService::GOOGLE_CALENDAR_MEMBER_SYNC, $user->id, 'Role', $role->id, null, null, 'enabled');

                return response()->json([
                    'message' => __('messages.member_sync_enabled'),
                ]);
            } else {
                // Disable sync - delete synced events from Google Calendar
                $oldCalendarId = $roleUser->google_calendar_id;

                if ($oldCalendarId) {
                    // Ensure valid token
                    if ($this->googleCalendarService->ensureValidToken($user)) {
                        $this->googleCalendarService->setAccessToken([
                            'access_token' => $user->google_token,
                            'refresh_token' => $user->google_refresh_token,
                            'expires_in' => $this->calculateExpiresIn($user->google_token_expires_at),
                        ]);

                        // Delete synced events from member's Google Calendar
                        $syncs = \App\Models\CalendarSync::where('user_id', $user->id)
                            ->where('role_id', $role->id)
                            ->whereNotNull('google_event_id')
                            ->get();

                        foreach ($syncs as $sync) {
                            try {
                                $this->googleCalendarService->deleteEvent($sync->google_event_id, $oldCalendarId, $role->id);
                            } catch (\Exception $e) {
                                Log::warning('Failed to delete member synced event from Google Calendar', [
                                    'user_id' => $user->id,
                                    'event_id' => $sync->event_id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }

                    // Clean up sync records
                    \App\Models\CalendarSync::where('user_id', $user->id)
                        ->where('role_id', $role->id)
                        ->delete();
                }

                $roleUser->update(['google_calendar_id' => null]);

                AuditService::log(AuditService::GOOGLE_CALENDAR_MEMBER_SYNC, $user->id, 'Role', $role->id, null, null, 'disabled');

                return response()->json([
                    'message' => __('messages.member_sync_disabled'),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update member calendar sync', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => __('messages.sync_error')], 500);
        }
    }

    /**
     * Update role's sync direction and handle webhook management
     */
    private function updateRoleSyncDirection($user, $syncDirection, $role = null)
    {
        try {
            // If no specific role provided, get the first role for this user
            if (! $role) {
                $role = $user->roles()->first();
                if (! $role) {
                    return;
                }
            }

            $oldSyncDirection = $role->sync_direction;

            // Update sync direction
            $role->update(['sync_direction' => $syncDirection]);

            // Handle webhook management based on sync direction
            if ($syncDirection === 'from' || $syncDirection === 'both') {
                // Need webhook for syncing from Google
                if (! $role->hasActiveWebhook()) {
                    // Ensure user has valid token before creating webhook
                    if (! $this->googleCalendarService->ensureValidToken($user)) {
                        Log::warning('Google Calendar token invalid and refresh failed during sync direction update', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                        ]);

                        return;
                    }

                    $calendarId = $role->getGoogleCalendarId();
                    $webhookUrl = route('google.calendar.webhook.handle');

                    // Create webhook
                    $webhook = $this->googleCalendarService->createWebhook($calendarId, $webhookUrl);

                    $role->update([
                        'google_webhook_id' => $webhook['id'],
                        'google_webhook_resource_id' => $webhook['resourceId'],
                        'google_webhook_expires_at' => \Carbon\Carbon::createFromTimestamp($webhook['expiration'] / 1000),
                    ]);
                }
            } else {
                // No webhook needed for 'to' direction or no sync
                if ($role->google_webhook_id) {
                    // Delete existing webhook
                    if ($this->googleCalendarService->ensureValidToken($user)) {
                        $this->googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    }
                }

                $role->update([
                    'google_webhook_id' => null,
                    'google_webhook_resource_id' => null,
                    'google_webhook_expires_at' => null,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update role sync direction', [
                'user_id' => $user->id,
                'role_id' => $role ? $role->id : null,
                'sync_direction' => $syncDirection,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extract Google ID from ID token
     */
    private function extractGoogleId(string $idToken): ?string
    {
        try {
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                return null;
            }

            // A token's parts are base64url: "-" and "_" where base64 has "+" and "/". Read as
            // plain base64 those two are skipped, everything after them shifts, and the payload
            // is no longer JSON. A name with a letter outside ASCII is enough to put one there.
            $payload = json_decode((string) base64_decode(strtr($parts[1], '-_', '+/')), true);

            return is_array($payload) && isset($payload['sub']) ? (string) $payload['sub'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
