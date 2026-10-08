{{-- The month's two surfaces that stand over it: the card an event opens beside its day, and the
     panel that lists a whole day. Both are drawn from plain values the script has worked out
     (`monthPeek`, `monthDayPanel` in role/partials/month-script), in <Teleport to="body"> as the
     filter panels are, because the month's own box cuts what leaves it.

     The card keeps the id the hover popup had (#event-popup). Its classes for being shown,
     pinned, compact and so on are the script's, so its root carries no :class.

     Somebody's text inside a block of ours (a name, a venue) is isolated with <bdi> and never
     given a dir of its own: that would throw an English name to the far edge of a Hebrew card. --}}
@php
    $monthNewTab = $route !== 'guest' || $guestEmbed;
    $monthIcon = fn (string $d, string $class = '') => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"'.($class ? ' class="'.$class.'"' : '').'><path stroke-linecap="round" stroke-linejoin="round" d="'.$d.'" /></svg>';
    $monthIcons = [
        'x' => 'M6 18L18 6M6 6l12 12',
        'prev' => 'M15.75 19.5L8.25 12l7.5-7.5',
        'next' => 'M8.25 4.5l7.5 7.5-7.5 7.5',
        'lock' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        'pin' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
        'globe' => 'M12 21a9 9 0 100-18 9 9 0 000 18zm0 0c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m-9 9h18',
        'mic' => 'M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z',
        'cal' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5M12 12.75v4.5m2.25-2.25h-4.5',
        'share' => 'M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M12 2.25v12m0-12l-3 3m3-3l3 3',
        'check' => 'M4.5 12.75l6 6 9-13.5',
        'edit' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125',
    ];
    $monthTarget = $monthNewTab ? ' target="_blank" rel="noopener"' : '';
@endphp
<Teleport to="body">
<div id="event-popup" ref="monthPeekEl" class="gk-peek" role="dialog" aria-modal="false" aria-labelledby="gk-peek-title" :dir="isRtl ? 'rtl' : 'ltr'"
     @pointerenter="monthPeekEnter" @pointerleave="monthPeekLeave" @focusout="monthPeekFocusOut" @keydown="monthPeekKey">
    <template v-if="monthPeek">
    <i class="gk-peek-caret"></i>
    <div class="gk-peek-card">
        <button type="button" class="gk-peek-x" aria-label="{{ __('messages.close') }}" @click="monthPeekClose">{!! $monthIcon($monthIcons['x']) !!}</button>

        {{-- The picture, with the state it is in on it; or, where there is none, the day in its place. --}}
        <a v-if="monthPeek.src" class="gk-peek-media" :class="{ 'gk-peek-media-tall': monthPeek.tall }" :href="monthPeek.url"{!! $monthTarget !!} tabindex="-1" aria-hidden="true">
            <img class="gk-peek-glow" :src="monthPeek.glow" alt=""><img class="gk-peek-img" :src="monthPeek.src" alt="" @load="monthPeekImg">
            <span v-if="monthPeek.pills.length" class="gk-peek-state"><span v-for="pill in monthPeek.pills" :key="pill.text" class="gk-peek-pill" :class="pill.cls" v-text="pill.text"></span></span>
        </a>
        <div v-else class="gk-peek-plain">
            <div class="gk-peek-tile"><i v-text="monthPeek.tile.month"></i><b v-text="monthPeek.tile.day"></b></div>
            <div class="gk-peek-plain-when">
                <span v-if="monthPeek.pills.length" class="gk-peek-state"><span v-for="pill in monthPeek.pills" :key="pill.text" class="gk-peek-pill" :class="pill.cls" v-text="pill.text"></span></span>
                <p class="gk-peek-when">
                    <b v-if="monthPeek.word" v-text="monthPeek.word"></b>
                    <template v-if="monthPeek.span"><template v-for="(part, at) in monthPeek.when" :key="at"><bdi v-if="part.clock" dir="ltr" class="gk-peek-clock" v-text="part.text"></bdi><span v-else v-text="part.text"></span></template></template>
                    <template v-else><span v-text="monthPeek.tile.weekday"></span><br v-if="monthPeek.tile.clock"><bdi v-if="monthPeek.tile.clock" dir="ltr" class="gk-peek-clock" v-text="monthPeek.tile.clock"></bdi></template>
                </p>
            </div>
        </div>

        <div class="gk-peek-body">
            <p v-if="monthPeek.src" class="gk-peek-when"><b v-if="monthPeek.word" v-text="monthPeek.word"></b><template v-for="(part, at) in monthPeek.when" :key="at"><bdi v-if="part.clock" dir="ltr" class="gk-peek-clock" v-text="part.text"></bdi><span v-else v-text="part.text"></span></template></p>
            <h3 class="gk-peek-title" id="gk-peek-title"><a :href="monthPeek.url"{!! $monthTarget !!}><span v-if="monthPeek.locked" class="gk-cal-flag gk-peek-lock">{!! $monthIcon($monthIcons['lock']) !!}</span><bdi v-text="monthPeek.name"></bdi></a></h3>
            <p v-if="monthPeek.where" class="gk-peek-line">{!! $monthIcon($monthIcons['pin']) !!}<a v-if="monthPeek.where.url" :href="monthPeek.where.url"{!! $monthTarget !!}><bdi v-text="monthPeek.where.name"></bdi></a><span v-else><bdi v-text="monthPeek.where.name"></bdi></span></p>
            <p v-else-if="monthPeek.online" class="gk-peek-line">{!! $monthIcon($monthIcons['globe']) !!}<span>{{ __('messages.online') }}</span></p>
            <p v-if="monthPeek.who" class="gk-peek-line"><span v-if="monthPeek.faces.length" class="gk-peek-faces"><img v-for="face in monthPeek.faces" :key="face" :src="face" alt="" loading="lazy"></span><template v-else>{!! $monthIcon($monthIcons['mic']) !!}</template><span><bdi v-text="monthPeek.who"></bdi></span></p>
            <p v-if="monthPeek.locked" class="gk-peek-line">{!! $monthIcon($monthIcons['lock']) !!}<span>{{ __('messages.password_protected') }}</span></p>
            <p v-if="monthPeek.desc" class="gk-peek-desc" :dir="monthPeek.descDir" v-text="monthPeek.desc"></p>
            {{-- Its other dates, named, and one press away: what the rings in the month are about. --}}
            <p v-if="monthPeek.also.length" class="gk-peek-also"><span>{{ __('messages.other_dates') }}</span><button v-for="other in monthPeek.also" :key="other.date" type="button" :class="{ 'gk-peek-also-out': other.out }" :title="other.out ? monthL('sold_out') : null" :aria-label="other.aria" @click="monthPeekGo(other.date, $event)" v-text="other.label"></button><i v-if="monthPeek.alsoMore" v-text="'+' + monthPeek.alsoMore"></i></p>
            <div v-if="monthPeek.chips.length" class="gk-peek-chips"><span v-for="chip in monthPeek.chips" :key="chip.text" class="gk-peek-chip" :class="chip.cls"><bdi v-text="chip.text"></bdi></span></div>
            {{-- The way on is last in the row, as on every form of the app; what it costs is on it. --}}
            <div class="gk-peek-actions">
                <a v-if="monthPeek.view" class="gk-peek-btn gk-peek-btn-secondary" :href="monthPeek.view" target="_blank" rel="noopener">{{ __('messages.view_event') }}</a>
                <a v-if="monthPeek.details" class="gk-peek-btn gk-peek-btn-secondary gk-peek-more" :href="monthPeek.details"{!! $monthTarget !!}>{{ __('messages.details') }}</a>
                <a class="gk-peek-btn gk-peek-go" :class="'gk-peek-btn-' + monthPeek.go.kind" data-peek-go :href="monthPeek.go.href"{!! $monthTarget !!}
                   :aria-label="monthPeek.go.price ? monthPeek.go.label + ', ' + monthPeek.go.price : null" @click="countListTap()"><template v-if="monthPeek.go.edit">{!! $monthIcon($monthIcons['edit']) !!}</template><span class="gk-peek-go-l" v-text="monthPeek.go.label"></span><span v-if="monthPeek.go.price" class="gk-peek-go-price"><bdi v-text="monthPeek.go.price"></bdi></span></a>
            </div>
        </div>

        {{-- Add to calendar and share: two small tools at the picture's corner, out of the button's way. --}}
        <div v-if="monthPeek.edit || monthPeek.links || monthPeek.share" class="gk-peek-tools" :class="{ 'gk-peek-tools-over': monthPeek.src }">
            <a v-if="monthPeek.edit" class="gk-peek-tool" :href="monthPeek.edit" aria-label="{{ __('messages.edit_event') }}" title="{{ __('messages.edit_event') }}">{!! $monthIcon($monthIcons['edit']) !!}</a>
            <button v-if="monthPeek.links" type="button" class="gk-peek-tool" data-peek-cal aria-haspopup="menu" :aria-expanded="monthPeek.menu ? 'true' : 'false'" aria-label="{{ $label('add_to_calendar') }}" title="{{ $label('add_to_calendar') }}" @click="monthPeekMenu">{!! $monthIcon($monthIcons['cal']) !!}</button>
            <button v-if="monthPeek.share" type="button" class="gk-peek-tool" data-peek-share :aria-label="monthPeek.copied ? monthL('copied') : monthL('share')" title="{{ __('messages.share') }}" @click="monthPeekShare"><template v-if="monthPeek.copied">{!! $monthIcon($monthIcons['check']) !!}</template><template v-else>{!! $monthIcon($monthIcons['share']) !!}</template></button>
        </div>
        <div v-if="monthPeek.links" class="gk-peek-menu" role="menu" :hidden="monthPeek.menu ? null : true">
            <a role="menuitem" rel="nofollow noopener noreferrer" :href="monthPeek.links.google" target="_blank">Google Calendar</a>
            <a role="menuitem" rel="nofollow" :href="monthPeek.links.apple">Apple Calendar</a>
            <a role="menuitem" rel="nofollow noopener noreferrer" :href="monthPeek.links.outlook" target="_blank">Outlook</a>
        </div>
        {{-- A finger's way through a day: shown only while the card is pinned. --}}
        <div v-if="monthPeek.step" class="gk-peek-step">
            <button type="button" aria-label="{{ __('messages.previous') }}" :disabled="!monthPeek.step.prev" @click="monthPeekStep(-1)">{!! $monthIcon($monthIcons['prev'], 'gk-flip') !!}</button>
            <span v-text="monthPeek.step.label"></span>
            <button type="button" aria-label="{{ __('messages.next') }}" :disabled="!monthPeek.step.next" @click="monthPeekStep(1)">{!! $monthIcon($monthIcons['next'], 'gk-flip') !!}</button>
        </div>
    </div>
    </template>
</div>

<div ref="monthDayEl" class="gk-dayp" role="dialog" aria-modal="false" aria-labelledby="gk-dayp-title" :dir="isRtl ? 'rtl' : 'ltr'" @keydown="monthDayKey">
    <template v-if="monthDayPanel">
    <div class="gk-dayp-head">
        <span v-if="monthDayPanel.word" class="gk-cal-word" v-text="monthDayPanel.word"></span>
        <h3 id="gk-dayp-title" v-text="monthDayPanel.title"></h3>
        <span class="gk-dayp-count" v-text="monthDayPanel.count"></span>
        <button type="button" class="gk-dayp-x" data-day-step="-1" aria-label="{{ __('messages.previous') }}" :disabled="!monthDayPanel.prev" @click="monthDayStep(-1)">{!! $monthIcon($monthIcons['prev'], 'gk-flip') !!}</button>
        <button type="button" class="gk-dayp-x" data-day-step="1" aria-label="{{ __('messages.next') }}" :disabled="!monthDayPanel.next" @click="monthDayStep(1)">{!! $monthIcon($monthIcons['next'], 'gk-flip') !!}</button>
        <button type="button" class="gk-dayp-x" data-day-close aria-label="{{ __('messages.close') }}" @click="monthCloseDay()">{!! $monthIcon($monthIcons['x']) !!}</button>
    </div>
    <p v-if="monthDayPanel.away" class="gk-dayp-away"><b>{{ __('messages.unavailable') }}:</b> <bdi v-text="monthDayPanel.away"></bdi></p>
    <ul class="gk-dayp-rows">
        <li v-for="row in monthDayPanel.rows" :key="row.key">
            <a class="gk-dayp-row" :class="row.cls" :href="row.url"{!! $monthTarget !!} :data-ev="row.id" @click="countListTap()">
                <time class="gk-dayp-time"><i v-if="row.dot" class="gk-cal-dot gk-dayp-dot" :style="{ background: row.dot }"></i><bdi dir="ltr" v-text="row.time"></bdi></time>
                <span class="gk-dayp-body">
                    <span class="gk-dayp-name"><bdi v-text="row.name"></bdi><span v-if="row.locked" class="gk-cal-flag">{!! $monthIcon($monthIcons['lock']) !!}</span></span>
                    <span v-if="row.sub.length" class="gk-dayp-sub"><template v-for="(part, at) in row.sub" :key="part.k"><i v-if="at" aria-hidden="true">&middot;</i><span v-if="part.venue"><bdi v-text="part.text"></bdi></span><b v-else :class="part.cls"><bdi v-text="part.text"></bdi></b></template></span>
                </span>
                <img v-if="row.img" class="gk-dayp-img" :src="row.img" alt="" loading="lazy"><span v-else></span>
            </a>
        </li>
    </ul>
    <div v-if="monthDayPanel.add" class="gk-dayp-foot"><a class="gk-peek-btn gk-peek-btn-secondary" :href="monthDayPanel.add">{{ __('messages.add_event') }}</a></div>
    </template>
</div>
</Teleport>
