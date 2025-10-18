@php
    $emailValue = old('email', optional($user)->email ?? $role->email);
    $acceptRequests = (int) old('accept_requests', $role->accept_requests ?? 0);
@endphp

<input type="hidden" name="type" value="{{ $role->type }}">
<input type="hidden" name="timezone" value="{{ old('timezone', $role->timezone) }}">
<input type="hidden" name="language_code" value="{{ old('language_code', $role->language_code) }}">
<input type="hidden" name="background" value="{{ old('background', $role->background) }}">
<input type="hidden" name="background_colors" value="{{ old('background_colors', $role->background_colors) }}">
<input type="hidden" name="background_color" value="{{ old('background_color', $role->background_color) }}">
<input type="hidden" name="background_image" value="{{ old('background_image', $role->background_image) }}">
<input type="hidden" name="accent_color" value="{{ old('accent_color', $role->accent_color) }}">
<input type="hidden" name="font_color" value="{{ old('font_color', $role->font_color) }}">
<input type="hidden" name="font_family" value="{{ old('font_family', $role->font_family) }}">
<input type="hidden" name="country_code" value="{{ old('country_code', $role->country_code ?? 'us') }}">
<input type="hidden" name="use_24_hour_time" value="{{ old('use_24_hour_time', $role->use_24_hour_time ?? 0) }}">
<input type="hidden" name="custom_color1" value="{{ old('custom_color1', '#1A2980') }}">
<input type="hidden" name="custom_color2" value="{{ old('custom_color2', '#26D0CE') }}">
<input type="hidden" name="email" value="{{ $emailValue }}">
<input type="hidden" name="custom_domain" value="{{ old('custom_domain', $role->custom_domain) }}">

@if(($mode ?? 'create') === 'edit')
    <input type="hidden" name="new_subdomain" value="{{ old('new_subdomain', $role->subdomain) }}">
@endif

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Name') }}
        </label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $role->name) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            required
        >
    </div>

    <div>
        <label for="website" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Website') }}
        </label>
        <input
            id="website"
            name="website"
            type="url"
            value="{{ old('website', $role->website) }}"
            placeholder="https://example.com"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
        >
    </div>

    <div>
        <label for="address1" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Address Line 1') }}
        </label>
        <input
            id="address1"
            name="address1"
            type="text"
            value="{{ old('address1', $role->address1) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
        >
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('City') }}
            </label>
            <input
                id="city"
                name="city"
                type="text"
                value="{{ old('city', $role->city) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            >
        </div>

        <div>
            <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('State / Region') }}
            </label>
            <input
                id="state"
                name="state"
                type="text"
                value="{{ old('state', $role->state) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            >
        </div>

        <div>
            <label for="postal_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Postal Code') }}
            </label>
            <input
                id="postal_code"
                name="postal_code"
                type="text"
                value="{{ old('postal_code', $role->postal_code) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
            >
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ __('Description') }}
        </label>
        <textarea
            id="description"
            name="description"
            rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
        >{{ old('description', $role->description) }}</textarea>
    </div>

    <div class="flex items-center space-x-3">
        <input type="hidden" name="accept_requests" value="0">
        <input
            id="accept_requests"
            name="accept_requests"
            type="checkbox"
            value="1"
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            {{ $acceptRequests ? 'checked' : '' }}
        >
        <label for="accept_requests" class="text-sm text-gray-700 dark:text-gray-300">
            {{ __('Allow booking requests') }}
        </label>
    </div>

    <div id="subschedules-section" class="space-y-4 rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.subschedules') }}
        </h2>

        @php
            $normalizedGroups = \App\Support\GroupPayloadNormalizer::forView(
                old('groups', $role->groups ?? [])
            );
        @endphp

        <div id="group-items" class="space-y-4">
            @foreach($normalizedGroups as $index => $group)
                @php
                    $groupKey = $group['id'] === '' ? 'existing_' . $index : $group['id'];
                @endphp

                <div class="space-y-3 rounded-md border border-gray-200 p-4 dark:border-gray-700" data-group-item>
                    <div>
                        <label for="group_name_{{ $groupKey }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('messages.name') }}
                        </label>
                        <input
                            id="group_name_{{ $groupKey }}"
                            name="groups[{{ $groupKey }}][name]"
                            type="text"
                            value="{{ $group['name'] }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                        >
                    </div>

                    @if(!empty($group['slug']))
                        <input type="hidden" name="groups[{{ $groupKey }}][slug]" value="{{ $group['slug'] }}">
                    @endif

                    <div class="flex justify-end">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold uppercase text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                            onclick="this.closest('[data-group-item]').remove()"
                        >
                            {{ __('messages.remove') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-start">
            <button
                type="button"
                class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                onclick="addGroupField()"
            >
                {{ __('messages.add') }}
            </button>
        </div>

    </div>

    <div class="flex justify-end">
        <button
            type="submit"
            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
            {{ __('messages.save') }}
        </button>
    </div>
</div>

<script {!! nonce_attr() !!}>
    window.addGroupField = function () {
        const container = document.getElementById('group-items');
        if (!container) {
            return;
        }

        const index = container.children.length;
        const wrapper = document.createElement('div');
        wrapper.className = 'space-y-3 rounded-md border border-gray-200 p-4 dark:border-gray-700';
        wrapper.setAttribute('data-group-item', '');

        wrapper.innerHTML = `
            <div>
                <label for="group_name_new_${index}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('messages.name') }}
                </label>
                <input
                    id="group_name_new_${index}"
                    name="groups[new_${index}][name]"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                >
            </div>
            <div class="flex justify-end">
                <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold uppercase text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                    onclick="this.closest('[data-group-item]').remove()"
                >
                    {{ __('messages.remove') }}
                </button>
            </div>
        `;

        container.appendChild(wrapper);

        const input = wrapper.querySelector('input');
        if (input) {
            input.focus();
        }
    };

    document.querySelectorAll('#group-items > div').forEach((item) => {
        item.setAttribute('data-group-item', '');
    });
</script>
