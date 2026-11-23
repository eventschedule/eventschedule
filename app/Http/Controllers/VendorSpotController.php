<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\VendorMap;
use App\Models\VendorMapOccupant;
use App\Models\VendorMapSpot;
use App\Services\GuestAccountService;
use App\Services\VendorMaps\VendorMapClaims;
use App\Services\VendorMaps\VendorMapImages;
use App\Services\VendorMaps\VendorMapKinds;
use App\Services\VendorMaps\VendorMapOccupancy;
use App\Services\VendorMaps\VendorMapPresenter;
use App\Services\VendorMaps\VendorMapSchematic;
use App\Utils\HoneypotUtils;
use App\Utils\ImageUtils;
use App\Utils\TurnstileUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The vendor's side of a vendor map: the link the organizer sends on WhatsApp
 * (`/{schedule}/spot/{token}`) and the "Not coming" link in the approval mail.
 *
 * The person on the other end is a stallholder on a phone who has never heard of the app. So the
 * link asks "Is one of these you?" first, then what they sell, and only last for an account,
 * which is an emailed code or Google and never a password (GuestAccountService). Nothing here
 * says "schedule": their schedule is "your page".
 *
 * Everything that writes is a POST. A request is held server-side while the code is typed or
 * Google answers, and asked for only after the person is signed in: by the code step itself, or
 * by HomeController::home() for Google (completePending()), never by a GET that writes.
 *
 * Signed in with a stall for the season, the same link is the vendor's home: Not coming this
 * week, Change what you sell, Share my stall, Stop coming.
 */
class VendorSpotController extends Controller
{
    /** The request held while Google answers. Read by post_signup_redirect_url() and home(). */
    public const PENDING = 'pending_vendor_claim';

    /** The request held while the emailed code is typed. Not a marker: only the code step reads it. */
    private const CODE_PENDING = 'vendor_claim_code';

    /** Photos uploaded on this link and not yet sent with a request: opaque reference => private path. */
    private const PHOTOS = 'vendor_claim_photos';

    private const PENDING_MINUTES = 60;

    private const MAX_PHOTOS = 3;

    /** The words the Vue app says, as `vm_claim_{key}` (placeholders such as :name are filled there). */
    private const STRINGS = [
        'see_map',
        'step_of', 'picked', 'few_words', 'few_words_hint', 'photo_optional', 'keep_title', 'keep_lead', 'keep_lead_plain', 'code_hint',
        'sent_lead', 'status_waiting', 'status_asked', 'status_confirms', 'status_on_map', 'link_back', 'copy', 'preview_page',
        'tap_your_spot', 'choose_another',
        'is_one_you', 'search', 'map', 'list', 'free', 'someone_asked', 'taken_short', 'search_empty',
        'spot_has_page', 'sign_in', 'back', 'is_this_you', 'not_me', 'yes_me', 'about_title', 'which_page',
        'new_page', 'categories_hint', 'sells_placeholder', 'photo_label', 'photo_change', 'photo_add',
        'photo_remove', 'uploading', 'photo_hint', 'phone_label', 'phone_hint', 'choose_dates', 'send',
        'continue', 'account_title', 'account_lead', 'in_app_title', 'in_app_body', 'open_chrome',
        'copy_link', 'copied', 'google', 'or', 'email_label', 'send_code', 'code_sent', 'code_label',
        'other_email', 'resend', 'terms_link', 'privacy_link', 'terms_line', 'stopped', 'sent_title',
        'waiting_title', 'sent_body', 'share', 'your_page', 'save_link', 'choose_another', 'name_required',
        'pick_category', 'pick_date', 'turnstile_wait', 'photo_heic', 'photo_failed', 'no_signal', 'failed',
        'expired', 'coming_after_all', 'not_coming_on', 'coming_dates', 'not_coming', 'change_details',
        'cancel', 'save', 'stop', 'stop_confirm', 'stop_yes', 'absent_done', 'saved',
    ];

    public function show(Request $request, $subdomain, $token)
    {
        // Signing in happens here, and a custom domain's session is that domain's alone: the
        // link goes on at the schedule's own address, as the guest submit form does.
        if ($request->attributes->get('custom_domain_host')) {
            return redirect(route('vendor_map.claim', ['subdomain' => $subdomain, 'token' => $token]));
        }

        $role = Role::findForGuestOrFail($subdomain);
        $this->useScheduleLanguage($role);
        $map = $this->mapFor($role, $token);

        $user = $request->user();

        // The vendor's home outlives the organizer closing the link to NEW vendors, as long as
        // the map is on and its event is still running.
        $home = $map && $user && $this->homeOpen($map) ? $this->homeFor($map, $user) : null;

        if (! $home && (! $map || ! $map->takesClaims())) {
            return response()->view('vendor-map.claim-closed', ['role' => $role, 'map' => $map], 200);
        }

        $props = $this->props($request, $role, $map, $token, $home);

        return response()->view('vendor-map.claim', [
            'role' => $role,
            'map' => $map,
            'props' => $props,
            'ogTitle' => VendorMapKinds::say($map->kind, 'share_title', ['name' => $role->translatedName()]),
            'ogImage' => $map->share_image_path ? ImageUtils::storedUrl($map->share_image_path) : null,
        ]);
    }

    /**
     * Ask for a spot. `action` is one of:
     *  - `claim`: signed in, ask now (with `page`: one of their pages, or `new`);
     *  - `send_code`: signed out, hold the request and mail a code to `email`;
     *  - `verify_code`: signed out, check `code`, sign in, ask;
     *  - `google`: signed out, hold the request and go to Google.
     * The honeypot and Turnstile are on the account step only.
     */
    public function submit(Request $request, $subdomain, $token): JsonResponse
    {
        // Never signs in on a custom domain (show() sends the link to the schedule's address).
        abort_if((bool) $request->attributes->get('custom_domain_host'), 404);

        $role = Role::findForGuestOrFail($subdomain);
        $this->useScheduleLanguage($role);
        $map = $this->mapFor($role, $token);

        if (! $map || ! $map->takesClaims()) {
            return $this->closedJson();
        }

        // The demo's visitors share one account; nothing is asked for from it.
        if (is_demo_mode()) {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        $action = (string) $request->input('action');
        $user = $request->user();

        if ($action === 'verify_code') {
            return $this->verifyCode($request, $role, $map, $token);
        }

        if (! in_array($action, ['claim', 'send_code', 'google'], true)) {
            return response()->json(['message' => __('messages.invalid_request')], 422);
        }

        if ($action !== 'claim' && HoneypotUtils::isTripped($request)) {
            return response()->json(['message' => __('messages.invalid_request')], 422);
        }

        $pending = $this->pendingFrom($request, $role, $map, $token, $user);

        if ($action === 'claim') {
            if (! $user) {
                return response()->json(['message' => __('messages.invalid_request')], 422);
            }

            $this->finishClaim($user, $pending);

            return response()->json(['waiting' => $this->waitingFor($map, $user, true)]);
        }

        if ($user) {
            // Signed in since the page was drawn (another tab): ask now.
            $this->finishClaim($user, $pending);

            return response()->json(['waiting' => $this->waitingFor($map, $user, true)]);
        }

        if ($action === 'google') {
            if (! $this->googleAvailable($request)) {
                return response()->json(['message' => __('messages.invalid_request')], 422);
            }

            session()->put(self::PENDING, $pending);

            return response()->json(['redirect' => app_url(route('auth.google', [], false))]);
        }

        // send_code
        if (TurnstileUtils::isActiveForRequest() && ! config('app.is_testing')
            && ! TurnstileUtils::verify($request->input('cf-turnstile-response'), $request->ip())) {
            throw ValidationException::withMessages(['cf-turnstile-response' => [__('messages.turnstile_verification_failed')]]);
        }

        $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        $email = strtolower(trim((string) $request->input('email')));

        app(GuestAccountService::class)->sendSignInCode($request, $email);

        session()->put(self::CODE_PENDING, $pending + ['email' => $email]);

        return response()->json(['sent' => true, 'email' => $email]);
    }

    /** A photo of the stall or what it sells, uploaded on pick and kept PRIVATE until approved. */
    public function photo(Request $request, $subdomain, $token): JsonResponse
    {
        $role = Role::findForGuestOrFail($subdomain);
        $this->useScheduleLanguage($role);
        $map = $this->mapFor($role, $token);

        if (! $map || (! $map->takesClaims() && ! ($request->user() && $this->homeFor($map, $request->user())))) {
            return $this->closedJson();
        }

        $file = $request->file('photo');

        if (! $file || ! $file->isValid()) {
            throw ValidationException::withMessages(['photo' => [__('messages.vm_claim_photo_failed')]]);
        }

        $mime = strtolower((string) $file->getMimeType());
        $name = strtolower((string) $file->getClientOriginalName());

        if (str_contains($mime, 'heic') || str_contains($mime, 'heif') || str_ends_with($name, '.heic') || str_ends_with($name, '.heif')) {
            throw ValidationException::withMessages(['photo' => [__('messages.vm_claim_photo_heic')]]);
        }

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true) || $file->getSize() > 10 * 1024 * 1024) {
            throw ValidationException::withMessages(['photo' => [__('messages.vm_claim_photo_failed')]]);
        }

        $jpeg = $this->shrink((string) file_get_contents($file->getRealPath()));
        if ($jpeg === null) {
            throw ValidationException::withMessages(['photo' => [__('messages.vm_claim_photo_failed')]]);
        }

        $path = VendorMapImages::storePrivate($jpeg, 'jpg');

        $photos = (array) session(self::PHOTOS, []);
        $ref = Str::random(32);
        $photos[$ref] = $path;

        // A phone that picks photo after photo keeps the last few; the rest are deleted now.
        while (count($photos) > self::MAX_PHOTOS) {
            VendorMapImages::deletePrivate(array_shift($photos));
        }

        session()->put(self::PHOTOS, $photos);

        return response()->json(['ref' => $ref]);
    }

    /** The vendor's home: Not coming on a date, I'm coming after all, Change what you sell, Stop coming. */
    public function home(Request $request, $subdomain, $token): JsonResponse
    {
        $role = Role::findForGuestOrFail($subdomain);
        $this->useScheduleLanguage($role);
        $map = $this->mapFor($role, $token);
        $user = $request->user();

        if (! $map || ! $user || ! $this->homeOpen($map)) {
            return $this->closedJson();
        }

        if (is_demo_mode()) {
            return response()->json(['message' => __('messages.not_authorized')], 403);
        }

        $season = $this->seasonFor($map, $user);
        if (! $season) {
            return $this->closedJson();
        }

        $action = (string) $request->input('action');

        switch ($action) {
            case 'absent':
                $date = $this->upcomingDate($map, $request->input('date'));
                VendorMapClaims::markAbsent($map, $season, [$date], $user);
                $this->stampVendorMark($map, $season, [$date]);
                break;

            case 'present':
                $date = $this->upcomingDate($map, $request->input('date'));
                VendorMapClaims::clearAbsent($map, $season, $date);
                break;

            case 'update':
                $data = $request->validate([
                    'sells' => ['nullable', 'string', 'max:200'],
                    'categories' => ['required', 'array', 'min:1', 'max:2'],
                    'categories.*' => ['string'],
                ]);
                $categories = array_values(array_unique(array_filter($data['categories'], fn ($c) => VendorMapKinds::isCategory($map->kind, $c))));
                if (! $categories) {
                    throw ValidationException::withMessages(['categories' => [__('messages.vm_claim_pick_category')]]);
                }
                $season->forceFill([
                    'sells' => mb_substr(trim((string) ($data['sells'] ?? '')), 0, 200) ?: null,
                    'categories' => $categories,
                ])->save();
                break;

            case 'stop':
                VendorMapClaims::stop($map, $season);

                return response()->json(['stopped' => true]);

            default:
                return response()->json(['message' => __('messages.invalid_request')], 422);
        }

        return response()->json(['home' => $this->homeFor($map, $user)]);
    }

    /**
     * The "Not coming" link from the approval mail. No account: the link is the proof, an
     * occupant id and a hash of what that row says (VendorMapOccupant::stateHash()), so a link
     * about a stall the vendor no longer holds stops working rather than acting on a stranger.
     * This GET only shows; away() is the POST that marks.
     */
    public function awayForm(Request $request, $subdomain, $occupant, $hash)
    {
        $role = Role::findForGuestOrFail($subdomain);
        $this->useScheduleLanguage($role);
        [$map, $row] = $this->awayTarget($role, $occupant, $hash);

        if (! $row) {
            return response()->view('vendor-map.away-stale', ['role' => $role, 'map' => $map], 200);
        }

        return response()->view('vendor-map.away', $this->awayData($role, $map, $row, $occupant, $hash));
    }

    public function away(Request $request, $subdomain, $occupant, $hash)
    {
        $role = Role::findForGuestOrFail($subdomain);
        $this->useScheduleLanguage($role);
        [$map, $row] = $this->awayTarget($role, $occupant, $hash);

        if (! $row) {
            return response()->view('vendor-map.away-stale', ['role' => $role, 'map' => $map], 200);
        }

        $back = redirect()->route('vendor_map.away', ['subdomain' => $role->subdomain, 'occupant' => $occupant, 'hash' => $hash]);

        if (HoneypotUtils::isTripped($request)) {
            return $back->with('error', __('messages.invalid_request'));
        }

        $offered = array_column($this->nextDates($map, 4), 'date');
        $by = $row->vendor?->user_id ? User::find($row->vendor->user_id) : null;

        if (is_string($come = $request->input('come')) && in_array($come, $offered, true)) {
            VendorMapClaims::clearAbsent($map, $row, $come);

            return $back->with('vm_status', 'present');
        }

        $dates = array_values(array_intersect($offered, array_filter((array) $request->input('dates', []), 'is_string')));

        if (! $dates) {
            return $back->with('error', __('messages.vm_claim_pick_date'));
        }

        VendorMapClaims::markAbsent($map, $row, $dates, $by);
        $this->stampVendorMark($map, $row, $dates);

        return $back->with('vm_status', 'absent');
    }

    /**
     * Finish a request held while Google answered. Called by HomeController::home(), which every
     * social sign-in lands on (post_signup_redirect_url() keeps a new account out of the
     * new-schedule chooser while the marker is set). Sends the browser back to the link, which
     * shows "Sent to ..." or what went wrong.
     */
    public function completePending(Request $request)
    {
        $pending = session()->pull(self::PENDING);
        $user = $request->user();

        if (! is_array($pending) || ! isset($pending['subdomain'], $pending['token'])) {
            return redirect()->route('home');
        }

        $back = route('vendor_map.claim', ['subdomain' => $pending['subdomain'], 'token' => $pending['token']]);

        if (! $user || ($pending['at'] ?? 0) < now()->subMinutes(self::PENDING_MINUTES)->timestamp) {
            return redirect($back);
        }

        $role = Role::subdomain($pending['subdomain'])->first();
        if ($role) {
            $this->useScheduleLanguage($role);
        }

        try {
            $this->finishClaim($user, $pending);
            session()->flash('vendor_claim_sent', true);
        } catch (ValidationException $e) {
            session()->flash('vendor_claim_error', collect($e->errors())->flatten()->first());
        }

        return redirect($back);
    }

    /* ---------------------------------------------------------------------------------------- */

    private function verifyCode(Request $request, Role $role, VendorMap $map, string $token): JsonResponse
    {
        $pending = session(self::CODE_PENDING);

        if (! is_array($pending) || (int) ($pending['map'] ?? 0) !== (int) $map->id
            || ($pending['at'] ?? 0) < now()->subMinutes(self::PENDING_MINUTES)->timestamp) {
            throw ValidationException::withMessages(['code' => [__('messages.vm_claim_start_again')]]);
        }

        $request->validate(['code' => ['required', 'string']]);
        $code = preg_replace('/\D/', '', (string) $request->input('code'));

        $user = app(GuestAccountService::class)->signInWithCode(
            $request, $pending['email'], $code, 'vendor', (string) ($pending['details']['name'] ?? ''), $role
        );

        session()->forget(self::CODE_PENDING);

        try {
            $this->finishClaim($user, $pending);
        } catch (ValidationException $e) {
            // Signed in now, but somebody asked for the stall in the meantime (one WhatsApp
            // message reaches sixty vendors at once): back to the map, signed in, with the reason.
            return response()->json([
                'signedIn' => true,
                'error' => collect($e->errors())->flatten()->first() ?: __('messages.vm_claim_failed'),
                'back' => 'list',
            ]);
        }

        return response()->json(['waiting' => $this->waitingFor($map, $user, true), 'signedIn' => true]);
    }

    /**
     * Validate what the vendor typed and hold it as the request to make. Nothing is written.
     *
     * @throws ValidationException
     */
    private function pendingFrom(Request $request, Role $role, VendorMap $map, string $token, ?User $user): array
    {
        $kind = $map->kind();

        $data = $request->validate([
            'spot' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'categories' => ['required', 'array', 'min:1', 'max:2'],
            'categories.*' => ['string', 'in:'.implode(',', VendorMapKinds::categoriesFor($kind))],
            'sells' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:32'],
            'photo' => ['nullable', 'string', 'max:64'],
            'every' => ['nullable', 'boolean'],
            'dates' => ['nullable', 'array', 'max:60'],
            'dates.*' => ['string', 'date_format:Y-m-d'],
            'page' => ['nullable', 'string', 'max:64'],
        ], [
            'categories.required' => __('messages.vm_claim_pick_category'),
            'categories.min' => __('messages.vm_claim_pick_category'),
        ]);

        $spot = $this->spotOf($map, (int) $data['spot']);
        $this->assertSpotOpen($map, $spot, $user);

        $dates = null;
        if (! $request->boolean('every', true)) {
            $dates = VendorMapClaims::validDates($map, $data['dates'] ?? []);
            if (! $dates) {
                throw ValidationException::withMessages(['dates' => [__('messages.vm_claim_pick_date')]]);
            }
        }

        $photoRef = $data['photo'] ?? null;
        $photoPath = $photoRef ? (((array) session(self::PHOTOS, []))[$photoRef] ?? null) : null;

        return [
            'map' => $map->id,
            'subdomain' => $role->subdomain,
            'token' => $token,
            'spot' => $spot->id,
            'page' => $data['page'] ?? null,
            'at' => now()->timestamp,
            'details' => [
                'name' => trim($data['name']),
                'categories' => array_values(array_unique($data['categories'])),
                'sells' => isset($data['sells']) ? trim($data['sells']) : null,
                'phone' => $data['phone'] ?? null,
                'dates' => $dates,
                'photo_path' => VendorMapImages::isPrivatePath($photoPath) ? $photoPath : null,
            ],
        ];
    }

    /**
     * Make the request for a signed-in person: their page (one they own or run, or a new one in
     * the market's language), then a waiting row on the spot.
     *
     * @throws ValidationException
     */
    private function finishClaim(User $user, array $pending): VendorMapOccupant
    {
        $map = VendorMap::with('role')->find($pending['map'] ?? 0);

        if (! $map || ! $map->role || ! hash_equals((string) $map->claim_token, (string) ($pending['token'] ?? '')) || ! $map->takesClaims()) {
            throw ValidationException::withMessages(['spot' => [__('messages.vm_claim_link_closed')]]);
        }

        $role = $map->role;
        $spot = $this->spotOf($map, (int) ($pending['spot'] ?? 0));
        $this->assertSpotOpen($map, $spot, $user);

        $details = (array) ($pending['details'] ?? []);
        // A photo picked long ago may have been cleaned up meanwhile (unsent files go after a
        // day): the request goes without it rather than pointing at nothing.
        if (! empty($details['photo_path']) && VendorMapImages::size($details['photo_path']) === 0) {
            $details['photo_path'] = null;
        }

        if ($this->seasonFor($map, $user)) {
            // Already at this market for the season: the link is their home, not a second stall.
            throw ValidationException::withMessages(['spot' => [__('messages.vm_claim_already_here')]]);
        }

        $vendor = $this->vendorPageFor($user, $role, $pending['page'] ?? null, (string) ($details['name'] ?? ''));

        try {
            $row = VendorMapClaims::request($map, $spot, $vendor, $user, $details);
        } catch (QueryException $e) {
            report($e);
            throw ValidationException::withMessages(['spot' => [__('messages.vm_claim_failed')]]);
        }

        // The photo now belongs to the request: no longer an upload to tidy away.
        $photos = (array) session(self::PHOTOS, []);
        session()->put(self::PHOTOS, array_filter($photos, fn ($p) => $p !== ($details['photo_path'] ?? null)));

        return $row;
    }

    /**
     * The page the request is made for. One the person owns or runs, never one they only follow
     * (`talents()` is owners and admins), never a deleted or unowned one; otherwise one of theirs
     * with the same name, otherwise a new page in the MARKET's language and timezone.
     *
     * @throws ValidationException
     */
    private function vendorPageFor(User $user, Role $market, ?string $page, string $name): Role
    {
        if ($page !== null && $page !== '' && $page !== 'new') {
            $id = UrlUtils::decodeId($page);
            $vendor = $id ? $user->talents()->where('roles.id', $id)->first() : null;

            if (! $vendor || $vendor->is_deleted || ! $vendor->hasRealOwner()) {
                throw ValidationException::withMessages(['page' => [__('messages.vm_claim_page_unavailable')]]);
            }

            return $vendor;
        }

        $name = trim($name);

        if ($page !== 'new') {
            $norm = fn ($s) => mb_strtolower(preg_replace('/[^\p{L}\p{N}]+/u', '', (string) $s));
            $same = $user->talents()->get()->first(fn ($r) => $norm($r->name) !== '' && $norm($r->name) === $norm($name) && $r->hasRealOwner());
            if ($same) {
                return $same;
            }
        }

        if (config('app.hosted') && $user->owner()->count() >= 50) {
            throw ValidationException::withMessages(['page' => [__('messages.schedule_limit')]]);
        }

        return app(GuestAccountService::class)->createTalentSchedule(
            $user,
            $name !== '' ? $name : (string) $user->name,
            $market->timezone,
            $market->language_code
        );
    }

    /** @throws ValidationException */
    private function spotOf(VendorMap $map, int $id): VendorMapSpot
    {
        $spot = $id > 0 ? VendorMapSpot::where('id', $id)->where('vendor_map_id', $map->id)->whereNull('retired_at')->first() : null;

        if (! $spot) {
            throw ValidationException::withMessages(['spot' => [__('messages.vm_claim_spot_gone')]]);
        }

        return $spot;
    }

    /**
     * A spot somebody else asked for, or whose season vendor has a page, is not offered: the first
     * waits for the organizer, the second is that vendor's (they sign in, never ask again).
     *
     * @throws ValidationException
     */
    private function assertSpotOpen(VendorMap $map, VendorMapSpot $spot, ?User $user): void
    {
        $mine = $user ? $this->vendorIdsOf($user) : [];

        $holder = VendorMapOccupant::where('spot_id', $spot->id)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->first(['id', 'vendor_role_id', 'user_id']);

        if ($holder && $holder->vendor_role_id && ! in_array((int) $holder->vendor_role_id, $mine, true)) {
            throw ValidationException::withMessages(['spot' => [__('messages.vm_claim_spot_has_page')]]);
        }

        $waiting = VendorMapOccupant::where('spot_id', $spot->id)
            ->where('status', 'pending')
            ->get(['vendor_role_id', 'user_id'])
            ->reject(fn ($r) => ($user && (int) $r->user_id === (int) $user->id) || in_array((int) $r->vendor_role_id, $mine, true));

        if ($waiting->isNotEmpty()) {
            throw ValidationException::withMessages(['spot' => [__('messages.vm_claim_spot_waiting')]]);
        }
    }

    /** @return int[] the pages this person may ask for a spot as */
    private function vendorIdsOf(User $user): array
    {
        return $user->talents()->pluck('roles.id')->map(fn ($id) => (int) $id)->all();
    }

    /** The approved season row that is one of this person's pages on this map. */
    private function seasonFor(VendorMap $map, User $user): ?VendorMapOccupant
    {
        $ids = $this->vendorIdsOf($user);

        if (! $ids) {
            return null;
        }

        return VendorMapOccupant::where('vendor_map_id', $map->id)
            ->whereIn('vendor_role_id', $ids)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->with(['spot', 'vendor'])
            ->first();
    }

    /**
     * The vendor's home: open while the map is on and its event runs. Not tied to Pro: when a
     * plan lapses, vendors already confirmed keep their stall and "Not coming" (docs/FEATURES.md);
     * only new requests stop (takesClaims()).
     */
    private function homeOpen(VendorMap $map): bool
    {
        return $map->enabled && $map->role && $map->eventTakesVendors();
    }

    /** What the home view draws, or null when this person has no stall here for the season. */
    private function homeFor(VendorMap $map, User $user): ?array
    {
        $season = $this->seasonFor($map, $user);

        if (! $season || ! $season->spot || $season->spot->retired_at) {
            return null;
        }

        $kind = $map->kind();
        $dates = $this->nextDates($map, 4);
        $absent = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('spot_id', $season->spot_id)
            ->where('vendor_role_id', $season->vendor_role_id)
            ->where('kind', 'absent')
            ->whereIn('event_date', array_column($dates, 'date') ?: ['-'])
            ->pluck('event_date')
            ->all();

        return [
            'spotId' => $season->spot_id,
            'spot' => VendorMapKinds::say($kind, 'spot', ['number' => $season->spot->label]),
            'label' => $season->spot->label,
            'name' => $season->displayName(),
            'pageUrl' => $season->vendor && ! $season->vendor->is_deleted ? $season->vendor->getGuestUrl() : null,
            'every' => $this->everyLine($map),
            'sells' => $season->sells,
            'categories' => array_values((array) $season->categories),
            'dates' => array_map(fn ($d) => $d + ['absent' => in_array($d['date'], $absent, true)], $dates),
            'share' => $this->shareFor($map, $season->spot_id, $season->spot->label),
        ];
    }

    /**
     * The request this person has waiting on this map, for the Done screen ("Sent to Dana") and
     * the link opened again later ("Waiting for Dana").
     */
    private function waitingFor(VendorMap $map, User $user, bool $justSent = false): ?array
    {
        $ids = $this->vendorIdsOf($user);

        $row = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('status', 'pending')
            ->where(fn ($q) => $q->where('user_id', $user->id)->when($ids, fn ($q) => $q->orWhereIn('vendor_role_id', $ids)))
            ->with(['spot', 'vendor'])
            ->latest('id')
            ->first();

        if (! $row || ! $row->spot) {
            return null;
        }

        return [
            'justSent' => $justSent,
            'spotId' => $row->spot_id,
            'spot' => VendorMapKinds::say($map->kind(), 'spot', ['number' => $row->spot->label]),
            'pageUrl' => $row->vendor && ! $row->vendor->is_deleted ? $row->vendor->getGuestUrl() : null,
            'pageName' => $row->vendor ? $row->vendor->translatedName() : null,
            'share' => $this->shareFor($map, $row->spot_id, $row->spot->label),
        ];
    }

    private function shareFor(VendorMap $map, int $spotId, string $label): array
    {
        $role = $map->role;
        $day = $this->weekdayOf($map);
        $spot = VendorMapKinds::say($map->kind(), 'spot', ['number' => $label]);
        // The stall NUMBER, which the map page looks up first (and a person can read in the link).
        $url = VendorMapPresenter::publicUrl($role).'?s='.rawurlencode($label);

        return [
            'url' => $url,
            'text' => $day
                ? __('messages.vm_claim_share_text_day', ['day' => $day, 'name' => $role->translatedName(), 'spot' => $spot])
                : __('messages.vm_claim_share_text', ['name' => $role->translatedName(), 'spot' => $spot]),
        ];
    }

    /** "Every Friday at the market", or the kind's own "Every market day" for anything else. */
    private function everyLine(VendorMap $map): string
    {
        $day = $this->weekdayOf($map);

        return $day
            ? __('messages.vm_claim_every_day_at', ['day' => $day, 'name' => $map->role->translatedName()])
            : VendorMapKinds::say($map->kind(), 'every_date');
    }

    /** The weekday name when the market runs on exactly one day of the week, else null. */
    private function weekdayOf(VendorMap $map): ?string
    {
        $event = $map->event;
        $days = (string) ($event?->days_of_week ?? '');

        if (! $event || substr_count($days, '1') !== 1) {
            return null;
        }

        $next = VendorMapOccupancy::currentDate($map);

        return $next ? Carbon::parse($next)->translatedFormat('l') : null;
    }

    /** A one-off event over more than a day (an expo): "Every day of the expo", no weekday. */
    private function isMultiDay(VendorMap $map): bool
    {
        $event = $map->event;

        return $event && ! $event->days_of_week && (float) $event->duration > 24;
    }

    /** @return array<int, array{date: string, label: string}> the next market days from today */
    private function nextDates(VendorMap $map, int $count): array
    {
        $event = $map->event;
        if (! $event) {
            return [];
        }

        $out = [];
        $cursor = Carbon::now($event->scheduleTimezone())->format('Y-m-d');
        for ($i = 0; $i < $count; $i++) {
            $cursor = $event->nextOccurrenceFrom($cursor);
            if (! $cursor) {
                break;
            }
            $out[] = ['date' => $cursor, 'label' => VendorMapPresenter::longDate($cursor)];
            $cursor = Carbon::parse($cursor)->addDay()->format('Y-m-d');
        }

        return $out;
    }

    /** @throws ValidationException a date that is not one of the market's coming days */
    private function upcomingDate(VendorMap $map, $date): string
    {
        $offered = array_column($this->nextDates($map, 12), 'date');

        if (! is_string($date) || ! in_array($date, $offered, true)) {
            throw ValidationException::withMessages(['date' => [__('messages.vm_claim_pick_date')]]);
        }

        return $date;
    }

    /**
     * Mark the absent rows the vendor made as the vendor's, so the organizer's notice can tell
     * "Avi is not coming" from a mark the organizer made themselves.
     */
    private function stampVendorMark(VendorMap $map, VendorMapOccupant $season, array $dates): void
    {
        VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('spot_id', $season->spot_id)
            ->where('kind', 'absent')
            ->whereIn('event_date', $dates)
            ->when($season->vendor_role_id, fn ($q) => $q->where('vendor_role_id', $season->vendor_role_id), fn ($q) => $q->whereNull('vendor_role_id'))
            ->whereNull('note')
            ->update(['note' => 'vendor']);
    }

    /** @return array{0: ?VendorMap, 1: ?VendorMapOccupant} */
    private function awayTarget(Role $role, $occupant, $hash): array
    {
        $map = VendorMap::where('role_id', $role->id)->first();
        $map?->setRelation('role', $role);
        $id = UrlUtils::decodeId($occupant);

        if (! $map || ! $id || ! is_string($hash) || ! $map->enabled || ! $map->eventTakesVendors()) {
            return [$map, null];
        }

        $row = VendorMapOccupant::where('id', $id)->where('vendor_map_id', $map->id)->with(['spot', 'vendor'])->first();

        if (! $row || ! $row->isSeason() || $row->status !== 'approved' || $row->kind !== 'present'
            || ! $row->spot || $row->spot->retired_at || ! hash_equals($row->stateHash(), $hash)) {
            return [$map, null];
        }

        return [$map, $row];
    }

    private function awayData(Role $role, VendorMap $map, VendorMapOccupant $row, $occupant, $hash): array
    {
        $dates = $this->nextDates($map, 4);
        $absent = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('spot_id', $row->spot_id)
            ->where('kind', 'absent')
            ->whereIn('event_date', array_column($dates, 'date') ?: ['-'])
            ->when($row->vendor_role_id, fn ($q) => $q->where('vendor_role_id', $row->vendor_role_id), fn ($q) => $q->whereNull('vendor_role_id'))
            ->pluck('event_date')
            ->all();

        return [
            'role' => $role,
            'map' => $map,
            'row' => $row,
            'spotLabel' => VendorMapKinds::say($map->kind(), 'spot', ['number' => $row->spot->label]),
            'dates' => array_map(fn ($d) => $d + ['absent' => in_array($d['date'], $absent, true)], $dates),
            'ownerFirstName' => VendorMapPresenter::ownerFirstName($role),
            'action' => route('vendor_map.away.submit', ['subdomain' => $role->subdomain, 'occupant' => $occupant, 'hash' => $hash]),
        ];
    }

    /** Everything the Vue app is given. Never a phone, a user, or the name on a waiting request. */
    private function props(Request $request, Role $role, VendorMap $map, string $token, ?array $home): array
    {
        $user = $request->user();
        $kind = $map->kind();
        $sub = $role->subdomain;
        $mine = $user ? $this->vendorIdsOf($user) : [];

        $date = VendorMapOccupancy::currentDate($map);
        $payload = VendorMapPresenter::publicPayload($role, $map, $date);

        $season = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('event_date', VendorMapOccupant::SEASON)
            ->where('kind', 'present')
            ->where('status', 'approved')
            ->with('vendor')
            ->get()
            ->keyBy('spot_id');

        $waitingSpots = VendorMapOccupant::where('vendor_map_id', $map->id)
            ->where('status', 'pending')
            ->get(['spot_id', 'user_id', 'vendor_role_id'])
            ->reject(fn ($r) => ($user && (int) $r->user_id === (int) $user->id) || in_array((int) $r->vendor_role_id, $mine, true))
            ->pluck('spot_id')
            ->unique()
            ->all();

        $areas = $map->areas->keyBy('id');

        $spots = $map->spots()->get(['id', 'label', 'area_id'])->map(function ($spot) use ($season, $waitingSpots, $areas, $mine) {
            $row = $season->get($spot->id);
            $hidden = $row && $row->vendor_role_id && (! $row->vendor || $row->vendor->is_deleted);

            return [
                'id' => $spot->id,
                'label' => $spot->label,
                'area' => $spot->area_id ? ($areas[$spot->area_id]->name ?? null) : null,
                'name' => $row && ! $hidden ? $row->displayName() : null,
                // A vendor with a page holds it: they sign in, nobody asks for it again.
                'page' => (bool) ($row && $row->vendor_role_id),
                'mine' => (bool) ($row && $row->vendor_role_id && in_array((int) $row->vendor_role_id, $mine, true)),
                'sells' => $row && ! $row->vendor_role_id ? $row->sells : null,
                'categories' => $row && ! $row->vendor_role_id ? array_values((array) $row->categories) : [],
                'waiting' => in_array($spot->id, $waitingSpots, true),
            ];
        })->values()->all();

        $pages = $user
            ? $user->talents()->get()->filter(fn ($r) => $r->hasRealOwner())->map(fn ($r) => [
                'id' => UrlUtils::encodeId($r->id),
                'name' => $r->translatedName(),
            ])->values()->all()
            : [];

        return [
            'map' => $payload,
            // The market as blocks of equal stalls: what a vendor taps to say where they stand.
            'schematic' => VendorMapSchematic::build($map, $payload),
            'kind' => $kind,
            'strings' => VendorMapKinds::stringsFor($kind),
            'categories' => VendorMapKinds::categoryLabels($kind),
            'spots' => $spots,
            'dates' => $this->nextDates($map, 12),
            'multiDay' => $this->isMultiDay($map),
            'oneDay' => ! $map->event?->days_of_week && ! $this->isMultiDay($map),
            'marketName' => $role->translatedName(),
            'ownerFirstName' => VendorMapPresenter::ownerFirstName($role),
            'rtl' => is_rtl(),
            'signedIn' => (bool) $user,
            'userName' => $user?->name,
            'pages' => $pages,
            'home' => $home,
            'waiting' => $user && ! $home ? $this->waitingFor($map, $user, (bool) session('vendor_claim_sent')) : null,
            'error' => session('vendor_claim_error'),
            'google' => $this->googleAvailable($request),
            'turnstileSiteKey' => TurnstileUtils::isActiveForRequest() ? TurnstileUtils::getSiteKey() : null,
            'honeypotField' => HoneypotUtils::FIELD,
            't' => collect(self::STRINGS)->mapWithKeys(fn ($k) => [$k => __('messages.vm_claim_'.$k)])->all(),
            'storageKey' => 'vm-claim:'.substr(hash('sha256', $sub.'|'.$map->id), 0, 16),
            'terms' => config('app.hosted') ? [
                'terms' => policy_url('terms'),
                'privacy' => policy_url('privacy'),
            ] : null,
            'loginUrl' => app_url(route('login', [], false)),
            'publicUrl' => VendorMapPresenter::publicUrl($role),
            // The address a vendor comes back to: this same page, signed in.
            'linkBack' => VendorMapPresenter::claimUrl($role, $map),
            'codeMinutes' => GuestAccountService::CODE_MINUTES,
            // A big market on a phone: its areas first (AreaMap.vue).
            'areaMap' => [
                'allAreas' => __('messages.vm_public_all_areas'),
                'tapArea' => __('messages.vm_claim_tap_area'),
                'areaFree' => __('messages.vm_claim_area_free'),
                'zoomIn' => __('messages.venue_map_zoom_in'),
                'zoomOut' => __('messages.venue_map_zoom_out'),
                'map' => __('messages.vm_claim_map'),
                'results' => __('messages.vm_public_results'),
            ],
            'urls' => [
                'submit' => route('vendor_map.claim.submit', ['subdomain' => $sub, 'token' => $token], false),
                'photo' => route('vendor_map.claim.photo', ['subdomain' => $sub, 'token' => $token], false),
                'home' => route('vendor_map.claim.home', ['subdomain' => $sub, 'token' => $token], false),
            ],
        ];
    }

    private function googleAvailable(Request $request): bool
    {
        // As the submit page's button: Google is configured, a new account could be made, and
        // the session is the app's (a custom domain's session is scoped to that origin, so the
        // callback on app. could not see the held request).
        return (bool) config('services.google.client_id')
            && public_registration_enabled()
            && ! $request->attributes->get('custom_domain_host');
    }

    /** The map on this schedule whose vendor link is $token. Constant-time compare. */
    private function mapFor(Role $role, $token): ?VendorMap
    {
        $map = VendorMap::where('role_id', $role->id)->first();

        if (! $map || ! $map->claim_token || ! is_string($token) || ! hash_equals((string) $map->claim_token, $token)) {
            return null;
        }

        $map->setRelation('role', $role);

        return $map;
    }

    private function useScheduleLanguage(Role $role): void
    {
        if (is_valid_language_code($role->language_code)) {
            app()->setLocale($role->language_code);
        }
    }

    private function closedJson(): JsonResponse
    {
        return response()->json(['message' => __('messages.vm_claim_link_closed'), 'closed' => true], 410);
    }

    /** Re-encode an upload as a JPEG of at most 1600px: no camera metadata (a location) survives. */
    private function shrink(string $bytes): ?string
    {
        if ($bytes === '' || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        // Read the size first: a small file can declare a huge picture, and decoding it would run
        // out of memory (the browser sends ~1600px; the app's own 12-megapixel limit holds a camera's).
        $size = @getimagesizefromstring($bytes);
        if (! $size || $size[0] * $size[1] > ImageUtils::VARIANT_MAX_PIXELS) {
            return null;
        }
        $image = @imagecreatefromstring($bytes);
        if (! $image) {
            return null;
        }

        $w = imagesx($image);
        $h = imagesy($image);
        $scale = min(1, 1600 / max($w, $h, 1));
        $tw = max(1, (int) round($w * $scale));
        $th = max(1, (int) round($h * $scale));

        $out = imagecreatetruecolor($tw, $th);
        $white = imagecolorallocate($out, 255, 255, 255);
        imagefill($out, 0, 0, $white);
        imagecopyresampled($out, $image, 0, 0, 0, 0, $tw, $th, $w, $h);

        ob_start();
        imagejpeg($out, null, 85);
        $jpeg = (string) ob_get_clean();
        imagedestroy($image);
        imagedestroy($out);

        return $jpeg !== '' ? $jpeg : null;
    }
}
