<x-app-layout :title="__('messages.event_feedback') . ' | ' . $event->name">

    <x-slot name="meta">
        <meta name="robots" content="noindex, nofollow">
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-[#1e1e1e] py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg mx-auto">

            {{-- Schedule branding --}}
            @if ($role->profile_image_url)
            <div class="text-center mb-6">
                <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" class="h-12 mx-auto rounded-lg">
            </div>
            @endif

            {{-- Event info card --}}
            <div class="bg-white dark:bg-[#2d2d30] rounded-xl shadow-sm border border-gray-200 dark:border-[#2d2d30] p-6 mb-6">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">{{ $event->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-[#9ca3af]">
                    {{ $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}
                    &middot;
                    {{ $event->getStartEndTime($sale->event_date) }}
                </p>
            </div>

            {{-- Feedback form --}}
            <div class="bg-white dark:bg-[#2d2d30] rounded-xl shadow-sm border border-gray-200 dark:border-[#2d2d30] p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('messages.feedback_how_was') }}</h2>
                <p class="text-sm text-gray-500 dark:text-[#9ca3af] mb-6">{{ __('messages.feedback_rate_event') }}</p>

                @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-400 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('feedback.store', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}">
                    @csrf

                    {{-- Star rating --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-3">{{ __('messages.rating') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-2" id="star-rating">
                            @for ($i = 1; $i <= 5; $i++)
                            <button type="button" data-rating="{{ $i }}"
                                class="star-btn p-1 rounded-lg transition-all duration-150 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-[#2d2d30]"
                                style="min-width: 44px; min-height: 44px;"
                                aria-label="{{ $i }} {{ __('messages.stars') }}">
                                <svg class="w-8 h-8 star-icon transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                </svg>
                            </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating-input" value="{{ old('rating') }}">
                    </div>

                    {{-- Comment --}}
                    <div class="mb-6">
                        <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-[#d1d5db] mb-2">{{ __('messages.comment') }} <span class="text-gray-400 dark:text-[#9ca3af] font-normal">({{ __('messages.optional') }})</span></label>
                        <textarea id="comment" name="comment" rows="4" maxlength="2000" dir="auto"
                            class="w-full rounded-lg border border-gray-300 dark:border-[#2d2d30] bg-white dark:bg-[#1e1e1e] text-gray-900 dark:text-[#d1d5db] px-3 py-2 text-sm focus:ring-2 focus:ring-[#4E81FA] focus:border-[#4E81FA] resize-y"
                            placeholder="{{ __('messages.feedback_comment_placeholder') }}">{{ old('comment') }}</textarea>
                        <p class="mt-1 text-xs text-gray-400 dark:text-[#9ca3af]"><span id="char-count">0</span>/2000</p>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" id="submit-btn"
                        class="w-full px-4 py-3 text-base font-medium text-white rounded-lg transition-opacity focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-[#2d2d30] opacity-50 cursor-not-allowed"
                        style="background-color: {{ $role->accent_color ?? '#4E81FA' }}; --tw-ring-color: {{ $role->accent_color ?? '#4E81FA' }};"
                        disabled>
                        {{ __('messages.feedback_submit') }}
                    </button>
                </form>
            </div>
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

</x-app-layout>
