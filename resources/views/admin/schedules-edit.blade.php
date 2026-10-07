<x-app-admin-layout>

    @include('admin.partials._navigation', ['active' => 'schedules'])

    @php
        $hosted = (bool) config('app.hosted');
        // The subscription's state in the reader's language: it was the raw English key.
        $statusWords = [
            'active' => __('messages.active'),
            'trial' => __('messages.trial'),
            'grace_period' => __('messages.grace_period'),
            'cancelled' => __('messages.cancelled'),
            'past_due' => __('messages.past_due'),
            'inactive' => __('messages.inactive'),
        ];
        $field = 'block w-full mt-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
    @endphp

    {{-- One schedule, as the platform's operator sees it: who it is, whether its contact is
         verified, its plan, and taking it down. The way back is the list's own name, and the
         schedule's name and address stand where the old page had "Edit Schedule". --}}
    <div class="page-shell">
        <div class="page-head">
            <div class="min-w-0">
                <a href="{{ route('admin.schedules') }}" class="page-back">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                    <span>@lang('messages.schedules')</span>
                </a>
                <h2 class="page-title"><bdi>{{ $role->name }}</bdi></h2>
                <p class="page-lead">
                    <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank" rel="noopener" class="event-link" dir="ltr">{{ $role->subdomain }}</a>
                    @if ($role->is_deleted)
                    <span class="event-chip">@lang('messages.deleted')</span>
                    @endif
                    @if (! $role->user_id)
                    <span class="event-chip">@lang('messages.unclaimed')</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            {{-- The subscription card and the plan form are hosted only. On a plain selfhost every
                 schedule is enterprise whatever plan_type says (Role::actualPlanTier()), so the form
                 saved values nothing read, and updateSchedule() 404s there. --}}
            @if ($hosted)
            <x-page-card beside :title="__('messages.current_subscription_status')">
                <dl class="page-kv">
                    <div>
                        <dt>@lang('messages.status')</dt>
                        <dd>{{ $statusWords[$role->subscriptionStatusLabel()] ?? __('messages.none') }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.stripe_customer')</dt>
                        <dd>@if ($role->stripe_id)<span dir="ltr">{{ $role->stripe_id }}</span>@else{{ __('messages.none') }}@endif</dd>
                    </div>
                    @if ($role->trial_ends_at)
                    <div>
                        <dt>@lang('messages.trial_ends')</dt>
                        <dd>{{ $role->trial_ends_at->translatedFormat('M j, Y') }}</dd>
                    </div>
                    @endif
                    @if ($role->hasActiveSubscription())
                    <div>
                        <dt>@lang('messages.active_subscription')</dt>
                        <dd>@lang('messages.yes')</dd>
                    </div>
                    @endif
                </dl>
            </x-page-card>
            @endif

            {{-- Schedule details --}}
            <x-page-card beside :title="__('messages.schedule_details')">
                <form method="POST" action="{{ route('admin.schedules.update_details', ['role' => $role->encodeId()]) }}">
                    @csrf
                    @method('PUT')

                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="name" :value="__('messages.name')" />
                            <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" dir="auto" class="{{ $field }}">
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="new_subdomain" :value="__('messages.subdomain')" />
                            <input type="text" name="new_subdomain" id="new_subdomain" value="{{ old('new_subdomain', $role->subdomain) }}" dir="ltr" autocomplete="off" class="{{ $field }}">
                            <x-input-error :messages="$errors->get('new_subdomain')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('messages.email')" />
                            <input type="email" name="email" id="email" value="{{ old('email', $role->email) }}" dir="ltr" autocomplete="off" class="{{ $field }}">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />

                            {{-- Role::boot()'s `updating` hook does this, and it is easy to trip over. --}}
                            <x-page-notice tone="warn" class="mt-2">@lang('messages.email_change_resends_verification')</x-page-notice>
                        </div>

                        <div>
                            <x-input-label for="phone" :value="__('messages.phone')" />
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $role->phone) }}" placeholder="+15551234567" dir="ltr" autocomplete="off" class="{{ $field }}">
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <div class="page-form-actions">
                        <x-secondary-link :href="route('admin.schedules')">@lang('messages.cancel')</x-secondary-link>
                        <x-brand-button type="submit">@lang('messages.save_changes')</x-brand-button>
                    </div>
                </form>
            </x-page-card>

            {{-- Whether each contact is verified, and the operator's way to vouch for one. It was
                 a card or an amber panel per contact, four looks in all. --}}
            <x-page-card beside :title="__('messages.verification')">
                <div class="event-setting">
                    <div class="min-w-0">
                        <div class="event-setting-label">@lang('messages.email')</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 break-all" dir="ltr">{{ $role->email ?: '-' }}</div>
                    </div>
                    @if ($role->hasVerifiedEmail())
                    <span class="event-status is-on">@lang('messages.verified') &middot; {{ \Carbon\Carbon::parse($role->email_verified_at)->translatedFormat('M j, Y') }}</span>
                    @else
                    <div class="page-actions">
                        <span class="event-status is-warn">@lang('messages.email_not_verified')</span>
                        <form method="POST" action="{{ route('admin.schedules.verify_email', ['role' => $role->encodeId()]) }}">
                            @csrf
                            <button type="submit" class="page-tool" data-confirm="{{ __('messages.confirm_mark_email_verified') }}">@lang('messages.mark_email_verified')</button>
                        </form>
                    </div>
                    @endif
                </div>

                @if ($role->phone)
                <div class="event-setting border-t border-gray-200 dark:border-gray-700 mt-2 pt-3">
                    <div class="min-w-0">
                        <div class="event-setting-label">@lang('messages.phone')</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400" dir="ltr">{{ $role->phone }}</div>
                    </div>
                    @if ($role->phone_verified_at)
                    <span class="event-status is-on">@lang('messages.verified') &middot; {{ \Carbon\Carbon::parse($role->phone_verified_at)->translatedFormat('M j, Y') }}</span>
                    @else
                    <div class="page-actions">
                        <span class="event-status is-warn">@lang('messages.phone_not_verified')</span>
                        <form method="POST" action="{{ route('admin.schedules.verify_phone', ['role' => $role->encodeId()]) }}">
                            @csrf
                            <button type="submit" class="page-tool" data-confirm="{{ __('messages.confirm_mark_phone_verified') }}">@lang('messages.mark_phone_verified')</button>
                        </form>
                    </div>
                    @endif
                </div>
                @endif
            </x-page-card>

            {{-- The plan --}}
            @if ($hosted)
            <x-page-card beside :title="__('messages.plan')">
                <form method="POST" action="{{ route('admin.schedules.update', ['role' => $role->encodeId()]) }}">
                    @csrf
                    @method('PUT')

                    {{-- Seeded from what was typed when a save is refused: the three fields used
                         to come back as the stored plan, with the message beside a value nobody
                         had entered. --}}
                    @php
                        $planTypeValue = old('plan_type', $role->plan_type ?? 'free');
                        $planTermValue = old('plan_term', $role->plan_term);
                    @endphp
                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="plan_type" :value="__('messages.plan_type')" />
                            <select name="plan_type" id="plan_type" class="{{ $field }}">
                                <option value="free" {{ $planTypeValue === 'free' ? 'selected' : '' }}>@lang('messages.free')</option>
                                <option value="pro" {{ $planTypeValue === 'pro' ? 'selected' : '' }}>@lang('messages.pro')</option>
                                <option value="enterprise" {{ $planTypeValue === 'enterprise' ? 'selected' : '' }}>@lang('messages.enterprise')</option>
                            </select>
                            <x-input-error :messages="$errors->get('plan_type')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="plan_term" :value="__('messages.plan_term')" />
                            <select name="plan_term" id="plan_term" class="{{ $field }}">
                                <option value="">@lang('messages.none')</option>
                                <option value="month" {{ $planTermValue === 'month' ? 'selected' : '' }}>@lang('messages.monthly')</option>
                                <option value="year" {{ $planTermValue === 'year' ? 'selected' : '' }}>@lang('messages.yearly')</option>
                            </select>
                            <x-input-error :messages="$errors->get('plan_term')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="plan_expires" :value="__('messages.plan_expires')" />
                            <input type="text" name="plan_expires" id="plan_expires" value="{{ old('plan_expires', $role->plan_expires) }}" class="datepicker-date {{ $field }}">
                            <x-input-error :messages="$errors->get('plan_expires')" class="mt-2" />

                            <div class="page-actions mt-3">
                                <button type="button" data-add-days="30" class="js-add-days page-tool">@lang('messages.add_30_days')</button>
                                <button type="button" data-add-days="90" class="js-add-days page-tool">@lang('messages.add_90_days')</button>
                                <button type="button" data-add-days="365" class="js-add-days page-tool">@lang('messages.add_1_year')</button>
                                <button type="button" id="clear-expiration-btn" class="page-tool">@lang('messages.clear')</button>
                            </div>
                        </div>
                    </div>

                    <div class="page-form-actions">
                        <x-secondary-link :href="route('admin.schedules')">@lang('messages.cancel')</x-secondary-link>
                        <x-brand-button type="submit">@lang('messages.save_changes')</x-brand-button>
                    </div>
                </form>
            </x-page-card>
            @endif

            {{-- Deletion / subdomain release, apart from the forms and in red.

                 roles.subdomain is UNIQUE, so freeing a squatted name means renaming the row that
                 holds it. The schedule and all its history survive and this is reversible; what may
                 not survive is getting the original name back, because the whole point is that
                 somebody else can take it.

                 The heading is neutral once deleted: the card can offer Restore AND Release at that
                 point, so naming it after either one reads as the only choice. --}}
            <x-page-card beside :title="$role->is_deleted ? __('messages.deleted') : __('messages.mark_schedule_deleted')" class="border border-red-200 dark:border-red-800">
                {{-- What the button does, and the button beside it. --}}
                @if ($role->is_deleted)
                    <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
                        <p class="min-w-[240px] flex-1 text-sm text-gray-600 dark:text-gray-400">
                            @if ($role->subdomain_before_delete && $originalTaken)
                                @lang('messages.restore_keeps_subdomain', ['original' => $role->subdomain_before_delete, 'subdomain' => $role->subdomain])
                            @elseif ($role->subdomain_before_delete)
                                @lang('messages.restore_reclaims_subdomain', ['subdomain' => $role->subdomain_before_delete])
                            @else
                                @lang('messages.release_subdomain_description', ['subdomain' => $role->subdomain])
                            @endif
                        </p>

                        <div class="page-actions">
                            @if (! $role->subdomain_before_delete)
                                <form method="POST" action="{{ route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]) }}">
                                    @csrf
                                    <x-danger-button data-confirm="{{ __('messages.release_subdomain_confirm') }}">
                                        @lang('messages.release_subdomain')
                                    </x-danger-button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.schedules.restore', ['role' => $role->encodeId()]) }}">
                                @csrf
                                <x-brand-button type="submit" data-confirm="{{ __('messages.restore_schedule_confirm') }}">
                                    @lang('messages.restore_schedule')
                                </x-brand-button>
                            </form>
                        </div>
                    </div>
                @else
                    @if ($role->hasActiveSubscription())
                        <x-page-notice tone="warn" class="mb-3">@lang('messages.delete_keeps_subscription')</x-page-notice>
                    @endif

                    @if ($role->custom_domain_host)
                        <x-page-notice tone="warn" class="mb-3">@lang('messages.delete_keeps_custom_domain')</x-page-notice>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
                        <p class="min-w-[240px] flex-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $role->user_id ? __('messages.mark_deleted_description') : __('messages.mark_deleted_description_unclaimed') }}
                        </p>

                        <form method="POST" action="{{ route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]) }}">
                            @csrf
                            <x-danger-button data-confirm="{{ __('messages.mark_deleted_confirm') }}">
                                @lang('messages.mark_schedule_deleted')
                            </x-danger-button>
                        </form>
                    </div>
                @endif
            </x-page-card>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            // The plan form is hosted only, so there may be nothing to wire up.
            if (! document.getElementById('plan_expires')) {
                return;
            }

            var fp = flatpickr('#plan_expires', {
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: 'M j, Y',
                dateFormat: 'Y-m-d',
            });

            document.querySelectorAll('.js-add-days').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var days = parseInt(this.getAttribute('data-add-days'));
                    var startDate;

                    if (fp.selectedDates.length) {
                        startDate = new Date(fp.selectedDates[0]);
                    } else {
                        startDate = new Date();
                    }

                    startDate.setDate(startDate.getDate() + days);
                    fp.setDate(startDate);
                });
            });

            document.getElementById('clear-expiration-btn').addEventListener('click', function() {
                fp.clear();
            });
        });
    </script>

</x-app-admin-layout>
