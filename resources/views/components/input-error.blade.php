@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        {{-- Flattened: $errors->get('amounts.*') answers with a list per row, and printing a list
             as text is a 500 (a refused gift card amount took the whole schedule form down). --}}
        @foreach (\Illuminate\Support\Arr::flatten((array) $messages) as $message)
            {{-- v-pre: this list is printed inside Vue mounts (the event form, some thirty times),
                 and a message may one day carry what somebody typed. Harmless outside one. --}}
            <li v-pre>{{ $message }}</li>
        @endforeach
    </ul>
@endif
