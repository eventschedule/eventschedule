@php
    $isOwner = auth()->user()->id == $role->user_id;
    // Set by RoleController::viewAdmin, owner-only. An admin can manage members but
    // cannot give the schedule away.
    $openTransfer = $openTransfer ?? null;
@endphp

@if ($isOwner && $openTransfer)
{{-- Pending handover. Replaces the Transfer button until it is answered or withdrawn. --}}
<div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="text-sm text-amber-800 dark:text-amber-200">
                <p class="font-semibold">{{ __('messages.schedule_transfer_pending', ['email' => $openTransfer->to_email]) }}</p>
                <p class="mt-1">{{ __('messages.schedule_transfer_pending_expires', ['date' => $openTransfer->expires_at?->format('M j, Y')]) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <form method="POST" action="{{ route('role.transfer.resend', ['subdomain' => $role->subdomain]) }}" class="inline">
                @csrf
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('messages.resend_invite') }}
                </button>
            </form>
            <form method="POST" action="{{ route('role.transfer.cancel', ['subdomain' => $role->subdomain]) }}" class="inline"
                data-confirm="{{ __('messages.are_you_sure') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('messages.cancel') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endif

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <!--
        <h1 class="text-base font-semibold leading-6 text-gray-900">Users</h1>
        <p class="mt-2 text-sm text-gray-700">A list of all the users in your account including their name, title,
            email and role.</p>
        -->
    </div>
    <div class="mt-6 sm:ms-16 sm:mt-0 sm:flex-none flex flex-col gap-2 md:flex-row md:items-center">
        @if ($isOwner && ! $openTransfer)
        {{-- Not plan gated, unlike Add member below: handing a schedule over is account --}}
        {{-- management, available on Free, Pro and Enterprise alike. --}}
        <x-secondary-link href="{{ route('role.transfer.create', ['subdomain' => $role->subdomain]) }}" class="w-full md:w-auto">
            {{ __('messages.transfer_ownership') }}
        </x-secondary-link>
        @endif
        @if (!$isViewer)
        @if ($role->isEnterprise())
        <x-brand-link href="{{ route('role.create_member', ['subdomain' => $role->subdomain]) }}" class="w-full md:w-auto">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.add_member') }}
        </x-brand-link>
        @elseif (config('app.hosted'))
        <button type="button" x-data x-on:click.prevent="$dispatch('open-modal', 'upgrade-members')"
                class="w-full md:w-auto inline-flex items-center justify-center px-4 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path
                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.add_member') }}
        </button>
        @if(config('app.hosted'))
        <x-upgrade-modal name="upgrade-members" tier="enterprise" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/team-scheduling')">
            {{ __('messages.upgrade_feature_description_members') }}
        </x-upgrade-modal>
        @endif
        @endif
        @endif
    </div>
</div>

{{-- Members added while the schedule was Enterprise stay listed after a downgrade, but the plan
     filter in User::planAllowsTeamAccess() has already closed their Sales, /scan and /checkin
     pages. Nothing on this tab said so, so the owner had no way to know their staff had gone
     blind - which is how one customer's controller spent a week reporting an empty Sales page. --}}
@if (config('app.hosted') && ! $role->isEnterprise() && $members->count() > 1)
<div class="mt-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-3">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
    <p class="text-sm text-amber-700 dark:text-amber-300">
        {{ __('messages.team_access_blocked_owner') }}
    </p>
</div>
@endif

<div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black/5 dark:ring-gray-700 md:rounded-lg">
                <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <x-sortable-header column="name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'asc'" class="py-3.5 ps-4 pe-3 sm:ps-6">{{ __('messages.name') }}</x-sortable-header>
                                <x-sortable-header column="email" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'asc'">{{ __('messages.email') }}</x-sortable-header>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.role') }}
                                </th>
                                <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6">
                                    <span class="sr-only">{{ __('messages.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($members as $member)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                    {{ $member->name }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <a href="mailto:{{ $member->email }}" class="hover:text-gray-700 dark:hover:text-gray-300">{{ $member->email }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @php
                                        $hasActuallySignedUp = ! $member->isStub();
                                    @endphp
                                    @if ($isOwner && $member->pivot->level != 'owner' && $hasActuallySignedUp)
                                        <form method="POST" action="{{ route('role.update_member_level', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="level" data-auto-submit="true"
                                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                                <option value="admin" {{ $member->pivot->level == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                                                <option value="viewer" {{ $member->pivot->level == 'viewer' ? 'selected' : '' }}>{{ __('messages.viewer') }}</option>
                                            </select>
                                        </form>
                                    @elseif ($hasActuallySignedUp)
                                        {{ __('messages.' . strtolower($member->pivot->level)) }}
                                    @else
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('role.resend_invite', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    {{ __('messages.resend_invite') }}
                                                </button>
                                            </form>
                                            @if ($member->phone && \App\Services\SmsService::isConfigured() && config('app.hosted'))
                                                <form method="POST" action="{{ route('role.resend_invite', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="via" value="sms">
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-lg bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        SMS
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6">
                                    @if ($member->pivot->level != 'owner' && $isOwner)
                                        <form method="POST" action="{{ route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" data-confirm="{{ __('messages.are_you_sure') }}" class="inline form-confirm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[var(--brand-blue)] hover:text-[var(--brand-blue)]">{{ __('messages.remove') }}</button>
                                        </form>
                                    @elseif ($member->id == auth()->user()->id && $member->pivot->level != 'owner')
                                        <form method="POST" action="{{ route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" data-confirm="{{ __('messages.are_you_sure') }}" class="inline form-confirm">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[var(--brand-blue)] hover:text-[var(--brand-blue)]">{{ __('messages.remove') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
