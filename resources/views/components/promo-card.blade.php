@props(['promo'])

{{--
    A paid promotion from another schedule.

    Labelled "Promoted" rather than "Advertisement": the AdSense unit is required by Google
    policy to use the latter, and using the same word for both would either break that policy
    or imply this card came from an ad network. It is genuinely a different thing, so it says
    so. rel="sponsored" is Google's required attribute for a paid link - omitting it is a
    webspam problem for the host schedule as well as the advertiser.

    Server-rendered with no JavaScript, so there is nothing here for CSP to block.
--}}
<div class="w-full max-w-3xl mx-auto px-4 sm:px-0 mt-8">
    <div class="overflow-hidden rounded-2xl bg-white/95 dark:bg-gray-900/95 shadow-sm ring-1 ring-black/5 dark:ring-white/10 backdrop-blur-sm transition-all duration-200 hover:shadow-md">
        <a href="{{ $promo['click_url'] }}"
           rel="noopener nofollow sponsored"
           class="flex items-stretch gap-4 p-4 no-underline">

            @if (! empty($promo['image_url']))
                {{-- Fixed box plus intrinsic width/height: the native branch has zero layout shift. --}}
                <img src="{{ $promo['image_url'] }}" alt="" loading="lazy" decoding="async"
                     width="96" height="96"
                     class="h-24 w-24 flex-none rounded-xl object-cover bg-gray-100 dark:bg-gray-700">
            @endif

            <div class="flex min-w-0 flex-col">
                <span class="mb-1.5 inline-flex self-start items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                    {{ __('messages.promoted') }}
                </span>

                <span class="line-clamp-2 text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ $promo['headline'] }}
                </span>

                @if (! empty($promo['body']))
                    <span class="mt-0.5 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $promo['body'] }}
                    </span>
                @endif

                {{-- mt-auto keeps the attribution on the baseline whatever the headline length. --}}
                <span class="mt-auto pt-2 text-xs text-gray-400 dark:text-gray-500">
                    {{ $promo['advertiser'] }}
                </span>
            </div>
        </a>
    </div>
</div>
