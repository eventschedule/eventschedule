{{-- The end of the upcoming rows of a list in role/partials/calendar (inside its Vue mount).

     The list drew 200 rows and stopped with nothing after the last one, so on a busy schedule
     whatever came later could not be reached by scrolling. Now the cut is a number the visitor can
     raise, and when the rows run out because the SERVER's row cap cut the payload, the list says
     that later events exist instead of ending as though the schedule did. --}}
<div v-if="hasMoreListRows" data-list-more class="mt-6 text-center">
    <button type="button" @click.stop="showMoreListRows"
            class="inline-flex items-center px-6 py-2.5 text-sm font-semibold rounded-xl border-2 shadow-sm transition-all duration-200 hover:shadow-lg"
            style="border-color: {{ $accentColor }}; background-color: {{ $accentColor }}; color: {{ $contrastColor }}">
        {{ $label('show_more') }}
    </button>
</div>
<div v-else-if="listTruncated" data-list-truncated class="mt-6 text-center">
    <span class="inline-block rounded-lg bg-white/95 dark:bg-gray-900/95 px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.later_events_not_listed') }}</span>
</div>
