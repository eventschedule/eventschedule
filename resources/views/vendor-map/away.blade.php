{{--
    "Not coming" from the approval mail (VendorSpotController::awayForm()). No account: the link
    carries the stall's id and a hash of what that row says. This page only shows; the form POSTs.
    Plain HTML, no Vue, so it works on the oldest phone and with a weak signal.
--}}
<x-app-layout :title="$role->translatedName()">
    <x-slot name="meta">
        @include('vendor-map.claim-meta', ['ogTitle' => null, 'ogImage' => null])
    </x-slot>

    <x-slot name="head">
        @include('vendor-map.claim-styles')
    </x-slot>

    <main id="main-content" class="vmc">
        <div class="vmc-wrap">
            <p class="vmc-kicker"><bdi>{{ $role->translatedName() }}</bdi></p>
            <h1 class="vmc-title">{{ __('messages.vm_away_title') }}</h1>
            <p class="vmc-lead"><bdi>{{ $row->displayName() }}</bdi> · {{ $spotLabel }}</p>

            @if (session('vm_status') === 'absent')
                <p class="vmc-note vmc-note-ok" role="status">{{ __('messages.vm_away_done_absent', ['name' => $ownerFirstName]) }}</p>
            @elseif (session('vm_status') === 'present')
                <p class="vmc-note vmc-note-ok" role="status">{{ __('messages.vm_away_done_present') }}</p>
            @elseif (session('error'))
                <p class="vmc-note vmc-note-err" role="alert">{{ session('error') }}</p>
            @endif

            @if (! count($dates))
                <p class="vmc-note">{{ __('messages.vm_claim_no_dates') }}</p>
            @else
            <form method="POST" action="{{ $action }}" class="vmc-stack" style="margin-top: 16px">
                @csrf
                <x-honeypot />
                <p class="vmc-muted" style="margin: 0">{{ __('messages.vm_away_lead', ['name' => $ownerFirstName]) }}</p>
                @foreach ($dates as $date)
                    @if ($date['absent'])
                        <div class="vmc-check vmc-check-on">
                            <span style="flex: 1">
                                <strong>{{ $date['label'] }}</strong><br>
                                <span class="vmc-muted">{{ __('messages.vm_claim_not_coming') }}</span>
                            </span>
                            <button type="submit" name="come" value="{{ $date['date'] }}" class="vmc-btn vmc-btn-quiet" style="width: auto">
                                {{ __('messages.vm_claim_coming_after_all') }}
                            </button>
                        </div>
                    @else
                        <label class="vmc-check">
                            <input type="checkbox" name="dates[]" value="{{ $date['date'] }}">
                            <span>{{ $date['label'] }}</span>
                        </label>
                    @endif
                @endforeach
                @if (collect($dates)->contains('absent', false))
                <button type="submit" class="vmc-btn vmc-btn-primary">{{ __('messages.vm_away_submit', ['name' => $ownerFirstName]) }}</button>
                @endif
            </form>
            @endif
        </div>
    </main>
</x-app-layout>
