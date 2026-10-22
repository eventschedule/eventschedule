{{-- The page's background layer (partials/guest-background-css draws on it): fixed to the window,
     behind everything, in the root stacking context, and never a filter or transform on html or
     body. Aurora's lights are drawn inside it (partials/aurora-styles). --}}
<div class="gp-bg" aria-hidden="true">
    @if ($bgAurora)
        @include('partials.aurora-layer', ['aurora' => $bgAurora, 'still' => request()->embed || request()->graphic])
    @endif
</div>
