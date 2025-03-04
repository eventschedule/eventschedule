<x-app-layout>

    <x-slot name="head">
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700&display=swap" rel="stylesheet">
    </x-slot>

    <main
      class="font-['Manrope'] text-[15px] font-normal leading-[1.75em] flex flex-col gap-[10px] flex-1 relative z-0 overflow-y-auto p-[5px] sm:p-[10px] focus:outline-none"
      tabindex="0" 
    >
      <div
        class="bg-[#4e81fa26] pt-[20px] px-[15px] sm:px-[35px] pb-[20px] rounded-[24px] flex flex-col justify-between items-center w-full max-w-[400px] mx-auto"
      >
        <div
          class="uppercase font-extrabold leading-[1.2] text-center color-[#151B26] flex flex-col items-center"
        >
          @if ($role && $role->profile_image_url)
            <img
              class="w-[109px] mb-[18px] rounded-2xl object-cover"
              src="{{ $role->profile_image_url }}"
              alt="Logo"
            />
          @endif
          <h1 class="text-[32px] mb-2">{{ $event->name }}</h1>
        </div>
      </div>

      <p
          class="text-[12px] uppercase font-extrabold leading-[1.2] text-center color-[#151B26]"
        >
        @if ($event->event_url)
          <a href="{{ $event->event_url }}" target="_blank">
            {{ \App\Utils\UrlUtils::clean($event->event_url) }}
          </a>
        @elseif ($event->venue)
          <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->venue->bestAddress()) }}" target="_blank">
            {{ $event->venue->shortAddress() }}
        </a>
        @endif
      </p>

      <div
        class="bg-[#4E81FA] p-[12px] sm:p-[18px] rounded-[24px] flex flex-col justify-between w-full max-w-[400px] mx-auto text-white leading-[1.2] font-bold uppercase relative"
      >
        @if ($sale->status !== 'paid')
          <div class="absolute inset-0 flex items-center justify-center overflow-hidden pointer-events-none">
            <div class="text-white/30 text-[60px] font-extrabold rotate-[-45deg] whitespace-nowrap">
              {{ strtoupper($sale->status) }}
            </div>
          </div>
        @endif
        <div class="grid grid-cols-2 gap-x-[18px] gap-y-[12px]">
          <div class="flex gap-[8px] flex-row items-center">
            <svg
              width="18"
              height="18"
              viewBox="0 0 18 18"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g clip-path="url(#clip0_673_2337)">
                <path
                  d="M5.17194 0.665383C5.17194 0.298036 4.87019 0 4.49826 0C4.12633 0 3.82458 0.298036 3.82458 0.665383V2.07239C2.52633 2.17636 1.67721 2.42588 1.05265 3.04274C0.428085 3.65961 0.175454 4.49827 0.0701904 5.78052H17.9298C17.8246 4.49827 17.5719 3.65961 16.9474 3.04274C16.3228 2.42588 15.4737 2.17636 14.1755 2.07239V0.665383C14.1755 0.298036 13.8737 0 13.5018 0C13.1298 0 12.8281 0.298036 12.8281 0.665383V2.01001C12.2316 1.99615 11.5579 1.99615 10.8 1.99615H7.20002C6.44212 1.99615 5.77545 1.99615 5.17194 2.01001V0.665383Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M0 9.11436C0 8.36581 0 7.70735 0.0140351 7.11128H17.993C18.007 7.70042 18.007 8.36581 18.007 9.11436V10.8956C18.007 14.2503 18.007 15.9276 16.9544 16.9673C15.9018 18.0069 14.2035 18.0069 10.807 18.0069H7.20702C3.81053 18.0069 2.11228 18.0069 1.05965 16.9673C0.00701754 15.9276 0.00701754 14.2503 0.00701754 10.8956V9.11436H0ZM13.5018 10.8887C14 10.8887 14.4 10.4936 14.4 10.0015C14.4 9.50943 14 9.11436 13.5018 9.11436C13.0035 9.11436 12.6035 9.50943 12.6035 10.0015C12.6035 10.4936 13.0035 10.8887 13.5018 10.8887ZM13.5018 14.4444C14 14.4444 14.4 14.0493 14.4 13.5572C14.4 13.0651 14 12.67 13.5018 12.67C13.0035 12.67 12.6035 13.0651 12.6035 13.5572C12.6035 14.0493 13.0035 14.4444 13.5018 14.4444ZM9.90175 10.0015C9.90175 10.4936 9.50175 10.8887 9.00351 10.8887C8.50526 10.8887 8.10526 10.4936 8.10526 10.0015C8.10526 9.50943 8.50526 9.11436 9.00351 9.11436C9.50175 9.11436 9.90175 9.50943 9.90175 10.0015ZM9.90175 13.5572C9.90175 14.0493 9.50175 14.4444 9.00351 14.4444C8.50526 14.4444 8.10526 14.0493 8.10526 13.5572C8.10526 13.0651 8.50526 12.67 9.00351 12.67C9.50175 12.67 9.90175 13.0651 9.90175 13.5572ZM4.49825 10.8887C4.99649 10.8887 5.39649 10.4936 5.39649 10.0015C5.39649 9.50943 4.99649 9.11436 4.49825 9.11436C4 9.11436 3.6 9.50943 3.6 10.0015C3.6 10.4936 4 10.8887 4.49825 10.8887ZM4.49825 14.4444C4.99649 14.4444 5.39649 14.0493 5.39649 13.5572C5.39649 13.0651 4.99649 12.67 4.49825 12.67C4 12.67 3.6 13.0651 3.6 13.5572C3.6 14.0493 4 14.4444 4.49825 14.4444Z"
                  fill="white"
                />
              </g>
              <defs>
                <clipPath id="clip0_673_2337">
                  <rect width="18" height="18" fill="white" />
                </clipPath>
              </defs>
            </svg>

            <p class="text-[10px]">{{ $event->getStartDateTime($sale->date, true)->format('F j, Y') }}</p>
          </div>
          <div class="flex gap-[8px] flex-row items-center">
            <svg
              width="18"
              height="18"
              viewBox="0 0 18 18"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g clip-path="url(#clip0_673_2348)">
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M17.3755 10.0491C17.7193 10.0491 18 10.3298 18 10.6737V10.7228C18 12.2596 18 13.4807 17.8737 14.4351C17.7404 15.4175 17.4667 16.2105 16.8351 16.8351C16.2105 17.4596 15.4176 17.7403 14.4351 17.8737C13.4807 18 12.2597 18 10.7228 18H10.6737C10.3298 18 10.0491 17.7193 10.0491 17.3754C10.0491 17.0316 10.3298 16.7509 10.6737 16.7509C12.2667 16.7509 13.4035 16.7509 14.2667 16.6316C15.1088 16.5193 15.593 16.3088 15.9509 15.9509C16.3018 15.6 16.5193 15.1088 16.6316 14.2667C16.7439 13.4035 16.7509 12.2737 16.7509 10.6737C16.7509 10.3298 17.0316 10.0491 17.3755 10.0491Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M0.624561 10.0491C0.968421 10.0491 1.24912 10.3298 1.24912 10.6737C1.24912 12.2667 1.24912 13.4035 1.36842 14.2667C1.4807 15.1088 1.69123 15.593 2.04912 15.9509C2.4 16.3018 2.89123 16.5193 3.73333 16.6316C4.59649 16.7439 5.72632 16.7509 7.32632 16.7509C7.67018 16.7509 7.95088 17.0316 7.95088 17.3754C7.95088 17.7193 7.67018 18 7.32632 18H7.27719C5.74035 18 4.5193 18 3.56491 17.8737C2.58246 17.7403 1.78947 17.4667 1.16491 16.8351C0.540351 16.2105 0.259649 15.4105 0.126316 14.4351C0 13.4807 0 12.2596 0 10.7228C0 10.7088 0 10.6947 0 10.6737C0 10.3298 0.280702 10.0491 0.624561 10.0491Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M7.27719 0H7.32632C7.67018 0 7.95088 0.280702 7.95088 0.624561C7.95088 0.968421 7.67018 1.24912 7.32632 1.24912C5.73333 1.24912 4.59649 1.24912 3.73333 1.36842C2.89123 1.4807 2.40702 1.69123 2.04912 2.04912C1.69825 2.4 1.4807 2.89123 1.36842 3.73333C1.25614 4.59649 1.24912 5.72632 1.24912 7.32632C1.24912 7.67018 0.968421 7.95088 0.624561 7.95088C0.280702 7.95088 0 7.67018 0 7.32632V7.27719C0 5.74035 0 4.5193 0.126316 3.56491C0.259649 2.58246 0.533333 1.78947 1.16491 1.16491C1.78947 0.540351 2.58246 0.259649 3.56491 0.126316C4.5193 0 5.74035 0 7.27719 0Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M14.2667 1.37544C13.4035 1.26316 12.2737 1.25614 10.6737 1.25614C10.3298 1.25614 10.0491 0.975439 10.0491 0.631579C10.0491 0.287719 10.3298 0 10.6737 0H10.7228C12.2597 0 13.4807 0 14.4351 0.126316C15.4176 0.259649 16.2105 0.533333 16.8351 1.16491C17.4597 1.78947 17.7404 2.58947 17.8737 3.56491C18 4.5193 18 5.74035 18 7.27719V7.32632C18 7.67018 17.7193 7.95088 17.3755 7.95088C17.0316 7.95088 16.7509 7.67018 16.7509 7.32632C16.7509 5.73333 16.7509 4.59649 16.6316 3.73333C16.5193 2.89123 16.3088 2.40702 15.9509 2.04912C15.6 1.69825 15.1088 1.4807 14.2667 1.36842V1.37544Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M3.0386 3.0386C2.30176 3.77544 2.30176 4.96141 2.30176 7.32632V10.6737C2.30176 13.0386 2.30176 14.2246 3.0386 14.9614C3.77544 15.6982 4.96141 15.6983 7.32632 15.6983H10.6737C13.0386 15.6983 14.2246 15.6982 14.9614 14.9614C15.6982 14.2246 15.6983 13.0386 15.6983 10.6737V7.32632C15.6983 4.96141 15.6982 3.77544 14.9614 3.0386C14.2246 2.30176 13.0386 2.30176 10.6737 2.30176H7.32632C4.96141 2.30176 3.77544 2.30176 3.0386 3.0386ZM6.3579 11.2912C6.54036 11.0456 6.88421 10.9965 7.12983 11.179C7.66316 11.5719 8.30878 11.8035 9.00351 11.8035C9.69825 11.8035 10.3439 11.5719 10.8772 11.179C11.1228 10.9965 11.4667 11.0526 11.6491 11.2912C11.8316 11.5368 11.7754 11.8807 11.5368 12.0632C10.8211 12.5895 9.95088 12.9053 9.01053 12.9053C8.07018 12.9053 7.2 12.5895 6.48421 12.0632C6.2386 11.8807 6.18948 11.5368 6.37193 11.2912H6.3579ZM11.9298 7.58597C11.9298 8.18948 11.6 8.68772 11.2 8.68772C10.8 8.68772 10.4702 8.1965 10.4702 7.58597C10.4702 6.97544 10.8 6.48421 11.2 6.48421C11.6 6.48421 11.9298 6.97544 11.9298 7.58597ZM6.8 8.68772C7.20702 8.68772 7.52983 8.1965 7.52983 7.58597C7.52983 6.97544 7.2 6.48421 6.8 6.48421C6.4 6.48421 6.07018 6.97544 6.07018 7.58597C6.07018 8.1965 6.4 8.68772 6.8 8.68772Z"
                  fill="white"
                />
              </g>
              <defs>
                <clipPath id="clip0_673_2348">
                  <rect width="18" height="18" fill="white" />
                </clipPath>
              </defs>
            </svg>

            <p class="text-[10px]">{{ __('messages.attendee') }}: {{ $sale->name }}</p>
          </div>
          <div class="flex gap-[8px] flex-row items-center">
            <svg
              width="18"
              height="18"
              viewBox="0 0 18 18"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g clip-path="url(#clip0_673_2344)">
                <path
                  d="M15.6133 5.05201L12.0476 1.84359C11.033 0.928942 10.5256 0.471617 9.90393 0.235809V2.69393C9.89679 4.8162 9.89679 5.87376 10.5542 6.53117C11.2116 7.18857 12.2763 7.18857 14.3914 7.18857H17.6141C17.2854 6.5526 16.7066 6.03097 15.6062 5.04486L15.6133 5.05201Z"
                  fill="white"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M10.7971 18H7.19572C3.80151 18 2.10798 18 1.05042 16.9424C-0.00714111 15.8849 -0.00714111 14.1913 -0.00714111 10.7971V7.19571C4.57931e-06 3.80865 4.57279e-06 2.10798 1.05757 1.05756C2.10798 0 3.8158 0 7.2243 0C7.76737 0 8.20326 0 8.57484 0.0142914C8.56054 0.0857483 8.5534 0.157205 8.5534 0.235808V2.78682C8.54625 3.77293 8.54625 4.6447 8.63915 5.35212C8.73919 6.11671 8.97499 6.87416 9.59667 7.49583C10.2183 8.11751 10.9829 8.35331 11.7404 8.45335C12.4407 8.54625 13.3196 8.54625 14.3057 8.54625H17.95C17.9857 9.02501 17.9857 9.6181 17.9857 10.4041V10.7971C17.9857 14.1913 17.9857 15.8849 16.9282 16.9424C15.8706 18 14.1771 18 10.7829 18H10.7971ZM7.6459 11.1973C7.92458 11.4474 7.94601 11.869 7.70306 12.1477L5.30211 14.8488C5.17349 14.9917 4.9877 15.0774 4.79476 15.0774C4.60183 15.0774 4.41604 14.9917 4.28742 14.8488L3.08694 13.4982C2.83684 13.2195 2.86543 12.7908 3.14411 12.5478C3.42279 12.2977 3.85153 12.3263 4.09449 12.605L4.78762 13.3839L6.68123 11.2545C6.93133 10.9758 7.35292 10.9472 7.6316 11.1973H7.6459Z"
                  fill="white"
                />
              </g>
              <defs>
                <clipPath id="clip0_673_2344">
                  <rect width="18" height="18" fill="white" />
                </clipPath>
              </defs>
            </svg>
            <p class="text-[10px]">{{ $event->getStartEndTime($sale->date) }}</p>
          </div>
          <div class="flex gap-[8px] flex-row items-center">
            <svg
              width="18"
              height="18"
              viewBox="0 0 18 18"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <g clip-path="url(#clip0_673_2341)">
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M0 9C0 4.02667 4.02667 0 9 0C13.9733 0 18 4.02667 18 9C18 13.9733 13.9733 18 9 18C4.02667 18 0 13.9733 0 9ZM9.67333 5.4C9.67333 5.02667 9.37333 4.72667 9 4.72667C8.62667 4.72667 8.32667 5.02667 8.32667 5.4V9C8.32667 9.18 8.4 9.35333 8.52667 9.48L10.7733 11.7267C11.04 11.9933 11.4667 11.9933 11.7267 11.7267C11.9933 11.46 11.9933 11.0333 11.7267 10.7733L9.67333 8.72V5.4Z"
                  fill="white"
                />
              </g>
              <defs>
                <clipPath id="clip0_673_2341">
                  <rect width="18" height="18" fill="white" />
                </clipPath>
              </defs>
            </svg>

            <p class="text-[10px]">{{ __('messages.number_of_attendees') }}: {{ $sale->quantity() }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-x-[18px] gap-y-[12px] mt-[20px]">
          <div>
            <p class="text-[44px] leading-[0.8]">{{ __('messages.ticket') }}</p>
            @foreach ($sale->saleTickets as $saleTicket)
              <p class="text-[14px] leading-[1.2] pt-[12px]">{{ $saleTicket->ticket->type ?: __('messages.ticket') }} x {{ $saleTicket->quantity }}</p>
            @endforeach
          </div>
          <div class="justify-center flex">
            <img class="w-[82px] h-[82px]" src="{{ route('ticket.qr_code', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}" alt="QR Code" />
          </div>
        </div>
      </div>

      <div
        class="bg-[#4e81fa26] p-[12px] sm:p-[18px] rounded-[24px] flex flex-col justify-between w-full max-w-[400px] h-[185px] mx-auto"
      >
        @if ($event->ticket_notes_html)
        <div class="flex flex-col gap-[14px] text-[10px] uppercase leading-[1]">
          <div>
            <p class="mb-[8px] font-extrabold text-[#4E81FA]">{{ __('messages.notes') }}:</p>
            <div class="font-bold text-[#151B26] custom-content">
              {!! $event->ticket_notes_html !!}
            </div>
          </div>
        </div>
        @endif

        <div
          class="flex flex-row justify-between gap-[14px] text-[10px] uppercase leading-[1]"
        >
          <div>
            <p class="mb-[8px] font-extrabold text-[#4E81FA]">
              {{ __('messages.terms_and_conditions') }}
            </p>
            <p class="font-bold text-[#151B26]"><a href="https://www.eventschedule.com/' . (config('app.hosted') ? 'terms-of-service' : 'self-hosting-terms-of-service') . '" target="_blank">eventschedule.com/terms</a></p>
          </div>
          <div>
            <p class="mb-[8px] font-extrabold text-[#4E81FA]">
              {{ __('messages.event_support_contact') }}:
            </p>
            <p class="font-bold text-[#151B26]"><a href="mailto:{{ $event->user->email }}" target="_blank">{{ $event->user->email }}</a></p>
          </div>
        </div>
      </div>
    </main>

</x-app-layout>