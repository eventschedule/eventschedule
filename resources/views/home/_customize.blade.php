{{-- Customize: which cards sit under the tiles, and the period the numbers cover.

     A small Vue island of its own. Nothing server-rendered lives inside it: every label comes
     from the MSG object and is printed by Vue's interpolation, because Vue compiles a mustache in
     a text node as code and a translated string is text an operator can override.

     What it saves is the shape it has always been (HomeController::saveDashboardConfig()): a list
     of panels by id, each visible or not. The four tiles always show, so their entries only carry
     the period. Dragging to reorder, the two sizes and a count per card are gone from here: the
     layout is designed now. A size or an order someone saved before is sent back untouched and
     ignored; a count saved before still decides how many rows its card lists
     (HomeController::home()), there is just no longer a control for it.

     Opened by anything with [data-dashboard-customize] (the header button, and the phone menu). --}}
@php
    $customizeConfig = [
        'panels' => $dashboardConfig['panels'],
        'url' => route('home.save_config'),
        // In the order they sit on the page.
        'cards' => ['upcoming_events', 'recent_activity', 'top_events', 'traffic_sources', 'newsletters', 'boosts', 'calendar'],
        'periodPanels' => ['views', 'revenue', 'top_events', 'traffic_sources'],
        'period' => $period,
    ];
    $customizeMsg = [
        'title' => __('messages.dash_customize_title'),
        'period' => __('messages.panel_period'),
        'note' => __('messages.dash_customize_note'),
        'cancel' => __('messages.cancel'),
        'save' => __('messages.save'),
        'saving' => __('messages.saving'),
        'close' => __('messages.close'),
        'periods' => [7 => __('messages.last_7_days'), 14 => __('messages.last_14_days'), 30 => __('messages.last_30_days')],
        'cards' => [
            'upcoming_events' => __('messages.dash_coming_up'),
            'recent_activity' => __('messages.recent_activity'),
            'top_events' => __('messages.panel_top_events'),
            'traffic_sources' => __('messages.panel_traffic_sources'),
            'newsletters' => __('messages.panel_newsletters'),
            'boosts' => __('messages.panel_boosts'),
            'calendar' => __('messages.calendar'),
        ],
    ];
@endphp

<div id="dashboard-customize" v-cloak>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0, 0, 0, 0.45)" @click.self="close" @keydown.esc="close">
        <div ref="dialog" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="dashboard-customize-title" class="ap-card rounded-2xl w-full max-w-md max-h-full overflow-y-auto focus:outline-none">
            <div class="px-5 py-4 flex items-center justify-between" style="border-bottom: 1px solid var(--ap-hairline)">
                <h2 id="dashboard-customize-title" class="text-lg font-semibold text-gray-900 dark:text-white">@{{ msg.title }}</h2>
                <button type="button" @click="close" :aria-label="msg.close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-5 py-2">
                <div class="py-3 flex flex-wrap items-center justify-between gap-3" style="border-bottom: 1px solid var(--ap-hairline)">
                    <p id="dashboard-customize-period" class="text-sm font-medium text-gray-900 dark:text-white">@{{ msg.period }}</p>
                    {{-- A segmented control: the chosen one is pressed in, not outlined. --}}
                    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1" role="group" aria-labelledby="dashboard-customize-period">
                        <button v-for="days in [7, 14, 30]" :key="days" type="button" @click="period = days" :aria-pressed="period === days ? 'true' : 'false'"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]"
                            :class="period === days ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)]' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'">@{{ msg.periods[days] }}</button>
                    </div>
                </div>

                <p class="pt-3 pb-1 text-xs text-gray-500 dark:text-gray-400">@{{ msg.note }}</p>
                <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]">
                    <li v-for="card in cards" :key="card" class="flex items-center gap-3 py-2.5">
                        <span :id="'dashboard-customize-' + card" class="min-w-0 flex-1 text-sm font-medium text-gray-900 dark:text-white">@{{ msg.cards[card] }}</span>
                        <button type="button" role="switch" :aria-checked="visible[card] ? 'true' : 'false'" :aria-labelledby="'dashboard-customize-' + card" @click="visible[card] = !visible[card]"
                            class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                            :class="visible[card] ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700'">
                            <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transition-all duration-200"
                                :class="visible[card] ? 'ltr:translate-x-5 rtl:-translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- Forward action at the end. --}}
            <div class="px-5 py-4 flex justify-end gap-3" style="border-top: 1px solid var(--ap-hairline)">
                <button type="button" @click="close" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">@{{ msg.cancel }}</button>
                <x-brand-button @click="save" ::disabled="saving">@{{ saving ? msg.saving : msg.save }}</x-brand-button>
            </div>
        </div>
    </div>
</div>

<style {!! nonce_attr() !!}>[v-cloak] { display: none; }</style>
<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var CONFIG = @json($customizeConfig);
        var MSG = @json($customizeMsg);
        var opener = null;

        function visibleMap() {
            var map = {};
            CONFIG.cards.forEach(function (id) { map[id] = true; });
            CONFIG.panels.forEach(function (panel) { if (panel.id in map) { map[panel.id] = !!panel.visible; } });
            return map;
        }

        var app = Vue.createApp({
            data: function () {
                return { open: false, saving: false, msg: MSG, cards: CONFIG.cards, period: CONFIG.period, visible: visibleMap() };
            },
            methods: {
                show: function () {
                    var self = this;
                    this.period = CONFIG.period;
                    this.visible = visibleMap();
                    this.open = true;
                    this.$nextTick(function () { if (self.$refs.dialog) { self.$refs.dialog.focus(); } });
                },
                close: function () {
                    if (this.saving) return;
                    this.open = false;
                    if (opener && opener.focus) { opener.focus(); }
                },
                save: function () {
                    var self = this;
                    this.saving = true;

                    // Every panel the page knows, as it was saved, with only what this dialog
                    // decides changed: a card's visibility, and the period where a panel has one.
                    var seen = {};
                    var panels = CONFIG.panels.map(function (panel) {
                        var next = Object.assign({}, panel);
                        seen[panel.id] = true;
                        if (panel.id in self.visible) { next.visible = self.visible[panel.id]; }
                        if (CONFIG.periodPanels.indexOf(panel.id) !== -1) { next.period = self.period; }
                        return next;
                    });
                    CONFIG.cards.forEach(function (id) { if (!seen[id]) { panels.push({ id: id, visible: self.visible[id] }); } });

                    var token = document.querySelector('meta[name="csrf-token"]');
                    fetch(CONFIG.url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token ? token.getAttribute('content') : '' },
                        credentials: 'same-origin',
                        body: JSON.stringify({ panels: panels }),
                    }).then(function (response) {
                        if (!response.ok) throw new Error('status ' + response.status);
                        window.location.reload();
                    }).catch(function () {
                        self.saving = false;
                    });
                },
            },
        }).mount('#dashboard-customize');

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest && event.target.closest('[data-dashboard-customize]');
            if (!trigger) return;
            opener = trigger;
            app.show();
        });
    });
</script>
