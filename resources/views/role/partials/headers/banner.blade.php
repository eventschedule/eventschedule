{{--
    The banner header: a card on the owner's page, drawn once for every width (it used to be two
    hand-copied bodies, one for a phone and one for a laptop).

    Top to bottom: the schedule's picture, or a wash of its colour where it has none; the logo
    with the one main button and Share beside it; the name; a line of facts with the socials and
    the list's tools at its far end; the description folded to its first lines; and a second
    row for whatever else a visitor may do. The look is partials/guest-kit-styles (.gk-head*).

    Expects from the parent scope: $role, $isRtl, $accentColor, $contrastColor, $event,
    $hasHeaderImage, $logoWallRoles, $galleryImages, $upcoming, $headActions.
--}}
@php
    $hasEmail = $role->email && $role->show_email;
    $hasPhone = $role->showsPhone();
    // Every owner-typed href goes through UrlUtils::safeHref(): a javascript: value would run on
    // this page. Without a safe link the icon is left out.
    $websiteHref = \App\Utils\UrlUtils::safeHref($role->website);
    $hasWebsite = $websiteHref !== null;
    $hasSocial = $role->social_links && $role->social_links != '[]';
    $hasPayment = $role->payment_links && $role->payment_links != '[]';

    $headWall = $role->header_image === 'logos' && ($logoWallRoles ?? collect())->isNotEmpty();
    $headPicture = $hasHeaderImage && ! $headWall;
    $headName = $role->translatedName();
    // A venue's place is its address, as before. An act or a curator says where it is based.
    $headPlace = trim((string) ($role->isVenue() ? $role->shortAddress() : $role->translatedCity()));
    $headUpcoming = \App\Utils\GuestHeader::upcomingFact($role, $event ? 0 : count($upcoming ?? []));
    $headGallery = ($galleryImages ?? collect());
    // The second row: what else a visitor may do. A member's Manage joins it on a phone, so for
    // a member the row is always drawn and, where Manage is all it holds, shown on a phone only.
    $headRest = $headActions['gift'] || $headActions['submit'] || ($headActions['follow'] && $headActions['main'] !== 'follow');
@endphp
        <div id="gp-header"
          class="gk-head {{ $headPicture ? 'gk-head-pictured' : '' }} {{ $headWall ? 'gk-head-walled' : '' }} {{ $role->profile_image_url ? 'gk-head-logoed' : '' }} mb-0 transition-[max-width] duration-300 ease-in-out mx-auto"
          data-view-width
          style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}"
        >
          <div id="gp-header-image" class="gk-head-stage">
            @if ($headWall)
            {{-- Extra bottom padding when a profile image exists: it stands on this block's
                 lower edge and must not cover the last row. --}}
            <div id="gp-logo-wall" data-logo-wall role="group" aria-label="{{ $role->isVenue() ? __('messages.talents') : __('messages.venues') }}"
                 class="px-4 pt-4 sm:px-6 sm:pt-6 {{ $role->profile_image_url ? 'pb-20' : 'pb-4 sm:pb-6' }} {{ $isRtl ? 'rtl' : '' }}">
              <div class="mx-auto max-w-5xl flex flex-wrap justify-center gap-2 sm:gap-3">
                @foreach ($logoWallRoles as $wallRole)
                  @php $tileVisibility = $loop->index >= 16 ? 'hidden sm:flex' : 'flex'; @endphp
                  @if ($wallRole->isClaimed())
                  <a href="{{ route('role.view_guest', ['subdomain' => $wallRole->subdomain]) }}"
                     class="social-tooltip {{ $tileVisibility }} h-16 w-16 sm:h-20 sm:w-20 lg:h-24 lg:w-24 items-center justify-center rounded-lg bg-white border border-gray-200 dark:border-gray-600 p-2 shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                     data-tooltip="{{ $wallRole->translatedName() }}">
                  @else
                  <div class="social-tooltip {{ $tileVisibility }} h-16 w-16 sm:h-20 sm:w-20 lg:h-24 lg:w-24 items-center justify-center rounded-lg bg-white border border-gray-200 dark:border-gray-600 p-2"
                       data-tooltip="{{ $wallRole->translatedName() }}">
                  @endif
                    <img
                      class="max-h-full max-w-full object-contain rounded"
                      src="{{ $wallRole->profile_image_url }}"
                      alt="{{ $wallRole->translatedName() }}"
                      loading="lazy"
                      decoding="async"
                    />
                  @if ($wallRole->isClaimed())
                  </a>
                  @else
                  </div>
                  @endif
                @endforeach
              </div>
            </div>
            @elseif ($headPicture && $role->header_image)
            {{-- width and height give the box its shape before the file arrives, so nothing
                 below it jumps: every built-in header is 1536x768. fetchpriority="high" only
                 when the background is not an image - that one is the layout's high-priority
                 preload, and a page gets one. --}}
            <picture>
              <source srcset="{{ asset('images/headers') }}/{{ $role->header_image }}.webp" type="image/webp">
              <img
                class="gk-head-picture"
                src="{{ asset('images/headers') }}/{{ $role->header_image }}.png"
                width="{{ \App\Models\Role::BUILT_IN_HEADER_SIZE[0] }}" height="{{ \App\Models\Role::BUILT_IN_HEADER_SIZE[1] }}"
                @if (! $role->backgroundImageUrl()) fetchpriority="high" @endif
                alt="{{ $headName }}"
              />
            </picture>
            @elseif ($headPicture)
            @php
                // The owner's upload: its 960 derivative as the src, the 960 and 1920 for the
                // browser to choose between, and the original's recorded size for the box's shape.
                // Until the derivatives exist all three fall away and this is the plain original.
                // So is an animated upload (pageWidth), whose derivatives are stills.
                $headerSrcset = $role->imageVariantSrcset('header', pageWidth: true);
                $headerSize = $role->imageSourceDimensions('header');
            @endphp
            <img
              class="gk-head-picture"
              src="{{ $role->headerImageUrl(960, pageWidth: true) }}"
              @if ($headerSrcset) srcset="{{ $headerSrcset }}" sizes="(min-width: 1536px) 1496px, calc(100vw - 40px)" @endif
              @if ($headerSize) width="{{ $headerSize[0] }}" height="{{ $headerSize[1] }}" @endif
              @if (! $role->backgroundImageUrl()) fetchpriority="high" @endif
              alt="{{ $headName }}"
            />
            @endif
          </div>
          {{-- v-pre: nothing here is a Vue template, and the description is the owner's own HTML. --}}
          <header id="schedule-header" class="gk-head-body {{ $isRtl ? 'rtl' : '' }}" v-pre>
            {{-- The two ids owners were given for the header's contents, from when it was drawn
                 twice. Both now name this one body, so a rule written for either still lands. --}}
            <div id="gp-header-body-desktop" class="gk-head-wrap"><div id="gp-header-body-mobile" class="gk-head-wrap">
            <div class="gk-head-top">
              @if ($role->profile_image_url)
              <div id="gp-profile-image" class="gk-head-logo">
                <img
                  src="{{ $role->getProfileImageUrl(\App\Utils\ImageUtils::VARIANT_WIDTH) }}"
                  width="120" height="120"
                  alt="{{ $headName }}"
                />
              </div>
              @endif
              <div class="gk-head-actions">
                @include('role.partials.headers.action-buttons', ['actionPart' => 'main'])
                <button type="button" class="gk-btn gk-btn-secondary gk-btn-icon gk-head-share" data-head-share data-said="{{ __('messages.copied') }}" aria-label="{{ __('messages.share') }}" title="{{ __('messages.share') }}">
                  <svg class="gk-head-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 15V3.5"/><path d="M7.5 7.5L12 3l4.5 4.5"/><path d="M5 12v6.5A1.5 1.5 0 006.5 20h11a1.5 1.5 0 001.5-1.5V12"/></svg>
                  <span class="gk-head-said" role="status" aria-live="polite"></span>
                </button>
              </div>
            </div>

            {{-- The page's one <h1>. --}}
            <h1 class="gk-head-name {{ \App\Utils\GuestHeader::nameStep($headName) }}" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
              {!! str_replace(' , ', '<br>', e($headName)) !!}
            </h1>
            @if ($role->translatedShortDescription())
            <p class="gk-head-tagline">{{ $role->translatedShortDescription() }}</p>
            @endif

            <div class="gk-head-meta">
              @if ($headPlace !== '' || $headUpcoming || $headGallery->isNotEmpty() || $headActions['following'])
              <ul class="gk-head-facts">
                @if ($headPlace !== '')
                <li class="gk-head-fact">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-6.2 7-11.5A7 7 0 005 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                  @if ($role->isVenue())
                  <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($role->bestAddress()) }}" target="_blank" rel="noopener noreferrer nofollow">{{ $headPlace }}</a>
                  @else
                  <span>{{ $headPlace }}</span>
                  @endif
                </li>
                @endif
                @if ($headUpcoming)
                <li class="gk-head-fact">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
                  {{-- The figure in bold where the sentence opens with it. Escaped first. --}}
                  <span>{!! preg_replace('/^(\d+\+?)(?=\s)/u', '<b>$1</b>', e($headUpcoming)) !!}</span>
                </li>
                @endif
                {{-- The gallery sits below the calendar; this puts it one tap from the top. --}}
                @if ($headGallery->isNotEmpty())
                <li class="gk-head-fact">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="8.5" cy="10" r="1.6"/><path d="M21 15.5l-5-5L6 19"/></svg>
                  <a href="#gp-gallery" data-lightbox-set="gallery" @if ($headGallery->count() > 1) data-lightbox-grid @else data-lightbox-index="0" @endif>{{ trans_choice('messages.gallery_photo_count', $headGallery->count(), ['count' => $headGallery->count()]) }}</a>
                </li>
                @endif
                {{-- A follower is told so here, where the Follow button used to leave a gap. --}}
                @if ($headActions['following'])
                <li class="gk-head-fact gk-head-following">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                  <span>{{ __('messages.following') }}</span>
                </li>
                @endif
              </ul>
              @endif

              <div class="gk-head-side">
                @if ($hasEmail || $hasPhone || $hasWebsite || $hasSocial || $hasPayment)
                <div class="gk-head-social">
                    @if ($hasEmail)
                    <a href="mailto:{{ $role->email }}" class="gk-head-social-link social-tooltip" data-tooltip="Email: {{ $role->email }}" aria-label="Email: {{ $role->email }}">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"/></svg>
                    </a>
                    @endif
                    @if ($hasPhone)
                    <a href="tel:{{ $role->phone }}" class="gk-head-social-link social-tooltip" data-tooltip="Phone: {{ $role->phone }}" aria-label="Phone: {{ $role->phone }}">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    </a>
                    @endif
                    @if ($hasWebsite)
                    <a href="{{ $websiteHref }}" target="_blank" rel="noopener noreferrer nofollow" class="gk-head-social-link social-tooltip" data-tooltip="Website: {{ App\Utils\UrlUtils::clean($role->website) }}" aria-label="Website: {{ App\Utils\UrlUtils::clean($role->website) }}">
                        <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.52 2 22 6.48 22 12C22 17.52 17.52 22 12 22C6.48 22 2 17.52 2 12C2 6.48 6.48 2 12 2ZM11 19.93C7.05 19.44 4 16.08 4 12C4 11.38 4.08 10.79 4.21 10.21L9 15V16C9 17.1 9.9 18 11 18V19.93ZM17.9 17.39C17.64 16.58 16.9 16 16 16H15V13C15 12.45 14.55 12 14 12H8V10H10C10.55 10 11 9.55 11 9V7H13C14.1 7 15 6.1 15 5V4.59C17.93 5.78 20 8.65 20 12C20 14.08 19.2 15.97 17.9 17.39Z"/></svg>
                    </a>
                    @endif
                    @if ($hasSocial)
                        @foreach ($role->decodeLinks('social_links') as $link)
                        @if ($gpLinkHref = $role->socialLinkHref($link, $loop->index))
                        <a href="{{ $gpLinkHref }}" target="_blank" rel="noopener noreferrer nofollow" class="gk-head-social-link social-tooltip" data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}" aria-label="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                            <x-url-icon class="w-5 h-5" color="currentColor">
                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                            </x-url-icon>
                        </a>
                        @endif
                        @endforeach
                    @endif
                    @if ($hasPayment)
                        @foreach ($role->decodeLinks('payment_links') as $link)
                        @if ($gpPaymentHref = \App\Utils\UrlUtils::safeHref($link->url))
                        <a href="{{ $gpPaymentHref }}" target="_blank" rel="noopener noreferrer nofollow" class="gk-head-social-link social-tooltip" data-tooltip="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}" aria-label="{{ App\Utils\UrlUtils::getBrand($link->url) }}: {{ App\Utils\UrlUtils::getHandle($link->url) }}">
                            <x-url-icon class="w-5 h-5" color="currentColor">
                                {{ \App\Utils\UrlUtils::clean($link->url) }}
                            </x-url-icon>
                        </a>
                        @endif
                        @endforeach
                    @endif
                </div>
                @endif
                @include('role.partials.headers.tools')
              </div>
            </div>

            @if ($role->translatedDescription())
            {{-- Cut by LINES, by the browser, and only when it overflows: the clamp is the
                 stylesheet's, so the full text never flashes, and the button under it is shown
                 by the script below only where there is more to read. demoteH1(): the
                 schedule's name above is the page's one <h1>. --}}
            <div class="gk-head-about" dir="{{ content_dir($role, false, $role->translatedDescription()) }}" data-head-about>
              <div class="gk-head-about-text custom-content">
                {!! \App\Utils\UrlUtils::convertUrlsToLinks(\App\Utils\MarkdownUtils::demoteH1($role->translatedDescription())) !!}
              </div>
              <button type="button" class="gk-head-more" hidden aria-expanded="false" data-more="{{ $role->customLabel('show_more') }}" data-less="{{ $role->customLabel('show_less') }}">
                <span>{{ $role->customLabel('show_more') }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
              </button>
            </div>
            @endif

            @if ($headRest || $headActions['manage'])
            <div class="gk-head-more-actions{{ $headRest ? '' : ' gk-head-more-actions-phone' }}">
              @include('role.partials.headers.action-buttons', ['actionPart' => 'rest'])
            </div>
            @endif
            </div></div>
          </header>
        </div>
        <script {!! nonce_attr() !!}>
        (function () {
            var head = document.getElementById('gp-header');
            if (! head) return;

            // More, where the folded description is cut or leaves something out.
            var about = head.querySelector('[data-head-about]');
            if (about) {
                var text = about.querySelector('.gk-head-about-text');
                var more = about.querySelector('.gk-head-more');
                var measure = function () {
                    if (about.hasAttribute('data-open')) return;
                    more.hidden = ! (text.scrollHeight > text.clientHeight + 2 || text.querySelector('h2, h3, h4, h5, h6, hr, img, table, pre'));
                };
                measure();
                // The owner's typeface arrives after the first paint and can change where the cut falls.
                if (document.fonts && document.fonts.ready) { document.fonts.ready.then(measure); }
                window.addEventListener('resize', measure);
                more.addEventListener('click', function () {
                    var open = about.toggleAttribute('data-open');
                    more.setAttribute('aria-expanded', open ? 'true' : 'false');
                    more.querySelector('span').textContent = open ? more.dataset.less : more.dataset.more;
                });
            }

            // Share: the phone's own sheet where there is one, a copied link elsewhere.
            var share = head.querySelector('[data-head-share]');
            if (share) {
                var said = share.querySelector('.gk-head-said');
                var timer = null;
                share.addEventListener('click', function () {
                    var url = window.location.href.split('#')[0];
                    if (navigator.share && window.matchMedia('(pointer: coarse)').matches) {
                        navigator.share({ title: document.title, url: url }).catch(function () {});
                        return;
                    }
                    var done = function () {
                        said.textContent = share.dataset.said;
                        clearTimeout(timer);
                        timer = setTimeout(function () { said.textContent = ''; }, 1800);
                    };
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(done).catch(function () {});
                    }
                });
            }
        })();
        </script>
