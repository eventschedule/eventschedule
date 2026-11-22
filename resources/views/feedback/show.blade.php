<x-app-guest-layout :role="$role" :event="$event" :fonts="$fonts" :noIndex="true" :page-title="($event ? $event->translatedName() . ' - ' : '') . __('messages.feedback')">

    @php
        $accentColor = $role->accent_color ?? '#4E81FA';
    @endphp

    @include('partials.guest-form-pages-styles')
    <style {!! nonce_attr() !!}>
        .gk-pg-feedback .star-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 2.75rem; min-height: 2.75rem; padding: .25rem; border-radius: .75rem; color: var(--gk-ink-3); transition: transform var(--gk-swap), background-color var(--gk-swap); }
        .gk-pg-feedback .star-btn:hover { background: var(--gk-well); }
        .gk-pg-feedback .star-btn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
        .gk-pg-feedback .star-icon { width: 2.25rem; height: 2.25rem; }
        .gk-pg-feedback .gk-pg-count { margin-top: .375rem; font-size: .8125rem; color: var(--gk-ink-3); }
        .gk-pg-feedback .gk-pg-req { color: var(--gk-bad); }
        .gk-pg-feedback .gk-pg-opt { font-weight: 400; color: var(--gk-ink-3); }
    </style>

    <div class="gk-stage gk-pg gk-pg-sm gk-pg-feedback" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">

        {{-- Whose event, which event, when: the head of the page, as on the event's own. --}}
        <div class="gk-pg-head">
            @include('role.partials.form-page-who', ['whoRole' => $role, 'whoLink' => $role->getGuestUrl() ?: null])
            <p class="gk-dateline">
                <span>{{ $event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}</span><span class="gk-dateline-dot" aria-hidden="true">·</span><bdi dir="ltr">{{ $event->getStartEndTime($sale->event_date) }}</bdi>
            </p>
            <h1 class="gk-title" dir="auto">{{ $event->name }}</h1>
        </div>

        {{-- Feedback form --}}
        <div class="gk-card gk-band gk-pg-flush mt-8">
            <h2 class="gk-h gk-h-lg">{{ __('messages.feedback_how_was') }}</h2>
            <p class="gk-pg-fine">{{ __('messages.feedback_rate_event') }}</p>

            @if ($role->feedback_public)
            <div class="gk-note mt-5">
                <svg class="gk-note-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="gk-note-body">{{ __('messages.feedback_public_notice') }}</p>
            </div>
            @endif

            @if ($errors->any())
            <div class="gk-note gk-note-bad mt-5" role="alert">
                <div class="gk-note-body">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('feedback.store', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}" class="mt-6 flex flex-col gap-6">
                @csrf
                <x-honeypot />

                {{-- Star rating --}}
                <div>
                    <span class="gk-pg-label" id="star-rating-label">{{ __('messages.rating') }} <span class="gk-pg-req" aria-hidden="true">*</span></span>
                    <div class="flex gap-1" id="star-rating" role="group" aria-labelledby="star-rating-label">
                        @for ($i = 1; $i <= 5; $i++)
                        <button type="button" data-rating="{{ $i }}" class="star-btn"
                            aria-label="{{ $i }} {{ __('messages.stars') }}">
                            <svg class="star-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                        </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}">
                </div>

                {{-- Comment --}}
                <div>
                    <label for="comment" class="gk-pg-label">{{ __('messages.comment') }} <span class="gk-pg-opt">({{ __('messages.optional') }})</span></label>
                    <textarea id="comment" name="comment" rows="4" maxlength="2000" dir="auto"
                        class="gk-pg-input resize-y"
                        placeholder="{{ __('messages.feedback_comment_placeholder') }}">{{ old('comment') }}</textarea>
                    <p class="gk-pg-count"><span id="char-count">0</span>/2000</p>
                </div>

                {{-- Submit --}}
                <button type="submit" id="submit-btn" class="gk-btn gk-btn-primary gk-cta-main gk-pg-cta-wide opacity-50 cursor-not-allowed" disabled>
                    {{ __('messages.feedback_submit') }}
                </button>
            </form>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        (function() {
            const stars = document.querySelectorAll('.star-btn');
            const ratingInput = document.getElementById('rating-input');
            const submitBtn = document.getElementById('submit-btn');
            const commentEl = document.getElementById('comment');
            const charCount = document.getElementById('char-count');
            let currentRating = parseInt(ratingInput.value) || 0;

            function updateStars(rating) {
                stars.forEach(function(star) {
                    const val = parseInt(star.dataset.rating);
                    const icon = star.querySelector('.star-icon');
                    if (val <= rating) {
                        icon.setAttribute('fill', '#FBBF24');
                        icon.setAttribute('stroke', '#FBBF24');
                        icon.style.color = '#FBBF24';
                    } else {
                        icon.setAttribute('fill', 'none');
                        icon.setAttribute('stroke', 'currentColor');
                        icon.style.color = '';
                    }
                });
            }

            stars.forEach(function(star) {
                star.addEventListener('click', function() {
                    currentRating = parseInt(this.dataset.rating);
                    ratingInput.value = currentRating;
                    updateStars(currentRating);
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                });

                star.addEventListener('mouseenter', function() {
                    updateStars(parseInt(this.dataset.rating));
                });

                star.addEventListener('mouseleave', function() {
                    updateStars(currentRating);
                });
            });

            submitBtn.addEventListener('mouseenter', function() {
                if (!submitBtn.disabled) {
                    submitBtn.style.opacity = '0.9';
                }
            });
            submitBtn.addEventListener('mouseleave', function() {
                if (!submitBtn.disabled) {
                    submitBtn.style.opacity = '';
                }
            });

            submitBtn.closest('form').addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                submitBtn.style.opacity = '';
            });

            if (currentRating > 0) {
                updateStars(currentRating);
                submitBtn.disabled = false;
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

            commentEl.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });
            charCount.textContent = commentEl.value.length;
        })();
    </script>

</x-app-guest-layout>
