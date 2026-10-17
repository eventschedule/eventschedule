@php
    // A row leads to the newsletter's statistics where the person may open them: the owners and
    // admins of its schedule (NewsletterController::getRole()); for anybody else it stays a line.
    $newsletterRoleIds = auth()->user()->roles()->wherePivotIn('level', ['owner', 'admin'])->get()
        ->filter(fn ($role) => auth()->user()->planAllowsTeamAccess($role))
        ->pluck('id')->values()->all();
@endphp
<div class="ap-card rounded-xl h-full flex flex-col overflow-hidden">
    <div class="dashboard-panel-header px-5 py-4">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.panel_newsletters') }}</h2>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($latestNewsletters as $newsletter)
        @php
            $canOpen = in_array($newsletter->role_id, $newsletterRoleIds);
            $statsUrl = $canOpen ? route('newsletter.stats', ['hash' => \App\Utils\UrlUtils::encodeId($newsletter->id), 'role_id' => \App\Utils\UrlUtils::encodeId($newsletter->role_id)]) : null;
        @endphp
        <div class="px-5 py-3">
            @if ($statsUrl)
            <a href="{{ $statsUrl }}" class="block text-sm font-medium text-gray-900 dark:text-white truncate hover:text-[var(--brand-blue)] transition-colors"><bdi>{{ $newsletter->subject }}</bdi></a>
            @else
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate"><bdi>{{ $newsletter->subject }}</bdi></p>
            @endif
            <div class="flex items-center gap-3 mt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.sent') }}: {{ number_format($newsletter->sent_count ?? 0) }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.opened') }}: {{ number_format($newsletter->open_count ?? 0) }}</span>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_newsletters_sent') }}</p>
            @if ($newsletterRoleIds)
            {{-- One schedule goes straight to a new newsletter; with several, the section picks first. --}}
            <x-link href="{{ count($newsletterRoleIds) === 1 ? route('newsletter.create', ['role_id' => \App\Utils\UrlUtils::encodeId($newsletterRoleIds[0])]) : route('newsletter.index') }}" class="inline-block mt-2 text-sm">{{ __('messages.create_newsletter') }}</x-link>
            @endif
        </div>
        @endforelse
    </div>
</div>
