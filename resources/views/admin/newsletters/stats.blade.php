<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- How one of the platform's newsletters did: the same figures, curves and list of
         recipients a schedule's owner sees (newsletter/partials/_stats). --}}
    <div class="page-shell">
        @php
            $sentLead = __('messages.newsletter_status_' . $newsletter->status);
            if ($newsletter->sent_at) {
                $sentLead .= ': ' . $newsletter->sent_at->copy()->timezone(auth()->user()->timezone ?? 'UTC')->translatedFormat(get_use_24_hour_time(null) ? 'M j, Y H:i' : 'M j, Y g:i A');
            }
        @endphp

        @include('admin.newsletters.partials._subpage-head', [
            'title' => $newsletter->subject,
            'lead' => $sentLead,
            'back' => route('admin.newsletters.index'),
            'backLabel' => __('messages.admin_newsletters'),
        ])

        @include('newsletter.partials._stats')
    </div>
</x-app-admin-layout>
