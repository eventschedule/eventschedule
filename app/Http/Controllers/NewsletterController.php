<?php

namespace App\Http\Controllers;

use App\Jobs\EvaluateAbTestWinner;
use App\Models\Newsletter;
use App\Models\NewsletterAbTest;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\NewsletterTemplate;
use App\Models\NewsletterUnsubscribe;
use App\Models\User;
use App\Services\NewsletterService;
use App\Utils\NewsletterBuilderRoutes;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    use Traits\SanitizesNewsletterContent;

    protected function authorizeAccess()
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        // The people who run a schedule. A viewer used to pass here, and with it could write and
        // send a newsletter, import ten thousand addresses and read every recipient list.
        $hasRole = $this->getRoles()->isNotEmpty();

        if (! $hasRole) {
            abort(403, __('messages.not_authorized'));
        }
    }

    protected function getRoles()
    {
        $user = auth()->user();

        return $user->roles()->wherePivotIn('level', ['owner', 'admin'])->get()
            ->filter(fn ($role) => $user->planAllowsTeamAccess($role))
            ->values();
    }

    protected function getRole(Request $request)
    {
        $roleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;
        if (! $roleId) {
            abort(404);
        }

        $role = auth()->user()->roles()->where('roles.id', $roleId)->first();
        if (! $role) {
            abort(404);
        }

        if (! auth()->user()->isAdmin()) {
            if (! in_array($role->pivot->level, ['owner', 'admin'], true) || ! auth()->user()->planAllowsTeamAccess($role)) {
                abort(403);
            }
        }

        return $role;
    }

    protected function roleIdParam($role)
    {
        return ['role_id' => UrlUtils::encodeId($role->id)];
    }

    /**
     * Delegates to the one shared gate so this and NewsletterService::send() cannot drift.
     *
     * $recipients scales it: a small audience goes out without SMTP or an SMS-verified phone.
     * Callers that have not resolved recipients yet pass 0, which is the strict answer and
     * preserves the pre-existing behaviour of the compose-time warning.
     */
    protected function requiresNewsletterVerification($role, int $recipients = 0): bool
    {
        return ! $role->canSendAudienceMail($recipients, auth()->user());
    }

    /**
     * The clock a scheduled time is typed in: the person's own, else the schedule's. The builder
     * names it beside the field, so the two must be asked in one place.
     */
    public static function scheduleTimezone(?\App\Models\Role $role): string
    {
        return auth()->user()->timezone ?: ($role?->timezone ?: 'UTC');
    }

    /** A variant of an A/B test that has not gone out yet. It is sent as a test, never on its own. */
    protected function isPendingAbVariant(Newsletter $newsletter): bool
    {
        return $newsletter->ab_test_id && $newsletter->abTest?->status === 'pending';
    }

    /**
     * How far each newsletter that is going out has got: [id => ['done' => n, 'total' => n]], from
     * its recipients (a test send is not one of them). One query for a whole list.
     */
    protected function sendingProgress($newsletters): array
    {
        $ids = collect($newsletters)->filter(fn ($newsletter) => $newsletter->status === 'sending')->pluck('id')->all();
        if (! $ids) {
            return [];
        }

        $progress = [];
        NewsletterRecipient::whereIn('newsletter_id', $ids)
            ->where('status', '!=', 'test')
            ->selectRaw('newsletter_id, COUNT(*) as total, SUM(status IN (\'sent\', \'failed\')) as done')
            ->groupBy('newsletter_id')
            ->get()
            ->each(function ($row) use (&$progress) {
                $progress[$row->newsletter_id] = ['done' => (int) $row->done, 'total' => (int) $row->total];
            });

        return $progress;
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();
        $roles = $this->getRoles();

        $selectedRoleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;

        // Validate selected role belongs to user
        if ($selectedRoleId && ! $roles->contains('id', $selectedRoleId)) {
            abort(403);
        }

        // Default to first role if none selected
        if (! $selectedRoleId && $roles->isNotEmpty()) {
            $selectedRoleId = $roles->first()->id;
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['subject', 'status', 'sent_count', 'created_at', 'open_rate', 'click_rate'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        $newsletters = collect();
        $role = null;
        if ($selectedRoleId) {
            $role = $roles->firstWhere('id', $selectedRoleId);
            $query = Newsletter::where('role_id', $selectedRoleId)
                ->where('type', 'schedule')
                ->where('status', '!=', 'cancelled')
                // Variant B of a test is listed under its variant A, so the two read as one test.
                // Without a live variant A it keeps a row of its own.
                ->where(function ($q) {
                    $q->whereNull('ab_variant')
                        ->orWhere('ab_variant', '!=', 'B')
                        ->orWhereNotExists(function ($sub) {
                            $sub->selectRaw('1')
                                ->from('newsletters as variant_a')
                                ->whereColumn('variant_a.ab_test_id', 'newsletters.ab_test_id')
                                ->where('variant_a.ab_variant', 'A')
                                ->where('variant_a.status', '!=', 'cancelled');
                        });
                })
                ->with('abTest.newsletters');

            if ($sortBy === 'open_rate') {
                $query->orderByRaw('CASE WHEN sent_count > 0 THEN open_count / sent_count ELSE 0 END '.$sortDir);
            } elseif ($sortBy === 'click_rate') {
                $query->orderByRaw('CASE WHEN sent_count > 0 THEN click_count / sent_count ELSE 0 END '.$sortDir);
            } else {
                $query->orderBy($sortBy, $sortDir);
            }

            $newsletters = $query->paginate(20)->withQueryString();
        }

        // Each variant B shown under its A, and how far every send in progress has got.
        $variantsB = collect($newsletters instanceof \Illuminate\Pagination\AbstractPaginator ? $newsletters->items() : [])
            ->filter(fn ($newsletter) => $newsletter->ab_variant === 'A' && $newsletter->abTest)
            ->mapWithKeys(fn ($newsletter) => [$newsletter->id => $newsletter->abTest->newsletters
                ->first(fn ($variant) => $variant->ab_variant === 'B' && $variant->status !== 'cancelled')])
            ->filter();
        $listed = collect($newsletters instanceof \Illuminate\Pagination\AbstractPaginator ? $newsletters->items() : [])->merge($variantsB->values());
        $progress = $this->sendingProgress($listed);

        return view('newsletter.index', compact('roles', 'role', 'selectedRoleId', 'newsletters', 'sortBy', 'sortDir', 'variantsB', 'progress'));
    }

    public function create(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segments = NewsletterSegment::where('role_id', $role->id)->get();
        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }
        $events = app(NewsletterService::class)->eventChoices($role);

        $defaultBlocks = Newsletter::defaultBlocks($role);

        $lastNewsletter = Newsletter::where('role_id', $role->id)
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->first();

        $defaultTemplate = $lastNewsletter ? $lastNewsletter->template : 'modern';
        $defaultStyleSettings = $lastNewsletter
            ? Newsletter::movedToCurrentPreset($lastNewsletter->template, Newsletter::designSettings($lastNewsletter->style_settings), $role)
            : Newsletter::defaultStyleSettingsForRole($role);
        $defaultSegmentIds = $lastNewsletter ? ($lastNewsletter->segment_ids ?? []) : [];

        // Load saved templates for the template picker
        $savedTemplates = NewsletterTemplate::where('role_id', $role->id)
            ->where('is_system', false)
            ->orderBy('name')
            ->get();
        // If a template_id is provided, use that template's design
        if ($request->has('template_id')) {
            $decoded = UrlUtils::decodeId($request->template_id);
            if ($decoded) {
                $fromTemplate = NewsletterTemplate::where('role_id', $role->id)
                    ->where('id', $decoded)
                    ->first();
                if ($fromTemplate) {
                    $defaultBlocks = ! empty($fromTemplate->blocks) ? $fromTemplate->blocks : $defaultBlocks;
                    $defaultTemplate = $fromTemplate->template ?? 'modern';
                    $defaultStyleSettings = Newsletter::designSettings($fromTemplate->style_settings) ?? $defaultStyleSettings;
                }
            }
        }

        return view('newsletter.create', compact('role', 'segments', 'events', 'defaultBlocks', 'defaultTemplate', 'defaultStyleSettings', 'defaultSegmentIds', 'savedTemplates'));
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
            'segment_ids' => 'nullable|array',
            'segment_ids.*' => 'integer',
        ]);

        $blocks = $this->parseBlocks($request);
        $service = app(NewsletterService::class);

        $newsletter = Newsletter::create([
            'role_id' => $role->id,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
            'segment_ids' => ! empty($validated['segment_ids']) ? array_map('intval', $validated['segment_ids']) : null,
            'status' => 'draft',
            'type' => 'schedule',
        ]);

        // Derive event_ids from blocks for the send pipeline
        $newsletter->event_ids = $service->deriveEventIds($newsletter);
        $newsletter->save();

        // The builder saves without leaving the page, and carries on with the addresses of the
        // newsletter that now exists.
        if ($request->header('X-Save-Before-Action')) {
            return response()->json([
                'saved' => true,
                'hash' => UrlUtils::encodeId($newsletter->id),
                'routes' => NewsletterBuilderRoutes::for($role, $newsletter),
            ]);
        }

        return redirect()->route('newsletter.edit', [
            'hash' => UrlUtils::encodeId($newsletter->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])->with('status', __('messages.newsletter_saved'));
    }

    public function edit(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $segments = NewsletterSegment::where('role_id', $role->id)->get();
        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }
        $events = app(NewsletterService::class)->eventChoices($role);

        return view('newsletter.edit', compact('role', 'newsletter', 'segments', 'events'));
    }

    public function update(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (in_array($newsletter->status, ['sending', 'sent'])) {
            if ($request->header('X-Save-Before-Action')) {
                return response()->json(['message' => __('messages.newsletter_already_sent')], 409);
            }

            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
            'segment_ids' => 'nullable|array',
            'segment_ids.*' => 'integer',
        ]);

        $blocks = $this->parseBlocks($request);
        $service = app(NewsletterService::class);

        $newsletter->update([
            'subject' => $validated['subject'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
            'segment_ids' => ! empty($validated['segment_ids']) ? array_map('intval', $validated['segment_ids']) : null,
        ]);

        // Derive event_ids from blocks
        $newsletter->event_ids = $service->deriveEventIds($newsletter);
        $newsletter->save();

        if ($request->header('X-Save-Before-Action')) {
            return response()->json(['saved' => true]);
        }

        return back()->with('status', __('messages.newsletter_saved'));
    }

    public function delete(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->status === 'sending') {
            return back()->with('error', __('messages.newsletter_is_sending'));
        }

        $newsletter->update(['status' => 'cancelled']);

        return redirect()->route('newsletter.index', $this->roleIdParam($role))
            ->with('status', __('messages.newsletter_deleted'));
    }

    public function send(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! in_array($newsletter->status, ['draft', 'scheduled'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        // One variant of a test on its own would reach everyone, and the other variant would then
        // reach them all again. A test goes out as a test, from the builder's Review and send.
        if ($this->isPendingAbVariant($newsletter)) {
            return back()->with('error', __('messages.ab_test_send_as_test'));
        }

        if (! $role->canSendNewsletter()) {
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();

            return back()->with('error', __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]));
        }

        // Resolved once, and used by both checks below.
        $estimatedCount = $service->resolveRecipients($role, $newsletter->segment_ids ?? [])->count();

        // Trust gate, scaled to the size of the send. Checked here rather than before the
        // newsletter is loaded, because the recipient count is what decides it.
        if ($this->requiresNewsletterVerification($role, $estimatedCount)) {
            return back()->with('error', __('messages.newsletter_requires_verification', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)]));
        }

        // On sync queue, large sends would timeout the HTTP request - advise scheduling instead
        if (config('queue.default') === 'sync' && $estimatedCount > 50) {
            return back()->with('error', __('messages.newsletter_sync_queue_limit'));
        }

        $result = $service->send($newsletter);

        if ($result === false) {
            return back()->with('error', __('messages.newsletter_send_failed'));
        }

        if (is_array($result) && $result[0] === 'no_recipients') {
            return back()->with('error', __('messages.newsletter_no_recipients'));
        }

        if (is_array($result) && $result[0] === 'requires_verification') {
            return back()->with('error', __('messages.newsletter_requires_verification', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)]));
        }

        if (is_array($result) && $result[0] === 'limit_exceeded') {
            $recipientCount = $result[1];
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();
            $remaining = max(0, $limit - $used);

            return back()->with('error', __('messages.newsletter_email_limit_exceeded', [
                'count' => $recipientCount,
                'remaining' => $remaining,
            ]));
        }

        return redirect()->route('newsletter.index', $this->roleIdParam($role))
            ->with('status', __('messages.newsletter_sending'));
    }

    public function schedule(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        // Scaled the same way as send(): the deferred send runs through NewsletterService::send(),
        // which applies this same gate, so refusing a small send here would be inconsistent.
        $estimatedCount = app(NewsletterService::class)
            ->resolveRecipients($role, $newsletter->segment_ids ?? [])->count();

        if ($this->requiresNewsletterVerification($role, $estimatedCount)) {
            return back()->with('error', __('messages.newsletter_requires_verification', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)]));
        }

        if (! in_array($newsletter->status, ['draft', 'scheduled'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        if ($this->isPendingAbVariant($newsletter)) {
            return back()->with('error', __('messages.ab_test_send_as_test'));
        }

        if (! $role->canSendNewsletter()) {
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();

            return back()->with('error', __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]));
        }

        $request->validate([
            'scheduled_at' => 'required|date',
        ]);

        $timezone = $this->scheduleTimezone($role);
        $scheduledAtUtc = Carbon::parse($request->scheduled_at, $timezone)->setTimezone('UTC');

        if ($scheduledAtUtc->lte(now())) {
            return back()->withErrors(['scheduled_at' => __('messages.newsletter_schedule_in_past')]);
        }

        $newsletter->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAtUtc,
        ]);

        return back()->with('status', __('messages.newsletter_scheduled'));
    }

    public function cancel(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->status !== 'scheduled') {
            return back()->with('error', __('messages.newsletter_not_scheduled'));
        }

        $newsletter->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);

        return back()->with('status', __('messages.newsletter_cancelled'));
    }

    public function cloneNewsletter(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $copy = $newsletter->replicate();
        $copy->status = 'draft';
        $copy->scheduled_at = null;
        $copy->sent_at = null;
        $copy->sent_count = 0;
        $copy->open_count = 0;
        $copy->click_count = 0;
        $copy->ab_test_id = null;
        $copy->ab_variant = null;
        $copy->send_token = null;
        $copy->save();

        return redirect()->route('newsletter.edit', [
            'hash' => UrlUtils::encodeId($copy->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])->with('status', __('messages.newsletter_cloned'));
    }

    public function previewDraft(Request $request, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $blocks = $this->parseBlocks($request);

        $newsletter = new Newsletter([
            'role_id' => $role->id,
            'subject' => $request->input('subject', ''),
            'blocks' => $blocks,
            'template' => $request->input('template', 'modern'),
            'style_settings' => $this->sanitizeStyleSettings($request->input('style_settings')),
        ]);
        $newsletter->setRelation('role', $role);

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function preview(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        // If form data was submitted, update the newsletter temporarily for preview
        if ($request->has('subject')) {
            $newsletter->subject = $request->input('subject');
            $newsletter->blocks = $this->parseBlocks($request);
            $newsletter->template = $request->input('template', 'modern');
            $newsletter->style_settings = $this->sanitizeStyleSettings($request->input('style_settings'));
        }

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function testSend(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        // The builder asks with fetch and shows the answer in its dialog; a plain post is answered
        // on the page it came from, as before.
        $answer = function (string $message, int $status) use ($request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], $status);
            }

            return back()->with($status === 200 ? 'status' : 'error', $message);
        };

        if ($this->requiresNewsletterVerification($role, 1)) {
            return $answer(__('messages.newsletter_requires_verification', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)]), 422);
        }

        // Only an address the app already knows: the person's own, or the schedule's contact
        // address. A typed address would make Send a test a way to mail anyone.
        $to = $request->input('to') === 'schedule' ? 'schedule' : 'me';
        $email = $to === 'schedule' ? $role->email : auth()->user()->email;
        $name = $to === 'schedule' ? $role->name : auth()->user()->name;

        if (empty($email)) {
            return $answer(__('messages.email_required'), 422);
        }

        $rateLimitKey = 'test-newsletter-'.$role->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return $answer(__('messages.please_wait').'...', 429);
        }

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        // Create a temporary recipient for the test
        $recipient = new NewsletterRecipient([
            'newsletter_id' => $newsletter->id,
            'user_id' => $to === 'me' ? auth()->id() : null,
            'email' => $email,
            'name' => $name,
            'token' => Str::random(64),
            'status' => 'pending',
        ]);
        $recipient->save();

        $result = $service->sendToRecipient($newsletter, $recipient, isTest: true);

        if (! $result) {
            $recipient->delete();
            RateLimiter::hit($rateLimitKey, 60);

            return $answer(__('messages.test_email_failed'), 422);
        }

        $recipient->update(['status' => 'test']);
        RateLimiter::hit($rateLimitKey, 60);

        return $answer(__('messages.newsletter_test_sent_to', ['email' => $email]), 200);
    }

    public function stats(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $sortBy = $request->get('sort_by', 'opened_at');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSortColumns = ['name', 'email', 'status', 'opened_at', 'clicked_at'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'opened_at';
        }

        $recipients = $newsletter->recipients()
            ->where('status', '!=', 'test')
            ->orderBy($sortBy, $sortDir)
            ->paginate(50)
            ->withQueryString();

        // Top clicked links
        $topLinks = \App\Models\NewsletterClick::whereHas('recipient', function ($q) use ($newsletter) {
            $q->where('newsletter_id', $newsletter->id)->where('status', '!=', 'test');
        })
            ->selectRaw('url, COUNT(*) as click_count')
            ->groupBy('url')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();

        // Open timeline data (group by hour for first 48h, then by day)
        $openTimeline = $newsletter->recipients()
            ->where('status', '!=', 'test')
            ->whereNotNull('opened_at')
            ->selectRaw('DATE(opened_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Click timeline data
        $clickTimeline = \App\Models\NewsletterClick::whereHas('recipient', function ($q) use ($newsletter) {
            $q->where('newsletter_id', $newsletter->id)->where('status', '!=', 'test');
        })
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // A/B test comparison
        $abTest = $newsletter->abTest;

        // How far a send has got, while it is going out, and who left the list from this one.
        $progress = $this->sendingProgress(collect([$newsletter]))[$newsletter->id] ?? null;
        $unsubscribeCount = NewsletterUnsubscribe::where('newsletter_id', $newsletter->id)->count();

        return view('newsletter.stats', compact(
            'role',
            'newsletter',
            'recipients',
            'topLinks',
            'openTimeline',
            'clickTimeline',
            'abTest',
            'sortBy',
            'sortDir',
            'progress',
            'unsubscribeCount'
        ));
    }

    public function getEvents(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        // The same list the builder is given, with the id as the app shows it.
        $events = array_map(fn (array $event) => [
            'id' => $event['id'],
            'hash' => UrlUtils::encodeId($event['id']),
            'name' => $event['name'],
            'date' => $event['date'],
        ], app(NewsletterService::class)->eventChoices($role));

        return response()->json($events);
    }

    /**
     * Who a newsletter would reach, and whether it may go now or later: what the builder shows
     * under the subject and in Review and send. The checks are send()'s and schedule()'s own, in
     * their order, so a refusal is read before the button is pressed; both still check again.
     */
    public function audience(Request $request, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segments = NewsletterSegment::where('role_id', $role->id)
            ->whereIn('id', array_map('intval', (array) $request->input('segment_ids', [])))
            ->get();

        $newsletter = $request->filled('newsletter')
            ? Newsletter::where('role_id', $role->id)->where('id', UrlUtils::decodeId($request->input('newsletter')))->first()
            : null;

        // A test goes to variant A's audience whichever variant sends it (sendAbTest()).
        $abTest = $newsletter && $this->isPendingAbVariant($newsletter) ? $newsletter->abTest : null;
        if ($abTest && $newsletter->ab_variant !== 'A') {
            $variantA = $abTest->newsletters->firstWhere('ab_variant', 'A');
            $segments = NewsletterSegment::where('role_id', $role->id)->whereIn('id', $variantA?->segment_ids ?? [])->get();
        }

        $count = $service->resolveRecipients($role, $segments->pluck('id')->all())->count();
        $limit = $role->newsletterLimit();
        $used = $limit !== null ? $role->newslettersSentThisMonth() : 0;
        $verificationLimit = (int) config('usage.audience_mail_unverified_max_recipients', 50);

        // What stops it altogether, then what stops only an immediate send.
        $refusal = null;
        if (! $role->canSendNewsletter()) {
            $refusal = [
                'message' => __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]),
                'links' => config('cashier.key') && $role->actualPlanTier() !== 'enterprise'
                    ? [['label' => __('messages.newsletter_upgrade_plan'), 'url' => route('role.subscribe', ['subdomain' => $role->subdomain])]]
                    : [],
            ];
        } elseif ($count === 0) {
            $refusal = ['message' => __('messages.newsletter_no_recipients'), 'links' => [
                ['label' => __('messages.import_emails'), 'url' => route('newsletter.import', ['role_id' => UrlUtils::encodeId($role->id)])],
            ]];
        } elseif ($this->requiresNewsletterVerification($role, $count)) {
            $refusal = ['message' => __('messages.newsletter_requires_verification', ['limit' => $verificationLimit]), 'links' => [
                ['label' => __('messages.newsletter_verify_phone'), 'url' => route('profile.edit').'?highlight=phone#section-profile'],
                ['label' => __('messages.newsletter_use_own_email'), 'url' => route('role.edit', ['subdomain' => $role->subdomain]).'#integration-tab-email'],
            ]];
        }

        $nowRefusal = null;
        if (! $refusal && $limit !== null && $used + $count > $limit) {
            $nowRefusal = __('messages.newsletter_email_limit_exceeded', ['count' => $count, 'remaining' => max(0, $limit - $used)]);
        } elseif (! $refusal && $abTest && config('queue.default') === 'sync') {
            // The winner is picked by a job that waits for hours; on the sync driver it would run
            // at once, before anyone could open either variant.
            $refusal = ['message' => __('messages.ab_test_needs_queue'), 'links' => []];
        } elseif (! $refusal && config('queue.default') === 'sync' && $count > 50) {
            $nowRefusal = __('messages.newsletter_sync_queue_limit');
        }

        $ab = null;
        if ($abTest) {
            $sample = (int) ceil($count * ($abTest->sample_percentage / 100));
            $ab = [
                'sample_a' => (int) ceil($sample / 2),
                'sample_b' => $sample - (int) ceil($sample / 2),
                'rest' => max(0, $count - $sample),
                'hours' => (int) $abTest->winner_wait_hours,
                'criteria' => $abTest->winner_criteria === 'click_rate' ? __('messages.click_rate') : __('messages.open_rate'),
            ];
        }

        return response()->json([
            'count' => $count,
            'label' => $segments->isEmpty()
                ? __('messages.newsletter_audience_everyone', ['name' => $role->name])
                : $segments->pluck('name')->implode(', '),
            'allowance' => $limit !== null ? ['used' => $used, 'limit' => $limit, 'after' => max(0, $limit - $used - $count)] : null,
            'refusal' => $refusal,
            'now_refusal' => $nowRefusal,
            'ab' => $ab,
            'timezone' => self::scheduleTimezone($role),
        ]);
    }

    public function segments(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segments = NewsletterSegment::where('role_id', $role->id)
            ->withCount('segmentUsers')
            ->get();

        // Add recipient counts
        foreach ($segments as $segment) {
            $segment->recipient_count = $segment->recipientCount();
        }

        $groups = $role->groups()->orderBy('name')->get();

        $eventsData = $role->events()
            ->with('creatorRole')
            ->where('event_role.is_accepted', true)
            ->where('is_draft', false)
            ->where('name', '!=', '')
            ->whereNotNull('name')
            ->orderBy('starts_at', 'desc')
            ->get()
            ->map(fn ($event) => [
                'id' => UrlUtils::encodeId($event->id),
                'name' => $event->translatedName(),
                'starts_at' => $event->starts_at ? $event->getShortDateRangeDisplay('D, M j, Y') : null,
                'image_url' => $event->getImageUrl(),
            ]);

        return view('newsletter.segments', compact('role', 'segments', 'groups', 'eventsData'));
    }

    public function storeSegment(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:all_followers,all_subscribers,ticket_buyers,manual,group,waitlist',
            'filter_criteria' => 'nullable|array',
            'emails' => 'nullable|string',
        ]);

        $filterCriteria = $validated['filter_criteria'] ?? null;
        if (! empty($filterCriteria['event_id'])) {
            $filterCriteria['event_id'] = UrlUtils::decodeId($filterCriteria['event_id']);
        }

        $segment = NewsletterSegment::create([
            'role_id' => $role->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'filter_criteria' => $filterCriteria,
        ]);

        // Add manual users if provided
        if ($validated['type'] === 'manual' && ! empty($validated['emails'])) {
            $emails = array_filter(array_map('trim', explode("\n", $validated['emails'])));
            $seen = [];
            foreach ($emails as $line) {
                $parts = array_map('trim', explode(',', $line));
                $email = strtolower($parts[0] ?? '');
                $name = $parts[1] ?? null;
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && ! in_array($email, $seen)) {
                    $seen[] = $email;
                    NewsletterSegmentUser::create([
                        'newsletter_segment_id' => $segment->id,
                        'email' => $email,
                        'name' => $name,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        return back()->with('status', __('messages.segment_created'));
    }

    public function updateSegment(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'filter_criteria' => 'nullable|array',
            'filter_criteria.event_id' => 'nullable|string',
            'filter_criteria.group_id' => 'nullable|integer',
        ]);

        $changes = ['name' => $validated['name']];

        // The filter changes only when the page sent one, and only the one this type has. A
        // rename used to post no filter and so wiped the event a ticket buyers segment was
        // narrowed to, which then reached the buyers of every event.
        if ($request->has('filter_criteria')) {
            $criteria = $validated['filter_criteria'] ?? [];
            // Whatever else the segment is narrowed by (a date range) stays as it was.
            $kept = is_array($segment->filter_criteria) ? $segment->filter_criteria : [];

            if (in_array($segment->type, ['ticket_buyers', 'waitlist'], true)) {
                $eventId = filled($criteria['event_id'] ?? null) ? UrlUtils::decodeId($criteria['event_id']) : null;
                // One of this schedule's events, or the one the segment already had.
                if ($eventId && $eventId !== (int) ($kept['event_id'] ?? 0) && ! $role->events()->where('events.id', $eventId)->exists()) {
                    return back()->withErrors(['filter_criteria.event_id' => __('messages.not_authorized')]);
                }
                unset($kept['event_id']);
                if ($eventId) {
                    $kept['event_id'] = $eventId;
                }
                $changes['filter_criteria'] = $kept ?: null;
            } elseif ($segment->type === 'group') {
                $groupId = (int) ($criteria['group_id'] ?? 0);
                if (! $role->groups()->where('id', $groupId)->exists()) {
                    return back()->withErrors(['filter_criteria.group_id' => __('messages.not_authorized')]);
                }
                $changes['filter_criteria'] = ['group_id' => $groupId] + $kept;
            }
        }

        $segment->update($changes);

        return back()->with('status', __('messages.segment_updated'));
    }

    public function deleteSegment(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $inUse = Newsletter::where('role_id', $role->id)
            ->whereIn('status', ['draft', 'scheduled'])
            ->whereJsonContains('segment_ids', $segment->id)
            ->exists();

        if ($inUse) {
            return back()->with('error', __('messages.segment_in_use'));
        }

        $segment->delete();

        return back()->with('status', __('messages.segment_deleted'));
    }

    public function editSegment(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $recipientCount = $segment->recipientCount();

        $sortBy = 'created_at';
        $sortDir = 'desc';

        if ($segment->type === 'manual') {
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
            $allowedSortColumns = ['name', 'email', 'created_at'];
            if (! in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'created_at';
            }

            $subscribers = $segment->segmentUsers()
                ->orderBy($sortBy, $sortDir)
                ->paginate(50)
                ->withQueryString();
        } else {
            $subscribers = $segment->resolveRecipients()->take(50);
        }

        // The choices for the one filter this type has, so it can be changed here: before, a
        // segment of the wrong event had to be deleted and made again.
        $filterEvents = [];
        if (in_array($segment->type, ['ticket_buyers', 'waitlist'], true)) {
            $filterEvents = $role->events()
                ->with('creatorRole')
                ->where('event_role.is_accepted', true)
                ->where('is_draft', false)
                ->where('name', '!=', '')
                ->whereNotNull('name')
                ->orderBy('starts_at', 'desc')
                ->get()
                ->map(fn ($event) => [
                    'id' => $event->id,
                    'hash' => UrlUtils::encodeId($event->id),
                    'name' => $event->translatedName(),
                    'date' => $event->starts_at ? $event->getShortDateRangeDisplay('M j, Y') : null,
                ])
                ->all();

            // The event the segment is narrowed to stays a choice even when it is no longer one of
            // the list's (a draft now, or turned down): otherwise the select would open on "All
            // events", and saving a new name would widen the segment to every buyer.
            $currentEventId = (int) ($segment->filter_criteria['event_id'] ?? 0);
            if ($currentEventId && ! collect($filterEvents)->contains('id', $currentEventId)) {
                $current = $role->events()->where('events.id', $currentEventId)->first();
                array_unshift($filterEvents, [
                    'id' => $currentEventId,
                    'hash' => UrlUtils::encodeId($currentEventId),
                    'name' => $current?->translatedName() ?: '#'.UrlUtils::encodeId($currentEventId),
                    'date' => $current?->starts_at ? $current->getShortDateRangeDisplay('M j, Y') : null,
                ]);
            }
        }
        $groups = $segment->type === 'group' ? $role->groups()->orderBy('name')->get() : collect();

        return view('newsletter.segment-edit', [
            'role' => $role,
            'segment' => $segment,
            'subscribers' => $subscribers,
            'recipientCount' => $recipientCount,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'filterEvents' => $filterEvents,
            'groups' => $groups,
        ]);
    }

    public function storeSegmentUser(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->where('type', 'manual')
            ->firstOrFail();

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        $exists = $segment->segmentUsers()->where('email', $email)->exists();
        if ($exists) {
            return back()->with('error', __('messages.email_already_in_segment'));
        }

        // A manual segment is a list of addresses (NewsletterSegment::resolveManual()), so adding
        // one creates no account for that person and makes them nobody's follower: they never
        // signed up for either. An existing account is linked only so its opt-outs apply.
        NewsletterSegmentUser::create([
            'newsletter_segment_id' => $segment->id,
            'user_id' => User::where('email', $email)->value('id'),
            'email' => $email,
            'name' => $validated['name'] ?? '',
            'created_at' => now(),
        ]);

        return back()->with('status', __('messages.subscriber_added'));
    }

    public function updateSegmentUser(Request $request, string $hash, string $userHash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->where('type', 'manual')
            ->firstOrFail();

        $segmentUser = $segment->segmentUsers()
            ->where('id', UrlUtils::decodeId($userHash))
            ->firstOrFail();

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $email = strtolower(trim($validated['email']));

        if ($email !== strtolower($segmentUser->email)) {
            $exists = $segment->segmentUsers()->where('email', $email)->exists();
            if ($exists) {
                return back()->with('error', __('messages.email_already_in_segment'));
            }
        }

        $segmentUser->update([
            'email' => $email,
            'name' => $validated['name'] ?? '',
        ]);

        return back()->with('status', __('messages.subscriber_updated'));
    }

    public function deleteSegmentUser(Request $request, string $hash, string $userHash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $segment = NewsletterSegment::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->where('type', 'manual')
            ->firstOrFail();

        $segment->segmentUsers()
            ->where('id', UrlUtils::decodeId($userHash))
            ->firstOrFail()
            ->delete();

        return back()->with('status', __('messages.subscriber_removed'));
    }

    public function createAbTest(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletter->ab_test_id) {
            return back()->with('error', __('messages.ab_test_already_exists'));
        }

        $validated = $request->validate([
            'test_field' => 'required|in:subject,blocks',
            'sample_percentage' => 'required|integer|min:5|max:50',
            'winner_criteria' => 'required|in:open_rate,click_rate',
            'winner_wait_hours' => 'required|integer|min:1|max:72',
        ]);

        $abTest = NewsletterAbTest::create([
            'role_id' => $role->id,
            'name' => $newsletter->subject.' - A/B Test',
            'test_field' => $validated['test_field'],
            'sample_percentage' => $validated['sample_percentage'],
            'winner_criteria' => $validated['winner_criteria'],
            'winner_wait_hours' => $validated['winner_wait_hours'],
            'status' => 'pending',
        ]);

        // Set the original as variant A
        $newsletter->update([
            'ab_test_id' => $abTest->id,
            'ab_variant' => 'A',
        ]);

        // Create variant B as a copy
        $variantB = $newsletter->replicate();
        $variantB->ab_test_id = $abTest->id;
        $variantB->ab_variant = 'B';
        $variantB->status = 'draft';
        $variantB->send_token = null;
        $variantB->save();

        return redirect()->route('newsletter.edit', [
            'hash' => UrlUtils::encodeId($variantB->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])->with('status', __('messages.ab_test_created'));
    }

    public function sendAbTest(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        if (! $role->canSendNewsletter()) {
            $limit = $role->newsletterLimit();
            $used = $role->newslettersSentThisMonth();

            return back()->with('error', __('messages.newsletter_limit_reached', ['used' => $used, 'limit' => $limit]));
        }

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if (! in_array($newsletter->status, ['draft', 'scheduled'])) {
            return back()->with('error', __('messages.newsletter_already_sent'));
        }

        if (! $newsletter->ab_test_id) {
            return back()->with('error', __('messages.no_ab_test'));
        }

        $abTest = $newsletter->abTest;

        if (! $abTest) {
            return back()->with('error', __('messages.no_ab_test'));
        }

        // EvaluateAbTestWinner waits hours for opens and clicks; the sync driver ignores the delay
        // and would pick a winner from a sample nobody has had time to open.
        if (config('queue.default') === 'sync') {
            return back()->with('error', __('messages.ab_test_needs_queue'));
        }

        $updated = NewsletterAbTest::where('id', $abTest->id)
            ->where('status', 'pending')
            ->update(['status' => 'sending']);

        if ($updated === 0) {
            return back()->with('error', __('messages.ab_test_already_sent'));
        }

        $abTest->refresh();
        // A variant that was deleted is not one of the two any more.
        $variants = $abTest->newsletters->where('status', '!=', 'cancelled')->values();

        // Every refusal from here on gives the test back, or it stayed "sending" with nothing sent
        // and could never be sent again.
        if ($variants->whereIn('ab_variant', ['A', 'B'])->count() < 2) {
            $abTest->update(['status' => 'pending']);

            return back()->with('error', __('messages.ab_test_needs_variants'));
        }

        // Resolve full recipient list: variant A's audience, whichever variant the test was sent
        // from. Variant B's builder does not offer its own (a copy of A's from when it was made).
        $variantA = $variants->firstWhere('ab_variant', 'A') ?? $newsletter;
        $allRecipients = $service->resolveRecipients($role, $variantA->segment_ids ?? []);

        if ($allRecipients->isEmpty()) {
            $abTest->update(['status' => 'pending']);

            return back()->with('error', __('messages.newsletter_no_recipients'));
        }

        // Scaled to the audience, exactly like send() and schedule(). Checked here rather than up
        // front because the recipient count is what decides it - leaving this one on the strict
        // gate meant an unverified schedule with eight subscribers could send a normal newsletter
        // but was hard-refused on an A/B test, with no explanation and no path forward.
        if ($this->requiresNewsletterVerification($role, $allRecipients->count())) {
            $abTest->update(['status' => 'pending']);

            return back()->with('error', __('messages.newsletter_requires_verification', ['limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50)]));
        }

        // Check if total recipients would exceed the email limit
        $limit = $role->newsletterLimit();
        if ($limit !== null) {
            $used = $role->newslettersSentThisMonth();
            if ($used + $allRecipients->count() > $limit) {
                $abTest->update(['status' => 'pending']);
                $remaining = max(0, $limit - $used);

                return back()->with('error', __('messages.newsletter_email_limit_exceeded', [
                    'count' => $allRecipients->count(),
                    'remaining' => $remaining,
                ]));
            }
        }

        $sampleSize = (int) ceil($allRecipients->count() * ($abTest->sample_percentage / 100));
        $sample = $allRecipients->random(min($sampleSize, $allRecipients->count()));

        // Split sample between variants
        $halfSize = (int) ceil($sample->count() / 2);
        $sampleA = $sample->slice(0, $halfSize)->values();
        $sampleB = $sample->slice($halfSize)->values();

        // Send each variant to its sample
        $variantData = [
            ['newsletter' => $variants->where('ab_variant', 'A')->first(), 'recipients' => $sampleA],
            ['newsletter' => $variants->where('ab_variant', 'B')->first(), 'recipients' => $sampleB],
        ];

        DB::beginTransaction();
        try {
            foreach ($variantData as $variant) {
                $vn = $variant['newsletter'];

                // Atomic status update to prevent double-send on double-click
                $updated = Newsletter::where('id', $vn->id)
                    ->whereIn('status', ['draft', 'scheduled'])
                    ->update(['status' => 'sending', 'send_token' => Str::random(64)]);

                if ($updated === 0) {
                    continue;
                }

                $vn->refresh();

                $chunk = [];
                foreach ($variant['recipients'] as $recipient) {
                    $chunk[] = [
                        'newsletter_id' => $vn->id,
                        'user_id' => $recipient->user_id,
                        'email' => $recipient->email,
                        'name' => $recipient->name,
                        'token' => Str::random(64),
                        'status' => 'pending',
                    ];
                    if (count($chunk) >= 500) {
                        NewsletterRecipient::insert($chunk);
                        $chunk = [];
                    }
                }
                if (! empty($chunk)) {
                    NewsletterRecipient::insert($chunk);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Reset variant statuses
            foreach ($variantData as $variant) {
                $variant['newsletter']?->update(['status' => 'draft', 'send_token' => null]);
            }
            $abTest->update(['status' => 'pending']);
            report($e);

            return back()->with('error', __('messages.newsletter_send_failed'));
        }

        // Dispatch batch jobs outside the transaction
        foreach ($variantData as $variant) {
            $vn = $variant['newsletter'];
            if ($vn->status !== 'sending') {
                continue;
            }

            $batchIndex = 0;
            NewsletterRecipient::where('newsletter_id', $vn->id)
                ->where('status', 'pending')
                ->chunkById(50, function ($recipientChunk) use ($vn, &$batchIndex) {
                    \App\Jobs\SendNewsletterBatch::dispatch($vn->id, $recipientChunk->pluck('id')->toArray())
                        ->delay(now()->addSeconds($batchIndex * 15));
                    $batchIndex++;
                });
        }

        // Schedule winner evaluation
        EvaluateAbTestWinner::dispatch($abTest->id)
            ->delay(now()->addHours($abTest->winner_wait_hours));

        return redirect()->route('newsletter.index', $this->roleIdParam($role))
            ->with('status', __('messages.ab_test_sending'));
    }

    public function importForm(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $manualSegments = NewsletterSegment::where('role_id', $role->id)
            ->where('type', 'manual')
            ->get();

        return view('newsletter.import', compact('role', 'manualSegments'));
    }

    public function importStore(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'segment_target' => 'required|in:new,existing',
            'segment_name' => 'required_if:segment_target,new|nullable|string|max:255',
            'segment_id' => 'required_if:segment_target,existing|nullable|string',
            'entries' => 'required|array|min:1|max:10000',
            'entries.*.email' => 'required|email',
            'entries.*.name' => 'nullable|string|max:255',
        ], [
            'entries.*.email.required' => __('messages.row_error', ['row' => ':index', 'error' => __('messages.email_required')]),
            'entries.*.email.email' => __('messages.row_error', ['row' => ':index', 'error' => __('messages.invalid_email')]),
        ]);

        if ($validated['segment_target'] === 'new') {
            $segment = NewsletterSegment::create([
                'role_id' => $role->id,
                'name' => $validated['segment_name'],
                'type' => 'manual',
            ]);
        } else {
            $segment = NewsletterSegment::where('role_id', $role->id)
                ->where('id', UrlUtils::decodeId($validated['segment_id']))
                ->where('type', 'manual')
                ->firstOrFail();
        }

        $result = DB::transaction(function () use ($segment, $validated) {
            $existingEmails = $segment->segmentUsers()
                ->pluck('email')
                ->map(fn ($e) => strtolower($e))
                ->toArray();

            // Addresses only, as in storeSegmentUser(): an import creates no accounts and no
            // follower links for people who never signed up. Existing accounts are linked so their
            // opt-outs apply.
            $allEmails = collect($validated['entries'])->pluck('email')->map(fn ($e) => strtolower(trim($e)))->unique();
            $existingUsers = User::whereIn('email', $allEmails)->get()->keyBy(fn ($u) => strtolower($u->email));

            $imported = 0;
            $duplicates = 0;
            $seen = [];
            foreach ($validated['entries'] as $entry) {
                $email = strtolower(trim($entry['email']));
                if (in_array($email, $seen) || in_array($email, $existingEmails)) {
                    $duplicates++;

                    continue;
                }
                $seen[] = $email;

                NewsletterSegmentUser::create([
                    'newsletter_segment_id' => $segment->id,
                    'user_id' => $existingUsers->get($email)?->id,
                    'email' => $email,
                    'name' => $entry['name'] ?? '',
                    'created_at' => now(),
                ]);
                $imported++;
            }

            return ['imported' => $imported, 'duplicates' => $duplicates];
        });

        if ($result['imported'] === 0) {
            $message = $result['duplicates'] > 0
                ? __('messages.emails_already_in_segment')
                : __('messages.no_valid_emails');

            return back()->with('error', $message);
        }

        return redirect()->route('newsletter.segments', $this->roleIdParam($role))
            ->with('status', __('messages.emails_imported', ['count' => $result['imported']]));
    }

    // ── Newsletter Templates ────────────────────────────────────────

    public function templates(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $userTemplates = NewsletterTemplate::where('role_id', $role->id)
            ->where('is_system', false)
            ->orderBy('name')
            ->get();

        return view('newsletter.templates', compact('role', 'userTemplates'));
    }

    public function createTemplate(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $defaultBlocks = Newsletter::defaultBlocks($role);
        $events = app(NewsletterService::class)->eventChoices($role);

        $segments = collect();

        return view('newsletter.template-edit', [
            'role' => $role,
            'newsletterTemplate' => null,
            'defaultBlocks' => $defaultBlocks,
            'events' => $events,
            'segments' => $segments,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
        ]);

        $blocks = $this->parseBlocks($request);

        NewsletterTemplate::create([
            'role_id' => $role->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
        ]);

        return redirect()->route('newsletter.templates', $this->roleIdParam($role))
            ->with('status', __('messages.template_saved'));
    }

    public function editTemplate(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletterTemplate = NewsletterTemplate::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletterTemplate->is_system) {
            abort(403);
        }

        $events = app(NewsletterService::class)->eventChoices($role);

        $segments = collect();

        return view('newsletter.template-edit', [
            'role' => $role,
            'newsletterTemplate' => $newsletterTemplate,
            'events' => $events,
            'segments' => $segments,
        ]);
    }

    public function updateTemplate(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletterTemplate = NewsletterTemplate::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletterTemplate->is_system) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blocks' => 'nullable|string',
            'template' => 'required|in:modern,classic,minimal,bold,compact',
            'style_settings' => 'nullable|array',
        ]);

        $blocks = $this->parseBlocks($request);

        $newsletterTemplate->update([
            'name' => $validated['name'],
            'blocks' => $blocks,
            'template' => $validated['template'],
            'style_settings' => $this->sanitizeStyleSettings($validated['style_settings'] ?? null),
        ]);

        return back()->with('status', __('messages.template_updated'));
    }

    public function deleteTemplate(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletterTemplate = NewsletterTemplate::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        if ($newsletterTemplate->is_system) {
            abort(403);
        }

        $newsletterTemplate->delete();

        return back()->with('status', __('messages.template_deleted'));
    }

    public function saveAsTemplate(Request $request, string $hash)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletter = Newsletter::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
        ]);

        NewsletterTemplate::create([
            'role_id' => $role->id,
            'user_id' => auth()->id(),
            'name' => $validated['template_name'],
            'blocks' => $newsletter->blocks,
            'template' => $newsletter->template,
            'style_settings' => Newsletter::designSettings($newsletter->style_settings),
        ]);

        return back()->with('status', __('messages.template_saved'));
    }

    public function previewTemplate(Request $request, string $hash, NewsletterService $service)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        $newsletterTemplate = NewsletterTemplate::where('role_id', $role->id)
            ->where('id', UrlUtils::decodeId($hash))
            ->firstOrFail();

        $newsletter = new Newsletter([
            'role_id' => $role->id,
            'subject' => '',
            'blocks' => $newsletterTemplate->blocks,
            'template' => $newsletterTemplate->template,
            'style_settings' => $newsletterTemplate->style_settings,
        ]);
        $newsletter->setRelation('role', $role);

        $html = $service->renderPreview($newsletter);

        return response()->json(['html' => $html]);
    }

    public function uploadImage(Request $request)
    {
        $this->authorizeAccess();
        $role = $this->getRole($request);

        if (! $role->isPro()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        return $this->handleNewsletterImageUpload($request);
    }
}
