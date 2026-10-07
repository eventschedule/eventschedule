@props([
    // On a page that works without a key: say what the key adds, not that it is missing.
    'optional' => false,
    // The line under the title, for a page whose reason is its own (the blog's AI draft).
    'text' => null,
    // Inside a card already (the guest import page): a bordered panel, not a card in a card.
    'flat' => false,
])

{{-- What an install without an AI key is missing, and the three steps that add one. It was an
     orange gradient box with a badge and three numbered tiles, louder than anything on the pages
     it sat on; it is a card like the ones around it now. Written in the stylesheet's own classes
     rather than the admin page kit, because the guest import page shows it too and that layout
     does not load the kit. --}}
@if (! config('services.google.gemini_key') && ! config('services.openai.api_key'))
<section {{ $attributes->merge(['class' => 'rounded-xl p-5 '.($flat ? 'border border-gray-200 dark:border-gray-700' : 'ap-card')]) }}>
    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
            {{ $optional ? __('messages.get_api_key') : __('messages.setup_required_gemini') }}
        </h2>
        @if ($optional)
        <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('messages.optional') }}</span>
        @endif
    </div>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
        {{ $text ?? ($optional ? __('messages.import_ai_optional') : __('messages.gemini_setup_description')) }}
    </p>

    <ol class="mt-4 max-w-2xl list-decimal ps-5 space-y-3 text-sm text-gray-700 dark:text-gray-300">
        <li>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.get_api_key') }}</span>
            <span class="block text-gray-500 dark:text-gray-400">
                {{ __('messages.get_api_key_description') }}
                <x-link href="https://aistudio.google.com/app/apikey" target="_blank">{{ __('messages.open_ai_studio') }}</x-link>
            </span>
        </li>
        <li>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.add_to_environment') }}</span>
            <span class="block text-gray-500 dark:text-gray-400">{{ __('messages.add_to_environment_description', ['file' => '.env']) }}</span>
            <span class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1" dir="ltr">
                <code class="rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 font-mono text-xs text-gray-800 dark:text-gray-200">GEMINI_API_KEY=your_api_key_here</code>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
                <code class="rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 font-mono text-xs text-gray-800 dark:text-gray-200">OPENAI_API_KEY=your_api_key_here</code>
            </span>
        </li>
        <li>
            <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.restart_application') }}</span>
            <span class="block text-gray-500 dark:text-gray-400">{{ __('messages.restart_application_description') }}</span>
        </li>
    </ol>
</section>
@endif
