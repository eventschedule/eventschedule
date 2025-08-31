<div class="min-h-screen p-8">
    <div class="max-w-6xl mx-auto">

        <!-- Events List -->
        <div class="space-y-6 mb-12">
            @php
                // Filter out events without flyers/images
                $eventsWithFlyers = collect($events)->filter(function($event) {
                    return $event->getImageUrl();
                });
                $displayEvents = $eventsWithFlyers->take(9);
            @endphp

            @foreach($displayEvents as $index => $event)
                <div class="flex {{ is_rtl() ? 'flex-row-reverse' : 'flex-row' }} gap-6">
                    <!-- Event Image -->
                    <div class="w-80 h-64 overflow-hidden rounded-lg">
                        <img src="{{ $event->getImageUrl() }}" alt="{{ $event->translatedName() }}" 
                             class="w-full h-full object-contain">
                    </div>
                    
                    <!-- Event Details Panel -->
                    <div class="flex-1 bg-white border border-gray-200 rounded-lg p-6 min-w-0">
                        <!-- Event Name - Limited to 1 line -->
                        <h3 class="text-2xl font-bold text-gray-900 truncate" title="{{ $event->translatedName() }}">
                            {{ $event->translatedName() }}
                        </h3>
                        
                        <!-- Venue and Date/Time -->
                        <p class="text-lg text-gray-700 mt-2">
                            {{ $event->getVenueDisplayName() ?? 'No venue specified' }}
                        </p>
                        
                        <p class="text-lg text-gray-700 mt-1">
                            {{ $event->localStartsAt(true) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
