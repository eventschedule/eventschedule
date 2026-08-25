{{--
    Ownership handover, recipient side (discussion #119). Public page, so it uses the auth
    layout rather than the admin shell - the invitee may not have an account yet.

    $state is one of: missing, closed, accepted, guest, wrong_account, ready.
--}}
<x-auth-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php
        $role = $transfer?->role;
    @endphp

    @if ($state === 'missing' || $state === 'closed')

        <div class="text-center">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.schedule_transfer_unavailable') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.schedule_transfer_unavailable_message') }}
            </p>
        </div>

    @elseif ($state === 'accepted')

        <div class="text-center">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.schedule_transfer_already_accepted') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" dir="auto">
                {{ __('messages.schedule_transfer_already_accepted_message', ['name' => $role?->name]) }}
            </p>
        </div>

    @else

        <div class="text-center">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.schedule_transfer_invite_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.schedule_transfer_invite_intro', ['user' => $transfer->fromUser?->name, 'name' => $role?->name]) }}
            </p>
        </div>

        <div class="mt-6 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-base font-semibold text-gray-900 dark:text-gray-100" dir="auto">{{ $role?->name }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.' . $role?->type) }}</p>

            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('messages.events') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($role?->events()->count() ?? 0) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">{{ __('messages.followers') }}</dt>
                    <dd class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($role?->followers()->count() ?? 0) }}</dd>
                </div>
            </dl>
        </div>

        <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div class="text-sm text-amber-800 dark:text-amber-200">
                    <p>{{ __('messages.schedule_transfer_accept_responsibility') }}</p>
                    @if (config('app.hosted'))
                    <p class="mt-2">{{ __('messages.schedule_transfer_accept_billing') }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($state === 'guest')

            <p class="mt-6 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.schedule_transfer_invite_sign_in', ['email' => $transfer->to_email]) }}
            </p>

            <div class="mt-4 flex flex-col gap-3">
                <a href="{{ $signUpUrl }}"
                    class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200">
                    {{ __('messages.sign_up') }}
                </a>
                <a href="{{ $signInUrl }}"
                    class="inline-flex items-center justify-center px-4 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)]">
                    {{ __('messages.log_in') }}
                </a>
            </div>

        @elseif ($state === 'wrong_account')

            <div class="mt-6 rounded-lg border border-gray-200 dark:border-gray-700 p-4 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.schedule_transfer_wrong_account', ['email' => $transfer->to_email]) }}
            </div>

        @else

            <div class="mt-6 flex items-center gap-3">
                <form method="POST" action="{{ route('role.transfer.decline', ['token' => $transfer->token]) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.decline') }}
                    </button>
                </form>

                {{-- No data-confirm: the app-wide form[data-confirm] handler lives in --}}
                {{-- layouts/app.blade.php, which this public page does not use. The page itself --}}
                {{-- is the confirmation step. --}}
                <form method="POST" action="{{ route('role.transfer.accept', ['token' => $transfer->token]) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.accept') }}
                    </button>
                </form>
            </div>

        @endif

    @endif
</x-auth-layout>
