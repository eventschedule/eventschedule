<div class="min-h-screen p-8">
    <div class="max-w-6xl mx-auto">

        <!-- Events Grid -->
        <div class="grid grid-cols-3 gap-6 mb-12">
            @php
                $displayEvents = collect($events)->take(9);
            @endphp

            @foreach($displayEvents as $index => $event)
                @php
                    $eventDate = $event->starts_at ? Carbon\Carbon::parse($event->starts_at) : null;
                    $day = $eventDate ? $eventDate->format('j') : 'TBD';
                    $month = $eventDate ? strtoupper($eventDate->format('M')) : 'TBD';
                    $daySuffix = '';
                    if ($day != 'TBD') {
                        $dayInt = (int)$day;
                        if ($dayInt >= 11 && $dayInt <= 13) {
                            $daySuffix = 'th';
                        } else {
                            switch ($dayInt % 10) {
                                case 1: $daySuffix = 'st'; break;
                                case 2: $daySuffix = 'nd'; break;
                                case 3: $daySuffix = 'rd'; break;
                                default: $daySuffix = 'th';
                            }
                        }
                    }
                @endphp
                
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" style="min-height: 320px;">
                    <!-- Event Image - Takes up more space with increased height -->
                    <div class="w-full h-64 overflow-hidden">
                        @if($event->getImageUrl())
                            <img src="{{ $event->getImageUrl() }}" alt="{{ $event->translatedName() }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-400 text-sm">No Image</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Event Details - More space for title and better proportions -->
                    <div class="p-5 flex flex-col h-32">
                        <!-- Event Title - More vertical space and better typography -->
                        <h3 style="font-family: 'Montserrat', sans-serif; font-size: 1.25rem; font-weight: 700; color: #2C2C2C; line-height: 1.25; margin-bottom: 1rem; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; max-height: 2.5em;">
                            {{ $event->translatedName() }}
                        </h3>
                        
                        <!-- Bottom row with location and date -->
                        <div class="flex justify-between items-end mt-auto">
                            <!-- Location - Bottom left -->
                            <div class="flex items-center text-xs text-gray-600" style="font-family: 'Open Sans', sans-serif;">
                                <svg class="w-3 h-3 text-gray-500 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="line-clamp-1">{{ $event->getVenueDisplayName() ?: 'Location TBD' }}</span>
                            </div>
                            
                            <!-- Date - Bottom right -->
                            <div class="text-xs font-semibold text-[#2C2C2C] uppercase tracking-wide" style="font-family: 'Montserrat', sans-serif;">
                                {{ $month }} {{ $day }}{{ $daySuffix }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Contact Information -->
        <!--
        <div class="text-center">
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-6 inline-block">
                <p class="text-xl font-bold text-[#2C2C2C] uppercase tracking-wider" style="font-family: 'Montserrat', sans-serif;">
                    {{ $role->getGuestUrl() }}
                </p>
            </div>
        </div>
        -->
    </div>
</div>
