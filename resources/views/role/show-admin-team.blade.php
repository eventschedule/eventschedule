@php
    $isOwner = auth()->user()->id == $role->user_id;
    // Set by RoleController::viewAdmin, owner-only. An admin can manage members but
    // cannot give the schedule away.
    $openTransfer = $openTransfer ?? null;
    $teamSortBy = $sortBy ?? '';
    $teamSortDir = $sortDir ?? 'asc';
    // Whether the owner has been offered Transfer ownership on a row yet (see the foot of the list).
    $transferOffered = false;
@endphp

<div class="page-head">
    {{-- Who is here and what each kind of member can do: the page was a bare table, and "Admin"
         and "Viewer" were explained nowhere on it. --}}
    <p class="page-lead">{{ __('messages.team_lead') }}</p>
    <div class="page-actions">
        @if (! $isViewer)
        @if ($role->isEnterprise())
        <x-brand-link href="{{ route('role.create_member', ['subdomain' => $role->subdomain]) }}">
            <svg class="-ms-0.5 me-1.5 h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            {{ __('messages.add_member') }}
        </x-brand-link>
        @elseif (config('app.hosted'))
        {{-- Locked on this plan, and says so before it is pressed. A real address for the plan
             page behind it; pressing opens the explanation (layouts/app: data-modal-open). --}}
        <x-secondary-link href="{{ route('role.subscribe', ['subdomain' => $role->subdomain, 'tier' => 'enterprise']) }}" data-modal-open="upgrade-members">
            {{ __('messages.add_member') }}
            <x-lock-badge tier="enterprise" class="ms-2" />
        </x-secondary-link>
        <x-upgrade-modal name="upgrade-members" tier="enterprise" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/team-scheduling')">
            {{ __('messages.upgrade_feature_description_members') }}
        </x-upgrade-modal>
        @endif
        @endif
    </div>
</div>

@if ($isOwner && $openTransfer)
{{-- Pending handover. Replaces the Transfer button until it is answered or withdrawn. --}}
<div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="text-sm text-amber-800 dark:text-amber-200">
                <p class="font-semibold">{{ __('messages.schedule_transfer_pending', ['email' => $openTransfer->to_email]) }}</p>
                <p class="mt-1">{{ __('messages.schedule_transfer_pending_expires', ['date' => $openTransfer->expires_at?->translatedFormat('M j, Y')]) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 flex-shrink-0">
            <form method="POST" action="{{ route('role.transfer.resend', ['subdomain' => $role->subdomain]) }}" class="inline">
                @csrf
                <button type="submit" class="event-link">{{ __('messages.resend_invite') }}</button>
            </form>
            <form method="POST" action="{{ route('role.transfer.cancel', ['subdomain' => $role->subdomain]) }}" class="inline"
                data-confirm="{{ __('messages.are_you_sure') }}">
                @csrf
                <button type="submit" class="event-link is-danger">{{ __('messages.cancel') }}</button>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Members added while the schedule was Enterprise stay listed after a downgrade, but the plan
     filter in User::planAllowsTeamAccess() has already closed their Sales, /scan and /checkin
     pages. Nothing on this tab said so, so the owner had no way to know their staff had gone
     blind - which is how one customer's controller spent a week reporting an empty Sales page. --}}
@if (config('app.hosted') && ! $role->isEnterprise() && $members->count() > 1)
<div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-3">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
    </svg>
    <p class="text-sm text-amber-700 dark:text-amber-300">
        {{ __('messages.team_access_blocked_owner') }}
    </p>
</div>
@endif

{{-- One row a member: who they are, what they may do, and the one thing that can be done about
     them. On a phone the row stacks, where the table used to scroll sideways and leave the role
     and Remove off the edge. --}}
<div class="ap-card rounded-xl overflow-hidden">
    <table role="table" class="page-table">
        <thead role="rowgroup">
            <tr role="row">
                <th scope="col" role="columnheader" @if ($teamSortBy === 'name') aria-sort="{{ $teamSortDir === 'asc' ? 'ascending' : 'descending' }}" @endif>
                    <button type="button" class="page-sort" data-sort="name">{{ __('messages.name') }}@if ($teamSortBy === 'name') <span aria-hidden="true">{{ $teamSortDir === 'asc' ? '↑' : '↓' }}</span>@endif</button>
                </th>
                <th scope="col" role="columnheader">{{ __('messages.role') }}</th>
                <th scope="col" role="columnheader"><span class="sr-only">{{ __('messages.actions') }}</span></th>
            </tr>
        </thead>
        <tbody role="rowgroup">
            @foreach ($members as $member)
            @php
                $hasActuallySignedUp = ! $member->isStub();
                $memberLevel = strtolower($member->pivot->level);
                $memberInitial = mb_strtoupper(mb_substr(trim((string) ($member->name ?: $member->email)), 0, 1));
                $memberHash = App\Utils\UrlUtils::encodeId($member->id);
            @endphp
            <tr role="row">
                <td role="cell" class="c-main">
                    <div class="page-person">
                        <span class="event-avatar" aria-hidden="true">{{ $memberInitial }}</span>
                        <div class="page-person-text">
                            <div class="event-list-name">
                                <span dir="auto" v-pre>{{ $member->name }}</span>
                                @if ($member->id == auth()->user()->id)
                                <span class="event-chip">{{ __('messages.you') }}</span>
                                @endif
                                @if (! $hasActuallySignedUp)
                                <span class="event-chip">{{ __('messages.pending') }}</span>
                                @endif
                            </div>
                            <div class="event-list-sub"><a href="mailto:{{ $member->email }}" class="hover:underline" dir="ltr" v-pre>{{ $member->email }}</a></div>
                        </div>
                    </div>
                </td>
                <td role="cell">
                    @if ($isOwner && $memberLevel != 'owner' && $hasActuallySignedUp)
                        <form method="POST" action="{{ route('role.update_member_level', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <label class="sr-only" for="member-level-{{ $memberHash }}">{{ __('messages.role') }}</label>
                            <select name="level" id="member-level-{{ $memberHash }}" data-auto-submit="true"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <option value="admin" {{ $memberLevel == 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                                <option value="viewer" {{ $memberLevel == 'viewer' ? 'selected' : '' }}>{{ __('messages.viewer') }}</option>
                            </select>
                        </form>
                    @else
                        {{ __('messages.' . $memberLevel) }}
                    @endif
                </td>
                <td role="cell" class="c-actions">
                    @if (! $hasActuallySignedUp && ! $isViewer)
                        <form method="POST" action="{{ route('role.resend_invite', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}">
                            @csrf
                            <button type="submit" class="event-link">{{ __('messages.resend_invite') }}</button>
                        </form>
                        @if ($member->phone && \App\Services\SmsService::isConfigured() && config('app.hosted'))
                        <form method="POST" action="{{ route('role.resend_invite', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}">
                            @csrf
                            <input type="hidden" name="via" value="sms">
                            <button type="submit" class="event-link">{{ __('messages.resend_invite_sms') }}</button>
                        </form>
                        @endif
                    @endif
                    @if ($isOwner && ! $openTransfer && $member->id == auth()->user()->id)
                        {{-- On the owner's own row, where it is about the owner: it stood in the
                             head as a button the size of Add member, on a page most owners are
                             alone on. Not plan gated: handing a schedule over is account
                             management, available on Free, Pro and Enterprise alike. The row is
                             found by who is signed in, not by what the pivot calls them: the
                             owner on roles.user_id and the pivot's level can drift apart. --}}
                        @php $transferOffered = true; @endphp
                        <a href="{{ route('role.transfer.create', ['subdomain' => $role->subdomain]) }}" class="event-link">{{ __('messages.transfer_ownership') }}</a>
                    @endif
                    @if ($memberLevel != 'owner' && ($isOwner || $member->id == auth()->user()->id))
                        <form method="POST" action="{{ route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($member->id)]) }}" data-confirm="{{ __('messages.are_you_sure') }}">
                            @csrf
                            @method('DELETE')
                            {{-- Red: it takes someone off the schedule. It was the brand's blue. --}}
                            <button type="submit" class="event-link is-danger">{{ $member->id == auth()->user()->id ? __('messages.leave_schedule') : __('messages.remove') }}</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if ($isOwner && ! $openTransfer && ! $transferOffered)
{{-- The owner is not among the rows (it should not happen, and has): the way to hand the schedule
     over is still on the page. --}}
<p class="event-hint mt-3"><a href="{{ route('role.transfer.create', ['subdomain' => $role->subdomain]) }}" class="event-link">{{ __('messages.transfer_ownership') }}</a></p>
@endif
