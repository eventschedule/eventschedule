{{--
    The slim bar that takes over once the banner header has scrolled away: the logo, the name,
    the main button and a way back to the top.

    Included by role/show-guest as a child of <main>, never inside the header's card: the card
    has a backdrop-filter, which would make it this bar's containing block and un-fix it. It is
    inert until it is shown, so its buttons are not reached by the keyboard while off screen.

    Expects $role, $isRtl, $accentColor, $contrastColor and $headActions.
--}}
<div id="gp-header-bar" class="gk-headbar {{ $isRtl ? 'rtl' : '' }}" inert>
    <div class="container mx-auto px-5">
        <div class="gk-headbar-in mx-auto" data-view-width style="max-width: {{ $role->activeEventLayout() === 'list' ? '56rem' : '200rem' }}">
            @if ($role->profile_image_url)
            <img class="gk-headbar-logo" src="{{ $role->getProfileImageUrl(\App\Utils\ImageUtils::VARIANT_WIDTH) }}" width="36" height="36" alt="" loading="lazy">
            @endif
            <div class="gk-headbar-name" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;"><x-user-text>{{ $role->translatedName() }}</x-user-text></div>
            @include('role.partials.headers.action-buttons', ['actionPart' => 'bar'])
            <button type="button" class="gk-headbar-top" data-head-top aria-label="{{ __('messages.back_to_top') }}" title="{{ __('messages.back_to_top') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
            </button>
        </div>
    </div>
</div>
<script {!! nonce_attr() !!}>
(function () {
    var bar = document.getElementById('gp-header-bar');
    var body = document.getElementById('schedule-header');
    if (! bar || ! body) return;
    var waiting = false;
    // On once the header's own body has left the top of the window.
    var check = function () {
        waiting = false;
        var on = body.getBoundingClientRect().bottom < 0;
        if (on === bar.hasAttribute('data-on')) return;
        bar.toggleAttribute('data-on', on);
        bar.inert = ! on;
    };
    window.addEventListener('scroll', function () {
        if (waiting) return;
        waiting = true;
        window.requestAnimationFrame(check);
    }, { passive: true });
    check();
    bar.querySelector('[data-head-top]').addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    });
})();
</script>
