{{-- One part of a card's line in the schedule's caption wording (App\Utils\ListWording), inside a
     v-for whose item is `part` and whose event is `event`. The name is the card's link and the
     venue links to the venue's page, as on the standard card; a value is isolated, and drawn left
     to right when it has no letters (18+, 50 ₪, which a right-to-left line turned into +18); a
     literal that is only decoration is kept from a screen reader. Every part is the owner's or
     the event's text, so all of it is v-text, never compiled.

     Each tag on its own line: whitespace between tags that holds a newline is dropped by Vue's
     compiler, so the parts meet exactly as the caption writes them. --}}
<a v-if="part.k === 'name'" :href="getEventUrl(event)" :target="eventLinkTarget()" @click="onEventLinkClick(event, $event)" :class="part.c"><bdi v-text="part.v"></bdi></a>
<a v-else-if="part.k === 'venue' && event.venue_guest_url" class="gk-w-venue" :class="part.c" :href="event.venue_guest_url" :target="eventLinkTarget()"><bdi v-text="part.v"></bdi></a>
<bdi v-else-if="part.k !== 'lit'" :class="part.c" :dir="part.l ? 'ltr' : null" v-text="part.v"></bdi>
<span v-else :class="part.c" :aria-hidden="part.d ? 'true' : null" v-text="part.v"></span>
