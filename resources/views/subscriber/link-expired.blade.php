{{--
    A confirm link that has already been spent.

    Reached when confirm_token no longer matches a row, which happens on an ordinary second click,
    and whenever a mail gateway prefetched the link and burned it before the human got there. The
    row cannot be looked up - the token is gone - so this page knows nothing about the schedule or
    the address, and says nothing about either. 410 rather than 404: the link WAS valid.
--}}
<x-auth-layout>
    <div class="text-center">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.subscription_link_expired_heading') }}
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.subscription_link_expired_body') }}
        </p>
    </div>
</x-auth-layout>
