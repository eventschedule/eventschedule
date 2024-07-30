<x-app-layout>

    <div class="mt-8 flow-root">
        <div class="overflow-x-auto px-4 sm:px-6 lg:px-8 bg-white">
            <div class="inline-block min-w-full py-2 align-middle">
                <div class="overflow-hidden min-w-full">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    {{ __('messages.name') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.email') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.phone') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.website') }}
                                </th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($roles as $role)
                            <tr class="bg-white">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    <a href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}"
                                        target="_blank">{{ $role->name }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <a href="mailto:{{ $role->email }}">{{ $role->email }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $role->phone }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <a href="{{ $role->website }}"
                                        target="_blank">{{ App\Utils\UrlUtils::clean($role->website) }}</a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <button type="button"
                                        onclick="var confirmed = confirm('{{ __('messages.are_you_sure') }}'); if (confirmed) { location.href = '{{ route('role.unfollow', ['subdomain' => $role->subdomain]) }}'; return false; }"
                                        class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        {{ __('messages.unfollow') }}
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>