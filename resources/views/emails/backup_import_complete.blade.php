@php
    $theme = \App\Utils\EmailTheme::account();
    $entities = ['schedules', 'sub_schedules', 'events', 'tickets', 'sales', 'promo_codes', 'newsletters'];
@endphp
<x-email.layout :theme="$theme" :title="__('messages.backup_import_email_subject')" :preheader="__('messages.backup_import_email_intro')">
<x-email.heading>{{ __('messages.backup_import_email_subject') }}</x-email.heading>

<x-email.text>{{ __('messages.backup_import_email_intro') }}</x-email.text>

@foreach ($report as $schedule)
<x-email.section />
<x-email.text variant="lead"><strong dir="auto">{{ $schedule['name'] ?? 'Unknown' }}</strong></x-email.text>

@if (! empty($schedule['error']))
<x-email.callout tone="danger">{{ $schedule['error'] }}</x-email.callout>
@else
@php
    $rows = [];
    $failures = [];
    foreach ($entities as $entity) {
        if (isset($schedule[$entity])) {
            $failed = $schedule[$entity]['failed'] ?? 0;
            $rows[] = [
                __('messages.backup_entity_'.$entity),
                [(string) ($schedule[$entity]['success'] ?? 0), 'success'],
                $failed > 0 ? [(string) $failed, 'danger'] : (string) $failed,
            ];
        }
        if (! empty($schedule[$entity]['failures'])) {
            $failures = array_merge($failures, $schedule[$entity]['failures']);
        }
    }
    $failureItems = array_slice($failures, 0, 10);
    if (count($failures) > 10) {
        $failureItems[] = '... '.__('messages.backup_and_more', ['count' => count($failures) - 10]);
    }
@endphp
<x-email.table :head="[__('messages.backup_entity'), __('messages.backup_created'), __('messages.backup_failed')]" :align="['start', 'end', 'end']" :rows="$rows" />

@if (! empty($failures))
<x-email.callout tone="warning" :title="__('messages.backup_failures')">
@foreach ($failureItems as $failure)
{{ $failure }}@if (! $loop->last)<br>@endif
@endforeach
</x-email.callout>
@endif

@if (! empty($schedule['subdomain']))
<x-email.text variant="small"><x-email.link :href="config('app.hosted') ? 'https://'.$schedule['subdomain'].'.'._base_domain().'/schedule' : url('/'.$schedule['subdomain'].'/schedule')">{{ __('messages.backup_view_schedule') }}</x-email.link></x-email.text>
@endif
@endif
@endforeach
</x-email.layout>
