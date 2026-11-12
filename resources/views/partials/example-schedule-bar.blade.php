{{-- The bar at the top of an example schedule's pages: the demos /examples links to, and the
     Springfield town DemoService seeds. A visitor who opens one from the marketing site had no way
     back to sign-up but the free plan's credit chip, which goes to the homepage.

     Role::isDemoContent() is the question here, the one /search and discovery ask ("is this content
     we made to show off"), never is_demo_role(): the hand-made examples are ordinary schedules on
     ordinary accounts, and only their contact address (DemoService::DEMO_EMAIL) says what they
     are. Only on the nexus, where those schedules live, and never in an embed or a graphic: an
     embed is somebody's own website. Nothing here is stored, so the demo's hourly reset does not
     touch it. The ref rides into users.arrival_intent as `example`.

     The colours are the guest kit's tokens, so it reads on every theme, in light and dark; the
     button is the kit's, in the schedule's own accent. --}}
@php
    $showExampleBar = config('app.is_nexus')
        && ! request()->embed
        && ! request()->graphic
        && $role->exists
        && $role->isDemoContent();
@endphp
@if ($showExampleBar)
    @php
        $exampleSignUp = app_url('/sign_up?'.http_build_query(array_filter([
            'type' => in_array($role->type, ['talent', 'venue', 'curator'], true) ? $role->type : null,
            'ref' => 'example',
        ])));
    @endphp
    <style {!! nonce_attr() !!}>
        .gp-example-bar { position: relative; z-index: 20; background: var(--gk-panel); border-bottom: 1px solid var(--gk-line); color: var(--gk-ink-2); -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); }
        .gp-example-bar-inner { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: .375rem 1rem; padding-top: .5rem; padding-bottom: .5rem; text-align: center; }
        .gp-example-bar p { margin: 0; font-size: .90625rem; line-height: 1.35; }
        .gp-example-bar .gk-btn { min-height: 2.25rem; padding: 0 .875rem; font-size: .875rem; }
        .gp-example-bar svg { width: 1rem; height: 1rem; flex: none; }
        [dir="rtl"] .gp-example-bar svg { transform: scaleX(-1); }
        @media print { .gp-example-bar { display: none; } }
    </style>
    <div class="gp-example-bar" data-example-bar>
        <div class="container mx-auto px-5 gp-example-bar-inner">
            <p>{{ __('messages.example_schedule_bar') }}</p>
            <x-guest.button :href="$exampleSignUp" size="sm">
                {{ __('messages.example_schedule_bar_cta') }}
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
            </x-guest.button>
        </div>
    </div>
@endif
