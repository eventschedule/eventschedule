{{--
    Rendered by App\View\Components\DocsPage.

    Slots:
      toc     - the "On this page" rail (raw markup; use <x-doc-nav-link> and
                <x-doc-nav-group>). Kept as a slot rather than an array prop
                because 12 pages nest accordion groups, the API reference puts
                data-search on 31 of its links, and some pages indent
                second-level entries - an array schema covering all of that
                would be worse than the markup it replaced.
      schema  - extra JSON-LD (FAQPage, HowTo) appended after the TechArticle
      cta     - full override of the tail panel
      default - the page body: the existing <section class="doc-section"> blocks

    The boolean props are named withToc / withPager / withCta so they cannot
    collide with the `toc` and `cta` slot variables.
--}}
@php
    $hasToc = $withToc && isset($toc) && trim((string) $toc) !== '';

    // The reference layout (API docs) keeps its endpoint/code split inside the
    // content column, so there is no room for a third rail. Its page TOC moves
    // under the site nav in the LEFT rail instead. The alternative - a stubby
    // 180px rail holding only the four group links - would make this the one
    // page whose rail looks different from every other, which is exactly the
    // consistency this shell exists to buy.
    $tocInLeftRail = $hasToc && $layoutVariant() === 'reference';
    $hasRightRail = $hasToc && ! $tocInLeftRail;
@endphp

<x-marketing-layout :docs="true" :title="$pageTitle()" :description="$metaDescription()">
    <x-slot name="breadcrumbTitle">{{ $page['title'] }}</x-slot>

    {{-- Only a page that declares one; every other doc page keeps the layout's self-canonical. --}}
    @if ($canonicalUrl())
        <x-slot name="canonical">{{ $canonicalUrl() }}</x-slot>
    @endif

    <x-slot name="structuredData">
        <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! json_encode($structuredData(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
        {{ $schema ?? '' }}
    </x-slot>

    <div class="doc-accent-{{ $accent() }} bg-white dark:bg-[#0a0a0f]">
        <x-docs.icon-sprite />

        <x-docs.hero
            :accent="$accent()"
            :icon="$page['icon']"
            :title="$headingText()"
            :emphasis="$emphasis"
            :lede="$ledeText()"
            :eyebrow="$eyebrow"
            :plan="$plan"
            :section="$crumb['section']"
            :sectionTitle="$crumb['sectionTitle']"
            :sectionRoute="$crumb['sectionRoute']" />

        {{-- Mobile bar. Sticky under the header rather than fixed to the
             bottom: the accessibility widget owns bottom-right at
             z-index 2147483647, and a bottom bar would sit under it. --}}
        <div class="doc-mobilebar">
            <button type="button"
                    class="doc-mobilebar-btn min-w-0 flex-1"
                    data-docs-drawer-toggle="docs-nav"
                    aria-expanded="false"
                    aria-controls="docs-nav">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="flex-shrink-0">Docs</span>
                <span class="flex-shrink-0 text-gray-400" aria-hidden="true">/</span>
                <span class="font-normal text-gray-500 dark:text-gray-400">{{ $page['title'] }}</span>
            </button>

            {{-- Only when the TOC is its own drawer. In the reference layout it
                 sits inside the nav drawer, which the button on the left opens. --}}
            @if ($hasRightRail)
                <button type="button"
                        class="doc-mobilebar-btn flex-shrink-0"
                        data-docs-drawer-toggle="docs-toc"
                        aria-expanded="false"
                        aria-controls="docs-toc">
                    <span>On this page</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            @endif
        </div>

        <div id="docs-drawer-backdrop" class="doc-drawer-backdrop lg:hidden"></div>

        <section class="py-10">
            <div class="mx-auto max-w-[92rem] px-4 sm:px-6 lg:px-8">
                <div @class(['doc-shell', 'doc-shell--reference' => $layoutVariant() === 'reference'])>

                    <aside id="docs-nav" class="doc-rail doc-rail-left doc-drawer">
                        {{-- On the reference layout the page's own TOC goes FIRST.
                             An API reader navigates by endpoint, so burying that
                             list under 34 site-nav links would put the rail's most
                             useful content out of reach. --}}
                        @if ($tocInLeftRail)
                            <nav id="docs-toc" class="mb-6 border-b border-[var(--doc-border-soft)] pb-5" aria-labelledby="doc-toc-title">
                                <p id="doc-toc-title" class="doc-toc-title">On this page</p>
                                <div class="doc-toc-list">
                                    <span class="doc-toc-rail" aria-hidden="true"></span>
                                    {{ $toc }}
                                </div>
                            </nav>
                        @endif

                        <x-docs.nav :current="$page" :searchFirst="! $tocInLeftRail" />
                    </aside>

                    <main class="doc-main min-w-0">
                        <div class="doc-prose">
                            {{ $slot }}
                        </div>

                        @if ($withPager)
                            <x-docs.pager :prev="$siblings['prev']" :next="$siblings['next']" />
                        @endif

                        @if ($withCta)
                            @isset($cta)
                                {{ $cta }}
                            @else
                                <x-docs.cta :group="$page['group']" />
                            @endisset
                        @endif
                    </main>

                    @if ($hasRightRail)
                        <aside id="docs-toc" class="doc-rail doc-rail-right doc-drawer">
                            <nav aria-labelledby="doc-toc-title">
                                <p id="doc-toc-title" class="doc-toc-title">On this page</p>
                                <div class="doc-toc-list">
                                    <span class="doc-toc-rail" aria-hidden="true"></span>
                                    {{ $toc }}
                                </div>
                            </nav>
                        </aside>
                    @endif

                </div>
            </div>
        </section>
    </div>
</x-marketing-layout>
