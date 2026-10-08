{{-- The month: a schedule's events on a grid of days, drawn once for every page that shows one.
     Its look is partials/month-kit-styles; what each day and each event prints is worked out in
     role/partials/month-script (`monthWeeks`), so nothing here computes: it prints.

     An event is written one way. Its name, on two lines (three on a quiet day), with the time at
     the end of the line the name finishes on; under it at most one line, for a state (Now, Sold
     Out, Few left, Free entry) and on a quiet day a price. A day with one event carries its
     picture, and today leads with the picture of what is on now or next. Past three events a
     day says "+N more", which opens the whole day (role/partials/month-peek).

     Three things here are held by the script after Vue has drawn, so they have no binding:
       - a name is printed by v-clamp, which sets it as text once; the script shortens it;
       - `hidden` on a li.gk-cal-spare (a day's events past the cap) is lifted where the week's
         row has room, and the three parts of "+N more" are said again;
       - classes the script adds (gk-cal-ev-on, gk-cal-kin, gk-cal-name-cut, gk-cal-tight).
     So the root and a name carry no :class, and no event's text is ever printed as markup. --}}
@php
    $monthDayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    $monthDayKeys = array_merge(array_slice($monthDayKeys, $firstDay), array_slice($monthDayKeys, 0, $firstDay));
    // An embed, the admin's pages and the dashboard open an event in a new tab, as they always have.
    $monthNewTab = $route !== 'guest' || $guestEmbed;
@endphp
<div v-cloak v-show="!isLoadingEvents" ref="monthRoot" class="gk-cal hidden md:block" data-month role="grid"
     :aria-label="monthYearLabel" :dir="isRtl ? 'rtl' : 'ltr'"
     @pointerover="monthOver" @pointerout="monthOut" @focusin="monthFocusIn" @focusout="monthFocusOut" @click="monthClick" @keydown="monthKey">
    <div class="gk-cal-head" role="row">
        @foreach ($monthDayKeys as $i => $key)
        <div class="gk-cal-wd" :class="{ 'gk-cal-wd-now': monthTodayCol === {{ $i }} }" role="columnheader">{{ __('messages.' . $key) }}</div>
        @endforeach
    </div>
    {{-- A month with nothing in it says so above its weeks, over nobody's day, and where the
         page holds a later event it names it and goes to its month. Behind !loadFailed like
         every empty state: "nothing scheduled" is a statement about the schedule. --}}
    <div v-if="!isLoadingEvents && !loadFailed && monthIsBare" class="gk-cal-empty">
        <div class="gk-cal-empty-card">
            <b v-text="monthEmpty.title"></b>
            <span v-if="monthEmpty.next"><span>{{ __('messages.next_up') }}</span> <bdi v-text="monthEmpty.next.name"></bdi><span v-text="', ' + monthEmpty.next.when"></span></span>
            <span v-if="monthEmpty.next" class="gk-cal-empty-go"><button type="button" class="gk-peek-btn gk-peek-btn-primary gk-cal-empty-btn" @click.stop="monthGoTo(monthEmpty.next.date)" v-text="monthEmpty.next.go"></button></span>
        </div>
    </div>
    {{-- Keyed by the month and by whether it is still loading, so the weeks are made anew when a
         month arrives: that is what lets them come in from the side they were asked from. --}}
    <div class="gk-cal-weeks" :key="monthYearDatetime + (isLoadingEvents ? ':loading' : '')" :class="[{ 'gk-cal-bare': monthIsBare }, monthCame]">
        <div v-for="week in monthWeeks" :key="week.key" class="gk-cal-week" :class="{ 'gk-cal-week-past': week.small }" role="row">
            <div v-for="day in week.days" :key="day.date" class="gk-cal-day" :class="day.cls" role="gridcell"
                 :data-date="day.date" :data-col="day.col" :data-full="day.full" :aria-current="day.today ? 'date' : null">
                <div class="gk-cal-dayhead">
                    <button v-if="day.count" type="button" class="gk-cal-num" :data-day-open="day.date" aria-expanded="false" :aria-label="day.label"><time :datetime="day.date" v-text="day.num"></time></button>
                    <span v-else class="gk-cal-num"><time :datetime="day.date" v-text="day.num"></time></span>
                    <span v-if="day.today" class="gk-cal-word">{{ __('messages.today') }}</span>
                </div>
                <div v-if="day.lanes.length" class="gk-cal-lanes">
                    <template v-for="(lane, at) in day.lanes" :key="at">
                        <a v-if="lane" class="gk-cal-span" :class="lane.cls" :style="{ '--span': lane.cols }" :href="lane.url"
                           @if ($monthNewTab) target="_blank" rel="noopener" @endif
                           :data-ev="lane.id" :data-date="day.date" :data-cols="lane.cols" :aria-label="lane.label"><span class="gk-cal-name" :dir="lane.dir" v-text="lane.name"></span></a>
                        <div v-else class="gk-cal-lane"></div>
                    </template>
                </div>
                <ul v-if="day.chips.length || day.more" class="gk-cal-list" :class="day.listCls">
                    <li v-for="chip in day.chips" :key="chip.key" :class="{ 'gk-cal-spare': chip.spare }" :hidden="chip.spare ? true : null" :data-at="chip.at" :data-draft="chip.draft ? '' : null">
                        <a class="gk-cal-ev" :class="chip.cls" :href="chip.url"
                           @if ($monthNewTab) target="_blank" rel="noopener" @endif
                           :data-ev="chip.id" :data-date="day.date" :aria-label="chip.label">
                            <span v-if="chip.art" class="gk-cal-art" :class="{ 'gk-cal-art-tall': chip.tall }"><img class="gk-cal-art-glow" :src="chip.art" alt="" loading="lazy" decoding="async"><img class="gk-cal-art-img" :src="chip.art" alt="" loading="lazy" decoding="async" @load="monthArtLoaded"></span>
                            <span class="gk-cal-top">
                                <i v-if="chip.dot" class="gk-cal-dot" :style="{ background: chip.dot }"></i>
                                <span class="gk-cal-name"><span class="gk-cal-nm" :dir="chip.dir" v-clamp="chip.name"></span><span v-if="chip.lock || chip.time" class="gk-cal-end"><span v-if="chip.lock" class="gk-cal-flag" title="{{ __('messages.password_protected') }}"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></span><time v-if="chip.time" class="gk-cal-time" v-text="chip.time"></time></span></span>
                            </span>
                            <span v-if="chip.notes.length || chip.time" class="gk-cal-sub" :class="{ 'gk-cal-sub-bare': !chip.notes.length }"><span v-if="chip.time" class="gk-cal-time gk-cal-time-under" aria-hidden="true" v-text="chip.time"></span><span v-for="note in chip.notes" :key="note.k" class="gk-cal-note" :class="note.cls" :data-month-mark="note.mark" v-text="note.text"></span></span>
                        </a>
                    </li>
                    <li v-if="day.more" class="gk-cal-more-li">
                        <button type="button" class="gk-cal-more" :data-day-open="day.date" aria-expanded="false" :data-gone="day.more.gone" :data-gone-drafts="day.more.goneDrafts" :aria-label="day.more.label"><span class="gk-cal-more-n" v-text="day.more.words"></span><span class="gk-cal-more-say" data-month-mark="draft" :hidden="day.more.drafts ? null : true" v-text="day.more.drafts"></span><bdi dir="ltr" class="gk-cal-more-from" :hidden="day.more.hours ? null : true" v-text="day.more.hours"></bdi></button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
