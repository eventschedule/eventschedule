<div class="relative flex items-start">
    <div class="flex h-6 items-center">
        <input type="hidden" name="{{ $name }}" value="0">
        <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="1" {{ $checked ? 'checked' : '' }}
            class="h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
    </div>
    <div class="ml-3 text-sm leading-6">
        <label for="{{ $name }}" class="font-medium text-gray-900 dark:text-gray-300 cursor-pointer">{{ $label }}</label>
    </div>
</div>