{{-- "Download my data" (GDPR Arts. 15 and 20): ProfileController::requestDataExport() queues
     App\Jobs\ExportPersonalData, which emails a signed link to the file. --}}
<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.data_export_description') }}</p>

    @if (\App\Services\PersonalDataExportService::canDeliver())
    <form method="post" action="{{ route('profile.data_export') }}">
        @csrf
        <x-brand-button type="submit">{{ __('messages.data_export_button') }}</x-brand-button>
        {{-- The button does not download anything on the spot: the file is prepared and a link
             is emailed. Said before the press, not only in the message after it. --}}
        <p class="event-hint mt-3">{{ __('messages.settings_data_export_how') }}</p>
    </form>
    @else
        @include('profile.partials.notice', ['noticeText' => __('messages.data_export_unavailable'), 'noticeClass' => 'mb-0'])
    @endif
</section>
