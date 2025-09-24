@props([
    'role' => null,
    'type' => null,
    'current' => null,
])

@php
    $roleType = $type ?? ($role?->type);
    $currentLabel = $current ?? ($role?->name);

    $listConfig = [
        'venue' => [
            'route' => 'role.venues',
            'label' => __('messages.venues'),
        ],
        'curator' => [
            'route' => 'role.curators',
            'label' => __('messages.curators'),
        ],
        'talent' => [
            'route' => 'role.talent',
            'label' => \Illuminate\Support\Str::plural(__('messages.talent')),
        ],
    ];

    $items = collect();

    if ($roleType && array_key_exists($roleType, $listConfig)) {
        $config = $listConfig[$roleType];
        $items->push([
            'label' => $config['label'],
            'url' => route($config['route']),
        ]);

        if (filled($currentLabel)) {
            $items->push([
                'label' => $currentLabel,
                'current' => true,
            ]);
        }
    }
@endphp

@if ($items->count() > 1)
    <x-breadcrumbs :items="$items->all()" {{ $attributes }} />
@endif
