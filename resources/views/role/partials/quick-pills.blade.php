{{-- The one filter most visitors want, one press away: the schedule's sub-schedules where it has
     them, its categories otherwise. Everything else stays behind Filters. The names are the
     owner's text, drawn by Vue from data (v-text), never compiled.

     Included twice by role/partials/calendar: in the header beside the month's name (from a
     tablet up, in the month only) and above the list (a phone, and the list on any screen). Only
     one of the two is ever shown (partials/guest-kit-styles: gk-pills-head, gk-pills-desk-off). --}}
@if ($pillsInHeader)
<div v-if="quickChips.length > 1 && currentView === 'calendar'" v-cloak class="gk-pills gk-pills-head" role="group" aria-label="{{ $label('filters') }}">
@else
<div v-if="quickChips.length > 1" v-cloak class="gk-pills {{ rtl_class($role ?? null, 'rtl', '', $ownPage) }}" :class="{ 'gk-pills-desk-off': currentView === 'calendar' }" role="group" aria-label="{{ $label('filters') }}">
@endif
    <button type="button" class="gk-pill" :class="{ 'gk-pill-on': !quickChipValue }" :aria-pressed="!quickChipValue ? 'true' : 'false'" @click="pickQuickChip('', $event)">{{ $label('show_all') }}</button>
    <button v-for="chip in quickChips" :key="chip.value" type="button" class="gk-pill" :class="{ 'gk-pill-on': quickChipValue === chip.value }"
            :aria-pressed="quickChipValue === chip.value ? 'true' : 'false'" @click="pickQuickChip(chip.value, $event)">
        <i v-if="chip.color" class="gk-row-dot" :style="{ backgroundColor: chip.color }"></i><span v-text="chip.name"></span>
    </button>
</div>
