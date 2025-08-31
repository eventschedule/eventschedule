<div class="min-h-screen p-8">
    <div class="max-w-7xl mx-auto">

        @if($role->profile_image_url)
            <div class="flex justify-center pb-6">
                <img src="{{ $role->profile_image_url }}" 
                        alt="{{ $role->translatedName() }}" 
                        class="w-24 h-24 rounded-lg object-cover shadow-lg">
            </div>
        @endif
     
        <!-- Events Grid -->
        <div class="grid gap-5">
            @php
                // Filter out events without flyers/images
                $eventsWithFlyers = collect($events)->filter(function($event) {
                    return $event->getImageUrl();
                });
                $displayEvents = $eventsWithFlyers->take(9);
            @endphp

            @foreach($displayEvents as $index => $event)
                <div class="bg-white rounded-lg shadow-xl overflow-hidden transform hover:scale-[1.02] transition-all duration-300 hover:shadow-2xl">
                    <div class="flex {{ is_rtl() ? 'flex-row-reverse' : 'flex-row' }} min-h-0">
                        
                        <!-- Event Image Section -->
                        <div class="w-52 h-52 flex-shrink-0 relative overflow-hidden">
                            <img src="{{ $event->getImageUrl() }}" 
                                 alt="{{ $event->translatedName() }}" 
                                 class="w-full h-full object-cover object-center">
                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                        
                        <!-- Event Details Section -->
                        <div class="flex-1 p-8 flex flex-col justify-between min-w-0">
                            <!-- Event Info -->
                            <div class="space-y-4">
                                <!-- Event Name -->
                                <div class="pt-1">
                                    <h3 class="text-3xl font-bold text-gray-900 leading-tight truncate" 
                                        title="{{ $event->translatedName() }}">
                                        {{ $event->translatedName() }}
                                    </h3>
                                </div>
                                
                                <!-- Venue -->
                                <div class="pt-1 flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-lg text-gray-700 font-medium">
                                        {{ $event->getVenueDisplayName() }}
                                    </p>
                                </div>
                                
                                <!-- Date & Time -->
                                <div class="flex items-center space-x-3 {{ is_rtl() ? 'space-x-reverse' : '' }}">
                                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-lg text-gray-700 font-medium">
                                        {{ $event->localStartsAt(true) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="w-42 h-52 flex-shrink-0 p-6 bg-gray-50 flex items-center justify-center">
                            @php
                                $qrCode = Endroid\QrCode\QrCode::create($event->getGuestUrl($role->subdomain))
                                    ->setSize(200)
                                    ->setMargin(10);

                                $writer = new Endroid\QrCode\Writer\PngWriter();
                                $result = $writer->write($qrCode);
                                
                                // Convert to base64 data URI for display in img tag
                                $base64 = base64_encode($result->getString());
                                $mimeType = $result->getMimeType();
                                $dataUri = "data:{$mimeType};base64,{$base64}";
                            @endphp
                            
                            <div class="text-center">
                                <img src="{{ $dataUri }}" 
                                     alt="QR Code for {{ $event->title }}" 
                                     class="w-32 h-32 mx-auto">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($role->website)
            <div class="text-center pt-8 pb-16">
                <div class="flex items-center justify-center gap-4">
                    <p class="text-white text-xl font-bold">
                        {{ \App\Utils\UrlUtils::clean($role->website) }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
