<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'domains'])

    @php
        $figure = 'dashboard-stat-value text-3xl font-bold text-center text-gray-900 dark:text-white';
        $filtered = request()->filled('search') || request()->filled('mode') || request()->filled('status');
        $select = 'block rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_domains_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
            <x-admin-stat-tile :label="__('messages.total')" icon="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"
                tint="bg-gray-100 dark:bg-gray-500/10" ink="text-gray-500" glow="rgba(107, 114, 128, 0.15)">
                <span class="{{ $figure }}">{{ number_format($totalCustomDomains) }}</span>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.custom_domain_mode_direct')" icon="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"
                tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)">
                <span class="{{ $figure }}">{{ number_format($directCount) }}</span>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.domain_active')" icon="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                tint="bg-green-50 dark:bg-green-500/10" ink="text-green-500" glow="rgba(34, 197, 94, 0.15)">
                <span class="{{ $figure }}">{{ number_format($activeCount) }}</span>
            </x-admin-stat-tile>

            <x-admin-stat-tile :label="__('messages.domain_pending')" icon="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                tint="bg-amber-50 dark:bg-amber-500/10" ink="text-amber-500" glow="rgba(245, 158, 11, 0.15)">
                <span class="{{ $figure }}">{{ number_format($pendingCount) }}</span>
            </x-admin-stat-tile>
        </div>

        <div>
            <form method="GET" action="{{ route('admin.domains') }}" class="page-filters">
                {{-- The suggestions hang from this box: the autocomplete script looks for them
                     beside the input it is on. --}}
                <div class="page-filter is-grow relative">
                    <label for="search">@lang('messages.search')</label>
                    <x-text-input id="search" name="search" type="text" class="block w-full"
                        :value="request('search')" :placeholder="__('messages.search_domains')" autocomplete="off" data-subdomain-autocomplete />
                    <div data-subdomain-dropdown class="hidden absolute inset-x-0 top-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto z-50"></div>
                </div>
                <label class="page-filter">
                    <span>@lang('messages.custom_domain_mode')</span>
                    <select id="mode" name="mode" class="{{ $select }}">
                        <option value="">@lang('messages.all')</option>
                        <option value="redirect" {{ request('mode') === 'redirect' ? 'selected' : '' }}>@lang('messages.custom_domain_mode_redirect')</option>
                        <option value="direct" {{ request('mode') === 'direct' ? 'selected' : '' }}>@lang('messages.custom_domain_mode_direct')</option>
                    </select>
                </label>
                <label class="page-filter">
                    <span>@lang('messages.status')</span>
                    <select id="status" name="status" class="{{ $select }}">
                        <option value="">@lang('messages.all')</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>@lang('messages.domain_pending')</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>@lang('messages.domain_active')</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>@lang('messages.domain_failed')</option>
                    </select>
                </label>
                <div class="is-end">
                    @if ($filtered)
                    <a href="{{ route('admin.domains') }}" class="page-tool">@lang('messages.clear')</a>
                    @endif
                    <x-brand-button type="submit" size="sm" class="!py-2">@lang('messages.search')</x-brand-button>
                </div>
            </form>

            @if ($roles->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.schedule')</th>
                            <th scope="col">@lang('messages.custom_domain')</th>
                            <th scope="col">@lang('messages.custom_domain_mode')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                        @php
                            $direct = $role->custom_domain_mode === 'direct';
                            $doPhase = $direct && $role->custom_domain_host ? ($doStatuses[$role->custom_domain_host]['phase'] ?? null) : null;
                            $domainTone = ['active' => 'is-on', 'pending' => 'is-warn', 'failed' => 'is-bad'][$role->custom_domain_status] ?? null;
                            $domainHost = $role->custom_domain_host ?? parse_url($role->custom_domain, PHP_URL_HOST);
                        @endphp
                        <tr>
                            <td class="c-main">
                                <span class="c-strong"><bdi>{{ $role->name }}</bdi></span>
                                <span class="c-sub" dir="ltr">{{ $role->subdomain }}</span>
                            </td>
                            {{-- The address under the host only when it says more than the host does
                                 (a redirect to a page of another site). --}}
                            <td class="c-wrap">
                                <span class="domain-host" dir="ltr">{{ $domainHost }}</span>
                                @if (rtrim((string) $role->custom_domain, '/') !== 'https://'.$domainHost)
                                <span class="c-sub" dir="ltr">{{ $role->custom_domain }}</span>
                                @endif
                            </td>
                            <td><span class="event-chip">{{ $direct ? __('messages.custom_domain_mode_direct') : __('messages.custom_domain_mode_redirect') }}</span></td>
                            {{-- Only a direct domain has a setup to be in; a redirect just points. What
                                 DigitalOcean says of it stands under what the app believes, where it was
                                 a column that was empty on every row but these. --}}
                            <td class="domain-state">@if ($direct && ($domainTone || $doPhase))
                                @if ($domainTone)
                                <span class="event-status {{ $domainTone }}">{{ __('messages.domain_'.$role->custom_domain_status) }}</span>
                                @endif
                                @if ($role->custom_domain_status === 'failed' && $role->custom_domain_error)
                                <span class="c-sub" title="{{ $role->custom_domain_error }}">{{ \Illuminate\Support\Str::limit($role->custom_domain_error, 90) }}</span>
                                @endif
                                @if ($doPhase)
                                <span class="c-sub">@lang('messages.do_status'): <span class="c-mono" dir="ltr">{{ $doPhase }}</span></span>
                                @endif
                            @endif</td>
                            <td class="c-actions">
                                @if ($direct)
                                <form method="POST" action="{{ route('admin.domains.reprovision', $role) }}">
                                    @csrf
                                    <button type="submit" class="event-link" data-confirm="{{ __('messages.reprovision_confirm') }}">@lang('messages.reprovision')</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.domains.remove', $role) }}">
                                    @csrf
                                    <button type="submit" class="event-link is-danger" data-confirm="{{ __('messages.domain_remove_confirm') }}">@lang('messages.remove')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            @elseif ($filtered || $roles->currentPage() > 1)
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_results_found')" icon="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z">
                    <x-secondary-link :href="route('admin.domains')">@lang('messages.clear_filters')</x-secondary-link>
                </x-page-empty>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_custom_domains')" :text="__('messages.no_custom_domains_text')"
                    icon="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253" />
            </div>
            @endif

            {{-- Outside the list's own branch: after the last rows of the last page are removed
                 the page is past the end, and the way back to page one must still be here. --}}
            @if ($roles->hasPages())
            <div class="page-pager">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
        <style {!! nonce_attr() !!}>
            /* Why a domain's setup failed runs to a sentence: it gets room to be read in. Left to
               the table it was given a word a line, since every word of it may break. */
            @media (min-width: 640px) {
              .page-table .domain-state {
                min-width: 13rem;
                max-width: 22rem;
              }
              .page-table .domain-host {
                white-space: nowrap;
              }
            }
        </style>
    </x-slot>

    @include('admin.partials._subdomain-autocomplete')
</x-app-admin-layout>
