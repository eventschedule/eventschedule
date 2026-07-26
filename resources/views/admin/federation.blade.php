<x-app-admin-layout>

    <div class="space-y-4">
        @include('admin.partials._navigation', ['active' => 'federation'])

        <div class="ap-card rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.federation')</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-2xl">@lang('messages.federation_admin_intro')</p>
                </div>

                {{-- Status filter --}}
                <div class="flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-[#252526] p-1">
                    @foreach (['pending', 'approved', 'suspended', 'all'] as $key)
                        <a href="{{ route('admin.federation', ['status' => $key]) }}"
                           class="rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 {{ $status === $key ? 'bg-white dark:bg-[#1e1e1e] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}"
                           @if ($status === $key) style="box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);" @endif>
                            @lang('messages.federation_status_'.$key)
                            @if ($key === 'pending' && $pendingCount > 0)
                                <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($instances->isEmpty())
            <div class="ap-card rounded-xl p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">@lang('messages.federation_no_instances')</p>
            </div>
        @else
            <form method="POST" action="{{ route('admin.federation.bulk') }}" class="space-y-4">
                @csrf

                @foreach ($instances as $instance)
                    @php $hash = \App\Utils\UrlUtils::encodeId($instance->id); @endphp
                    <div class="ap-card rounded-xl p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0">
                                <input type="checkbox" name="hashes[]" value="{{ $hash }}"
                                       class="mt-1 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                <div class="min-w-0">
                                    {{-- Instance-supplied text: escape and keep it out of any Vue template. --}}
                                    <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $instance->name ?: $instance->site_url }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 break-all">
                                        <a href="{{ $instance->site_url }}" target="_blank" rel="noopener nofollow" class="hover:underline">{{ $instance->site_url }}</a>
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                        {{ $instance->contact_email ?: __('messages.none') }}
                                        &middot; {{ $instance->app_version ?: '-' }}
                                        &middot; {{ trans_choice('messages.federation_listing_count', $instance->events_count, ['count' => number_format($instance->events_count)]) }}
                                        @if ($instance->last_seen_at)
                                            &middot; {{ $instance->last_seen_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                    @if ($instance->status === 'approved') bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400
                                    @elseif ($instance->status === 'suspended') bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400
                                    @else bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 @endif">
                                    @lang('messages.federation_status_'.$instance->status)
                                </span>

                                {{-- Destructive first, forward action last. --}}
                                @if ($instance->status !== 'suspended')
                                    <button type="submit" formaction="{{ route('admin.federation.suspend', $hash) }}"
                                            class="px-4 py-3 text-base rounded-lg font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                                        @lang('messages.federation_suspend')
                                    </button>
                                @endif
                                @if ($instance->status !== 'approved')
                                    <button type="submit" formaction="{{ route('admin.federation.approve', $hash) }}"
                                            class="px-4 py-3 text-base rounded-lg font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                                        @lang('messages.federation_approve')
                                    </button>
                                @endif
                            </div>
                        </div>

                        @if ($instance->flagged_at)
                            {{-- Bordered panel, not coloured text, per the AP warning convention. --}}
                            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <p class="text-sm text-amber-800 dark:text-amber-200">@lang('messages.federation_flagged_warning')</p>
                                </div>
                            </div>
                        @endif

                        {{-- What is actually being approved. Approving on a name alone is
                             approving unseen third-party content onto this domain. --}}
                        @if (! empty($samples[$instance->id]) && count($samples[$instance->id]))
                            <div class="mt-4 rounded-lg bg-gray-50 dark:bg-[#252526] p-4">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">@lang('messages.federation_sample_listings')</p>
                                <ul class="space-y-2">
                                    @foreach ($samples[$instance->id] as $sample)
                                        <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                            <a href="{{ $sample->url }}" target="_blank" rel="noopener nofollow"
                                               class="font-medium text-gray-900 dark:text-white hover:underline">{{ $sample->name }}</a>
                                            <span class="text-gray-500 dark:text-gray-400">
                                                {{ $sample->locationLabel() ?: __('messages.online') }}
                                                @if ($sample->next_occurrence_at)
                                                    &middot; {{ $sample->next_occurrence_at->format('M j, Y') }}
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="ap-card rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.federation_bulk_hint')</p>
                    <div class="flex items-center gap-2">
                        <button type="submit" name="action" value="suspend"
                                class="px-4 py-3 text-base rounded-lg font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200">
                            @lang('messages.federation_suspend_selected')
                        </button>
                        <button type="submit" name="action" value="approve"
                                class="px-4 py-3 text-base rounded-lg font-medium text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200">
                            @lang('messages.federation_approve_selected')
                        </button>
                    </div>
                </div>
            </form>

            <div>{{ $instances->links() }}</div>
        @endif
    </div>

</x-app-admin-layout>
