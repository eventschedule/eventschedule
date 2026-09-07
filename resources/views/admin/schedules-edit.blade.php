<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'schedules'])

    <div class="max-w-3xl mx-auto space-y-4">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">@lang('messages.edit_schedule')</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $role->name }}
                    </a>
                    <span class="mx-2">&bull;</span>
                    <span>{{ $role->subdomain }}</span>
                </p>
            </div>
            <x-secondary-link :href="route('admin.schedules')">
                @lang('messages.back_to_schedules')
            </x-secondary-link>
        </div>

        @if (session('success'))
            <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-200">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Current Status Info Box --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-3">@lang('messages.current_subscription_status')</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.status'):</span>
                    <span class="ms-2 font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $role->subscriptionStatusLabel()) }}</span>
                </div>
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.stripe_customer'):</span>
                    <span class="ms-2 font-medium text-gray-900 dark:text-white">{{ $role->stripe_id ?: __('messages.none') }}</span>
                </div>
                @if ($role->trial_ends_at)
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.trial_ends'):</span>
                    <span class="ms-2 font-medium text-gray-900 dark:text-white">{{ $role->trial_ends_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if ($role->hasActiveSubscription())
                <div>
                    <span class="text-gray-600 dark:text-gray-400">@lang('messages.active_subscription'):</span>
                    <span class="ms-2 font-medium text-green-600 dark:text-green-400">@lang('messages.yes')</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Schedule Details --}}
        <form method="POST" action="{{ route('admin.schedules.update_details', ['role' => $role->encodeId()]) }}" class="ap-card rounded-xl shadow">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <h3 class="text-sm font-medium text-gray-900 dark:text-white">@lang('messages.schedule_details')</h3>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.name')</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" dir="auto"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_subdomain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.subdomain')</label>
                    <input type="text" name="new_subdomain" id="new_subdomain" value="{{ old('new_subdomain', $role->subdomain) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    @error('new_subdomain')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.email')</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $role->email) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    {{-- Role::boot()'s `updating` hook does this, and it is easy to trip over. --}}
                    <div class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm text-amber-800 dark:text-amber-300">@lang('messages.email_change_resends_verification')</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.phone')</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $role->phone) }}" placeholder="+15551234567"
                        class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg flex items-center justify-end gap-4">
                <x-secondary-link :href="route('admin.schedules')">
                    @lang('messages.cancel')
                </x-secondary-link>
                <x-brand-button type="submit">
                    @lang('messages.save_changes')
                </x-brand-button>
            </div>
        </form>

        {{-- Email Verification --}}
        @if ($role->hasVerifiedEmail())
            <div class="ap-card rounded-xl shadow p-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-sm">
                        <span class="text-gray-600 dark:text-gray-400">@lang('messages.email'):</span>
                        <span class="ms-2 font-medium text-gray-900 dark:text-white">{{ $role->email ?: '-' }}</span>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                        @lang('messages.verified') &bull; {{ \Carbon\Carbon::parse($role->email_verified_at)->format('M d, Y') }}
                    </span>
                </div>
            </div>
        @else
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1 text-sm text-amber-800 dark:text-amber-300">
                        @lang('messages.email_not_verified'){{ $role->email ? ': '.$role->email : '' }}
                    </div>
                    <form method="POST" action="{{ route('admin.schedules.verify_email', ['role' => $role->encodeId()]) }}">
                        @csrf
                        <button type="submit" data-confirm="Mark this schedule's email as verified?"
                            class="inline-flex items-center px-3 py-1.5 border border-amber-300 dark:border-amber-700 rounded-lg text-xs font-medium text-amber-800 dark:text-amber-300 bg-white dark:bg-gray-700 hover:bg-amber-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200">
                            @lang('messages.mark_email_verified')
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Phone Verification --}}
        @if ($role->phone)
            @if ($role->phone_verified_at)
                <div class="ap-card rounded-xl shadow p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="text-sm">
                            <span class="text-gray-600 dark:text-gray-400">@lang('messages.phone'):</span>
                            <span class="ms-2 font-medium text-gray-900 dark:text-white">{{ $role->phone }}</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            @lang('messages.verified') &bull; {{ \Carbon\Carbon::parse($role->phone_verified_at)->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-1 text-sm text-amber-800 dark:text-amber-300">
                            @lang('messages.phone_not_verified'){{ $role->phone ? ': '.$role->phone : '' }}
                        </div>
                        <form method="POST" action="{{ route('admin.schedules.verify_phone', ['role' => $role->encodeId()]) }}">
                            @csrf
                            <button type="submit" data-confirm="Mark this schedule's phone as verified?"
                                class="inline-flex items-center px-3 py-1.5 border border-amber-300 dark:border-amber-700 rounded-lg text-xs font-medium text-amber-800 dark:text-amber-300 bg-white dark:bg-gray-700 hover:bg-amber-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200">
                                @lang('messages.mark_phone_verified')
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        {{-- Edit Form --}}
        <form method="POST" action="{{ route('admin.schedules.update', ['role' => $role->encodeId()]) }}" class="ap-card rounded-xl shadow">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                {{-- Plan Type --}}
                <div>
                    <label for="plan_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_type')</label>
                    <select name="plan_type" id="plan_type" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="free" {{ ($role->plan_type ?? 'free') === 'free' ? 'selected' : '' }}>@lang('messages.free')</option>
                        <option value="pro" {{ $role->plan_type === 'pro' ? 'selected' : '' }}>@lang('messages.pro')</option>
                        <option value="enterprise" {{ $role->plan_type === 'enterprise' ? 'selected' : '' }}>@lang('messages.enterprise')</option>
                    </select>
                    @error('plan_type')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Plan Term --}}
                <div>
                    <label for="plan_term" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_term')</label>
                    <select name="plan_term" id="plan_term" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                        <option value="">@lang('messages.none')</option>
                        <option value="month" {{ $role->plan_term === 'month' ? 'selected' : '' }}>@lang('messages.monthly')</option>
                        <option value="year" {{ $role->plan_term === 'year' ? 'selected' : '' }}>@lang('messages.yearly')</option>
                    </select>
                    @error('plan_term')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Plan Expires --}}
                <div>
                    <label for="plan_expires" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.plan_expires')</label>
                    <input type="text" name="plan_expires" id="plan_expires" value="{{ $role->plan_expires }}"
                        class="datepicker-date mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    @error('plan_expires')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    {{-- Quick Action Buttons --}}
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" data-add-days="30" class="js-add-days inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.add_30_days')
                        </button>
                        <button type="button" data-add-days="90" class="js-add-days inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.add_90_days')
                        </button>
                        <button type="button" data-add-days="365" class="js-add-days inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.add_1_year')
                        </button>
                        <button type="button" id="clear-expiration-btn" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition-all duration-200">
                            @lang('messages.clear')
                        </button>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 rounded-b-lg flex items-center justify-end gap-4">
                <x-secondary-link :href="route('admin.schedules')">
                    @lang('messages.cancel')
                </x-secondary-link>
                <x-brand-button type="submit">
                    @lang('messages.save_changes')
                </x-brand-button>
            </div>
        </form>

        {{-- Deletion / subdomain release.

             roles.subdomain is UNIQUE, so freeing a squatted name means renaming the row that
             holds it. The schedule and all its history survive and this is reversible; what may
             not survive is getting the original name back, because the whole point is that
             somebody else can take it. --}}
        <div class="ap-card rounded-xl shadow p-6 space-y-4">
            {{-- Neutral heading once deleted: the card can offer Restore AND Release at that
                 point, so naming it after either one reads as the only choice. --}}
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                {{ $role->is_deleted ? __('messages.deleted') : __('messages.mark_schedule_deleted') }}
            </h3>

            @if ($role->is_deleted)
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if ($role->subdomain_before_delete && $originalTaken)
                        @lang('messages.restore_keeps_subdomain', ['original' => $role->subdomain_before_delete, 'subdomain' => $role->subdomain])
                    @elseif ($role->subdomain_before_delete)
                        @lang('messages.restore_reclaims_subdomain', ['subdomain' => $role->subdomain_before_delete])
                    @else
                        @lang('messages.release_subdomain_description', ['subdomain' => $role->subdomain])
                    @endif
                </p>

                <div class="flex items-center justify-end gap-4">
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
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $role->user_id ? __('messages.mark_deleted_description') : __('messages.mark_deleted_description_unclaimed') }}
                </p>

                @if ($role->hasActiveSubscription())
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm text-amber-800 dark:text-amber-300">@lang('messages.delete_keeps_subscription')</p>
                        </div>
                    </div>
                @endif

                @if ($role->custom_domain_host)
                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm text-amber-800 dark:text-amber-300">@lang('messages.delete_keeps_custom_domain')</p>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end">
                    <form method="POST" action="{{ route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]) }}">
                        @csrf
                        <x-danger-button data-confirm="{{ __('messages.mark_deleted_confirm') }}">
                            @lang('messages.mark_schedule_deleted')
                        </x-danger-button>
                    </form>
                </div>
            @endif
        </div>
    </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
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
