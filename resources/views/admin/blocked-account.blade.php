<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'blocked'])

    {{-- One account, as the operator needs it before blocking: who it is, what it owns, where it
         was made from and who else shares that. The decision is the last card, after the
         evidence, and the two optional list entries stand beside the number of other accounts
         each would also refuse.

         The switches and the button are plain form controls, and the page is not a Vue mount: a
         name or an address printed here is text and nothing compiles it. --}}
    @php
        $hash = \App\Utils\UrlUtils::encodeId($user->id);
        $when = fn ($moment) => $moment ? $moment->copy()->setTimezone(auth()->user()->timezone ?: config('app.timezone'))->translatedFormat('M j, Y') : null;
        $field = 'block w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $signIn = array_filter([
            $user->password ? __('messages.password') : null,
            ($user->google_id || $user->google_oauth_id) ? __('messages.google') : null,
            $user->facebook_id ? 'Facebook' : null,
        ]);
        $liveSchedules = $schedules->where('is_deleted', false);
        $paidSchedules = $liveSchedules->filter(fn ($role) => config('app.hosted') && $role->hasActiveSubscription());

        // The two switches' words. x-toggle prints its label and help as HTML, so what comes
        // from the account (its address, its domain) is escaped here, and marked left-to-right.
        $ltr = fn ($text) => '<span dir="ltr" class="font-mono">'.e($text).'</span>';
        $others = fn (string $key, int $count) => $count > 0 ? ' '.e(__($key, ['count' => number_format($count)])).'.' : '';
        $addressLabel = $range ? __('messages.block_also_address', ['address' => $ltr($range)]) : '';
        $addressHelp = e(__('messages.block_also_address_note')).$others('messages.block_others_same_address', $sameAddressCount);
        $domainLabel = $domain ? __('messages.block_also_domain', ['domain' => $ltr($domain)]) : '';
        $domainHelp = e(__('messages.block_also_domain_note')).$others('messages.block_others_same_domain', $sameDomainCount);
    @endphp

    <div class="page-shell">
        <div class="page-head">
            <div class="min-w-0">
                <a href="{{ route('admin.blocked') }}" class="page-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                    <span>@lang('messages.blocked')</span>
                </a>
                <h2 class="page-title"><bdi>{{ $user->name ?: $user->email }}</bdi></h2>
                <p class="page-lead">
                    <span dir="ltr">{{ $user->email }}</span>
                    @if ($user->isBlocked())
                    <span class="event-chip">@lang('messages.blocked')</span>
                    @endif
                    @if ($user->isAdmin())
                    <span class="event-chip">@lang('messages.admin')</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'warning' => 'warn', 'error' => 'error']" />

            {{-- Who it is. --}}
            <x-page-card beside :title="__('messages.account')">
                <dl class="page-kv block-facts">
                    <div>
                        <dt>@lang('messages.funnel_signed_up')</dt>
                        <dd>{{ $when($user->created_at) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.email')</dt>
                        <dd>
                            <span class="event-status {{ $user->email_verified_at ? 'is-on' : 'is-warn' }}">{{ $user->email_verified_at ? __('messages.verified') : __('messages.unverified') }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt>@lang('messages.block_signs_in_with')</dt>
                        <dd>{{ $signIn ? implode(', ', $signIn) : __('messages.none') }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.block_signed_up_from')</dt>
                        <dd>
                            @if ($user->signup_ip)
                            <span class="font-mono" dir="ltr">{{ $user->signup_ip }}</span>
                            @if ($sameAddressCount > 0)
                            <small>{{ __('messages.block_others_same_address', ['count' => number_format($sameAddressCount)]) }}</small>
                            @endif
                            @else
                            {{ __('messages.block_address_unknown') }}
                            @endif
                        </dd>
                    </div>
                    @if ($domain)
                    <div>
                        <dt>@lang('messages.blocklist_type_domain')</dt>
                        <dd>
                            <span class="font-mono" dir="ltr">{{ $domain }}</span>
                            @if ($sameDomainCount > 0)
                            <small>{{ __('messages.block_others_same_domain', ['count' => number_format($sameDomainCount)]) }}</small>
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>
            </x-page-card>

            {{-- What it owns. --}}
            <x-page-card flush :title="__('messages.schedules')" :lead="__('messages.block_schedules_lead')">
                @if ($schedules->isNotEmpty())
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.schedule')</th>
                                <th scope="col" class="c-num">@lang('messages.events')</th>
                                <th scope="col">@lang('messages.status')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $role)
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.schedules.edit', ['role' => $role->encodeId()]) }}" class="c-strong"><bdi>{{ $role->name }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $role->is_deleted && $role->subdomain_before_delete ? $role->subdomain_before_delete : $role->subdomain }}</span>
                                </td>
                                <td class="c-num" data-label="{{ __('messages.events') }}">{{ number_format($role->events_count) }}</td>
                                <td>
                                    @if (! $role->is_deleted)
                                    <span class="event-status is-on">@lang('messages.block_state_live')</span>
                                    @elseif (in_array($role->id, $takenDown, true))
                                    <span class="event-status is-bad">@lang('messages.block_state_taken_down')</span>
                                    @else
                                    <span class="event-status">@lang('messages.deleted')</span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    @if (! $role->is_deleted)
                                    <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank" rel="noopener" class="event-link">@lang('messages.view')</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <x-page-empty compact :title="__('messages.block_no_schedules')" />
                @endif
            </x-page-card>

            {{-- Who else was made from the same address. --}}
            @if ($sameAddress->isNotEmpty())
            <x-page-card flush :title="__('messages.block_same_address_title')" :lead="__('messages.block_same_address_lead')">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.account')</th>
                                <th scope="col">@lang('messages.funnel_signed_up')</th>
                                <th scope="col">@lang('messages.status')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sameAddress as $other)
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($other->id)]) }}" class="c-strong"><bdi>{{ $other->name ?: $other->email }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $other->email }}</span>
                                </td>
                                <td class="c-date" data-label="{{ __('messages.funnel_signed_up') }}">{{ $when($other->created_at) }}</td>
                                <td>
                                    @if ($other->isBlocked())
                                    <span class="event-status is-bad">@lang('messages.blocked')</span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    <a href="{{ route('admin.blocked.account', ['hash' => \App\Utils\UrlUtils::encodeId($other->id)]) }}" class="event-link">@lang('messages.review')</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-page-card>
            @endif

            {{-- The decision, apart from the rest and in red, as taking a schedule down is on its
                 own page. Once blocked the card is the way back, and says what the block did. --}}
            @if ($user->isBlocked())
            <x-page-card beside :title="__('messages.blocked')" class="border border-red-200 dark:border-red-800">
                <dl class="page-kv block-facts">
                    <div>
                        <dt>@lang('messages.date')</dt>
                        <dd>{{ $when($block?->created_at ?? $user->blocked_at) }}@if ($block?->blocker) <small><bdi>{{ $block->blocker->name }}</bdi></small>@endif</dd>
                    </div>
                    @if ($block?->note)
                    <div>
                        <dt>@lang('messages.note')</dt>
                        <dd><bdi>{{ $block->note }}</bdi></dd>
                    </div>
                    @endif
                    @if ($block && $block->entries->isNotEmpty())
                    <div>
                        <dt>@lang('messages.block_entries_added')</dt>
                        <dd>
                            @foreach ($block->entries as $entry)
                            <span class="block-line"><span class="font-mono" dir="ltr">{{ $entry->value }}</span></span>
                            @endforeach
                        </dd>
                    </div>
                    @endif
                </dl>

                <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4 mt-4">
                    <p class="min-w-[240px] flex-1 text-sm text-gray-600 dark:text-gray-400">@lang('messages.unblock_description')</p>
                    <form method="POST" action="{{ route('admin.blocked.unblock', ['hash' => $hash]) }}">
                        @csrf
                        <x-brand-button type="submit" data-confirm="{{ __('messages.unblock_confirm', ['name' => $user->name ?: $user->email]) }}">
                            @lang('messages.unblock_account')
                        </x-brand-button>
                    </form>
                </div>
            </x-page-card>
            @elseif ($refusal)
            <x-page-card beside :title="__('messages.block_account')">
                <x-page-notice tone="info">{{ __($refusal) }}</x-page-notice>
            </x-page-card>
            @else
            <x-page-card beside :title="__('messages.block_account')" class="border border-red-200 dark:border-red-800">
                <form method="POST" action="{{ route('admin.blocked.block', ['hash' => $hash]) }}">
                    @csrf

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">@lang('messages.block_description')</p>

                    @if ($paidSchedules->isNotEmpty())
                    <x-page-notice tone="warn" class="mb-4">@lang('messages.block_cancels_subscription')</x-page-notice>
                    @endif

                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="block-note" :value="__('messages.note')" />
                            <input type="text" name="note" id="block-note" value="{{ old('note') }}" maxlength="255" dir="auto" autocomplete="off"
                                placeholder="{{ __('messages.blocklist_note_placeholder') }}" class="{{ $field }}">
                            <x-input-error :messages="$errors->get('note')" class="mt-2" />
                        </div>

                        {{-- Each switch beside what it costs: the other accounts it would also
                             have refused. Off by default; an address or a domain is shared far
                             more often than it is one person's. --}}
                        <div>
                            @if ($range)
                            <x-toggle name="block_address" id="block-address" :checked="(bool) old('block_address')" :label="$addressLabel" :help="$addressHelp" />
                            @else
                            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.block_address_unknown_help')</p>
                            @endif
                        </div>

                        @if ($domain)
                        <div>
                            <x-toggle name="block_domain" id="block-domain" :checked="(bool) old('block_domain')" :label="$domainLabel" :help="$domainHelp" />
                        </div>
                        @endif
                    </div>

                    <div class="page-form-actions">
                        <x-secondary-link :href="route('admin.blocked')">@lang('messages.cancel')</x-secondary-link>
                        <x-danger-button data-confirm="{{ __('messages.block_confirm', ['name' => $user->name ?: $user->email]) }}">
                            @lang('messages.block_account')
                        </x-danger-button>
                    </div>
                </form>
            </x-page-card>
            @endif
        </div>
    </div>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* The kit keeps a pair's value on one line, which suits a date or a figure. These
               values are an address with a count beside it, a list of entries and a note of up
               to 255 characters: on one line they pushed the label off a phone and the note out
               of its card. So a value here wraps, and what belongs under it stands under it. */
            .block-facts dt {
              flex-shrink: 0;
            }
            .block-facts dd {
              min-width: 0;
              text-align: end;
              white-space: normal;
              overflow-wrap: anywhere;
            }
            .block-facts dd small,
            .block-facts dd .block-line {
              display: block;
              margin-inline-start: 0;
            }
        </style>
    </x-slot>
</x-app-admin-layout>
