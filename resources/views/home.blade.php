<x-app-admin-layout>
    <div>
        
        <!-- Get Started Panel -->
        @if($schedules->isEmpty() && $venues->isEmpty() && $curators->isEmpty() && auth()->user()->tickets()->count() === 0)
        <div class="mb-8">
            <!-- Header Panel -->
            <div class="relative border border-slate-200/50 dark:border-slate-800/50 rounded-md p-10 mb-8 overflow-hidden shadow-xl welcome-panel">
                <style>
                    .welcome-panel {
                        background: linear-gradient(to bottom right, #f8fafc, #eff6ff, #f1f5f9);
                    }
                    @media (prefers-color-scheme: dark) {
                        .welcome-panel {
                            background: linear-gradient(to bottom right, rgba(15, 40, 84, 0.1), rgba(28, 77, 141, 0.08), rgba(73, 136, 196, 0.06));
                        }
                    }
                    .dark .welcome-panel {
                        background: linear-gradient(to bottom right, rgba(15, 40, 84, 0.1), rgba(28, 77, 141, 0.08), rgba(73, 136, 196, 0.06));
                    }
                </style>
                <div class="absolute inset-0 rounded-md" style="background: radial-gradient(circle, transparent 0%, rgba(15, 40, 84, 0.15) 50%, rgba(28, 77, 141, 0.2) 100%);"></div>
                <div class="absolute inset-0 rounded-md dark:opacity-100 opacity-0" style="background: radial-gradient(circle, transparent 0%, rgba(15, 40, 84, 0.05) 50%, rgba(28, 77, 141, 0.04) 100%);"></div>
                <div class="relative text-center">
                    <h2 class="text-4xl font-bold text-slate-900 dark:text-slate-100 mb-4 tracking-tight">Welcome {{ auth()->user()->firstName() }}, let's get started...</h2>
                    <p class="text-xl text-slate-700 dark:text-slate-200 max-w-3xl mx-auto leading-relaxed font-medium">{{ __('messages.create_your_first_schedule') }}</p>
                </div>
            </div>
            
            <!-- Schedule Types Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Talent Card -->
                <div class="group relative rounded-md p-8 border-2 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="background: linear-gradient(to bottom right, rgba(15, 40, 84, 0.08), rgba(15, 40, 84, 0.12)); border-color: rgba(15, 40, 84, 0.2);" onmouseover="this.style.borderColor='rgba(15, 40, 84, 0.4)'" onmouseout="this.style.borderColor='rgba(15, 40, 84, 0.2)'">
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 rounded-md mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-md" style="background: linear-gradient(to right, #0F2854, #1C4D8D);">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-3 text-center">{{ __('messages.talent') }}</h3>
                        <p class="text-slate-700 dark:text-slate-200 text-center mb-8 leading-relaxed">{{ __('messages.new_schedule_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'talent']) }}" class="inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-md transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl hover:opacity-90" style="background: linear-gradient(to right, #0F2854, #1C4D8D);">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Venue Card -->
                <div class="group relative rounded-md p-8 border-2 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="background: linear-gradient(to bottom right, rgba(28, 77, 141, 0.08), rgba(28, 77, 141, 0.12)); border-color: rgba(28, 77, 141, 0.2);" onmouseover="this.style.borderColor='rgba(28, 77, 141, 0.4)'" onmouseout="this.style.borderColor='rgba(28, 77, 141, 0.2)'">
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 rounded-md mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-md" style="background: linear-gradient(to right, #1C4D8D, #4988C4);">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-3 text-center">{{ __('messages.venue') }}</h3>
                        <p class="text-slate-700 dark:text-slate-200 text-center mb-8 leading-relaxed">{{ __('messages.new_venue_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-md transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl hover:opacity-90" style="background: linear-gradient(to right, #1C4D8D, #4988C4);">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Curator Card -->
                <div class="group relative rounded-md p-8 border-2 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="background: linear-gradient(to bottom right, rgba(73, 136, 196, 0.08), rgba(73, 136, 196, 0.12)); border-color: rgba(73, 136, 196, 0.2);" onmouseover="this.style.borderColor='rgba(73, 136, 196, 0.4)'" onmouseout="this.style.borderColor='rgba(73, 136, 196, 0.2)'">
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 rounded-md mb-6 mx-auto group-hover:scale-110 transition-transform duration-300 shadow-md" style="background: linear-gradient(to right, #0F2854, #4988C4);">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-3 text-center">{{ __('messages.curator') }}</h3>
                        <p class="text-slate-700 dark:text-slate-200 text-center mb-8 leading-relaxed">{{ __('messages.new_curator_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="inline-flex items-center justify-center px-8 py-3.5 text-white font-semibold rounded-md transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl hover:opacity-90" style="background: linear-gradient(to right, #0F2854, #4988C4);">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @elseif (config('app.hosted'))
        <div class="mb-6 w-full">
            <form id="feedback-form" class="w-full">
                @csrf
                <div class="relative w-full">
                    <textarea 
                        id="feedback-textarea"
                        name="feedback" 
                        placeholder="{{ __('messages.feedback_placeholder') }}"
                        class="w-full px-4 py-2 pr-12 pb-10 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
                        rows="2"
                    ></textarea>
                    <button 
                        type="button"
                        id="feedback-submit-btn"
                        class="absolute bottom-2 right-2 p-2 mb-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all opacity-0 pointer-events-none disabled:opacity-50 disabled:cursor-not-allowed"
                        style="transition: opacity 0.2s ease-in-out;"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
        @endif

        @include('role/partials/calendar', ['route' => 'home', 'tab' => ''])

    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackForm = document.getElementById('feedback-form');
            const feedbackTextarea = document.getElementById('feedback-textarea');
            const submitButton = document.getElementById('feedback-submit-btn');
            
            if (feedbackForm && feedbackTextarea && submitButton) {
                // Show/hide submit button based on textarea content
                function toggleSubmitButton() {
                    const hasText = feedbackTextarea.value.trim().length > 0;
                    if (hasText) {
                        submitButton.classList.remove('opacity-0', 'pointer-events-none');
                        submitButton.classList.add('opacity-100');
                    } else {
                        submitButton.classList.add('opacity-0', 'pointer-events-none');
                        submitButton.classList.remove('opacity-100');
                    }
                }
                
                // Listen for input changes
                feedbackTextarea.addEventListener('input', toggleSubmitButton);
                feedbackTextarea.addEventListener('keyup', toggleSubmitButton);
                
                // Handle form submission
                async function submitFeedback() {
                    const feedback = feedbackTextarea.value.trim();
                    if (!feedback) {
                        return;
                    }
                    
                    submitButton.disabled = true;
                    
                    try {
                        const formData = new FormData();
                        formData.append('feedback', feedback);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');
                        
                        const response = await fetch('{{ route("home.feedback") }}', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            Toastify({
                                text: data.message || '{{ __("messages.feedback_submitted") }}',
                                duration: 3000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#4BB543',
                                }
                            }).showToast();
                            
                            feedbackTextarea.value = '';
                            toggleSubmitButton();
                        } else {
                            Toastify({
                                text: data.message || '{{ __("messages.feedback_failed") }}',
                                duration: 5000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#FF0000',
                                }
                            }).showToast();
                        }
                    } catch (error) {
                        Toastify({
                            text: '{{ __("messages.feedback_failed") }}',
                            duration: 5000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#FF0000',
                            }
                        }).showToast();
                    } finally {
                        submitButton.disabled = false;
                    }
                }
                
                // Handle button click
                submitButton.addEventListener('click', submitFeedback);
                
                // Handle Enter key (Ctrl+Enter or Cmd+Enter to submit)
                feedbackTextarea.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                        e.preventDefault();
                        submitFeedback();
                    }
                });
            }
        });
    </script>
</x-app-admin-layout>
