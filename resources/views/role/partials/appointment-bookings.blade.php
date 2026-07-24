@php use App\Utils\UrlUtils; $filter = request('filter', 'upcoming'); @endphp

<div class="flex gap-2 flex-wrap">
    @foreach (['upcoming', 'pending', 'past', 'cancelled'] as $f)
        <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?view=bookings&filter={{ $f }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium {{ $filter === $f ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-500 dark:text-gray-400' }}">{{ __('messages.appointments_filter_'.$f) }}</a>
    @endforeach
</div>

@if ($bookings->isEmpty())
    <div class="ap-card rounded-xl p-8 text-center text-gray-500 dark:text-gray-400">{{ __('messages.appointments_no_bookings') }}</div>
@else
    <div class="ap-card rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-[#252526] text-left text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="p-3 font-medium">{{ __('messages.date') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.appointment_types') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.name') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.status') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $s)
                    @php
                        $e = $s->event;
                        $cancelled = $e->is_cancelled || in_array($s->status, ['cancelled', 'refunded', 'expired']);
                        $pending = is_null(optional($e->roles()->where('roles.id', $e->creator_role_id)->first())->pivot->is_accepted);
                        $statusLabel = $cancelled ? __('messages.appointments_cancelled') : ($pending ? __('messages.appointments_request_sent') : ($s->status === 'paid' ? __('messages.appointments_confirmed_label') : __('messages.appointments_awaiting_payment')));
                    @endphp
                    <tr class="border-t border-gray-100 dark:border-[#2d2d30]">
                        <td class="p-3 whitespace-nowrap">{{ $e->getStartDateTime($s->event_date, true, $e->timezone)->format('M j, Y') }}<div class="text-xs text-gray-400">{{ $e->getStartEndTime($s->event_date) }}</div></td>
                        <td class="p-3">{{ $e->appointmentType?->name }}</td>
                        <td class="p-3">{{ $s->name }}<div class="text-xs text-gray-400">{{ $s->email }}</div></td>
                        <td class="p-3">
                            <span class="inline-block px-2 py-1 rounded-full text-xs {{ $cancelled ? 'bg-gray-100 dark:bg-[#2d2d30] text-gray-500' : ($pending ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400') }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a href="{{ route('appointments.manage', ['event_id' => UrlUtils::encodeId($e->id), 'secret' => $s->secret]) }}" target="_blank" rel="noopener" class="text-xs text-[var(--brand-blue)] me-3">{{ __('messages.view') }}</a>
                            @if (! $isViewer && ! $cancelled)
                                <form method="POST" action="{{ route('appointments.booking_cancel', ['subdomain' => $role->subdomain, 'saleHash' => UrlUtils::encodeId($s->id)]) }}" class="form-confirm inline" data-confirm="{{ __('messages.are_you_sure') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400">{{ __('messages.appointments_cancel_booking') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
