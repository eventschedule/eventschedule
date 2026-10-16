<?php

namespace App\Http\Controllers;

use App\Exceptions\MetaApiException;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Services\AuditService;
use App\Services\BoostPartnerNotifier;
use App\Services\BoostPartnerService;
use App\Services\MetaAdsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * The Boosts tab of a partner's settings: its Facebook Page and Meta ad account, how its
 * Businesses pay it, and the card the platform's monthly fee is charged to.
 *
 * Two ways to connect:
 *  - partner share: the partner shares its ad account and Page with the platform's Meta
 *    Business and enters the two IDs. We cannot tell which of our accounts shared an asset, so
 *    a platform admin confirms the IDs once before anything launches.
 *  - Facebook Login for Business: the partner approves on Facebook's own screen and picks from
 *    its own Pages and ad accounts. Offered only once META_LOGIN_CONFIG_ID is set (App Review).
 */
class BoostPartnerSettingsController extends Controller
{
    protected const SECTION = '#section-boost-account';

    public function saveShare(Request $request)
    {
        $partner = $this->partner();
        $request->validate([
            'page_id' => 'required|string|max:255',
            'ad_account_id' => 'required|string|max:255',
        ]);

        $pageId = self::digits($request->page_id);
        $adAccountId = self::digits($request->ad_account_id);
        if (! $pageId || ! $adAccountId) {
            return $this->back()->withErrors(['page_id' => __('messages.boost_partner_ids_invalid')])->withInput();
        }

        $changed = $pageId !== $partner->page_id || $adAccountId !== $partner->ad_account_id || $partner->connection_method !== BoostPartner::METHOD_PARTNER_SHARE;

        // A share is read with the PLATFORM's token (accessToken() is null for one), so any IDs
        // already shared with our Business would pass every check, show their account's, Business's
        // and Page's names here, and leave only an admin's eye between this account and boosts run
        // and billed on them. Another partner's, or the platform's own, are refused outright.
        if ($this->heldElsewhere($partner, $adAccountId, $pageId)) {
            return $this->back()->withErrors(['ad_account_id' => __('messages.boost_partner_ids_taken')])->withInput();
        }

        if ($refusal = $this->refuseChange($partner, BoostPartner::METHOD_PARTNER_SHARE, $adAccountId, $pageId)) {
            return $refusal;
        }

        $partner->forceFill([
            'connection_method' => BoostPartner::METHOD_PARTNER_SHARE,
            'page_id' => $pageId,
            'ad_account_id' => $adAccountId,
            'meta_token' => null,
            'meta_user_id' => null,
            'confirmed_at' => $changed ? null : $partner->confirmed_at,
            'confirmed_by' => $changed ? null : $partner->confirmed_by,
        ])->save();

        AuditService::log(AuditService::BOOST_PARTNER_CONNECT, auth()->id(), 'BoostPartner', $partner->id, null, ['method' => 'partner_share']);

        return $this->verify($partner);
    }

    public function check()
    {
        return $this->verify($this->partner());
    }

    public function disconnect()
    {
        $partner = $this->partner();
        $service = app(BoostPartnerService::class);

        // Running boosts are stopped first, while this connection can still reach them (the
        // confirm says so). A boost whose pause does not reach Facebook keeps the connection: it
        // is needed to try again. Requests still waiting for approval or payment are left as they
        // are; nothing launches until the partner connects again.
        $partner->campaigns()->whereIn('status', BoostCampaign::PARTNER_LIVE_STATUSES)->each(function (BoostCampaign $campaign) use ($service) {
            try {
                $service->stop($campaign, auth()->user(), BoostPartnerService::STOP_BY_PARTNER, __('messages.boost_partner_disconnected_reason'));
            } catch (\Exception $e) {
                report($e);
            }
        });

        if ($partner->campaigns()->whereNotNull('pause_pending_at')->exists()) {
            return $this->back()->with('error', __('messages.boost_partner_disconnect_blocked'));
        }

        $partner->forceFill([
            'connection_method' => null,
            'meta_token' => null,
            'meta_user_id' => null,
            'page_id' => null,
            'page_name' => null,
            'ad_account_id' => null,
            'ad_account_name' => null,
            'ad_account_business_name' => null,
            'confirmed_at' => null,
            'confirmed_by' => null,
            'connection_error' => null,
            'needs_attention_at' => null,
        ])->save();

        AuditService::log(AuditService::BOOST_PARTNER_DISCONNECT, auth()->id(), 'BoostPartner', $partner->id);

        return $this->back()->with('message', __('messages.boost_partner_disconnected'));
    }

    // -------------------------------------------------------------------------------------
    // Facebook Login for Business
    // -------------------------------------------------------------------------------------

    public function login(Request $request)
    {
        $this->partner();
        $configId = config('services.meta.login_config_id');
        if (! $configId || ! config('services.meta.app_id')) {
            abort(404);
        }

        $state = Str::random(40);
        $request->session()->put('boost_partner_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => route('boost.partner.settings.callback'),
            'config_id' => $configId,
            'response_type' => 'code',
            'override_default_response_type' => 'true',
            'state' => $state,
        ]);

        return redirect()->away('https://www.facebook.com/'.config('services.meta.api_version', 'v21.0').'/dialog/oauth?'.$query);
    }

    public function callback(Request $request)
    {
        $partner = $this->partner();
        $expected = $request->session()->pull('boost_partner_oauth_state');
        if (! $expected || ! hash_equals($expected, (string) $request->state) || ! $request->filled('code')) {
            return $this->back()->with('error', __('messages.boost_partner_login_failed'));
        }

        try {
            $response = Http::timeout(30)->get('https://graph.facebook.com/'.config('services.meta.api_version', 'v21.0').'/oauth/access_token', [
                'client_id' => config('services.meta.app_id'),
                'client_secret' => config('services.meta.app_secret'),
                'redirect_uri' => route('boost.partner.settings.callback'),
                'code' => $request->code,
            ]);
            $token = $response->json('access_token');
            if (! $response->successful() || ! $token) {
                return $this->back()->with('error', __('messages.boost_partner_login_failed'));
            }

            $assets = (new MetaAdsService($token, '', ''))->listOwnAssets();
            $metaUserId = (string) Http::timeout(30)->get('https://graph.facebook.com/'.config('services.meta.api_version', 'v21.0').'/me', ['access_token' => $token, 'fields' => 'id'])->json('id');
        } catch (\Throwable $e) {
            // A transfer failure's message ends with the address asked, which carries the app
            // secret, the code or the token in its query string, and not only for the five cURL
            // errors Laravel turns into a ConnectionException: never reported as it is.
            report(new \RuntimeException('Boost partner login failed: '.class_basename($e).': '.self::withoutQueries($e->getMessage())));

            return $this->back()->with('error', __('messages.boost_partner_login_failed'));
        }

        // Who the token belongs to is what Meta's deauthorize and data-deletion callbacks name
        // (forgetMetaUser()): a token stored without it, or under the id of the person who logged
        // in before, would outlive its owner's withdrawal or be wiped by somebody else's.
        if ($metaUserId === '') {
            return $this->back()->with('error', __('messages.boost_partner_login_failed'));
        }

        // What the token reaches, offered to pick from (selectAssets()) once the login is taken.
        $offered = [
            'pages' => collect($assets['pages'])->map(fn ($p) => ['id' => (string) $p['id'], 'name' => (string) ($p['name'] ?? $p['id'])])->values()->all(),
            'ad_accounts' => collect($assets['ad_accounts'])->map(fn ($a) => ['id' => MetaAdsService::bareAccountId((string) $a['id']), 'name' => (string) ($a['name'] ?? $a['id']), 'currency' => $a['currency'] ?? null])->values()->all(),
        ];

        // Logging in again over a login (a token near its expiry, or one Meta revoked) is a new
        // token for the same connection while it still reaches the Page and ad account in use: the
        // boosts on them keep running. Another pair can still be picked from the list above.
        $reachableAccounts = collect($assets['ad_accounts'])->map(fn ($a) => MetaAdsService::bareAccountId((string) $a['id']));
        $reachablePages = collect($assets['pages'])->map(fn ($p) => (string) $p['id']);
        if ($partner->connection_method === BoostPartner::METHOD_LOGIN && $partner->ad_account_id && $partner->page_id
            && $reachableAccounts->contains((string) $partner->ad_account_id) && $reachablePages->contains((string) $partner->page_id)) {
            $partner->forceFill([
                'meta_token' => $token,
                'meta_user_id' => $metaUserId,
                'meta_token_expires_at' => $response->json('expires_in') ? now()->addSeconds((int) $response->json('expires_in')) : null,
            ])->save();
            $request->session()->put('boost_partner_assets', $offered);

            AuditService::log(AuditService::BOOST_PARTNER_CONNECT, auth()->id(), 'BoostPartner', $partner->id, null, ['method' => 'login', 'refreshed' => true]);

            return $this->verify($partner);
        }

        // Anything else is a new connection, which clears the Page and ad account: never while a
        // boost runs on the old ones, which would then be read and paused with a token that
        // cannot reach them (and, once another account is picked, abandoned while still running).
        if ($this->hasLiveWork($partner)) {
            return $this->back()->with('error', __('messages.boost_partner_disconnect_blocked'));
        }

        // The new token, with no Page or ad account until the partner picks them from its own:
        // the old IDs belonged to another connection.
        $partner->forceFill([
            'page_id' => null,
            'page_name' => null,
            'ad_account_id' => null,
            'ad_account_name' => null,
            'connection_method' => BoostPartner::METHOD_LOGIN,
            'meta_token' => $token,
            'meta_user_id' => $metaUserId,
            'meta_token_expires_at' => $response->json('expires_in') ? now()->addSeconds((int) $response->json('expires_in')) : null,
            'confirmed_at' => null,
        ])->save();

        $request->session()->put('boost_partner_assets', $offered);

        AuditService::log(AuditService::BOOST_PARTNER_CONNECT, auth()->id(), 'BoostPartner', $partner->id, null, ['method' => 'login']);

        return $this->back()->with('message', __('messages.boost_partner_pick_assets'));
    }

    /** Whether another partner holds this ad account or Page, or they are the platform's own. */
    protected function heldElsewhere(BoostPartner $partner, string $adAccountId, string $pageId): bool
    {
        $platformAccount = MetaAdsService::bareAccountId((string) config('services.meta.ad_account_id'));
        $platformPage = (string) config('services.meta.page_id');

        if (($platformAccount !== '' && $adAccountId === $platformAccount) || ($platformPage !== '' && $pageId === $platformPage)) {
            return true;
        }

        return BoostPartner::where('id', '!=', $partner->id)
            ->where(fn ($q) => $q->where('ad_account_id', $adAccountId)->orWhere('page_id', $pageId))
            ->exists();
    }

    /** A message with every address's query string taken out. */
    private static function withoutQueries(string $message): string
    {
        return (string) preg_replace('~(\bhttps?://[^\s?#]+)\?\S*~i', '$1?[query removed]', $message);
    }

    public function selectAssets(Request $request)
    {
        $partner = $this->partner();
        $assets = $request->session()->get('boost_partner_assets', ['pages' => [], 'ad_accounts' => []]);

        $request->validate([
            'page_id' => 'required|string|in:'.implode(',', array_column($assets['pages'], 'id')),
            'ad_account_id' => 'required|string|in:'.implode(',', array_column($assets['ad_accounts'], 'id')),
        ]);

        if ($partner->connection_method !== BoostPartner::METHOD_LOGIN || ! $partner->meta_token) {
            return $this->back()->with('error', __('messages.boost_partner_login_failed'));
        }

        if ($refusal = $this->refuseChange($partner, BoostPartner::METHOD_LOGIN, $request->ad_account_id, $request->page_id)) {
            return $refusal;
        }

        // Another Page or ad account while a boost runs on the current ones: the sync would read
        // it with an account it no longer uses and, once another is in place, abandon it while it
        // still runs at Meta.
        $moves = (string) $request->ad_account_id !== (string) $partner->ad_account_id || (string) $request->page_id !== (string) $partner->page_id;
        if ($moves && $partner->ad_account_id && $this->hasLiveWork($partner)) {
            return $this->back()->with('error', __('messages.boost_partner_disconnect_blocked'));
        }

        $partner->forceFill(['page_id' => $request->page_id, 'ad_account_id' => $request->ad_account_id])->save();
        $request->session()->forget('boost_partner_assets');

        return $this->verify($partner);
    }

    // -------------------------------------------------------------------------------------
    // How Businesses pay, and the fee card
    // -------------------------------------------------------------------------------------

    public function saveInstructions(Request $request)
    {
        $partner = $this->partner();
        $request->validate(['payment_instructions' => 'nullable|string|max:500']);
        $partner->forceFill(['payment_instructions' => $request->payment_instructions ?: null])->save();

        return redirect()->to(route('profile.edit').self::SECTION)->with('message', __('messages.boost_partner_instructions_saved'));
    }

    public function cardIntent()
    {
        $partner = $this->partner();
        $secret = config('services.stripe_platform.secret');
        if (! $secret) {
            return response()->json(['error' => __('messages.boost_partner_card_unavailable')], 422);
        }

        try {
            $stripe = new \Stripe\StripeClient($secret);
            if (! $partner->stripe_customer_id) {
                $customer = $stripe->customers->create([
                    'email' => auth()->user()->email,
                    'name' => auth()->user()->name,
                    'metadata' => ['boost_partner_id' => (string) $partner->id],
                ]);
                $partner->forceFill(['stripe_customer_id' => $customer->id])->save();
            }

            $intent = $stripe->setupIntents->create([
                'customer' => $partner->stripe_customer_id,
                'usage' => 'off_session',
                'payment_method_types' => ['card'],
            ]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => __('messages.boost_partner_card_unavailable')], 422);
        }

        return response()->json(['client_secret' => $intent->client_secret, 'key' => config('services.stripe_platform.key')]);
    }

    public function saveCard(Request $request)
    {
        $partner = $this->partner();
        $request->validate(['setup_intent' => 'required|string|max:255']);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
            $intent = $stripe->setupIntents->retrieve($request->setup_intent);
            if ($intent->status !== 'succeeded' || $intent->customer !== $partner->stripe_customer_id || ! $intent->payment_method) {
                return response()->json(['error' => __('messages.boost_partner_card_failed')], 422);
            }

            $pm = $stripe->paymentMethods->retrieve($intent->payment_method);
            $stripe->customers->update($partner->stripe_customer_id, ['invoice_settings' => ['default_payment_method' => $pm->id]]);
        } catch (\Exception $e) {
            report($e);

            return response()->json(['error' => __('messages.boost_partner_card_failed')], 422);
        }

        $partner->forceFill([
            'stripe_payment_method_id' => $pm->id,
            'pm_brand' => $pm->card->brand ?? null,
            'pm_last_four' => $pm->card->last4 ?? null,
        ])->save();

        // A statement that failed on the old card is tried again on the new one.
        $partner->statements()->whereIn('status', ['failed', 'action_required'])
            // attempts only ever goes up: it is part of each confirm's idempotency key.
            ->update(['last_attempt_at' => null, 'status' => 'pending']);

        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------------------
    // Meta's callbacks for Facebook Login for Business (no session: Meta signs the request)
    // -------------------------------------------------------------------------------------

    public function deauthorize(Request $request)
    {
        $this->forgetMetaUser($request);

        return response()->json(['ok' => true]);
    }

    public function dataDeletion(Request $request)
    {
        $userId = $this->forgetMetaUser($request);
        $code = substr(hash('sha256', (string) $userId.config('app.key')), 0, 16);

        return response()->json([
            'url' => route('profile.edit').self::SECTION,
            'confirmation_code' => $code,
        ]);
    }

    /**
     * Clear the stored token of whoever Meta names in its signed request. Their live boosts
     * become "needs attention" and stop after the grace if it is not reconnected.
     */
    protected function forgetMetaUser(Request $request): ?string
    {
        $signed = (string) $request->input('signed_request');
        $parts = explode('.', $signed, 2);
        $secret = (string) config('services.meta.app_secret');
        if (count($parts) !== 2 || ! $secret) {
            abort(400);
        }

        [$sig, $payload] = $parts;
        $decode = fn ($v) => base64_decode(strtr($v, '-_', '+/'));
        if (! hash_equals(hash_hmac('sha256', $payload, $secret, true), (string) $decode($sig))) {
            abort(400);
        }

        $userId = (string) (json_decode((string) $decode($payload), true)['user_id'] ?? '');
        if ($userId === '') {
            return null;
        }

        BoostPartner::where('meta_user_id', $userId)->where('connection_method', BoostPartner::METHOD_LOGIN)->each(function (BoostPartner $partner) {
            $partner->forceFill(['meta_token' => null])->save();
            BoostPartnerNotifier::accessProblem($partner);
        });

        return $userId;
    }

    // -------------------------------------------------------------------------------------

    /**
     * Read what Meta says about the chosen Page and ad account, store it, and show each check's
     * result. A login confirms itself; a partner share waits for a platform admin.
     */
    protected function verify(BoostPartner $partner)
    {
        $checks = [];
        $error = null;

        try {
            // Inside the try: a login whose token Meta withdrew (the deauthorize webhook clears
            // it) throws here, and is a failed check like any other, not an error page.
            $meta = MetaAdsService::forPartner($partner);

            if ($partner->connection_method === BoostPartner::METHOD_PARTNER_SHARE) {
                $business = (string) config('services.meta.business_id');
                $systemUser = (string) config('services.meta.system_user_id');
                if (! $business || ! $systemUser) {
                    return $this->back()->with('error', __('messages.boost_partner_not_configured'));
                }

                try {
                    $meta->assignSharedAssets($business, $systemUser);
                } catch (MetaApiException $e) {
                    // Already assigned answers an error too; the read below is the real test.
                }

                $tasks = $meta->sharedAssetTasks($business);
                $accountTasks = $tasks['ad_account']['permitted_tasks'] ?? [];
                $pageTasks = $tasks['page']['permitted_tasks'] ?? [];
                $checks['ad_account_shared'] = ! empty($tasks['ad_account']) && (in_array('MANAGE', $accountTasks, true) || in_array('ADVERTISE', $accountTasks, true));
                $checks['page_shared'] = ! empty($tasks['page']) && (in_array('ADVERTISE', $pageTasks, true) || in_array('CREATE_CONTENT', $pageTasks, true) || in_array('MANAGE', $pageTasks, true));
            }

            $account = $meta->adAccountDetails();
            $page = $meta->pageDetails();
            $checks['ad_account_active'] = (int) ($account['account_status'] ?? 0) === 1;

            $currency = strtoupper((string) ($account['currency'] ?? ''));
            $hasOpen = $partner->campaigns()->whereIn('status', BoostCampaign::PARTNER_OPEN_STATUSES)->exists();
            if ($hasOpen && $partner->ad_account_currency && $currency && $currency !== $partner->ad_account_currency) {
                $checks['currency_matches'] = false;
            }

            $partner->forceFill([
                'page_name' => $page['name'] ?? null,
                'ad_account_name' => $account['name'] ?? null,
                'ad_account_currency' => ($checks['currency_matches'] ?? true) ? ($currency ?: null) : $partner->ad_account_currency,
                'ad_account_timezone' => $account['timezone_name'] ?? null,
                'ad_account_business_name' => $account['business']['name'] ?? null,
            ]);
        } catch (MetaApiException|\Illuminate\Http\Client\ConnectionException $e) {
            report($e);
            $error = __('messages.boost_partner_check_failed');
            $checks['reachable'] = false;
        }

        $ok = ! in_array(false, $checks, true);
        $partner->forceFill([
            'connection_error' => $ok ? null : ($error ?? __('messages.boost_partner_check_failed')),
            'connection_checked_at' => now(),
        ]);
        // Only a check that passed ends a "needs attention" (and its 72-hour clock).
        if ($ok) {
            $partner->needs_attention_at = null;
        }

        if ($ok && $partner->connection_method === BoostPartner::METHOD_LOGIN) {
            $partner->confirmed_at = now();
        }
        $partner->save();

        if ($ok) {
            app(BoostPartnerService::class)->refreshSnapshots($partner);
        }
        if ($ok && $partner->isUsable()) {
            app(BoostPartnerService::class)->launchReady($partner);
        }
        if ($ok && $partner->connection_method === BoostPartner::METHOD_PARTNER_SHARE && ! $partner->confirmed_at) {
            app(\App\Services\BoostPartnerAdminNotifier::class)->awaitingConfirmation($partner);
        }

        return $this->back()
            ->with('boost_partner_checks', $checks)
            ->with($ok ? 'message' : 'error', $ok ? __('messages.boost_partner_checks_passed') : ($error ?? __('messages.boost_partner_check_failed')));
    }

    /** A campaign running, in review at Meta, or still being paused there. */
    protected function hasLiveWork(BoostPartner $partner): bool
    {
        return $partner->campaigns()->where(fn ($q) => $q->whereIn('status', BoostCampaign::PARTNER_LIVE_STATUSES)
            ->orWhereNotNull('pause_pending_at'))->exists();
    }

    /**
     * Refuse a new connection before anything is saved when it would strand work: a change of
     * method while a campaign is live (it runs on the old token), or an ad account in another
     * currency while requests wait (their budgets were agreed in the old one).
     */
    protected function refuseChange(BoostPartner $partner, string $method, string $adAccountId, string $pageId)
    {
        if ($partner->connection_method && $partner->connection_method !== $method && $this->hasLiveWork($partner)) {
            return $this->back()->with('error', __('messages.boost_partner_disconnect_blocked'));
        }

        $hasOpen = $partner->campaigns()->whereIn('status', BoostCampaign::PARTNER_OPEN_STATUSES)->exists();
        if (! $hasOpen || ! $partner->ad_account_currency || $adAccountId === $partner->ad_account_id) {
            return null;
        }

        try {
            $probe = $partner->replicate()->forceFill(['connection_method' => $method, 'ad_account_id' => $adAccountId, 'page_id' => $pageId]);
            $currency = strtoupper((string) (MetaAdsService::forPartner($probe)->adAccountDetails()['currency'] ?? ''));
        } catch (\Exception $e) {
            return null; // The check that follows the save reports an unreachable account.
        }

        if ($currency && $currency !== strtoupper($partner->ad_account_currency)) {
            return $this->back()->with('error', __('messages.boost_partner_currency_locked'));
        }

        return null;
    }

    protected function partner(): BoostPartner
    {
        $partner = BoostPartner::forUser(auth()->user());
        if (! $partner) {
            abort(404);
        }

        return $partner;
    }

    protected function back()
    {
        return redirect()->to(route('profile.edit').self::SECTION);
    }

    /** Accepts 123, act_123, or a pasted Meta URL holding the id. */
    public static function digits(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/(?:act_)?(\d{5,25})/', $value, $m)) {
            return $m[1];
        }

        return null;
    }
}
