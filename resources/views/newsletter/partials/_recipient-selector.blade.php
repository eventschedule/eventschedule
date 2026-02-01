@if ($segments->count())
<div class="space-y-3">
    @foreach ($segments as $segment)
    <label class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:border-[#4E81FA] transition-colors"
        :class="selectedSegmentIds.includes({{ $segment->id }}) ? 'bg-blue-50 dark:bg-blue-900/20 border-[#4E81FA]' : ''">
        <div class="flex items-center gap-3">
            <input type="checkbox"
                :checked="selectedSegmentIds.includes({{ $segment->id }})"
                @change="toggleSegment({{ $segment->id }})"
                class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA]" />
            <div>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $segment->name }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 {{ is_rtl() ? 'me-2' : 'ms-2' }}">
                    ({{ $segment->type === 'all_followers' ? __('messages.all_followers') : ($segment->type === 'ticket_buyers' ? __('messages.ticket_buyers') : ($segment->type === 'manual' ? __('messages.manual') : __('messages.group'))) }})
                </span>
            </div>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($segment->recipient_count ?? 0) }}</span>
    </label>
    @endforeach
</div>
@else
<p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.no_segments') }}</p>
<p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.default_all_followers') }}</p>
@endif

<div class="mt-3">
    <a href="{{ route('newsletter.segments', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}" class="text-sm text-[#4E81FA] hover:text-blue-700">
        {{ __('messages.manage_segments') }}
    </a>
</div>

{{-- Hidden inputs for segment_ids --}}
<template x-for="segmentId in selectedSegmentIds" :key="segmentId">
    <input type="hidden" name="segment_ids[]" :value="segmentId" />
</template>
