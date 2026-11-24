@props(['value', 'optional' => false])

{{-- `optional` says "(optional)" after the name, in the quieter ink: forms mark the fields that
     can be left empty and never the ones that cannot (the 2026-11 design review). --}}
<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-gray-900 dark:text-gray-100']) }}>
    {{ $value ?? $slot }}@if ($optional) <span class="font-normal text-gray-600 dark:text-gray-400">{{ __('messages.label_optional_suffix') }}</span>@endif
</label>
