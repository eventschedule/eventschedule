{{-- One event as a row of a guest list: the schedule's list and the phone's month both draw it
     (role/partials/calendar, guest route), inside a v-for whose item is `event`.

     A row says when, what and where, then what it costs and whether any are left; performers,
     the agenda, polls and the fan buttons are on the event's own page.

     Its shape is the one the schedule's list animations were written for (resources/css/
     list-reveal.css), which is also the shape a reader and a crawler need: the card is a div
     under the element that carries v-list-reveal (Shine sweeps across it), the event's name is a
     heading holding the real link (the title rises out of a mask around the heading), and the
     picture is a link of its own inside its column (the motion goes on that link, and Curtain
     draws its two panels on the column). The picture's link repeats the name's, so it is kept
     out of the tab order and away from a screen reader; a press anywhere else on the row goes
     to the event too (navigateToEvent(), which leaves presses on a link to the link).

     Names are the owner's text, drawn by Vue from data (v-text), never compiled. --}}
<li v-if="isEventVisible(event)" v-list-reveal:m="event.uniqueKey" class="gk-row-item" @click="navigateToEvent(event, $event)">
    <div class="gk-row gk-row-press" :class="{ 'gk-row-bare': !(event.image_url || event.flyer_url) || event.is_password_protected }">
        <div class="gk-row-time">
            <i v-if="getEventDotColor(event)" class="gk-row-dot" :style="{ backgroundColor: getEventDotColor(event) }"></i><bdi v-text="getEventTime(event)"></bdi><span v-if="event._multiDayNum > 1" v-text="event._multiDayNum + ' / ' + event._multiDayTotal"></span>
        </div>
        <div class="gk-row-body" data-reveal-body>
            <div class="gk-row-title" data-reveal-title>
                <h3 :dir="event.dir || 'auto'">
                    <svg v-if="event.is_password_protected" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="gk-row-lock" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg><a :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" v-text="event.name"></a>
                </h3>
            </div>
            <p v-if="event.short_description && !event.is_password_protected" class="gk-row-desc" :dir="event.description_dir || event.dir || 'auto'" v-text="event.short_description"></p>
            {{-- Not on the venue's own schedule, where every row would say the same place. --}}
            <p v-if="event.venue_name && !event.is_password_protected && event.venue_subdomain !== subdomain" class="gk-row-where" :dir="event.venue_dir || 'auto'">
                <a v-if="event.venue_guest_url" :href="event.venue_guest_url" :target="eventLinkTarget()" v-text="event.venue_name"></a><template v-else><span v-text="event.venue_name"></span></template>
            </p>
            {{-- What it costs and whether there is any left, said before anybody has to open the
                 event to find out. Never a number of seats, nothing on a day that is over, and
                 no price for a night whose sales have closed. --}}
            <div v-if="rowHasChips(event)" class="gk-row-chips">
                <template v-if="!event._isPast">
                    <span v-if="rowSoldOut(event)" class="gk-chip gk-chip-out">{{ __('messages.sold_out') }}</span>
                    <template v-else>
                        <span v-if="rowFree(event)" class="gk-chip gk-chip-free">{{ $label('free_entry') }}</span>
                        <span v-else-if="rowPrice(event)" class="gk-chip"><bdi v-text="rowPrice(event)"></bdi></span>
                        <span v-if="rowLow(event)" class="gk-chip gk-chip-few">{{ __('messages.few_left') }}</span>
                    </template>
                    {{-- An event sold somewhere else: the owner's own price, and the code that takes something off it. --}}
                    <template v-if="rowSoldElsewhere(event)">
                        <span v-if="event.ticket_price == 0" class="gk-chip gk-chip-free">{{ $label('free_entry') }}</span>
                        <span v-else class="gk-chip"><bdi v-text="formatPrice(event.ticket_price, event.ticket_currency_code)"></bdi></span>
                        <span v-if="event.ticket_price != 0 && event.coupon_code" class="gk-chip gk-chip-accent">{{ __('messages.coupon_code') }}: <bdi v-text="event.coupon_code"></bdi><template v-if="event.coupon_discount_label"> (<bdi v-text="event.coupon_discount_label"></bdi>)</template></span>
                    </template>
                </template>
                <span v-if="event.is_internal" class="gk-chip gk-chip-few">{{ __('messages.internal') }}</span>
                <span v-else-if="event.is_draft" class="gk-chip">{{ __('messages.draft') }}</span>
            </div>
        </div>
        {{-- A 76px square: the 480 derivative, never the original. --}}
        <div v-if="(event.image_url || event.flyer_url) && !event.is_password_protected" class="gk-row-media" data-reveal-media>
            <a :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" tabindex="-1" aria-hidden="true">
                <img class="gk-row-img" :src="event.image_thumb_url || event.image_url || event.flyer_url" width="76" height="76" loading="lazy" decoding="async" alt="">
            </a>
        </div>
    </div>
    <a v-if="event.can_edit" :href="event.edit_url" class="gk-row-edit" @click.stop>{{ __('messages.edit_event') }}</a>
</li>
