<?php

namespace App\Http\Controllers;

use App\Jobs\SendQueuedEmail;
use App\Mail\CalendarPrivacyMail;
use App\Models\CalendarExposureReview;
use App\Models\CalendarImportHold;
use App\Models\Event;
use App\Models\Role;
use App\Services\AuditService;
use App\Services\CalendarArrival;
use App\Services\CalendarExposureWatch;
use App\Services\GoogleCalendarService;
use App\Services\MeetingLinkVenues;
use App\Services\MicrosoftCalendarService;
use App\Utils\CalendarPrivacy;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * /admin/private-calendars: schedules a calendar import made into somebody's private calendar.
 *
 * Two pages. The list shows the schedules CalendarExposureWatch found, most still public first.
 * A schedule's page shows the evidence first (what was found, in which events, read from the
 * calendar), then the decision as four steps an operator takes in order, each its own press and
 * each audited: hide what is public now, pause the sync, tell the owner, mark reviewed. Hide and
 * Pause can be undone from the same place. Nothing here is automatic: the watch only finds.
 */
class AdminCalendarPrivacyController extends Controller
{
    /** Days before the owner may be emailed again. */
    public const EMAIL_COOLDOWN_DAYS = 7;

    public function index(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $page = CalendarExposureWatch::schedules()->paginate(20)->withQueryString();

        // Mark reviewed on the last row of the last page comes back to a page that no longer exists.
        if ($page->isEmpty() && $page->currentPage() > 1) {
            return redirect()->to($page->url($page->lastPage()));
        }

        $roles = Role::with('user')->whereIn('id', collect($page->items())->pluck('role_id'))->get()->keyBy('id');
        $reviews = CalendarExposureReview::with(['hider', 'pauser', 'emailer'])->whereIn('role_id', $roles->keys())->get()->keyBy('role_id');

        $rows = collect($page->items())->map(function ($item) use ($roles, $reviews) {
            $role = $roles->get($item->role_id);
            if (! $role) {
                return null;
            }

            $events = CalendarExposureWatch::eventsFor($role, true, 10);

            return [
                'role' => $role,
                'public_now' => (int) $item->public_now,
                'kinds' => array_values(array_intersect(CalendarPrivacy::FLAGS, $events->pluck('kinds')->flatten()->unique()->all())),
                'snippets' => CalendarExposureWatch::snippets($events, 2, 80),
                'syncs' => CalendarExposureWatch::syncs($role),
                'last' => $this->lastAction($reviews->get($role->id)),
            ];
        })->filter()->values();

        return view('admin.private-calendars', [
            'page' => $page,
            'rows' => $rows,
        ]);
    }

    public function show(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        $events = CalendarExposureWatch::eventsFor($role);
        $review = CalendarExposureReview::with(['reviewer', 'hider', 'pauser', 'emailer'])->where('role_id', $role->id)->first();
        $syncs = CalendarExposureWatch::syncs($role);

        $public = $events->filter(fn ($item) => ! $item['event']->is_draft);
        $stubs = $this->meetingStubs($events->pluck('event'));
        $heldByUs = CalendarImportHold::where('role_id', $role->id)->whereNotNull('held_by')->whereNull('decided_at')->count();
        $owner = $role->user;

        return view('admin.private-calendars-schedule', [
            'role' => $role,
            'hash' => $hash,
            'events' => $events,
            'kinds' => array_values(array_intersect(CalendarPrivacy::FLAGS, $events->pluck('kinds')->flatten()->unique()->all())),
            'snippets' => CalendarExposureWatch::snippets($events, 3, 120),
            'syncs' => $syncs,
            'review' => $review,
            'publicCount' => $public->count(),
            'stubCount' => $stubs->count(),
            'heldByUs' => $heldByUs,
            'reading' => collect($syncs)->contains(fn ($sync) => ! $sync['paused']),
            'paused' => collect($syncs)->contains(fn ($sync) => $sync['paused']),
            'owner' => $owner,
            'canEmail' => $owner && filled($owner->email) && $this->mailConfigured(),
            'emailAgainAt' => $review?->emailed_at?->copy()->addDays(self::EMAIL_COOLDOWN_DAYS),
        ]);
    }

    /** Step 1: what is public now goes to drafts, and the venues made out of meeting links go. */
    public function hide(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        $events = CalendarExposureWatch::eventsFor($role, true);

        $hidden = 0;
        $kept = 0;
        foreach ($events as $item) {
            $reason = CalendarPrivacy::holdReason($item['findings']) ?? CalendarPrivacy::HOLD_MEETING_DETAILS;
            if (CalendarArrival::hide($item['event'], $role, $reason, $request->user()->id)) {
                $hidden++;
            } else {
                $kept++;
            }
        }

        $venues = 0;
        foreach ($this->meetingStubs($events->pluck('event')) as $venue) {
            if (MeetingLinkVenues::takeDown($venue, $request->user()->id)) {
                $venues++;
            }
        }

        CalendarExposureReview::updateOrCreate(['role_id' => $role->id], [
            'hidden_at' => now(),
            'hidden_by' => $request->user()->id,
            'hidden_count' => $hidden,
            'kept_count' => $kept,
            'venue_count' => $venues,
        ]);
        CalendarExposureWatch::flushCount();

        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_HIDE, $request->user()->id, 'App\\Models\\Role', $role->id, null,
            ['hidden' => $hidden, 'kept' => $kept, 'venues' => $venues], "Hid {$hidden} events of {$role->subdomain}");

        $message = __('messages.private_calendars_hidden_done', ['events' => $hidden, 'venues' => $venues]);
        if ($kept > 0) {
            $message .= ' '.trans_choice('messages.private_calendars_kept_with_tickets', $kept, ['count' => $kept]);
        }

        return redirect()->route('admin.private_calendars.show', ['hash' => $hash])->with('success', $message);
    }

    /** Undoes step 1 for the events (a venue taken down stays down). */
    public function putBack(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        $restored = 0;

        CalendarImportHold::where('role_id', $role->id)->whereNotNull('held_by')->whereNull('decided_at')->get()
            ->each(function (CalendarImportHold $hold) use (&$restored) {
                $event = Event::find($hold->event_id);
                if ($event && CalendarArrival::restore($event, $hold, $hold->held_by)) {
                    $restored++;
                }
            });

        CalendarExposureReview::where('role_id', $role->id)->update([
            'hidden_at' => null, 'hidden_by' => null, 'hidden_count' => 0, 'kept_count' => 0, 'updated_at' => now(),
        ]);
        CalendarExposureWatch::flushCount();

        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_PUT_BACK, $request->user()->id, 'App\\Models\\Role', $role->id, null,
            ['restored' => $restored], "Put back {$restored} events of {$role->subdomain}");

        return redirect()->route('admin.private_calendars.show', ['hash' => $hash])
            ->with('success', trans_choice('messages.private_calendars_put_back_done', $restored, ['count' => $restored]));
    }

    /** Step 2: stop reading the calendar. What the schedule pushes out goes on. */
    public function pause(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        $stopped = CalendarArrival::stopReading($role, 'admin');

        if ($stopped) {
            $this->stopListening($role->fresh(), $stopped);
        }

        CalendarExposureReview::updateOrCreate(['role_id' => $role->id], [
            'paused_at' => now(),
            'paused_by' => $request->user()->id,
        ]);

        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_PAUSE, $request->user()->id, 'App\\Models\\Role', $role->id, null,
            ['providers' => $stopped], "Paused calendar reading for {$role->subdomain}");

        return redirect()->route('admin.private_calendars.show', ['hash' => $hash])
            ->with($stopped ? 'success' : 'warning', $stopped ? __('messages.private_calendars_paused_done') : __('messages.private_calendars_nothing_to_pause'));
    }

    /** Undoes step 2, when the owner has not chosen a direction of their own since. */
    public function resume(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        $resumed = CalendarArrival::resumeReading($role, 'admin');

        CalendarExposureReview::where('role_id', $role->id)->update(['paused_at' => null, 'paused_by' => null, 'updated_at' => now()]);

        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_RESUME, $request->user()->id, 'App\\Models\\Role', $role->id, null,
            ['providers' => $resumed], "Resumed calendar reading for {$role->subdomain}");

        return redirect()->route('admin.private_calendars.show', ['hash' => $hash])
            ->with($resumed ? 'success' : 'warning', $resumed ? __('messages.private_calendars_resumed_done') : __('messages.private_calendars_not_resumed'));
    }

    /** The mail as the owner would receive it now, in their language, or in English with ?lang=en. */
    public function emailPreview(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        abort_unless($role->user, 404);

        $locale = $request->query('lang') === 'en' ? 'en' : $this->ownerLocale($role);

        return $this->mail($role)->locale($locale);
    }

    /** Step 3: tell the owner. Once a week at most. */
    public function email(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);
        $owner = $role->user;

        if (! $owner || blank($owner->email) || ! $this->mailConfigured()) {
            return redirect()->route('admin.private_calendars.show', ['hash' => $hash])->with('error', __('messages.private_calendars_email_unavailable'));
        }

        // Claimed in one statement, so a double press sends one mail.
        CalendarExposureReview::firstOrCreate(['role_id' => $role->id]);
        $claimed = CalendarExposureReview::where('role_id', $role->id)
            ->where(fn ($q) => $q->whereNull('emailed_at')->orWhere('emailed_at', '<', now()->subDays(self::EMAIL_COOLDOWN_DAYS)))
            ->update(['emailed_at' => now(), 'emailed_by' => $request->user()->id, 'updated_at' => now()]);

        if (! $claimed) {
            return redirect()->route('admin.private_calendars.show', ['hash' => $hash])->with('error', __('messages.private_calendars_email_too_soon'));
        }

        $mail = $this->mail($role);
        CalendarExposureReview::where('role_id', $role->id)->update(['emailed_kinds' => json_encode($mail->kinds)]);

        // A null schedule id: this is the platform writing, never through the schedule's own SMTP.
        SendQueuedEmail::dispatch($mail, $owner->email, null, $this->ownerLocale($role));

        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_EMAIL, $request->user()->id, 'App\\Models\\Role', $role->id, null,
            ['kinds' => $mail->kinds, 'hidden' => $mail->hidden, 'paused' => $mail->paused], "Told the owner of {$role->subdomain}");

        return redirect()->route('admin.private_calendars.show', ['hash' => $hash])->with('success', __('messages.private_calendars_email_done'));
    }

    /** Step 4: reviewed. Listed again only for a match newer than this. */
    public function reviewed(Request $request, string $hash)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = $this->role($hash);

        CalendarExposureReview::updateOrCreate(['role_id' => $role->id], [
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ]);
        CalendarExposureWatch::flushCount();

        AuditService::log(AuditService::ADMIN_CALENDAR_PRIVACY_REVIEWED, $request->user()->id, 'App\\Models\\Role', $role->id, null, null,
            "Marked {$role->subdomain} reviewed");

        return redirect()->route('admin.private_calendars')
            ->with('message', __('messages.private_calendars_reviewed_done', ['name' => $role->name]));
    }

    private function role(string $hash): Role
    {
        return Role::findOrFail(UrlUtils::decodeId($hash));
    }

    /**
     * The mail as it stands: the kinds still on the page (or, after Hide, the ones found), what
     * was hidden, and whether reading is paused.
     */
    private function mail(Role $role): CalendarPrivacyMail
    {
        $events = CalendarExposureWatch::eventsFor($role);
        $review = CalendarExposureReview::where('role_id', $role->id)->first();
        $syncs = CalendarExposureWatch::syncs($role);

        return new CalendarPrivacyMail(
            $role,
            $role->user,
            array_values(array_intersect(CalendarPrivacy::FLAGS, $events->pluck('kinds')->flatten()->unique()->all())),
            (int) ($review?->hidden_at ? $review->hidden_count : 0),
            collect($syncs)->contains(fn ($sync) => $sync['paused']),
            $syncs[0]['provider'] ?? null,
        );
    }

    private function ownerLocale(Role $role): string
    {
        $locale = $role->user?->language_code;

        return $locale && array_key_exists($locale, config('app.supported_languages')) ? $locale : 'en';
    }

    /** Mail goes nowhere on an install that has no mailer set up. */
    private function mailConfigured(): bool
    {
        return ! in_array(config('mail.default'), [null, '', 'log', 'array'], true) || config('app.is_testing');
    }

    /**
     * The placeholder venues among these events' venues: the ones a calendar import made out of a
     * meeting link.
     *
     * @param  \Illuminate\Support\Collection<int, Event>  $events
     * @return \Illuminate\Support\Collection<int, Role>
     */
    private function meetingStubs($events)
    {
        return $events
            ->flatMap(fn (Event $event) => $event->roles()->where('roles.type', 'venue')->get())
            ->unique('id')
            ->filter(fn (Role $venue) => MeetingLinkVenues::isMeetingStub($venue))
            ->values();
    }

    /**
     * A paused sync is not told about changes any more: the channels that would wake it are taken
     * down, best effort. The refresh commands and the owner's next save make new ones.
     *
     * @param  list<string>  $providers
     */
    private function stopListening(Role $role, array $providers): void
    {
        $owner = $role->user;

        if (in_array(CalendarArrival::GOOGLE, $providers, true) && $role->google_webhook_id) {
            try {
                $google = app(GoogleCalendarService::class);
                if ($owner && $owner->google_token && $role->google_webhook_resource_id && $google->ensureValidToken($owner)) {
                    $google->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                }
            } catch (\Throwable $e) {
                Log::warning('Could not stop a Google Calendar webhook while pausing', ['role_id' => $role->id, 'error' => $e->getMessage()]);
            }

            $role->writeOperationalColumns(['google_webhook_id' => null, 'google_webhook_resource_id' => null, 'google_webhook_expires_at' => null]);
        }

        if (in_array(CalendarArrival::MICROSOFT, $providers, true) && $role->microsoft_webhook_id) {
            try {
                if ($owner && $owner->microsoft_token) {
                    app(MicrosoftCalendarService::class)->deleteSubscription($owner, $role->microsoft_webhook_id);
                }
            } catch (\Throwable $e) {
                Log::warning('Could not delete an Outlook subscription while pausing', ['role_id' => $role->id, 'error' => $e->getMessage()]);
            }

            $role->writeOperationalColumns(['microsoft_webhook_id' => null, 'microsoft_webhook_expires_at' => null]);
        }
    }

    /** The last thing done about a schedule, for the list: a step and when. */
    private function lastAction(?CalendarExposureReview $review): ?array
    {
        if (! $review) {
            return null;
        }

        $steps = array_filter([
            'hidden' => $review->hidden_at,
            'paused' => $review->paused_at,
            'emailed' => $review->emailed_at,
            'reviewed' => $review->reviewed_at,
        ]);

        if (! $steps) {
            return null;
        }

        arsort($steps);
        $step = array_key_first($steps);

        return ['label' => __('messages.private_calendars_step_done_'.$step), 'at' => $steps[$step]];
    }
}
