<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <x-input-label :value="__('messages.background_color')" />
            <div class="flex items-center gap-2 mt-1">
                <input type="color" x-model="styleSettings.backgroundColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                <x-text-input type="text" x-model="styleSettings.backgroundColor" class="block w-full" />
            </div>
        </div>
        <div>
            <x-input-label :value="__('messages.accent_color')" />
            <div class="flex items-center gap-2 mt-1">
                <input type="color" x-model="styleSettings.accentColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                <x-text-input type="text" x-model="styleSettings.accentColor" class="block w-full" />
            </div>
        </div>
        <div>
            <x-input-label :value="__('messages.text_color')" />
            <div class="flex items-center gap-2 mt-1">
                <input type="color" x-model="styleSettings.textColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                <x-text-input type="text" x-model="styleSettings.textColor" class="block w-full" />
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label :value="__('messages.font_family')" />
            <select x-model="styleSettings.fontFamily" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                <option value="Arial">Arial</option>
                <option value="Georgia">Georgia</option>
                <option value="Verdana">Verdana</option>
                <option value="Trebuchet MS">Trebuchet MS</option>
                <option value="Courier New">Courier New</option>
            </select>
        </div>
        <div>
            <x-input-label :value="__('messages.button_style')" />
            <div class="flex gap-4 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="styleSettings.buttonRadius" value="rounded" class="border-gray-300 dark:border-gray-700 text-[#4E81FA] focus:ring-[#4E81FA]" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.rounded') }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" x-model="styleSettings.buttonRadius" value="square" class="border-gray-300 dark:border-gray-700 text-[#4E81FA] focus:ring-[#4E81FA]" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.square') }}</span>
                </label>
            </div>
        </div>
    </div>
</div>
