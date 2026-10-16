<?php

namespace App\Http\Controllers;

use App\Exceptions\BoostPartnerException;
use App\Exceptions\MetaApiException;
use App\Models\AuditLog;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\BoostStatement;
use App\Models\Event;
use App\Models\Role;
use App\Services\BoostPartnerService;
use App\Services\BoostStatementService;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use App\Utils\CsvUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

/**
 * Boosts that run through a boost partner's own Page and ad account.
 *
 * Two audiences: the Business (a member of one of the partner's schedules) builds a request,
 * edits it, withdraws or stops it; the partner (the account that owns those schedules) approves
 * or declines each version, records payment, stops campaigns, and reads its statements. The
 * rules live in BoostPartnerService; this controller only shows and routes.
 */
class BoostPartnerController extends Controller
{
    public function __construct(protected BoostPartnerService $service) {}

    // -------------------------------------------------------------------------------------
    // The Business
    // -------------------------------------------------------------------------------------

    public function create(Event $event, BoostPartner $partner)
    {
        if (! auth()->user()->runsEvent($event)) {
            abort(403);
        }

        $role = $this->owningRole($event);
        if (! $role) {
            abort(404);
        }

        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.pro_feature_required'));
        }

        $open = BoostCampaign::partnerOpen()->where('event_id', $event->id)->first();
        if ($open) {
            return redirect()->route('boost.show', ['hash' => $open->hashedId()]);
        }

        $defaults = $this->defaults($event);

        return view('boost.partner-form', [
            'event' => $event,
            'role' => $role,
            'partner' => $partner,
            'campaign' => null,
            'version' => null,
            'defaults' => $defaults,
            'audience' => \App\Utils\BoostAudience::geo($defaults['targeting'] ?? [], $event),
            'minBudget' => (float) config('services.meta.min_budget', 10),
            'maxBudget' => (float) config('services.meta.max_budget', 1000),
            'currency' => $partner->ad_account_currency ?: config('services.meta.default_currency', 'USD'),
        ]);
    }

    public function store(Request $request, Event $event, BoostPartner $partner)
    {
        if (! auth()->user()->runsEvent($event) || $event->is_draft) {
            abort(403);
        }

        $role = $this->owningRole($event);
        if (! $role || ! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.pro_feature_required'));
        }

        $data = $this->inScheduleTime($this->validated($request, true), $role);
        $defaults = $this->defaults($event);
        if (isset($data['ages'])) {
            $data['targeting'] = array_merge($defaults['targeting'] ?? [], $data['ages']);
        }

        try {
            $campaign = $this->service->submit($partner, $event, $role, auth()->user(), $data, $defaults);
        } catch (BoostPartnerException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('boost.show', ['hash' => $campaign->hashedId()])
            ->with('success', __('messages.boost_partner_sent', ['page' => $partner->displayName()]));
    }

    public function showCampaign(BoostCampaign $campaign)
    {
        $isPartnerViewer = $this->isPartnerOf($campaign);
        // Whoever asked may still READ a boost whose event is gone (the stopped mail links here);
        // working on it needs the event.
        $requesterOfGoneEvent = ! $campaign->event && (int) $campaign->user_id === (int) auth()->id();
        if (! $isPartnerViewer && ! $this->canWorkOn($campaign) && ! $requesterOfGoneEvent) {
            abort(403);
        }

        $campaign->load(['event', 'role', 'user', 'partner', 'versions', 'spendDays']);

        return view('boost.partner-show', [
            'campaign' => $campaign,
            'partner' => $campaign->partner,
            'isPartnerViewer' => $isPartnerViewer,
            'history' => $this->history($campaign),
            'audience' => \App\Utils\BoostAudience::summary($campaign->targeting, $campaign->event),
        ]);
    }

    public function edit(string $hash)
    {
        $campaign = $this->findCampaign($hash);
        if (! $this->canWorkOn($campaign) || in_array($campaign->status, ['completed', 'stopped'], true)) {
            abort(403);
        }

        $version = $campaign->currentVersion();

        return view('boost.partner-form', [
            'event' => $campaign->event,
            'role' => $campaign->role,
            'partner' => $campaign->partner,
            'campaign' => $campaign,
            'version' => $version,
            'defaults' => [],
            'audience' => \App\Utils\BoostAudience::geo($campaign->targeting ?? [], $campaign->event),
            'minBudget' => (float) config('services.meta.min_budget', 10),
            'maxBudget' => (float) config('services.meta.max_budget', 1000),
            'currency' => $campaign->currency_code,
        ]);
    }

    public function update(Request $request, string $hash)
    {
        $campaign = $this->findCampaign($hash);
        if (! $this->canWorkOn($campaign)) {
            abort(403);
        }

        $data = $this->inScheduleTime($this->validated($request, false), $campaign->role);
        if (isset($data['ages'])) {
            $data['targeting'] = array_merge($campaign->targeting ?? [], $data['ages']);
        }

        try {
            $result = $this->service->edit($campaign, auth()->user(), $data);
        } catch (BoostPartnerException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (MetaApiException|\Illuminate\Http\Client\ConnectionException $e) {
            report($e);

            return back()->withInput()->with('error', __('messages.boost_partner_meta_unreachable'));
        }

        $message = match (true) {
            $result['needs_approval'] => __('messages.boost_partner_sent_again', ['page' => $campaign->partner?->displayName()]),
            $result['needs_payment'] => __('messages.boost_partner_needs_payment_again'),
            default => __('messages.boost_partner_saved'),
        };

        return redirect()->route('boost.show', ['hash' => $campaign->hashedId()])->with('success', $message);
    }

    // -------------------------------------------------------------------------------------
    // The partner's pages
    // -------------------------------------------------------------------------------------

    public function requests(Request $request)
    {
        $partner = $this->partnerOrFail();

        $query = $partner->campaigns()
            ->whereIn('status', ['pending_approval', 'awaiting_payment', 'ready'])
            ->with(['event', 'role', 'user', 'versions'])
            ->orderBy('submitted_at');

        if ($request->filled('schedule')) {
            $query->where('role_id', UrlUtils::decodeId($request->schedule));
        }

        return view('boost.partner-requests', [
            'partner' => $partner,
            'requests' => $query->get(),
            'schedules' => $this->partnerSchedules($partner),
            'selectedSchedule' => $request->schedule,
            'tab' => 'requests',
        ]);
    }

    public function campaigns(Request $request)
    {
        $partner = $this->partnerOrFail();

        return view('boost.partner-campaigns', [
            'partner' => $partner,
            'campaigns' => $this->campaignQuery($partner, $request)->with(['event', 'role'])->latest('submitted_at')->paginate(50)->withQueryString(),
            // Paid is counted only where it was paid in the campaign's own currency, and
            // "paid minus spend" only over campaigns that are paid, as each row shows it.
            'totals' => $this->campaignQuery($partner, $request)
                ->selectRaw('currency_code, SUM(user_budget) as budget, '
                    .'SUM(CASE WHEN payment_confirmed_at IS NOT NULL AND (payment_currency IS NULL OR payment_currency = currency_code) THEN payment_amount ELSE 0 END) as paid, '
                    .'SUM(COALESCE(actual_spend, 0)) as spend, '
                    .'SUM(CASE WHEN payment_confirmed_at IS NOT NULL AND (payment_currency IS NULL OR payment_currency = currency_code) THEN COALESCE(actual_spend, 0) ELSE 0 END) as paid_spend')
                ->groupBy('currency_code')->get(),
            'schedules' => $this->partnerSchedules($partner),
            'filters' => $request->only(['status', 'schedule', 'month', 'unpaid', 'not_final']),
            'tab' => 'campaigns',
        ]);
    }

    public function export(Request $request)
    {
        $partner = $this->partnerOrFail();
        $rows = $this->campaignQuery($partner, $request)->with(['event', 'role'])->latest('submitted_at')->get();

        $headers = ['Id', 'Schedule', 'Event', 'Status', 'Budget', 'Currency', 'Paid', 'Payment reference', 'Spend', 'Spend final', 'Starts', 'Ends', 'Stopped', 'Stop reason'];

        return response()->streamDownload(function () use ($rows, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $c) {
                fputcsv($out, array_map(fn ($v) => CsvUtils::sanitizeCell((string) $v), [
                    $c->hashedId(),
                    $c->role?->getDisplayName(false) ?? $c->schedule_name,
                    $c->event?->translatedName() ?? $c->event_name,
                    $c->status,
                    number_format((float) $c->user_budget, 2, '.', ''),
                    $c->currency_code,
                    $c->payment_amount !== null ? number_format((float) $c->payment_amount, 2, '.', '') : '',
                    $c->payment_reference,
                    number_format((float) $c->actual_spend, 2, '.', ''),
                    $c->spend_final_at ? 'yes' : 'no',
                    $c->scheduled_start?->toIso8601String(),
                    $c->scheduled_end?->toIso8601String(),
                    $c->stopped_at?->toIso8601String(),
                    $c->stop_reason ?: $c->stop_code,
                ]));
            }
            fclose($out);
        }, 'boost-campaigns-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function statements()
    {
        $partner = $this->billedPartnerOrFail();

        return view('boost.partner-statements', [
            'partner' => $partner,
            'statements' => $partner->statements()->orderByDesc('period')->get(),
            'estimate' => app(BoostStatementService::class)->monthSoFar($partner),
            'tab' => 'statements',
        ]);
    }

    public function statement(Request $request, string $period)
    {
        $partner = $this->billedPartnerOrFail();
        $statement = $this->findStatement($partner, $period, $request->query('currency'), ['lines.campaign.event']);

        return view('boost.partner-statement', [
            'partner' => $partner,
            'statement' => $statement,
            'tab' => 'statements',
        ]);
    }

    public function payStatement(Request $request, string $period)
    {
        $partner = $this->billedPartnerOrFail();
        $statement = $this->findStatement($partner, $period, $request->input('currency'));

        $service = app(BoostStatementService::class);
        $result = $service->charge($statement, true);

        // The bank wants the partner to confirm: send them there; they come back to
        // statementReturn().
        if ($result === 'action_required' && $service->redirectUrl) {
            return redirect()->away($service->redirectUrl);
        }

        return back()->with($result === 'paid' ? 'success' : 'error', __($result === 'paid'
            ? 'messages.boost_statement_paid'
            : ($result === 'action_required' ? 'messages.boost_partner_payment_needs_action' : 'messages.boost_statement_charge_failed')));
    }

    /** Back from the bank's confirmation page: what Stripe says now is what is recorded. */
    public function statementReturn(Request $request, string $period)
    {
        $partner = $this->billedPartnerOrFail();
        $statement = $this->findStatement($partner, $period, $request->query('currency'));
        $result = app(BoostStatementService::class)->settleReturned($statement);

        return redirect()->route('boost.partner.statement', ['period' => $period, 'currency' => $statement->currency])
            ->with($result === 'paid' ? 'success' : 'error', __($result === 'paid' ? 'messages.boost_statement_paid' : 'messages.boost_statement_charge_failed'));
    }

    protected function findStatement(BoostPartner $partner, string $period, ?string $currency, array $with = []): BoostStatement
    {
        return $partner->statements()->where('period', $period)
            ->when($currency, fn ($q) => $q->where('currency', strtoupper((string) $currency)))
            ->with($with)
            ->orderBy('currency')
            ->firstOrFail();
    }

    /**
     * A picked day is the schedule's day: the end runs to the end of that day and the start from
     * its beginning (or now, if that has passed), in the schedule's timezone.
     */
    protected function inScheduleTime(array $data, ?Role $role): array
    {
        $tz = $role?->timezone ?: config('app.timezone');

        if (! empty($data['scheduled_end'])) {
            $data['scheduled_end'] = \Carbon\Carbon::parse($data['scheduled_end'], $tz)->endOfDay()->utc();
        }
        if (! empty($data['scheduled_start'])) {
            $data['scheduled_start'] = \Carbon\Carbon::parse($data['scheduled_start'], $tz)->startOfDay()->utc();
        }

        // An end before the start is refused by Facebook, three times over, and then reads as a
        // failed launch: refuse it here, where the person can still fix it.
        if (! empty($data['scheduled_end']) && ! empty($data['scheduled_start']) && $data['scheduled_end']->lte($data['scheduled_start'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'scheduled_end' => __('validation.after', ['attribute' => __('messages.end_date'), 'date' => __('messages.start_date')]),
            ]);
        }

        return $data;
    }

    // -------------------------------------------------------------------------------------
    // The partner's decisions (and Stop, which the Business may press too)
    // -------------------------------------------------------------------------------------

    public function approve(Request $request, string $hash)
    {
        $campaign = $this->partnerCampaign($hash);
        $request->validate([
            'version' => 'required|integer|min:1',
            'note' => 'nullable|string|max:1000',
            'with_payment' => 'nullable|boolean',
            'reference' => 'required_if:with_payment,1|nullable|string|max:255',
            'amount' => 'required_if:with_payment,1|nullable|numeric|min:0|max:1000000',
            // The budget on screen when the dialog was opened: a payment is for that budget.
            'budget' => 'nullable|numeric|min:0',
        ]);

        $payment = $request->boolean('with_payment')
            ? ['reference' => $request->reference, 'amount' => $request->amount, 'currency' => $campaign->currency_code, 'budget' => $request->budget]
            : null;

        return $this->act($campaign, fn () => $this->service->approve($campaign, (int) $request->version, $request->note, auth()->user(), $payment), 'messages.boost_partner_approved');
    }

    public function decline(Request $request, string $hash)
    {
        $campaign = $this->partnerCampaign($hash);
        $request->validate(['note' => 'required|string|max:1000']);

        return $this->act($campaign, fn () => $this->service->decline($campaign, $request->note, auth()->user()), 'messages.boost_partner_declined');
    }

    public function confirmPayment(Request $request, string $hash)
    {
        $campaign = $this->partnerCampaign($hash);
        $request->validate([
            'reference' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:1000000',
            'budget' => 'nullable|numeric|min:0',
        ]);

        return $this->act($campaign, fn () => $this->service->confirmPayment($campaign, [
            'reference' => $request->reference,
            'amount' => $request->amount,
            'currency' => $campaign->currency_code,
            'budget' => $request->budget,
        ], auth()->user()), 'messages.boost_partner_payment_recorded');
    }

    public function withdrawPayment(string $hash)
    {
        $campaign = $this->partnerCampaign($hash);

        return $this->act($campaign, fn () => $this->service->withdrawPayment($campaign, auth()->user()), 'messages.boost_partner_payment_withdrawn');
    }

    public function stop(Request $request, string $hash)
    {
        $campaign = $this->findCampaign($hash);
        $isPartner = $this->isPartnerOf($campaign);
        if (! $isPartner && ! $this->canWorkOn($campaign)) {
            abort(403);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        $code = $isPartner
            ? BoostPartnerService::STOP_BY_PARTNER
            : ($campaign->launched_at ? BoostPartnerService::STOP_BY_BUSINESS : BoostPartnerService::STOP_WITHDRAWN);

        try {
            $result = $this->service->stop($campaign, auth()->user(), $code, $request->reason);
        } catch (BoostPartnerException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            $result['paused_at_meta'] ? 'success' : 'error',
            $result['paused_at_meta'] ? __('messages.boost_partner_stopped') : __('messages.boost_partner_stop_pending'),
        );
    }

    // -------------------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------------------

    protected function act(BoostCampaign $campaign, callable $step, string $successKey)
    {
        try {
            $step();
        } catch (BoostPartnerException $e) {
            $message = in_array($e->errorCode, ['stale_version', 'stale_budget'], true) || $e->status === 409
                ? __('messages.boost_partner_changed_while_reviewing')
                : $e->getMessage();

            return redirect()->to(route('boost.partner.requests').'#request-'.$campaign->hashedId())
                ->with('error', $message)->with('focus_request', $campaign->hashedId());
        }

        return redirect()->back()->with('success', __($successKey));
    }

    protected function validated(Request $request, bool $creating): array
    {
        $min = (float) config('services.meta.min_budget', 10);
        $max = (float) config('services.meta.max_budget', 1000);

        $request->validate([
            'budget' => ($creating ? 'required' : 'nullable').'|numeric|min:'.$min.'|max:'.$max,
            'headline' => 'nullable|string|max:40',
            'primary_text' => 'nullable|string|max:125',
            'description' => 'nullable|string|max:30',
            'call_to_action' => 'nullable|in:LEARN_MORE,GET_TICKETS,SIGN_UP,BOOK_TRAVEL',
            'scheduled_start' => 'nullable|date|after_or_equal:yesterday',
            'scheduled_end' => 'nullable|date|after:now',
            'age_min' => 'nullable|integer|min:18|max:65',
            'age_max' => 'nullable|integer|min:18|max:65|gte:age_min',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'use_event_image' => 'nullable|boolean',
        ]);

        $data = $request->only(['budget', 'headline', 'primary_text', 'call_to_action', 'scheduled_start', 'scheduled_end']);
        $data = array_filter($data, fn ($v) => $v !== null && $v !== '');
        // The description is optional on the ad: an emptied box clears it, so it is kept as ''.
        if ($request->has('description')) {
            $data['description'] = (string) $request->input('description');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }
        if ($request->boolean('use_event_image')) {
            $data['use_event_image'] = true;
        }
        if ($request->filled('age_min') || $request->filled('age_max')) {
            $data['ages'] = array_filter(['age_min' => $request->integer('age_min') ?: null, 'age_max' => $request->integer('age_max') ?: null]);
        }

        return $data;
    }

    protected function defaults(Event $event): array
    {
        $meta = MetaAdsService::isBoostConfigured() ? new MetaAdsService : new MetaAdsServiceFake;

        return $meta->generateQuickBoostDefaults($event);
    }

    protected function owningRole(Event $event): ?Role
    {
        return $event->creatorRole ?: $event->roles()->orderBy('event_role.id')->first();
    }

    protected function findCampaign(string $hash): BoostCampaign
    {
        $campaign = BoostCampaign::with(['event', 'partner'])->findOrFail(UrlUtils::decodeIdOrFail($hash));
        if (! $campaign->isPartner()) {
            abort(404);
        }

        return $campaign;
    }

    protected function partnerCampaign(string $hash): BoostCampaign
    {
        $campaign = $this->findCampaign($hash);
        if (! $this->isPartnerOf($campaign)) {
            abort(403);
        }

        return $campaign;
    }

    protected function isPartnerOf(BoostCampaign $campaign): bool
    {
        return $campaign->partner && (int) $campaign->partner->user_id === (int) auth()->id();
    }

    /**
     * The Business: whoever may edit the event now. Having made the request is not enough on its
     * own: somebody who has left the team keeps no hold on a paid campaign.
     */
    protected function canWorkOn(BoostCampaign $campaign): bool
    {
        // runsEvent(), not canEditEvent(): the venue an event is at, or a performer on it, may
        // edit the event, but a boost is the business of the schedule that owns it.
        return $campaign->event && auth()->user()->runsEvent($campaign->event);
    }

    protected function partnerOrFail(): BoostPartner
    {
        $partner = BoostPartner::forUser(auth()->user());
        if (! $partner) {
            abort(404);
        }

        return $partner;
    }

    /**
     * A partner switched off by an admin is still billed for what its campaigns spent, so it
     * keeps its statements and Pay now.
     */
    protected function billedPartnerOrFail(): BoostPartner
    {
        $partner = config('app.hosted') ? BoostPartner::where('user_id', auth()->id())->first() : null;
        if (! $partner) {
            abort(404);
        }

        return $partner;
    }

    protected function partnerSchedules(BoostPartner $partner)
    {
        return Role::where('user_id', $partner->user_id)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'subdomain']);
    }

    protected function campaignQuery(BoostPartner $partner, Request $request)
    {
        // A request is a campaign once it has been at Meta: one paused for an edit, or declined
        // after it ran, spent the partner's money and is on its statement, so it stays listed here
        // and in the export.
        $query = BoostCampaign::where('boost_partner_id', $partner->id)
            ->where(fn ($q) => $q->whereNotIn('status', ['pending_approval', 'awaiting_payment', 'ready', 'declined'])
                ->orWhereNotNull('meta_campaign_id'));

        if ($request->filled('status') && in_array($request->status, BoostCampaign::PARTNER_STATUSES, true)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('schedule')) {
            $query->where('role_id', UrlUtils::decodeId($request->schedule));
        }
        if ($request->filled('month') && preg_match('/^\d{4}-\d{2}$/', (string) $request->month)) {
            $start = \Carbon\Carbon::createFromFormat('Y-m', $request->month)->startOfMonth();
            $query->where(fn ($q) => $q->whereBetween('launched_at', [$start, $start->copy()->endOfMonth()])
                ->orWhereHas('spendDays', fn ($d) => $d->whereBetween('day', [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()])));
        }
        if ($request->boolean('unpaid')) {
            $query->whereNull('payment_confirmed_at');
        }
        if ($request->boolean('not_final')) {
            $query->whereNull('spend_final_at');
        }

        return $query;
    }

    /** What happened to a request, from the audit log: sent, declined, approved, paid, launched. */
    protected function history(BoostCampaign $campaign)
    {
        return AuditLog::where('model_type', 'BoostCampaign')
            ->where('model_id', $campaign->id)
            ->where('action', 'like', 'boost.partner.%')
            ->orderBy('created_at')
            ->get(['action', 'new_values', 'created_at', 'user_id']);
    }
}
