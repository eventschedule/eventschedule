<div class="min-h-screen p-8">
    <div class="max-w-6xl mx-auto">

        <!-- Events Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @php
                // Filter out events without flyers/images
                $eventsWithFlyers = collect($events)->filter(function($event) {
                    return $event->getImageUrl();
                });
                $displayEvents = $eventsWithFlyers->take(9);
            @endphp

            @foreach($displayEvents as $index => $event)
                
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" style="min-height: 320px;">
                    <!-- Event Image - Increased height for taller flyers -->
                    <div class="w-full h-80 overflow-hidden">
                        <img src="{{ $event->getImageUrl() }}" alt="{{ $event->translatedName() }}" 
                             class="w-full h-full object-cover">
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
