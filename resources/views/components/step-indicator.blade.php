@props([
    'currentStep' => null,
    'totalSteps' => 3,
    'steps' => [
        1 => ['title' => 'messages.create_account', 'icon' => 'user'],
        2 => ['title' => 'messages.create_schedule', 'icon' => 'calendar'],
        3 => ['title' => 'messages.create_event', 'icon' => 'plus']
    ],
])

@php
    // Determine the current step if not provided
    if ($currentStep === null) {
        $user = auth()->user();
        
        if (!$user || ! $user->email_verified_at) {
            $currentStep = 1; // Create Account
        } elseif ($user->talents()->count() == 0) {
            $currentStep = 2; // Create Schedule
        } else {
            $currentStep = 3; // Create Event
        }
    }
@endphp

<div class="step-indicator p-6 relative overflow-hidden">

    <!-- Steps Layout - Always Mobile Style -->
    <div class="flex items-start justify-center space-x-6 relative z-10">
        @for ($i = 1; $i <= $totalSteps; $i++)
            @php
                $stepNumber = is_rtl() ? ($totalSteps - $i + 1) : $i;
            @endphp
            <div class="flex flex-col items-center">
                <!-- Step Circle -->
                <div class="relative">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-500 ease-out transform hover:scale-110 {{ $stepNumber < $currentStep ? 'bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg ring-4 ring-green-100 dark:ring-green-900' : ($stepNumber == $currentStep ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg ring-4 ring-blue-100 dark:ring-blue-900 animate-pulse' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border-2 border-gray-200 dark:border-gray-700') }}">
                        @if ($stepNumber < $currentStep)
                            <!-- Checkmark for completed steps -->
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @elseif ($stepNumber == $currentStep)
                            <!-- Icon for current step -->
                            @switch($steps[$stepNumber]['icon'])
                                @case('user')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    @break
                                @case('calendar')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                    @break
                                @case('plus')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    @break
                                @default
                                    {{ $i }}
                            @endswitch
                        @else
                            {{ $stepNumber }}
                        @endif
                    </div>
                    
                    <!-- Progress line (except for last step) -->
                    @if ($i < $totalSteps)
                        <div class="absolute top-7 left-14 w-20 h-1 {{ $stepNumber < $currentStep ? 'bg-gradient-to-r from-green-500 to-green-400' : 'bg-gray-200 dark:bg-gray-700' }} transition-all duration-500 ease-out rounded-full -z-10"></div>
                    @endif
                </div>
                
                <!-- Step Content -->
                <div class="mt-3 text-center max-w-24">
                    <div class="text-sm font-semibold {{ $stepNumber <= $currentStep ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }} transition-colors duration-300 leading-tight">
                        {{ __($steps[$stepNumber]['title']) }}
                    </div>
                    
                </div>
            </div>
        @endfor        
    </div>

    <!-- Clear Steps Button -->
    @if (session('pending_request'))
    <div class="absolute top-8 right-4 z-20">
        <form method="POST" action="{{ route('event.clear_pending_request') }}" class="inline">
            @csrf
            <input type="hidden" name="redirect_url" value="{{ request()->fullUrl() }}">
            <button type="submit" 
                class="w-10 h-10 min-w-[40px] min-h-[40px] max-w-[40px] max-h-[40px] rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 flex items-center justify-center transition-all duration-200 hover:scale-110"
                title="{{ __('messages.clear_steps') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </form>
    </div>
    @endif

    <!-- Continue as Guest option for step 1 -->
    @if (session('pending_request') && ! auth()->user())
    <div class="mt-6 mb-2 flex items-start justify-center space-x-6 relative z-10">
        <div class="flex flex-col items-center">
            <!-- Or divider -->
            <div class="flex items-center mb-5 w-full min-w-72">
                <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                <span class="px-3 text-xs text-gray-500 dark:text-gray-400 font-medium">{{ __('messages.or') }}</span>
                <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            
            <a href="{{ route('event.guest_import', ['subdomain' => session('pending_request'), 'lang' => request()->lang]) }}" 
                class="w-full inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-300 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md hover:bg-blue-100 dark:hover:bg-blue-800 hover:text-blue-700 dark:hover:text-blue-200 transition-colors duration-200">
                {{ __('messages.continue_as_guest') }}
            </a>
        </div>
    </div>
    @endif

    
</div>

<style>
.step-indicator {
    position: relative;
}

.step-indicator .bg-gradient-to-br {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.step-indicator .bg-gradient-to-r {
    background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 50%, #10b981 100%);
}

/* Enhanced animations */
@keyframes pulse-glow {
    0% {
        box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
    }
    100% {
        box-shadow: 0 0 0 15px rgba(59, 130, 246, 0);
    }
}

.step-indicator .animate-pulse {
    animation: pulse-glow 2s infinite;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .step-indicator {
        padding: 1rem;
        margin: 1rem 0;
    }
    
    .step-indicator .flex {
        gap: 0.75rem;
    }
    
    .step-indicator .space-x-6 {
        gap: 0.75rem;
    }
}

/* Smooth transitions for all interactive elements */
.step-indicator * {
    transition: all 0.3s ease;
}

/* Hover effects */
.step-indicator .hover\\:scale-110:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}
</style> 