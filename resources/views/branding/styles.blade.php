@php
    $branding = config('branding', []);
    $primary = data_get($branding, 'colors.primary', '#1F2937');
    $secondary = data_get($branding, 'colors.secondary', '#111827');
    $tertiary = data_get($branding, 'colors.tertiary', '#374151');
    $primaryRgb = data_get($branding, 'colors.primary_rgb', '31, 41, 55');
    $primaryLight = data_get($branding, 'colors.primary_light', '#848991');
@endphp
<style>
    :root {
        --brand-primary: {{ $primary }};
        --brand-primary-rgb: {{ $primaryRgb }};
        --brand-primary-10: rgba({{ $primaryRgb }}, 0.1);
        --brand-primary-20: rgba({{ $primaryRgb }}, 0.2);
        --brand-primary-50: rgba({{ $primaryRgb }}, 0.5);
        --brand-primary-light: {{ $primaryLight }};
        --brand-secondary: {{ $secondary }};
        --brand-tertiary: {{ $tertiary }};
        --brand-on-primary: #FFFFFF;
    }

    .text-\[\#4E81FA\],
    [class~="text-[#4E81FA]"],
    .hover\:text-\[\#4E81FA\]:hover,
    .focus\:text-\[\#4E81FA\]:focus,
    .dark .dark\:text-\[\#4E81FA\],
    .dark .dark\:focus\:text-\[\#4E81FA\]:focus {
        color: var(--brand-primary) !important;
    }

    .hover\:text-\[\#365fcc\]:hover,
    .dark .dark\:hover\:text-\[\#365fcc\]:hover {
        color: var(--brand-secondary) !important;
    }

    .text-\[\#9DB9FF\],
    .dark .dark\:text-\[\#9DB9FF\] {
        color: var(--brand-primary-light) !important;
    }

    .bg-\[\#4E81FA\],
    [class~="bg-[#4E81FA]"],
    .hover\:bg-\[\#4E81FA\]:hover,
    .focus\:bg-\[\#4E81FA\]:focus,
    .dark .dark\:bg-\[\#4E81FA\],
    .dark .dark\:focus\:bg-\[\#4E81FA\]:focus {
        background-color: var(--brand-primary) !important;
        color: inherit;
    }

    .bg-\[\#4E81FA\]\/10,
    .hover\:bg-\[\#4E81FA\]\/10:hover,
    .focus\:bg-\[\#4E81FA\]\/10:focus,
    .dark .dark\:bg-\[\#4E81FA\]\/10,
    .dark .dark\:hover\:bg-\[\#4E81FA\]\/10:hover {
        background-color: var(--brand-primary-10) !important;
    }

    .bg-\[\#4E81FA\]\/20,
    .dark .dark\:bg-\[\#4E81FA\]\/20,
    .dark .dark\:hover\:bg-\[\#4E81FA\]\/20:hover {
        background-color: var(--brand-primary-20) !important;
    }

    .dark .dark\:bg-\[\#4E81FA\]\/50 {
        background-color: var(--brand-primary-50) !important;
    }

    .hover\:bg-\[\#3A6BE0\]:hover,
    .dark .dark\:hover\:bg-\[\#3A6BE0\]:hover {
        background-color: var(--brand-tertiary) !important;
    }

    .hover\:bg-\[\#365fcc\]:hover,
    .dark .dark\:hover\:bg-\[\#365fcc\]:hover,
    .focus\:bg-\[\#365fcc\]:focus {
        background-color: var(--brand-secondary) !important;
    }

    .border-\[\#4E81FA\],
    [class~="border-[#4E81FA]"],
    .hover\:border-\[\#4E81FA\]:hover,
    .focus\:border-\[\#4E81FA\]:focus,
    .dark .dark\:border-\[\#4E81FA\],
    .dark .dark\:focus\:border-\[\#4E81FA\]:focus {
        border-color: var(--brand-primary) !important;
    }

    .focus\:ring-\[\#4E81FA\],
    .focus\:ring-\[\#4E81FA\]:focus,
    .focus-visible\:outline-\[\#4E81FA\]:focus-visible,
    .dark .dark\:focus\:ring-\[\#4E81FA\]:focus,
    .dark .dark\:focus-visible\:outline-\[\#4E81FA\]:focus-visible {
        --tw-ring-color: var(--brand-primary) !important;
        outline-color: var(--brand-primary) !important;
    }

    .file\:bg-\[\#4E81FA\]::file-selector-button,
    .dark .dark\:file\:bg-\[\#4E81FA\]::file-selector-button {
        background-color: var(--brand-primary) !important;
        color: var(--brand-on-primary) !important;
    }

    .hover\:file\:bg-\[\#365fcc\]:hover::file-selector-button,
    .dark .dark\:hover\:file\:bg-\[\#365fcc\]:hover::file-selector-button {
        background-color: var(--brand-secondary) !important;
    }
</style>
