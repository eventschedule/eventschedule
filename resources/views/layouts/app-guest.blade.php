<x-app-layout :title="$role->name . ' | Event Schedule'">

    @php
        $subdomain = $role->subdomain;
        if ($event) {
            $otherRole = $event->getOtherRole($subdomain);
        }
    @endphp

    <x-slot name="meta">
        @if ($event && $event->exists) 
            @if ($event->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->description_html)) }}">
            @elseif ($event->role() && $event->role()->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->role()->description_html)) }}">
            @endif
            <meta property="og:title" content="{{ $event->name }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date) }}">
            <meta property="og:image" content="{{ $event->getImageUrl() }}">
            <meta property="og:url" content="{{ request()->url() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $event->name }}">
            <meta name="twitter:description" content="{{ $event->getMetaDescription($date) }}">
            <meta name="twitter:image" content="{{ $event->getImageUrl() }}">
            <meta name="twitter:image:alt" content="{{ $event->name }}">
            <meta name="twitter:card" content="summary_large_image">
        @elseif ($role->exists)
            <meta name="description" content="{{ trim(strip_tags($role->description_html)) }}">
            <meta property="og:title" content="{{ $role->name }}">
            <meta property="og:description" content="{{ trim(strip_tags($role->description_html)) }}">
            <meta property="og:image" content="{{ $role->profile_image_url }}">
            <meta property="og:url" content="{{ request()->url() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $role->name }}">
            <meta name="twitter:description" content="{{ trim(strip_tags($role->description_html)) }}">
            <meta name="twitter:image" content="{{ $role->profile_image_url }}">
            <meta name="twitter:image:alt" content="{{ $role->name }}">
            <meta name="twitter:card" content="summary_large_image">
        @endif
    </x-slot>

    <x-slot name="head">

        @foreach($fonts as $font)
            <link href="https://fonts.googleapis.com/css2?family={{ $font }}:wght@400;700&display=swap" rel="stylesheet">
        @endforeach

        <style>
        @if (request()->embed)
        html {
            height: 100%;
        }
        @endif

        body {
            @media (prefers-color-scheme: dark) {
                color: #33383C !important;
            }
            @media (prefers-color-scheme: light) {
                color: #33383C !important;
            }
            font-family: '{{ isset($otherRole) && $otherRole ? $otherRole->font_family : $role->font_family }}', sans-serif !important;
            min-height: 100%;
            background-attachment: scroll;
            @if ($event && $otherRole && $otherRole->isClaimed())
                @if ($otherRole->background == 'gradient')
                    background-image: linear-gradient({{ $otherRole->background_rotation }}deg, {{ $otherRole->background_colors }});
                @elseif ($otherRole->background == 'solid')
                    background-color: {{ $otherRole->background_color }} !important;
                @elseif ($otherRole->background == 'image')
                    @if ($otherRole->background_image)
                        background-image: url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.png') }}");
                    @else
                        background-image: url("{{ $otherRole->background_image_url }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    height: 100%;
                    margin: 0;
                @endif
            @else
                @if ($role->background == 'gradient')
                    background-image: linear-gradient({{ $role->background_rotation }}deg, {{ $role->background_colors }});
                @elseif ($role->background == 'solid')
                    background-color: {{ $role->background_color }} !important;
                @elseif ($role->background == 'image')
                    @if ($role->background_image)
                        background-image: url("{{ asset('images/backgrounds/' . $role->background_image . '.png') }}");
                    @else
                        background-image: url("{{ $role->background_image_url }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    height: 100%;
                    margin: 0;
                @endif
            @endif
        }
        </style>

    </x-slot>
    
    @if (! request()->embed)
    <header class="bg-white dark:bg-[#151B26]">
        <div
        class="container mx-auto flex flex-row justify-between items-center py-7 px-5"
        >
        <a href="/">
            <svg
            width="205"
            height="40"
            viewBox="0 0 205 40"
            fill="none"
            class="fill-[#151B26] dark:fill-white w-[140px] sm:w-[205px]"
            xmlns="http://www.w3.org/2000/svg"
            >
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M58.4212 24.8872H49.6994V21.3533H58.1204V18.6466H49.6994V15.0375H58.4212V12.1804H46.767C46.767 17.3684 46.767 22.5563 46.767 27.7443H58.4212V24.8872ZM68.8723 16.7669L67.3685 20.4511L65.7144 25.0375L64.0603 20.4511L62.5565 16.8421H59.6242L64.2858 27.8195H67.2181L71.8798 16.8421H68.8723V16.7669ZM83.9851 23.233C84.4362 18.7217 82.1054 16.4661 78.4212 16.4661C74.8873 16.4661 72.6317 18.8721 72.6317 22.1804C72.6317 25.7142 74.8873 28.0451 78.6467 28.0451C80.3008 28.0451 82.1806 27.4436 83.3836 26.2405L81.6542 24.5112C81.0527 25.1879 79.6994 25.5638 78.7219 25.5638C76.8422 25.5638 75.6392 24.5864 75.4888 23.233H83.9851ZM75.4888 20.9774C75.8648 19.5488 77.0678 18.8721 78.5715 18.8721C80.1505 18.8721 81.2031 19.5488 81.4287 20.9774H75.4888ZM96.8422 27.7443V22.03C96.8422 18.5714 94.8121 16.6917 92.1805 16.6917C90.8272 16.6917 89.6994 17.218 88.5715 18.3458L88.4212 16.8421H86.0151V27.8195H88.7219V22.2556C88.7219 20.6014 89.8497 19.1729 91.5039 19.1729C93.2332 19.1729 94.1354 20.4511 94.1354 22.1052V27.8195H96.8422V27.7443ZM99.8497 13.9849V16.8421H97.8196V19.1729H99.8497V23.9097C99.8497 26.6165 101.429 27.9699 103.759 27.8947C104.587 27.8947 105.188 27.7443 106.015 27.4435L105.263 25.1127C104.887 25.3383 104.361 25.4135 103.985 25.4135C103.158 25.4135 102.556 24.8872 102.556 23.8345V19.0977H105.564V16.7669H102.556V13.6842L99.8497 13.9849ZM123.684 14.5864C122.181 12.3308 120.226 11.9548 117.97 11.9548C115.414 11.9548 112.03 13.0075 112.03 16.2405C112.03 19.0977 114.962 19.6992 117.744 20.2255C120.301 20.7518 122.857 21.2029 122.857 23.5338C122.857 26.3157 119.85 26.8421 117.895 26.8421C116.015 26.8421 113.534 25.9398 112.556 23.9849L111.504 24.5112C112.707 26.9172 115.489 27.8947 117.895 27.8947C120.526 27.8947 124.06 27.0676 124.06 23.5338C124.06 20.1503 120.978 19.624 117.97 19.0977C115.338 18.6466 113.233 18.1202 113.233 16.1654C113.233 13.6841 116.09 13.0075 117.97 13.0075C119.699 13.0075 121.579 13.3082 122.707 15.1127L123.684 14.5864ZM134.737 25.4887C133.835 26.3909 132.632 26.8421 131.504 26.8421C129.023 26.8421 126.917 25.1879 126.917 22.2556C126.917 19.3233 129.023 17.6691 131.504 17.6691C132.707 17.6691 133.91 18.1202 134.812 18.9473L135.489 18.2706C134.361 17.218 132.932 16.6165 131.504 16.6165C128.346 16.6165 125.865 18.7217 125.865 22.1804C125.865 25.639 128.421 27.7443 131.504 27.7443C132.932 27.7443 134.361 27.218 135.489 26.0902L134.737 25.4887ZM137.669 12.1804V27.7443H138.722V21.7293C138.722 19.4736 140.301 17.6691 142.632 17.6691C145.113 17.6691 146.165 19.1729 146.165 21.5037V27.8195H147.218V21.5037C147.218 18.6466 145.714 16.6917 142.632 16.6917C141.128 16.6917 139.549 17.2932 138.647 18.8721V12.2556H137.669V12.1804ZM155.038 27.8947C156.767 27.8947 158.722 27.218 159.775 25.7894L159.023 25.1879C158.196 26.2405 156.541 26.8421 155.038 26.8421C152.782 26.8421 150.827 25.3383 150.602 22.7819H160.451C160.902 18.4962 158.12 16.6165 155.038 16.6165C151.955 16.6165 149.474 19.0225 149.474 22.2556C149.474 25.7142 151.955 27.8947 155.038 27.8947ZM150.602 21.8044C150.827 19.0977 152.782 17.5939 155.038 17.5939C157.669 17.5939 159.474 19.0977 159.474 21.8044H150.602ZM168.271 17.6691C170.827 17.6691 172.782 19.5488 172.782 22.2556C172.782 24.9623 170.902 26.8421 168.271 26.8421C165.79 26.8421 163.759 25.2631 163.759 22.2556C163.759 19.1729 165.79 17.6691 168.271 17.6691ZM172.782 12.1804V19.1729C171.88 17.3684 170.15 16.6165 168.196 16.6165C165.113 16.6165 162.707 18.6466 162.707 22.2556C162.707 25.8646 165.113 27.8947 168.196 27.8947C170.075 27.8947 171.88 26.9924 172.782 25.2631V27.6691H173.835V12.1052H172.782V12.1804ZM186.617 27.7443V16.7669H185.564V22.8571C185.564 25.1127 183.985 26.8421 181.729 26.8421C179.323 26.8421 177.82 25.5639 177.82 23.0075V16.6917H176.767V23.0075C176.767 26.015 178.797 27.8195 181.729 27.8195C183.233 27.8195 184.812 27.1428 185.639 25.639V27.5939H186.617V27.7443ZM189.474 12.1804V27.7443H190.526V12.1804H189.474ZM198.572 27.8947C200.301 27.8947 202.256 27.218 203.308 25.7894L202.556 25.1879C201.729 26.2405 200.075 26.8421 198.572 26.8421C196.316 26.8421 194.361 25.3383 194.135 22.7819H203.985C204.436 18.4962 201.654 16.6165 198.572 16.6165C195.489 16.6165 193.008 19.0225 193.008 22.2556C193.008 25.7142 195.489 27.8947 198.572 27.8947ZM194.135 21.8044C194.361 19.0977 196.316 17.5939 198.572 17.5939C201.203 17.5939 203.008 19.0977 203.008 21.8044H194.135Z"
            />
            <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M20 0C31.0526 0 40 8.94737 40 20C40 31.0526 31.0526 40 20 40C8.94737 40 0 31.0526 0 20C0 8.94737 8.94737 0 20 0ZM21.203 25.7143C21.8045 25.8647 22.6316 25.8647 23.2331 25.7143C23.609 23.0827 23.0075 23.6842 21.8045 23.6842C21.0526 23.6842 21.1278 23.609 21.1278 24.2857C21.0526 24.7368 21.1278 25.2632 21.203 25.7143ZM17.3684 25.7143C17.9699 25.9399 18.7218 25.7895 19.3985 25.7143C19.8496 23.0827 19.0226 23.609 18.3459 23.609C17.7444 23.609 16.9173 23.1579 17.3684 25.7143ZM21.1278 15.8647L21.203 17.2932C21.8797 17.4436 22.6316 17.4436 23.3083 17.2932C23.609 14.7368 23.2331 15.2632 22.2556 15.2632C21.3534 15.188 21.1278 14.9624 21.1278 15.8647ZM9.69925 25.7143C10.3008 25.8647 11.1278 25.8647 11.7293 25.7143C11.8045 25.4135 11.8045 24.4361 11.8045 24.1353C11.7293 23.4586 11.5038 23.609 10.3008 23.609C9.62406 23.609 9.32331 23.7594 9.69925 25.7143ZM10.3008 17.3684C11.9549 17.3684 11.8045 17.4436 11.8045 16.2406C11.8045 15.0376 11.9549 15.188 10.3008 15.188C9.3985 15.188 9.62406 15.2632 9.62406 16.2406C9.62406 17.1429 9.47368 17.3684 10.3008 17.3684ZM17.3684 21.5038C18.1203 21.6541 18.6466 21.6541 19.3985 21.5038C19.5489 19.0226 19.6241 19.3985 18.1203 19.3985C17.3684 19.3985 17.3684 19.3233 17.2932 20C17.218 20.4511 17.2932 21.0526 17.3684 21.5038ZM21.203 21.5038H23.2331C23.3083 21.1278 23.3083 20.2256 23.3083 19.8496C23.2331 19.1729 23.0075 19.3233 22.1805 19.3985C21.5038 19.3985 20.7519 18.9474 21.203 21.5038ZM17.3684 17.218C18.3459 17.3684 18.421 17.2932 19.3985 17.2932C19.4737 16.9173 19.4737 15.9398 19.4737 15.6391C19.3985 15.0376 19.6241 15.3384 19.1729 15.188C19.1729 15.188 18.4962 15.188 18.4211 15.188C17.6692 15.188 17.4436 15.0376 17.3684 15.7143C17.218 16.015 17.2932 16.9173 17.3684 17.218ZM13.5338 21.5038H15.5639L15.6391 20C15.6391 19.3233 15.7143 19.3985 15.1128 19.3233C13.0075 19.1729 13.4586 19.6241 13.4586 20.0752C13.4586 20.6015 13.4586 21.0526 13.5338 21.5038ZM9.62406 20.3007V21.5038C10.6015 21.6541 10.6015 21.579 11.6541 21.579C11.7293 21.1278 11.7293 20.7519 11.7293 20.3759C11.7293 19.6992 11.8797 19.5489 11.2782 19.4737H9.92481C9.47368 19.5489 9.62406 19.6241 9.62406 20.3007ZM13.5338 25.7143H15.5639C15.6391 23.7594 15.8647 23.6842 15.1128 23.609C13.0827 23.4586 13.4586 23.9098 13.4586 24.3609C13.4586 24.812 13.4586 25.2632 13.5338 25.7143ZM13.5338 17.2932H15.5639C15.7143 15.4887 16.0902 15.0376 13.985 15.188C13.3083 15.2632 13.4586 15.188 13.4586 15.9399C13.4586 16.391 13.4586 16.8421 13.5338 17.2932ZM27.4436 20L32.1053 17.218C31.6541 15.0376 32.0301 15.2632 32.1804 13.4587C31.7293 13.0075 29.5489 11.5038 29.0977 11.3534C28.797 12.2556 27.9699 12.9323 27.4436 15.0376C27.0677 16.6917 27.2932 18.3459 27.4436 20ZM30.8271 25.5639L30.4511 21.4286V21.3534C30.4511 21.203 30.4511 20.9774 30.3759 20.8271C29.9248 21.0526 29.5489 21.3534 29.0977 21.579C28.9474 21.6541 28.8722 21.7293 28.7218 21.8045C28.7218 22.1053 28.797 22.406 28.797 22.7068C29.0226 24.7368 29.0226 26.391 29.0226 28.4962C29.0226 32.7068 28.1203 32.1053 22.4812 32.1053H12.406C11.6541 32.1053 11.1278 31.8797 10.7519 31.5789H10.1504C9.69925 31.5789 9.17293 31.5789 8.7218 31.5038C9.3985 33.3835 11.203 33.8346 13.3083 33.8346H26.0902C31.5038 33.7594 30.9023 29.8496 30.8271 25.5639ZM12.5564 6.2406C11.6541 6.2406 10.9774 6.99249 10.9774 7.81955V11.579C10.9774 12.4812 11.7293 13.1579 12.5564 13.1579C13.4586 13.1579 14.1353 12.406 14.1353 11.579V10.5263H18.9474V8.87218H14.1353V7.81955C14.2105 6.9173 13.4586 6.2406 12.5564 6.2406ZM12.5564 7.14286C12.1804 7.14286 11.8797 7.44361 11.8797 7.81955V11.579C11.8797 11.9549 12.1804 12.2556 12.5564 12.2556C12.9323 12.2556 13.2331 11.9549 13.2331 11.579V7.81955C13.3083 7.44361 13.0075 7.14286 12.5564 7.14286ZM21.1278 6.2406C20.2256 6.2406 19.5489 6.99249 19.5489 7.81955V11.579C19.5489 12.4812 20.3008 13.1579 21.1278 13.1579C22.0301 13.1579 22.7068 12.406 22.7068 11.579V10.6015H27.6692C25.9398 14.1353 25.1128 14.8872 25.7895 20C26.015 22.0301 26.015 23.6842 26.015 25.7895C26.015 30 25.1128 29.3985 19.4737 29.3985H9.39849C7.06767 29.3985 7.14286 27.594 7.14286 25.4887V13.3083C7.14286 9.92482 8.94737 10.7519 10.5263 10.4511V8.87218C7.96992 8.42105 6.09023 9.62406 5.56391 11.579C5.33835 12.5564 5.41353 24.9624 5.48872 26.9925C5.48872 30.3008 7.74436 30.9774 10.3008 30.9774H23.0827C28.7218 30.9774 27.594 26.5414 27.594 21.8797C28.2707 21.3534 29.3233 20.8271 30.0752 20.3007C31.1278 19.6241 37.5188 15.8647 37.9699 15.4887L37.8947 15.4135L37.218 14.8872C34.812 13.1579 32.2556 11.1278 29.9248 9.54888C29.0226 8.94738 29.1729 8.797 27.8947 8.797C26.5414 8.797 23.985 8.94738 22.5564 8.797V7.81955C22.7068 6.9173 21.9549 6.2406 21.1278 6.2406ZM21.1278 7.14286C20.7519 7.14286 20.4511 7.44361 20.4511 7.81955V11.579C20.4511 11.9549 20.7519 12.2556 21.1278 12.2556C21.5038 12.2556 21.8045 11.9549 21.8045 11.579V7.81955C21.8045 7.44361 21.5038 7.14286 21.1278 7.14286Z"
            />
            </svg>
        </a>
        <div class="flex flex-row gap-x-3 md:gap-x-10">
            <a
            href="{{ route('login') }}"
            class="inline-flex items-center justify-center"
            >
            <button
                type="button"
                name="login"
                style="color: {{ $otherRole && $otherRole->accent_color ? $otherRole->accent_color : ($role && $role->accent_color ? $role->accent_color : '#4E81FA') }}"
                class="text-base duration-300 hover:opacity-90 disabled:cursor-not-allowed"
            >
                {{ __('messages.log_in') }}
            </button>
            </a>
            <a
            href="{{ route('sign_up') }}"
            class="inline-flex items-center justify-center"
            >
            <button
                type="button"
                name="sign-up"
                style="background-color: {{ $otherRole && $otherRole->accent_color ? $otherRole->accent_color : ($role && $role->accent_color ? $role->accent_color : '#4E81FA') }}"
                class="inline-flex items-center rounded-md px-5 py-3 hover:opacity-90 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            >
                {{ __('messages.sign_up') }}
            </button>
            </a>
        </div>
        </div>
    </header>
    @endif

    {{ $slot }}

    @if (! request()->embed)
    <footer class="bg-[#151B26]">
      <div
        class="container mx-auto flex flex-row justify-center items-center py-8 px-5"
      >
        <p class="text-[#F5F9FE] text-base text-center">
            {!! str_replace(':link', '<a href="' . url('/') . '" target="_blank" class="hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
            •
            @if (($role->country_code == 'il' && $role->id != 6) || ($event && $event->venue && $event->venue->country_code == 'il' && $event->venue->id != 6))
            {!! str_replace(':link', '<a href="https://myjewishsoulmate.com" target="_blank" class="hover:underline">My Jewish Soulmate</a>',  __('messages.supported_by')) !!}
            @else
            {!! str_replace([':link1', ':link2'], ['<a href="https://invoiceninja.com" target="_blank" class="hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>', '<a href="https://mudeo.app" target="_blank" class="hover:underline" title="Make music together">mudeo</a>'],  __('messages.supported_by_both')) !!}
            @endif
        </p>
      </div>
    </footer>
    @endif

</x-app-layout>