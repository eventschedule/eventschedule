<x-app-admin-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ __('messages.contacts') }}</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.contacts_help') }}</p>
                </div>

                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('role.contacts.export', ['format' => 'csv']) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5v2.25A1.25 1.25 0 005.25 20h13.5A1.25 1.25 0 0020 18.75V16.5M16 12l-4 4m0 0l-4-4m4 4V3" />
                            </svg>
                            {{ __('messages.export_csv') }}
                        </a>
                        <a href="{{ route('role.contacts.export', ['format' => 'xlsx']) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5v2.25A1.25 1.25 0 005.25 20h13.5A1.25 1.25 0 0020 18.75V16.5M16 12l-4 4m0 0l-4-4m4 4V3" />
                            </svg>
                            {{ __('messages.export_excel') }}
                        </a>
                    </div>

                    <button type="button" x-data="" x-on:click="$dispatch('open-modal', 'add-contact')"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3a6ad6] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        {{ __('messages.add_contact') }}
                    </button>
                </div>
            </div>

            @if (session('success'))
                <div class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm dark:border-green-900 dark:bg-green-900/40 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $typeOrder = $typeOrder ?? [];
                $openModal = session('open_modal');
            @endphp

            @if ($hasContacts)
                @foreach ($typeOrder as $type)
                    @php
                        $contacts = collect(($contactsByType ?? collect())->get($type, []));
                        $label = $typeLabels[$type] ?? ucfirst($type);
                    @endphp

                    @if ($contacts->isNotEmpty())
                        <div class="mt-12">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $label }}</h2>

                            <div class="mt-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg dark:ring-white/10">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900/60">
                                        <tr>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.name') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.email') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.phone') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.role') }}
                                            </th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                {{ __('messages.actions') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        @foreach ($contacts as $entry)
                                            @php
                                                $role = $entry['role'];
                                                $contact = $entry['contact'];
                                                $roleUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
                                                $contactIndex = $loop->index;
                                                $modalName = 'edit-contact-' . $role->id . '-' . $contactIndex;
                                                $useOldValues = $openModal === $modalName;
                                                $editName = $useOldValues ? old('name') : ($contact['name'] ?? '');
                                                $editEmail = $useOldValues ? old('email') : ($contact['email'] ?? '');
                                                $editPhone = $useOldValues ? old('phone') : ($contact['phone'] ?? '');
                                            @endphp
                                            <tr class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/60">
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    @if (!empty($contact['name']))
                                                        {{ $contact['name'] }}
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    @if (!empty($contact['email']))
                                                        <a href="mailto:{{ $contact['email'] }}" class="text-[#4E81FA] hover:underline break-words">{{ $contact['email'] }}</a>
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    @if (!empty($contact['phone']))
                                                        <a href="tel:{{ $contact['phone'] }}" class="text-[#4E81FA] hover:underline">{{ $contact['phone'] }}</a>
                                                    @else
                                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                    <div class="flex flex-col">
                                                        <a href="{{ $roleUrl }}" class="font-semibold text-[#4E81FA] hover:underline">
                                                            {{ $role->getDisplayName(false) }}
                                                        </a>
                                                        <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.' . $role->type) }}</span>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-900 dark:text-gray-100">
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" x-data="" x-on:click="$dispatch('open-modal', '{{ $modalName }}')"
                                                            class="inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687 1.688a1.875 1.875 0 010 2.652l-9.545 9.546-4.06.451.451-4.06 9.546-9.545a1.875 1.875 0 012.652 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 19.5h-6" />
                                                            </svg>
                                                            {{ __('messages.edit') }}
                                                        </button>

                                                        <form method="POST" action="{{ route('role.contacts.destroy', ['role' => $role->id, 'contact' => $contactIndex]) }}" class="inline-flex"
                                                            onsubmit="return confirm('{{ __('messages.confirm_delete_contact') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-3 py-1.5 text-xs font-medium text-red-600 shadow-sm hover:bg-red-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500 dark:border-red-700 dark:bg-gray-900 dark:text-red-300 dark:hover:bg-red-900/40">
                                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                                </svg>
                                                                {{ __('messages.delete') }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <x-modal name="{{ $modalName }}" focusable>
                                                <form method="POST" action="{{ route('role.contacts.update', ['role' => $role->id, 'contact' => $contactIndex]) }}" class="space-y-6 p-6">
                                                    @csrf
                                                    @method('PUT')

                                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.edit_contact') }}</h2>

                                                    <div>
                                                        <x-input-label for="name-{{ $role->id }}-{{ $contactIndex }}" :value="__('messages.name')" />
                                                        <x-text-input id="name-{{ $role->id }}-{{ $contactIndex }}" name="name" type="text"
                                                            class="mt-1 block w-full" value="{{ $editName }}" />
                                                        <x-input-error :messages="$errors->updateContact->get('name')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="email-{{ $role->id }}-{{ $contactIndex }}" :value="__('messages.email')" />
                                                        <x-text-input id="email-{{ $role->id }}-{{ $contactIndex }}" name="email" type="email"
                                                            class="mt-1 block w-full" value="{{ $editEmail }}" />
                                                        <x-input-error :messages="$errors->updateContact->get('email')" class="mt-2" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="phone-{{ $role->id }}-{{ $contactIndex }}" :value="__('messages.phone')" />
                                                        <x-text-input id="phone-{{ $role->id }}-{{ $contactIndex }}" name="phone" type="text"
                                                            class="mt-1 block w-full" value="{{ $editPhone }}" />
                                                        <x-input-error :messages="$errors->updateContact->get('phone')" class="mt-2" />
                                                    </div>

                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                                                            x-on:click="$dispatch('close-modal', '{{ $modalName }}')">
                                                            {{ __('messages.cancel') }}
                                                        </button>
                                                        <button type="submit"
                                                            class="inline-flex items-center justify-center gap-2 rounded-md bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3a6ad6] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                                                            {{ __('messages.save') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </x-modal>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="mt-12 flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#4E81FA]/10">
                        <svg class="h-6 w-6 text-[#4E81FA]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15a.75.75 0 01.75.75v13.5a.75.75 0 01-.75.75h-15a.75.75 0 01-.75-.75V5.25a.75.75 0 01.75-.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h9M7.5 12h5.25" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.no_contacts_added') }}</h3>
                    <p class="mt-2 max-w-md text-sm text-gray-600 dark:text-gray-400">{{ __('messages.contacts_help') }}</p>
                </div>
            @endif
        </div>
    </div>

    @php
        $addRoleId = $openModal === 'add-contact' ? old('role_id') : '';
        $addName = $openModal === 'add-contact' ? old('name') : '';
        $addEmail = $openModal === 'add-contact' ? old('email') : '';
        $addPhone = $openModal === 'add-contact' ? old('phone') : '';
    @endphp

    <x-modal name="add-contact" focusable>
        <form method="POST" action="{{ route('role.contacts.store') }}" class="space-y-6 p-6">
            @csrf

            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.add_contact') }}</h2>

            <div>
                <x-input-label for="role_id" :value="__('messages.role')" />
                <select id="role_id" name="role_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white py-2 pl-3 pr-10 text-sm text-gray-900 shadow-sm focus:border-[#4E81FA] focus:outline-none focus:ring-1 focus:ring-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                    <option value="">{{ __('messages.select_role') }}</option>
                    @foreach ($typeOrder as $type)
                        @php
                            $rolesOfType = collect(($rolesByType ?? collect())->get($type, []));
                            $label = $typeLabels[$type] ?? ucfirst($type);
                        @endphp
                        @if ($rolesOfType->isNotEmpty())
                            <optgroup label="{{ $label }}">
                                @foreach ($rolesOfType as $roleOption)
                                    <option value="{{ $roleOption->id }}" @selected((string) $addRoleId === (string) $roleOption->id)>
                                        {{ $roleOption->getDisplayName(false) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <x-input-error :messages="$errors->createContact->get('role_id')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="add-contact-name" :value="__('messages.name')" />
                <x-text-input id="add-contact-name" name="name" type="text" class="mt-1 block w-full" value="{{ $addName }}" />
                <x-input-error :messages="$errors->createContact->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="add-contact-email" :value="__('messages.email')" />
                <x-text-input id="add-contact-email" name="email" type="email" class="mt-1 block w-full" value="{{ $addEmail }}" />
                <x-input-error :messages="$errors->createContact->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="add-contact-phone" :value="__('messages.phone')" />
                <x-text-input id="add-contact-phone" name="phone" type="text" class="mt-1 block w-full" value="{{ $addPhone }}" />
                <x-input-error :messages="$errors->createContact->get('phone')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    x-on:click="$dispatch('close-modal', 'add-contact')">
                    {{ __('messages.cancel') }}
                </button>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-md bg-[#4E81FA] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3a6ad6] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                    {{ __('messages.save') }}
                </button>
            </div>
        </form>
    </x-modal>

    @if ($openModal)
        <script {!! nonce_attr() !!}>
            document.addEventListener('DOMContentLoaded', function () {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: '{{ $openModal }}' }));
            });
        </script>
    @endif

</x-app-admin-layout>
