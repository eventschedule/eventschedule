@if(!$isViewer)
<form method="post" id="availability_form"
        action="{{ route('role.availability', ['subdomain' => $subdomain]) }}">

        @csrf

    <input type="hidden" id="unavailable_days" name="unavailable_days"/>
    <input type="hidden" id="available_days" name="available_days"/>
    <input type="hidden" id="month" name="month" value="{{ $month }}"/>
    <input type="hidden" id="year" name="year" value="{{ $year }}"/>

</form>
@endif

<div class="page-head">
    <p class="page-lead">{{ __('messages.availability_lead') }}</p>
    <div class="page-actions">
        <span class="page-legend"><i aria-hidden="true"></i>{{ __('messages.unavailable') }}</span>
    </div>
</div>

@if (config('app.hosted') && ! $role->isEnterprise())
{{-- Said before a month of days is marked, where it used to be said only when Save was pressed. --}}
<div class="mb-4" data-availability-gate>
    <x-plan-gate tier="enterprise" :role="$role" :subdomain="$role->subdomain" variant="banner"
        :title="__('messages.availability')" :learnMoreUrl="marketing_url('/features/availability')"
        :canUpgrade="auth()->user()->id == $role->user_id">
        {{ __('messages.upgrade_feature_description_availability') }}
    </x-plan-gate>
</div>
@endif

@include('role/partials/calendar', ['route' => 'admin', 'tab' => 'availability'])