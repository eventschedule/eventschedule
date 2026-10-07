{{-- The platform admin's navigation: five tabs, and under them every page of the group you are in.

     The three groups (Insights, Manage, System) used to be menus hanging from their tab: on any
     page inside one, nothing on screen said WHICH page it was (only "System" was lit), and a
     sibling was two presses away behind a menu. Now a group's tab leads to its first page and the
     second row names them all, the current one pressed in. A phone gets the whole section as one
     dropdown, grouped, because five tabs and nine pages do not fit across it.

     ONE list ($adminNav) feeds the strip, the second row and the dropdown: a page added to one
     alone is missing for half the visitors. It also carries the page's h1, which no admin page
     has of its own. Plain DOM script: the menus were the last Alpine on these pages.

     $active is the page's key, passed by each page as before. --}}
@props(['active' => 'dashboard'])

@php
    // Pending-work badges, shared by the AdminAlertService composer. Queues sit
    // unnoticed without them, and operators then conclude the feature is broken.
    // Keyed ['nav' => group totals, 'tab' => per-page counts]; every count is
    // already gated by install type, so a section that cannot exist here reads 0.
    $navBadges = $adminAlertBadges['nav'] ?? [];
    $tabBadges = $adminAlertBadges['tab'] ?? [];

    // Schedules is on every install (it is the only way back from a takedown, and the takedown
    // itself is registered on selfhost too); domains and referrals are hosted-only platform
    // machinery; the blog is the marketing site's, so nexus-only. App update is selfhost only:
    // eventschedule.com deploys from git, so there is nothing there to update.
    $adminNav = [
        'dashboard' => ['label' => __('messages.dashboard'), 'href' => route('admin.dashboard')],
        // A plain tab, label only: badges mean "needs attention", so a live count here would
        // read as an alert.
        'realtime' => ['label' => __('messages.realtime'), 'href' => route('admin.realtime')],
        'insights' => ['label' => __('messages.insights'), 'pages' => array_filter([
            'users' => ['label' => __('messages.users'), 'href' => route('admin.users')],
            'revenue' => ['label' => __('messages.revenue'), 'href' => route('admin.revenue')],
            'analytics' => ['label' => __('messages.analytics'), 'href' => route('admin.analytics')],
            'usage' => ['label' => __('messages.usage'), 'href' => route('admin.usage')],
            'growth' => config('app.hosted') ? ['label' => __('messages.growth'), 'href' => route('admin.growth')] : null,
        ])],
        'manage' => ['label' => __('messages.manage'), 'pages' => array_filter([
            'boost' => ['label' => __('messages.boost'), 'href' => route('admin.boost')],
            'schedules' => ['label' => __('messages.schedules'), 'href' => route('admin.schedules')],
            'domains' => config('app.hosted') ? ['label' => __('messages.domains'), 'href' => route('admin.domains')] : null,
            'referrals' => config('app.hosted') ? ['label' => __('messages.referrals'), 'href' => route('admin.referrals')] : null,
            'newsletters' => ['label' => __('messages.newsletters'), 'href' => route('admin.newsletters.index')],
            'blog' => config('app.is_nexus') ? ['label' => __('messages.blog'), 'href' => route('blog.admin.index')] : null,
        ])],
        'system' => ['label' => __('messages.system'), 'pages' => array_filter([
            'audit-log' => ['label' => __('messages.audit_log'), 'href' => route('admin.audit_log')],
            'queue' => ['label' => __('messages.queue'), 'href' => route('admin.queue')],
            'logs' => ['label' => __('messages.logs'), 'href' => route('admin.logs')],
            'app-update' => ! config('app.is_nexus') ? ['label' => __('messages.app_update'), 'href' => route('admin.app_update')] : null,
            'settings' => ['label' => __('messages.settings'), 'href' => route('admin.settings')],
            'translations' => ['label' => __('messages.translations'), 'href' => route('admin.translations')],
            'legal' => ['label' => __('messages.legal_pages'), 'href' => route('admin.legal')],
            'federation' => config('app.is_nexus') ? ['label' => __('messages.federation'), 'href' => route('admin.federation')] : null,
            'support' => config('app.hosted') ? ['label' => __('messages.support'), 'href' => route('admin.support')] : null,
        ])],
    ];

    // Which group the page is in, and the page's own name for the h1.
    $activeGroup = $active;
    $activeLabel = $adminNav[$active]['label'] ?? null;
    foreach ($adminNav as $groupKey => $group) {
        if (isset($group['pages'][$active])) {
            $activeGroup = $groupKey;
            $activeLabel = $group['pages'][$active]['label'];
        }
    }
    $subPages = $adminNav[$activeGroup]['pages'] ?? [];
@endphp

<h1 class="sr-only">{{ __('messages.admin') }}{{ $activeLabel ? ': '.$activeLabel : '' }}</h1>

<div class="admin-nav">
    <div class="admin-nav-phone md:hidden">
        <label for="admin-nav-select" class="sr-only">{{ __('messages.select_a_tab') }}</label>
        <select id="admin-nav-select" autocomplete="off" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            @if (! $activeLabel)
            <option value="" selected disabled>{{ __('messages.select_a_tab') }}</option>
            @endif
            @foreach ($adminNav as $groupKey => $group)
                @if (isset($group['pages']))
                <optgroup label="{{ $group['label'] }}">
                    @foreach ($group['pages'] as $pageKey => $page)
                    @php $count = (int) ($tabBadges[$pageKey]['count'] ?? 0); @endphp
                    <option value="{{ $page['href'] }}" @selected($active === $pageKey)>{{ $page['label'] }}{{ $count > 0 ? ' ('.number_format($count).')' : '' }}</option>
                    @endforeach
                </optgroup>
                @else
                <option value="{{ $group['href'] }}" @selected($active === $groupKey)>{{ $group['label'] }}</option>
                @endif
            @endforeach
        </select>
    </div>

    <div class="ap-tabs-wrap admin-nav-tabs hidden md:block" id="admin-nav-wrap">
        <nav class="ap-tabs" id="admin-nav" aria-label="{{ __('messages.admin') }}">
            @foreach ($adminNav as $groupKey => $group)
            <a href="{{ $group['href'] ?? reset($group['pages'])['href'] }}" class="ap-tab" data-admin-group="{{ $groupKey }}"
                @if ($activeGroup === $groupKey) aria-current="{{ isset($group['pages']) ? 'true' : 'page' }}" @endif>
                {{ $group['label'] }}
                <x-nav-badge :badge="$navBadges[$groupKey] ?? null" />
            </a>
            @endforeach
        </nav>
    </div>

    <button type="button" id="admin-nav-refresh-btn" class="page-tool admin-nav-refresh">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>{{ __('messages.refresh') }}</span>
    </button>
</div>

@if ($subPages)
<nav class="ap-subtabs admin-nav-pages" aria-label="{{ $adminNav[$activeGroup]['label'] }}">
    @foreach ($subPages as $pageKey => $page)
    <a href="{{ $page['href'] }}" class="ap-subtab" @if ($active === $pageKey) aria-current="page" @endif>
        {{ $page['label'] }}
        <x-nav-badge :badge="$tabBadges[$pageKey] ?? null" />
    </a>
    @endforeach
</nav>
@endif

@once
<style {!! nonce_attr() !!}>
    /* The strip and Refresh share a row; the strip's own margins are the row's. */
    .admin-nav {
      position: relative;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }
    .admin-nav-tabs {
      flex: 1 1 auto;
      min-width: 0;
      margin-top: 0;
    }
    .admin-nav-phone {
      flex: 1 1 auto;
      min-width: 0;
      margin: 0 0 1.25rem;
    }
    /* A group's tab is lit while one of its pages is open: aria-current="true" there, where a
       tab that IS the page says "page". */
    .admin-nav .ap-tab[aria-current="true"] {
      box-shadow: inset 0 -2px 0 var(--brand-blue);
      color: var(--brand-blue);
    }
    .admin-nav-refresh {
      flex: none;
      margin-bottom: 1.5rem;
    }
    @media (max-width: 767.98px) {
      /* Refresh is as tall as the dropdown beside it. */
      .admin-nav {
        align-items: stretch;
      }
      /* The dropdown above already lists every page, grouped. Said here and not with a utility
         class: the kit's own display rule for the row comes later in the page and would win. */
      .admin-nav-pages {
        display: none;
      }
      .admin-nav-refresh {
        margin-bottom: 1.25rem;
        padding: 0.6875rem;
      }
      .admin-nav-refresh span {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
      }
    }
</style>
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('admin-nav-refresh-btn').addEventListener('click', function() {
        window.location.reload();
    });

    // Bring the tab you are on into view, and say when there are more to either side.
    var wrap = document.getElementById('admin-nav-wrap');
    var tabs = document.getElementById('admin-nav');
    if (wrap && tabs) {
        var paint = function() {
            var start = Math.abs(tabs.scrollLeft);
            wrap.classList.toggle('more-before', start > 4);
            wrap.classList.toggle('more-after', start + tabs.clientWidth < tabs.scrollWidth - 4);
        };
        var current = tabs.querySelector('[aria-current]');
        var showCurrent = function() {
            if (current) {
                tabs.scrollLeft = current.offsetLeft - (tabs.clientWidth - current.offsetWidth) / 2;
            }
            paint();
        };
        showCurrent();
        window.addEventListener('load', showCurrent);
        tabs.addEventListener('scroll', paint, { passive: true });
        window.addEventListener('resize', paint);
    }

    var select = document.getElementById('admin-nav-select');
    if (select) {
        var here = select.value;
        select.addEventListener('change', function() {
            if (select.value) {
                window.location.href = select.value;
            }
        });
        // Back brings the page back with the option that was chosen still showing, and choosing
        // it again would fire nothing.
        window.addEventListener('pageshow', function() {
            select.value = here;
        });
    }
});
</script>
@endonce
