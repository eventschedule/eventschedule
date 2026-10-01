{{-- Activity: sign-ups, schedules, orders and plan changes from the audit log, last 24 hours. --}}
<section class="ap-card rounded-xl relative xl:min-h-[20rem]" aria-labelledby="realtime-activity-heading">
    <div class="xl:absolute xl:inset-0 flex flex-col p-4 sm:p-6">
        <div class="flex items-baseline justify-between gap-2">
            <h2 id="realtime-activity-heading" class="text-base font-semibold text-gray-900 dark:text-white">@{{ msg.activity }}</h2>
            <span class="text-xs text-gray-500 dark:text-gray-400">@{{ msg.last24h }}</span>
        </div>
        <p v-if="p.activity.summary" class="mt-1 text-sm text-gray-600 dark:text-gray-400">@{{ p.activity.summary }}</p>

        <p v-if="!p.activity.items.length" class="mt-6 text-sm text-gray-500 dark:text-gray-400">@{{ msg.activityEmpty }}</p>

        <ul v-else class="mt-4 flex-1 min-h-0 overflow-y-auto -mx-2 space-y-1">
            <li v-for="(item, index) in p.activity.items" :key="item.key"
                :class="[index >= 5 && !activityAll ? 'hidden xl:flex' : 'flex', fresh[item.key] ? 'realtime-fresh' : '']"
                class="items-start gap-3 rounded-lg px-2 py-2 transition-all duration-200">
                <span class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center" :class="activityTone(item.type).bg" aria-hidden="true">
                    <svg class="w-5 h-5" :class="activityTone(item.type).text" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.type] || icons.other" />
                    </svg>
                </span>
                <div class="flex-1 min-w-0">
                    <component :is="item.url ? 'a' : 'p'" :href="item.url || undefined"
                               class="block text-sm"
                               :class="[item.recent ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400', item.url ? 'hover:underline' : '']">@{{ item.text }}</component>
                    <p v-if="item.on_site" class="mt-0.5 inline-flex items-center gap-1.5 text-xs" :class="item.on_site === 'now' ? 'text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                        <span v-if="item.on_site === 'now'" class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span>
                        @{{ item.on_site === 'now' ? msg.onSiteNow : msg.seenRecently }}
                    </p>
                </div>
                <span class="shrink-0 text-xs tabular-nums text-gray-500 dark:text-gray-400">@{{ ago(item.ago) }}</span>
            </li>
        </ul>

        <button v-if="p.activity.items.length > 5" type="button" @click="activityAll = !activityAll"
                class="xl:hidden mt-3 self-center text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded">
            @{{ activityAll ? msg.showLess : msg.showAll }}
        </button>
    </div>
</section>
