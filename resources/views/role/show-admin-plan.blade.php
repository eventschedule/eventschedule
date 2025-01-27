<div class="pt-5 container mx-auto">
    <div class="bg-white rounded-lg shadow-md p-8 border border-gray-100 pb-10">
        <h4 class="text-xl font-bold mb-6 flex justify-between items-center pb-4 border-b border-gray-200">
            <span class="text-gray-800">{{ __('messages.plan') }}</span>
        </h4>

        <div class="space-y-4">
            <div class="flex items-center">
                <span class="text-gray-600 w-32">{{ __('messages.curent_plan') }}</span>
                <span class="font-medium text-gray-700">
                    {{ $role->plan_type == 'pro' ? __('messages.pro_plan') : __('messages.free_plan') }}
                </span>
            </div>

            @if ($role->plan_type == 'pro')
            <div class="flex items-center">
                <span class="text-gray-600 w-32">{{ __('messages.expires_on') }}</span>
                <span class="font-medium text-gray-700">
                    {{ $role->plan_expires ? \Carbon\Carbon::parse($role->plan_expires)->format('F j, Y') : '-' }}
                </span>
            </div>
            @endif
        </div>

        @if ($role->plan_type == 'pro' && auth()->user()->id == $role->user_id)
        <div class="pt-10">
            <a href="{{ route('role.change_plan', ['subdomain' => $role->subdomain, 'plan_type' => 'free']) }}"
                onclick="return confirm('{{ __('messages.are_you_sure') }}')"
                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                {{ __('messages.change_to_free_plan') }}
            </a>
        </div>
        @endif
    </div>
</div>