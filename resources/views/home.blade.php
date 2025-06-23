<x-app-admin-layout>
    <div class="py-5">
        
        <!-- Get Started Panel -->
        @if(is_hosted_or_admin() && $schedules->isEmpty() && $venues->isEmpty() && $curators->isEmpty())
        <div class="mb-8">
            <!-- Header Panel -->
            <div class="relative bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 border border-blue-100/50 rounded-3xl p-10 mb-8 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-radial from-transparent via-blue-50/30 to-indigo-100/40 rounded-3xl"></div>
                <div class="relative text-center">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4 tracking-tight">Welcome {{ auth()->user()->firstName() }}, let's get started...</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed font-medium">{{ __('messages.create_your_first_schedule') }}</p>
                </div>
            </div>
            
            <!-- Schedule Types Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Talent Card -->
                <div class="group relative bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl p-6 border border-purple-100 hover:border-purple-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.talent') }}</h3>
                        <p class="text-gray-600 text-center mb-6 leading-relaxed">{{ __('messages.new_schedule_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'talent']) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-medium rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Venue Card -->
                <div class="group relative bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl p-6 border border-emerald-100 hover:border-emerald-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.venue') }}</h3>
                        <p class="text-gray-600 text-center mb-6 leading-relaxed">{{ __('messages.new_venue_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Curator Card -->
                @if (config('app.hosted'))
                <div class="group relative bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-100 hover:border-amber-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.curator') }}</h3>
                        <p class="text-gray-600 text-center mb-6 leading-relaxed">{{ __('messages.new_curator_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        @include('role/partials/calendar', ['route' => 'home', 'tab' => ''])

    </div>
</x-app-admin-layout>
