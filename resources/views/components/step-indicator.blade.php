@props([
    'currentStep' => null,
    'totalSteps' => 3,
    'steps' => [
        1 => ['title' => 'messages.create_account', 'icon' => 'user'],
        2 => ['title' => 'messages.create_schedule', 'icon' => 'calendar'],
        3 => ['title' => 'messages.create_event', 'icon' => 'plus']
    ],
    'compact' => false
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

<div class="step-indicator bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-100 p-6 {{ $compact ? 'p-4' : 'p-6' }} mb-6 relative overflow-hidden">

    <!-- Steps Layout - Always Mobile Style -->
    <div class="flex items-start justify-center space-x-6 relative z-10">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <div class="flex flex-col items-center">
                <!-- Step Circle -->
                <div class="relative">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-500 ease-out transform hover:scale-110 {{ $i < $currentStep ? 'bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg ring-4 ring-green-100' : ($i == $currentStep ? 'bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg ring-4 ring-blue-100 animate-pulse' : 'bg-gray-100 text-gray-400 border-2 border-gray-200') }}">
                        @if ($i < $currentStep)
                            <!-- Checkmark for completed steps -->
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        @elseif ($i == $currentStep)
                            <!-- Icon for current step -->
                            @switch($steps[$i]['icon'])
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
                            {{ $i }}
                        @endif
                    </div>
                    
                    <!-- Progress line (except for last step) -->
                    @if ($i < $totalSteps)
                        <div class="absolute top-7 left-14 w-20 h-1 {{ $i < $currentStep ? 'bg-gradient-to-r from-green-500 to-green-400' : 'bg-gray-200' }} transition-all duration-500 ease-out rounded-full"></div>
                    @endif
                </div>
                
                <!-- Step Content -->
                <div class="mt-3 text-center max-w-24">
                    <div class="text-sm font-semibold {{ $i <= $currentStep ? 'text-gray-900' : 'text-gray-500' }} transition-colors duration-300 leading-tight">
                        {{ __($steps[$i]['title']) }}
                    </div>
                </div>
            </div>
        @endfor
    </div>
    
</div>

<style>
.step-indicator {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.02) 0%, rgba(16, 185, 129, 0.02) 100%);
    border-radius: inherit;
    pointer-events: none;
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