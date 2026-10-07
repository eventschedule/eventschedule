{{-- What kind of segment one of the platform's is, in words. The two pages that print it each
     had their own chain of conditions, and one of them left the plan out. Expects $segment. --}}
@php
    $planWords = ['free' => __('messages.free'), 'pro' => __('messages.pro'), 'enterprise' => __('messages.enterprise')];
    $segmentPlan = $segment->filter_criteria['plan_type'] ?? null;
@endphp
@switch ($segment->type)
    @case('all_users'){{ __('messages.all_platform_users') }}@break
    @case('plan_tier'){{ __('messages.plan_tier') }}{{ $segmentPlan ? ': ' . ($planWords[$segmentPlan] ?? $segmentPlan) : '' }}@break
    @case('signup_date'){{ __('messages.signup_date') }}@break
    @case('admins'){{ __('messages.admins') }}@break
    @case('manual'){{ __('messages.manual') }}@break
    @default{{ $segment->type }}
@endswitch
