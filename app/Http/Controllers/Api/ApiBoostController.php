<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BoostPartnerException;
use App\Http\Controllers\Controller;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\BoostStatement;
use App\Services\BoostPartnerService;
use App\Utils\UrlUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * A boost partner's side of its boosts, by API: the same steps and the same rules as its
 * Boost requests and Campaigns pages (BoostPartnerService holds them).
 *
 * Only the key of the account that IS the partner reads anything here. A team member's key,
 * a Business's key, answers 403: every boost the partner can see is on schedules other people
 * run, and the decisions are the partner's alone. An identical repeat of a decision answers the
 * boost as it stands, so a checkout can safely call again after a timeout.
 */
class ApiBoostController extends Controller
{
    protected const MAX_PER_PAGE = 500;

    protected const DEFAULT_PER_PAGE = 100;

    public function __construct(protected BoostPartnerService $service) {}

    public function index(Request $request): JsonResponse
    {
        $partner = $this->partner();
        if ($partner instanceof JsonResponse) {
            return $partner;
        }

        try {
            $request->validate([
                'status' => 'nullable|in:'.implode(',', BoostCampaign::PARTNER_STATUSES),
                'schedule' => 'nullable|string|max:255',
                'updated_since' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:'.self::MAX_PER_PAGE,
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        }

        $query = BoostCampaign::where('boost_partner_id', $partner->id)
            ->with(['event.roles', 'event.creatorRole', 'event.venue', 'role', 'versions']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('schedule')) {
            $query->where(fn ($q) => $q->where('schedule_subdomain', $request->schedule)
                ->orWhereHas('role', fn ($r) => $r->where('subdomain', $request->schedule)));
        }
        if ($request->filled('updated_since')) {
            // Timestamps are whole seconds, so ">=": a client keeps the last updated_at it saw
            // and drops the duplicates, rather than missing a change in the same second.
            $query->where('updated_at', '>=', \Carbon\Carbon::parse($request->updated_since)->utc()->setTimezone(config('app.timezone')));
        }

        $paginator = $query->orderBy('updated_at')->orderBy('id')
            ->paginate(min((int) $request->input('per_page', self::DEFAULT_PER_PAGE), self::MAX_PER_PAGE));

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (BoostCampaign $c) => $c->toPartnerApiData())->values(),
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

    public function show(string $id): JsonResponse
    {
        $campaign = $this->campaign($id);

        return $campaign instanceof JsonResponse ? $campaign : $this->boost($campaign);
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $campaign = $this->campaign($id);
        if ($campaign instanceof JsonResponse) {
            return $campaign;
        }

        try {
            $request->validate([
                'version' => 'required|integer|min:1',
                'note' => 'nullable|string|max:1000',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        }

        return $this->step(fn () => $this->service->approve($campaign, (int) $request->version, $request->note, auth()->user()));
    }

    public function decline(Request $request, string $id): JsonResponse
    {
        $campaign = $this->campaign($id);
        if ($campaign instanceof JsonResponse) {
            return $campaign;
        }

        try {
            $request->validate(['note' => 'required|string|max:1000']);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        }

        return $this->step(fn () => $this->service->decline($campaign, $request->note, auth()->user()));
    }

    public function confirmPayment(Request $request, string $id): JsonResponse
    {
        $campaign = $this->campaign($id);
        if ($campaign instanceof JsonResponse) {
            return $campaign;
        }

        try {
            $request->validate([
                'reference' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0|max:1000000',
                'currency' => 'nullable|string|size:3',
                // The budget.amount the payment was taken for; refused (409) once it has changed.
                'budget' => 'nullable|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        }

        return $this->step(fn () => $this->service->confirmPayment($campaign, [
            'reference' => $request->reference,
            'amount' => $request->amount,
            'currency' => $request->currency ?: $campaign->currency_code,
            'budget' => $request->budget,
        ], auth()->user()));
    }

    public function withdrawPayment(string $id): JsonResponse
    {
        $campaign = $this->campaign($id);
        if ($campaign instanceof JsonResponse) {
            return $campaign;
        }

        return $this->step(fn () => $this->service->withdrawPayment($campaign, auth()->user()));
    }

    public function stop(Request $request, string $id): JsonResponse
    {
        $campaign = $this->campaign($id);
        if ($campaign instanceof JsonResponse) {
            return $campaign;
        }

        try {
            $request->validate(['reason' => 'nullable|string|max:500']);
        } catch (ValidationException $e) {
            return $this->validationError($e);
        }

        return $this->step(fn () => $this->service->stop($campaign, auth()->user(), BoostPartnerService::STOP_BY_PARTNER, $request->reason)['campaign']);
    }

    public function statements(): JsonResponse
    {
        $partner = $this->partner();
        if ($partner instanceof JsonResponse) {
            return $partner;
        }

        $statements = $partner->statements()->orderByDesc('period')->get();

        return response()->json([
            'data' => $statements->map(fn (BoostStatement $s) => [
                'period' => $s->period,
                'currency' => $s->currency,
                'totals' => $this->totals($s),
                'charge' => $this->charge($s),
            ])->values(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function statement(string $period): JsonResponse
    {
        $partner = $this->partner();
        if ($partner instanceof JsonResponse) {
            return $partner;
        }

        if (! preg_match('/^\d{4}-\d{2}$/', $period)) {
            return response()->json(['error' => 'Statement not found'], 404);
        }

        // One statement per month and currency; ?currency= picks one when a partner has two.
        $statement = $partner->statements()->where('period', $period)
            ->when(request()->query('currency'), fn ($q, $c) => $q->where('currency', strtoupper((string) $c)))
            ->orderBy('currency')
            ->with(['lines.campaign.event', 'lines.campaign.role'])->first();
        if (! $statement) {
            return response()->json(['error' => 'Statement not found'], 404);
        }

        $money = fn ($v) => number_format((float) $v, 2, '.', '');

        return response()->json([
            'data' => [
                'period' => $statement->period,
                'currency' => $statement->currency,
                'lines' => $statement->lines->map(fn ($line) => [
                    'boost' => $line->campaign?->hashedId(),
                    'schedule' => $line->campaign?->role?->subdomain ?? $line->campaign?->schedule_subdomain,
                    'event' => $line->campaign?->event?->translatedName() ?? $line->campaign?->event_name,
                    'payment_reference' => $line->campaign?->payment_reference,
                    'budget' => $line->campaign ? $money($line->campaign->user_budget) : null,
                    'month' => $line->period,
                    'spend' => $money($line->spend),
                    'fee' => $money($line->fee),
                    'adjustment' => $money($line->adjustment),
                    'note' => $line->note,
                ])->values(),
                'totals' => $this->totals($statement),
                'charge' => $this->charge($statement),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    // -------------------------------------------------------------------------------------

    protected function partner(): BoostPartner|JsonResponse
    {
        $partner = BoostPartner::forUser(auth()->user());

        return $partner ?: response()->json(['error' => 'Only the boost partner account can use this endpoint'], 403);
    }

    protected function campaign(string $id): BoostCampaign|JsonResponse
    {
        $partner = $this->partner();
        if ($partner instanceof JsonResponse) {
            return $partner;
        }

        $decoded = UrlUtils::decodeId($id);
        $campaign = $decoded ? BoostCampaign::with(['event', 'role', 'versions'])->find($decoded) : null;
        if (! $campaign || ! $campaign->isPartner()) {
            return response()->json(['error' => 'Boost not found'], 404);
        }

        if ((int) $campaign->boost_partner_id !== (int) $partner->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $campaign;
    }

    protected function step(callable $action): JsonResponse
    {
        try {
            $campaign = $action();
        } catch (BoostPartnerException $e) {
            $error = match ($e->errorCode) {
                'stale_version' => 'Version is not the current one',
                'stale_budget' => 'Budget is not the current one',
                default => 'Not allowed in the boost\'s current state',
            };

            return response()->json(['error' => $error, 'code' => $e->errorCode], $e->status);
        }

        return $this->boost($campaign->fresh(['event', 'role', 'versions']));
    }

    protected function boost(BoostCampaign $campaign): JsonResponse
    {
        return response()->json(['data' => $campaign->toPartnerApiData()], 200, [], JSON_PRETTY_PRINT);
    }

    protected function totals(BoostStatement $s): array
    {
        $money = fn ($v) => number_format((float) $v, 2, '.', '');

        return [
            'spend' => $money($s->total_spend),
            'fee' => $money($s->total_fee),
            'adjustments' => $money($s->total_adjustments),
            'carried_in' => $money($s->carried_in),
            'charged' => $money($s->status === 'paid' ? $s->charged_amount : 0),
        ];
    }

    protected function charge(BoostStatement $s): array
    {
        return [
            'status' => $s->status,
            'charged_at' => $s->charged_at?->copy()->utc()->format('Y-m-d\TH:i:s\Z'),
        ];
    }

    protected function validationError(ValidationException $e): JsonResponse
    {
        return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
    }
}
