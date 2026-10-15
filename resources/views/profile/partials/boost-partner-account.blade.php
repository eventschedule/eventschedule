{{-- A boost partner's Facebook Page and Meta ad account: the ones its schedules' boosts run under.
     Rendered only for a partner account (profile/edit, the Boosts tab). Two ways to connect:
     sharing the two with our Business (numbered steps, then the two IDs, then a check of each),
     and, once Meta has approved it for this app, Facebook Login for Business. A share is
     confirmed once by a person here, because Meta does not say which account shared an asset.
     Below it, how the partner's Businesses pay it, which a Business reads when its boost is
     approved and waiting for payment.

     Expects $bpSettingsPartner (BoostPartner). --}}
@php
    $bpSet = $bpSettingsPartner;
    $bpSetState = $bpSet->connectionState();
    $bpSetTone = ['connected' => 'is-on', 'waiting' => 'is-warn', 'needs_attention' => 'is-bad'][$bpSetState] ?? '';
    $bpSetChecks = session('boost_partner_checks');
    $bpSetAssets = session('boost_partner_assets');
    $bpSetBusinessId = (string) config('services.meta.business_id');
    $bpSetLogin = (bool) config('services.meta.login_config_id');
    $bpSetField = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm';
    // What each check found, and what fixes it when it failed.
    $bpSetCheckWords = [
        'ad_account_shared' => ['messages.boost_partner_check_ad_account_shared_ok', 'messages.boost_partner_check_ad_account_shared_fail'],
        'page_shared' => ['messages.boost_partner_check_page_shared_ok', 'messages.boost_partner_check_page_shared_fail'],
        'ad_account_active' => ['messages.boost_partner_check_ad_account_active_ok', 'messages.boost_partner_check_ad_account_active_fail'],
        'currency_matches' => ['messages.boost_partner_check_currency_ok', 'messages.boost_partner_check_currency_fail'],
        'reachable' => ['messages.boost_partner_check_reachable_ok', 'messages.boost_partner_check_reachable_fail'],
    ];
@endphp
<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.boost_partner_account_lead') }}</p>

    <div class="form-kit-fields space-y-6">
        {{-- Where things stand: the state, and what is connected. --}}
        <div>
            <div class="event-picked">
                <div class="settings-picked-main">
                    <span class="event-status {{ $bpSetTone }}">{{ __('messages.boost_partner_state_'.$bpSetState) }}</span>
                    @if (filled($bpSet->page_name))
                    <span class="settings-picked-name"><bdi>{{ $bpSet->page_name }}</bdi></span>
                    @endif
                </div>
                @if ($bpSet->hasAssets())
                <div class="settings-picked-actions">
                    <form method="POST" action="{{ route('boost.partner.settings.check') }}">
                        @csrf
                        <button type="submit" class="event-link">{{ __('messages.boost_partner_check_again') }}</button>
                    </form>
                    <form method="POST" action="{{ route('boost.partner.settings.disconnect') }}" data-confirm="{{ __('messages.boost_partner_disconnect_confirm') }}">
                        @csrf
                        <button type="submit" class="event-link is-danger">{{ __('messages.disconnect') }}</button>
                    </form>
                </div>
                @endif
            </div>

            @if ($bpSet->hasAssets())
            <dl class="page-kv mt-4">
                <div>
                    <dt>{{ __('messages.boost_partner_page') }}</dt>
                    <dd><bdi>{{ $bpSet->page_name ?: $bpSet->page_id }}</bdi> <small dir="ltr">{{ $bpSet->page_id }}</small></dd>
                </div>
                <div>
                    <dt>{{ __('messages.boost_partner_ad_account') }}</dt>
                    <dd><bdi>{{ $bpSet->ad_account_name ?: $bpSet->ad_account_id }}</bdi> <small dir="ltr">act_{{ $bpSet->ad_account_id }}</small></dd>
                </div>
                @if ($bpSet->ad_account_currency || $bpSet->ad_account_timezone)
                <div>
                    <dt>{{ __('messages.boost_partner_currency_timezone') }}</dt>
                    <dd dir="ltr">{{ collect([$bpSet->ad_account_currency, $bpSet->ad_account_timezone])->filter()->implode(' · ') }}</dd>
                </div>
                @endif
                @if ($bpSet->connection_checked_at)
                <div>
                    <dt>{{ __('messages.boost_partner_last_checked') }}</dt>
                    <dd>{{ $bpSet->connection_checked_at->diffForHumans() }}</dd>
                </div>
                @endif
            </dl>
            @endif

            @if ($bpSetState === 'waiting')
            <p class="event-hint mt-3">{{ __('messages.boost_partner_waiting_text') }}</p>
            @endif
            @if (filled($bpSet->connection_error))
            @include('profile.partials.notice', ['noticeText' => $bpSet->connection_error, 'noticeClass' => 'mt-3 mb-0'])
            @endif
        </div>

        {{-- What the last check found, one line each. --}}
        @if (is_array($bpSetChecks) && $bpSetChecks !== [])
        <div>
            <p class="event-group-label">{{ __('messages.boost_partner_checks_title') }}</p>
            <ul class="space-y-2" role="list">
                @foreach ($bpSetCheckWords as $bpSetKey => [$bpSetOk, $bpSetFail])
                @if (array_key_exists($bpSetKey, $bpSetChecks))
                <li class="flex items-start gap-2 text-sm">
                    <span class="event-status {{ $bpSetChecks[$bpSetKey] ? 'is-on' : 'is-bad' }}"><span class="sr-only">{{ $bpSetChecks[$bpSetKey] ? __('messages.boost_partner_check_ok') : __('messages.boost_partner_check_failed_word') }}</span></span>
                    <span class="min-w-0 text-gray-700 dark:text-gray-300">{{ __($bpSetChecks[$bpSetKey] ? $bpSetOk : $bpSetFail) }}</span>
                </li>
                @endif
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Facebook Login for Business: offered once Meta has approved it for this app. --}}
        @if ($bpSetLogin)
        <div>
            <p class="event-group-label">{{ __('messages.boost_partner_login_title') }}</p>
            <p class="event-hint">{{ __('messages.boost_partner_login_text') }}</p>
            <a href="{{ route('boost.partner.settings.login') }}"
                class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.boost_partner_login_button') }}
            </a>

            @if (is_array($bpSetAssets) && (! empty($bpSetAssets['pages']) || ! empty($bpSetAssets['ad_accounts'])))
            <form method="POST" action="{{ route('boost.partner.settings.select') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-input-label for="bp-select-page" :value="__('messages.boost_partner_page')" />
                    <select id="bp-select-page" name="page_id" required class="{{ $bpSetField }}">
                        @foreach ($bpSetAssets['pages'] ?? [] as $bpSetPage)
                        <option value="{{ $bpSetPage['id'] }}">{{ $bpSetPage['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="bp-select-account" :value="__('messages.boost_partner_ad_account')" />
                    <select id="bp-select-account" name="ad_account_id" required class="{{ $bpSetField }}">
                        @foreach ($bpSetAssets['ad_accounts'] ?? [] as $bpSetAccount)
                        <option value="{{ $bpSetAccount['id'] }}">{{ $bpSetAccount['name'] }}{{ ! empty($bpSetAccount['currency']) ? ' ('.$bpSetAccount['currency'].')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                @include('profile.partials.save', ['saveLabel' => __('messages.boost_partner_use_these'), 'saveClass' => ''])
            </form>
            @endif
        </div>
        @endif

        {{-- Sharing the Page and the ad account with our Business. --}}
        <div>
            <p class="event-group-label">{{ $bpSetLogin ? __('messages.boost_partner_share_title_or') : __('messages.boost_partner_share_title') }}</p>
            {{-- Assign partners exists only for assets in a Meta Business portfolio: a personal ad
                 account has no such button, and the steps below would lead nowhere. --}}
            <p class="event-hint mb-2">{{ __('messages.boost_partner_share_prereq') }}</p>
            <ol class="list-decimal ps-5 space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li>
                    {{ __('messages.boost_partner_share_step_copy') }}
                    @if ($bpSetBusinessId !== '')
                    <span class="inline-flex flex-wrap items-center gap-2 align-middle">
                        <code id="bp-business-id" class="settings-mono rounded bg-gray-100 dark:bg-gray-800 px-2 py-0.5" dir="ltr">{{ $bpSetBusinessId }}</code>
                        <button type="button" class="event-link" data-bp-copy="bp-business-id">{{ __('messages.copy') }}</button>
                        <span class="text-xs text-green-700 dark:text-green-400" role="status" aria-live="polite" data-bp-copied></span>
                    </span>
                    @else
                    <span class="text-gray-500 dark:text-gray-400">{{ __('messages.boost_partner_business_id_missing') }}</span>
                    @endif
                </li>
                <li>
                    {{ __('messages.boost_partner_share_step_account') }}
                    <x-link href="https://business.facebook.com/settings/ad-accounts" target="_blank">{{ __('messages.boost_partner_open_ad_accounts') }}</x-link>
                </li>
                <li>
                    {{ __('messages.boost_partner_share_step_page') }}
                    <x-link href="https://business.facebook.com/settings/pages" target="_blank">{{ __('messages.boost_partner_open_pages') }}</x-link>
                </li>
                <li>{{ __('messages.boost_partner_share_step_ids') }}</li>
            </ol>

            <form method="POST" action="{{ route('boost.partner.settings.share') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-input-label for="bp-page-id" :value="__('messages.boost_partner_page_id')" />
                    <input type="text" id="bp-page-id" name="page_id" maxlength="255" required dir="ltr" autocomplete="off"
                        value="{{ old('page_id', $bpSet->connection_method === \App\Models\BoostPartner::METHOD_PARTNER_SHARE ? $bpSet->page_id : '') }}" class="{{ $bpSetField }}">
                    <p class="event-hint">{{ __('messages.boost_partner_page_id_help') }}</p>
                    <x-input-error :messages="$errors->get('page_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="bp-ad-account-id" :value="__('messages.boost_partner_ad_account_id')" />
                    <input type="text" id="bp-ad-account-id" name="ad_account_id" maxlength="255" required dir="ltr" autocomplete="off"
                        value="{{ old('ad_account_id', $bpSet->connection_method === \App\Models\BoostPartner::METHOD_PARTNER_SHARE ? $bpSet->ad_account_id : '') }}" class="{{ $bpSetField }}">
                    <p class="event-hint">{{ __('messages.boost_partner_ad_account_id_help') }}</p>
                    <x-input-error :messages="$errors->get('ad_account_id')" class="mt-2" />
                </div>
                @include('profile.partials.save', ['saveLabel' => __('messages.boost_partner_save_and_check'), 'saveClass' => ''])
            </form>
        </div>

        {{-- How the partner's Businesses pay it. --}}
        <form method="POST" action="{{ route('boost.partner.settings.instructions') }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="bp-payment-instructions" :value="__('messages.boost_partner_how_businesses_pay')" />
                <textarea id="bp-payment-instructions" name="payment_instructions" rows="3" maxlength="500" dir="auto" class="{{ $bpSetField }}">{{ old('payment_instructions', $bpSet->payment_instructions) }}</textarea>
                <p class="event-hint">{{ __('messages.boost_partner_how_businesses_pay_help') }}</p>
                <x-input-error :messages="$errors->get('payment_instructions')" class="mt-2" />
            </div>
            @include('profile.partials.save', ['saveClass' => ''])
        </form>
    </div>
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    // Copy our Business ID, and say so where a screen reader hears it.
    document.querySelectorAll('[data-bp-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
            var source = document.getElementById(button.getAttribute('data-bp-copy'));
            var said = button.parentNode.querySelector('[data-bp-copied]');
            if (! source) {
                return;
            }
            var text = source.textContent.trim();
            var done = function () {
                if (said) {
                    said.textContent = @json(__('messages.copied'));
                    setTimeout(function () { said.textContent = ''; }, 2500);
                }
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done, function () {});
            } else {
                var range = document.createRange();
                range.selectNodeContents(source);
                var selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(range);
                try { document.execCommand('copy'); done(); } catch (e) {}
                selection.removeAllRanges();
            }
        });
    });
});
</script>
