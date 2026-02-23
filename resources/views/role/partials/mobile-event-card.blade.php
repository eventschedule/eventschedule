{{-- Shared mobile event card content - used in both calendar and list views --}}
<div class="flex-1 py-3 px-4 flex flex-col min-w-0">
    <div class="flex items-start gap-1.5">
        <span v-if="getEventGroupColor(event)" class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0 mt-1" :style="{ backgroundColor: getEventGroupColor(event) }"></span>
        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base leading-snug line-clamp-2" dir="auto">
            <svg v-if="event.is_password_protected" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-4 h-4 text-gray-400 me-2 align-[-0.2em]"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
            <span v-text="event.name"></span>
        </h3>
    </div>
    <p v-if="event.short_description && !event.is_password_protected" class="text-sm text-gray-500 dark:text-gray-400 mt-1 line-clamp-2" dir="auto" v-text="event.short_description"></p>
    <a v-if="event.venue_name && event.venue_guest_url && !event.is_password_protected" :href="event.venue_guest_url" class="w-fit mt-1.5 flex items-center text-sm text-gray-500 dark:text-gray-400 hover:opacity-80 transition-opacity">
        <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
        </svg>
        <span class="line-clamp-2 hover:underline" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
    </a>
    <div v-else-if="event.venue_name && !event.is_password_protected" class="mt-1.5 flex items-center text-sm text-gray-500 dark:text-gray-400">
        <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 24 24" fill="currentColor">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z" />
        </svg>
        <span class="line-clamp-2" v-text="event.venue_name" {{ rtl_class($role ?? null, 'dir=rtl', '', $isAdminRoute) }}></span>
    </div>
    <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
        <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
        </svg>
        <span v-text="getEventTime(event)"></span>
    </div>
    <div v-if="event.registration_url && event.ticket_price != null && !event.is_password_protected" class="mt-1 flex items-start text-sm text-gray-500 dark:text-gray-400">
        <svg class="h-4 w-4 text-gray-400 flex-shrink-0 me-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.5 3A2.5 2.5 0 003 5.5v2.879a2.5 2.5 0 00.732 1.767l7.5 7.5a2.5 2.5 0 003.536 0l2.878-2.878a2.5 2.5 0 000-3.536l-7.5-7.5A2.5 2.5 0 008.38 3H5.5zM6 7a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>
        <span v-if="event.ticket_price == 0">{{ __('messages.free_entry') }}</span>
        <span v-else>
            <span v-text="formatPrice(event.ticket_price, event.ticket_currency_code)"></span><span v-if="event.coupon_code"> &bull; <span v-text="event.coupon_code"></span></span>
        </span>
    </div>
    <div v-if="event.can_edit" class="mt-auto pt-3">
        <a :href="event.edit_url"
            class="hover-accent inline-flex items-center px-4 py-1.5 text-sm font-medium text-gray-900 dark:text-white rounded-md border transition-all duration-200 hover:scale-105"
            style="border-color: {{ $accentColor }}"
            @click.stop>
            {{ __('messages.edit') }}
        </a>
    </div>
</div>
<div v-if="(event.image_url || event.flyer_url) && !event.is_password_protected" class="flex-shrink-0 w-24 self-stretch">
    <img :src="event.flyer_url || event.image_url" :class="event._isPast ? 'grayscale' : ''" class="w-full h-full object-cover" :alt="event.name">
</div>
