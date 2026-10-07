<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- The platform's segments, then the form that adds one: the list first, as on a schedule's
         own Segments tab. --}}
    <div class="page-shell">
        @include('admin.newsletters.partials._section', ['tab' => 'segments'])

        <div class="page-head">
            <p class="page-lead">{{ __('messages.admin_newsletter_segments_lead') }}</p>
        </div>

        <div class="news-block">
            @if ($segments->count())
            <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col">{{ __('messages.type') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.recipients') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($segments as $segment)
                        @php $segmentHash = \App\Utils\UrlUtils::encodeId($segment->id); @endphp
                        <tr>
                            <td class="c-main c-strong">
                                <a href="{{ route('admin.newsletters.segment.edit', ['hash' => $segmentHash]) }}" class="event-link"><bdi>{{ $segment->name }}</bdi></a>
                            </td>
                            <td>@include('admin.newsletters.partials._segment-type')</td>
                            <td class="c-num" data-label="{{ __('messages.recipients') }}">{{ number_format($segment->recipient_count) }}</td>
                            <td class="c-actions">
                                <a href="{{ route('admin.newsletters.segment.edit', ['hash' => $segmentHash]) }}" class="event-link">{{ __('messages.edit') }}</a>
                                <form method="POST" action="{{ route('admin.newsletters.segment.delete', ['hash' => $segmentHash]) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty
                    :title="__('messages.no_segments')"
                    :text="__('messages.default_all_platform_users')"
                    icon="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </div>
            @endif
        </div>

        {{-- The type decides which of the form's fields show. Plain script on a server-rendered
             form (it was an Alpine island); a field that is not showing is disabled, so it is
             not sent. --}}
        <x-page-card beside class="news-block" :title="__('messages.create_segment')">
            <form method="POST" action="{{ route('admin.newsletters.segment.store') }}" id="create-segment-form">
                @csrf
                <div class="page-form-fields">
                    <div>
                        <x-input-label for="segment_name" :value="__('messages.name')" />
                        <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" required />
                    </div>

                    <div>
                        <x-input-label for="segment_type" :value="__('messages.type')" />
                        <select id="segment_type" name="type" autocomplete="off" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <option value="all_users">{{ __('messages.all_platform_users') }}</option>
                            {{-- Hosted only: a plain selfhost has no plans to segment by. --}}
                            @if (config('app.hosted'))
                            <option value="plan_tier">{{ __('messages.plan_tier') }}</option>
                            @endif
                            <option value="signup_date">{{ __('messages.signup_date') }}</option>
                            <option value="admins">{{ __('messages.admins') }}</option>
                            <option value="manual">{{ __('messages.manual') }}</option>
                        </select>
                    </div>

                    @if (config('app.hosted'))
                    <div data-segment-pane="plan_tier" hidden>
                        <x-input-label for="segment_plan" :value="__('messages.plan_tier')" />
                        <select id="segment_plan" name="filter_criteria[plan_type]" disabled class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <option value="free">{{ __('messages.free') }}</option>
                            <option value="pro">{{ __('messages.pro') }}</option>
                            <option value="enterprise">{{ __('messages.enterprise') }}</option>
                        </select>
                    </div>
                    @endif

                    <div data-segment-pane="signup_date" hidden>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="segment_date_from" :value="__('messages.date_from')" />
                                <x-text-input id="segment_date_from" name="filter_criteria[date_from]" type="text" class="mt-1 block w-full js-segment-date" disabled />
                            </div>
                            <div>
                                <x-input-label for="segment_date_to" :value="__('messages.date_to')" />
                                <x-text-input id="segment_date_to" name="filter_criteria[date_to]" type="text" class="mt-1 block w-full js-segment-date" disabled />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.create_segment') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>
    </div>

    @include('newsletter.partials._list-script')
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var type = document.getElementById('segment_type');
            var panes = document.querySelectorAll('#create-segment-form [data-segment-pane]');

            {{-- The portal's date picker, in the reader's language. It draws a second field for
                 the day as people read it, so that one is switched on and off with the first. --}}
            var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            if (window.flatpickr) {
                document.querySelectorAll('.js-segment-date').forEach(function(el) {
                    window.flatpickr(el, Object.assign({
                        allowInput: true,
                        altInput: true,
                        altFormat: 'M j, Y',
                        dateFormat: 'Y-m-d',
                    }, fpLocale ? { locale: fpLocale } : {}));
                });
            }

            function paint() {
                panes.forEach(function(pane) {
                    var showing = pane.getAttribute('data-segment-pane') === type.value;
                    pane.hidden = ! showing;
                    pane.querySelectorAll('input, select').forEach(function(field) {
                        field.disabled = ! showing;
                    });
                });
            }
            type.addEventListener('change', paint);
            paint();
        });
    </script>
</x-app-admin-layout>
