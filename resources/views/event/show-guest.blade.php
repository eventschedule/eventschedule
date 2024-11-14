<x-app-guest-layout :role="$role" :event="$event" :date="$date">

  <main>
    <div
      class="bg-center bg-cover relative before:bg-[#1B212B80] before:absolute before:inset-0 before:z-0"
      style="background-image: url(./images/yoga.jpeg)"
    >
      <div class="container mx-auto pt-[100px] pb-10 px-5 relative z-10">
        <div class="flex flex-col sm:flex-row justify-between mb-10 py-[7px]">
          <h2
            class="text-white text-[40px] sm:text-{52px} leading-snug font-semibold"
          >
            {{ $event->name }}
          </h2>
          <a href="{{ route('role.follow', ['subdomain' => $event->role()->subdomain]) }}" class="inline-flex items-center">
            <button
              type="button"
              name="follow"
              class="inline-flex rounded-xl text-base duration-300 bg-[#4E81FA] hover:bg-[#1A48B3] text-white py-4 px-8 hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-90"
            >
              {{ __('messages.follow') }}
            </button>
          </a>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 items-center">
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M8.17 2.76C9.39 2.26 10.69 2 12 2C13.31 2 14.61 2.26 15.83 2.76C17.04 3.26 18.14 4 19.07 4.93C20 5.86 20.74 6.96 21.24 8.17C21.74 9.39 22 10.69 22 12C22 14.65 20.95 17.2 19.07 19.07C17.2 20.95 14.65 22 12 22C10.69 22 9.39 21.74 8.17 21.24C6.96 20.74 5.86 20 4.93 19.07C3.05 17.2 2 14.65 2 12C2 9.35 3.05 6.8 4.93 4.93C5.86 4 6.96 3.26 8.17 2.76M12 17L13.56 13.58L17 12L13.56 10.44L12 7L10.43 10.44L7 12L10.43 13.58L12 17Z"
              />
            </svg>
            @if ($event->venue->isClaimed())
              <a href="{{ route('event.view_guest', ['subdomain' => $event->venue->subdomain]) }}" class="text-sm hover:underline">
                {{ $event->venue->name }}
              </a>
            @else
              <p class="text-sm">{{ $event->venue->name }}</p>
            @endif
          </div>
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.75 2.5C7.75 2.08579 7.41421 1.75 7 1.75C6.58579 1.75 6.25 2.08579 6.25 2.5V4.07926C4.81067 4.19451 3.86577 4.47737 3.17157 5.17157C2.47737 5.86577 2.19451 6.81067 2.07926 8.25H21.9207C21.8055 6.81067 21.5226 5.86577 20.8284 5.17157C20.1342 4.47737 19.1893 4.19451 17.75 4.07926V2.5C17.75 2.08579 17.4142 1.75 17 1.75C16.5858 1.75 16.25 2.08579 16.25 2.5V4.0129C15.5847 4 14.839 4 14 4H10C9.16097 4 8.41527 4 7.75 4.0129V2.5Z"
              />
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2 12C2 11.161 2 10.4153 2.0129 9.75H21.9871C22 10.4153 22 11.161 22 12V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.8284C2 19.6569 2 17.7712 2 14V12ZM17 14C17.5523 14 18 13.5523 18 13C18 12.4477 17.5523 12 17 12C16.4477 12 16 12.4477 16 13C16 13.5523 16.4477 14 17 14ZM17 18C17.5523 18 18 17.5523 18 17C18 16.4477 17.5523 16 17 16C16.4477 16 16 16.4477 16 17C16 17.5523 16.4477 18 17 18ZM13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.5523 12 13 12.4477 13 13ZM13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17ZM7 14C7.55228 14 8 13.5523 8 13C8 12.4477 7.55228 12 7 12C6.44772 12 6 12.4477 6 13C6 13.5523 6.44772 14 7 14ZM7 18C7.55228 18 8 17.5523 8 17C8 16.4477 7.55228 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z"
              />
            </svg>
            <p class="text-sm">{{ $event->getStartDateTime($date)->format('F j, Y') }}</p>
          </div>
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white sm:pr-4 sm:after:content-[''] sm:after:block sm:after:absolute sm:after:right-0 sm:after:top-[50%] sm:after:translate-y-[-50%] sm:after:h-[12px] sm:after:w-[1px] sm:after:bg-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM12.75 8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V12C11.25 12.1989 11.329 12.3897 11.4697 12.5303L13.9697 15.0303C14.2626 15.3232 14.7374 15.3232 15.0303 15.0303C15.3232 14.7374 15.3232 14.2626 15.0303 13.9697L12.75 11.6893V8Z"
              />
            </svg>
            <p class="text-sm">
              {{ $event->getStartEndTime($date) }}
            </p>
          </div>
          <div
            class="flex flex-row gap-2 items-center relative text-white fill-white"
          >
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M12 2C7.58172 2 4 6.00258 4 10.5C4 14.9622 6.55332 19.8124 10.5371 21.6744C11.4657 22.1085 12.5343 22.1085 13.4629 21.6744C17.4467 19.8124 20 14.9622 20 10.5C20 6.00258 16.4183 2 12 2ZM12 12C13.1046 12 14 11.1046 14 10C14 8.89543 13.1046 8 12 8C10.8954 8 10 8.89543 10 10C10 11.1046 10.8954 12 12 12Z"
              />
            </svg>
            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->venue->bestAddress()) }}" target="_blank">
              <p class="text-sm hover:underline">
                {{ $event->venue->shortAddress() }}
              </p>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div
      class="container mx-auto flex flex-col-reverse sm:grid px-5 py-[80px] gap-[48px] sm:grid-cols-[minmax(0px,_auto)_minmax(0px,_344px)]"
    >
      <div class="flex flex-col gap-10">
        <div>
          <h2
            class="text-[#151B26] text-[40px] sm:text-{52px} leading-snug font-semibold mb-6"
          >
            About event
          </h2>
          <p class="text-[#33383C] text-base mb-10">
            Join us for an immersive Women's Breathwork Session, designed to
            help you relax, heal, and connect. This session will guide you
            through a series of breathing techniques to help release stress
            and improve mindfulness. Whether you're joining in-person or
            virtually, we invite all women to be part of this transformative
            experience.
          </p>
        </div>
        <div>
          <h3 class="text-[32px] leading-snug text-black mb-6">
            Date & time
          </h3>
          <div class="flex flex-col gap-3">
            <div
              class="flex flex-row gap-2 items-center relative text-[#33383C] fill-[#33383C]"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M7.75 2.5C7.75 2.08579 7.41421 1.75 7 1.75C6.58579 1.75 6.25 2.08579 6.25 2.5V4.07926C4.81067 4.19451 3.86577 4.47737 3.17157 5.17157C2.47737 5.86577 2.19451 6.81067 2.07926 8.25H21.9207C21.8055 6.81067 21.5226 5.86577 20.8284 5.17157C20.1342 4.47737 19.1893 4.19451 17.75 4.07926V2.5C17.75 2.08579 17.4142 1.75 17 1.75C16.5858 1.75 16.25 2.08579 16.25 2.5V4.0129C15.5847 4 14.839 4 14 4H10C9.16097 4 8.41527 4 7.75 4.0129V2.5Z"
                />
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 12C2 11.161 2 10.4153 2.0129 9.75H21.9871C22 10.4153 22 11.161 22 12V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.8284C2 19.6569 2 17.7712 2 14V12ZM17 14C17.5523 14 18 13.5523 18 13C18 12.4477 17.5523 12 17 12C16.4477 12 16 12.4477 16 13C16 13.5523 16.4477 14 17 14ZM17 18C17.5523 18 18 17.5523 18 17C18 16.4477 17.5523 16 17 16C16.4477 16 16 16.4477 16 17C16 17.5523 16.4477 18 17 18ZM13 13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13C11 12.4477 11.4477 12 12 12C12.5523 12 13 12.4477 13 13ZM13 17C13 17.5523 12.5523 18 12 18C11.4477 18 11 17.5523 11 17C11 16.4477 11.4477 16 12 16C12.5523 16 13 16.4477 13 17ZM7 14C7.55228 14 8 13.5523 8 13C8 12.4477 7.55228 12 7 12C6.44772 12 6 12.4477 6 13C6 13.5523 6.44772 14 7 14ZM7 18C7.55228 18 8 17.5523 8 17C8 16.4477 7.55228 16 7 16C6.44772 16 6 16.4477 6 17C6 17.5523 6.44772 18 7 18Z"
                />
              </svg>
              <p class="text-sm">October 1, 2024</p>
            </div>
            <div
              class="flex flex-row gap-2 items-center relative text-[#33383C] fill-[#33383C]"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM12.75 8C12.75 7.58579 12.4142 7.25 12 7.25C11.5858 7.25 11.25 7.58579 11.25 8V12C11.25 12.1989 11.329 12.3897 11.4697 12.5303L13.9697 15.0303C14.2626 15.3232 14.7374 15.3232 15.0303 15.0303C15.3232 14.7374 15.3232 14.2626 15.0303 13.9697L12.75 11.6893V8Z"
                />
              </svg>
              <p class="text-sm">6:00 PM - 8:00 PM</p>
            </div>
          </div>
        </div>
        <div>
          <h3 class="text-[32px] leading-snug text-black mb-6">
            Event Format
          </h3>
          <p class="text-[#33383C] text-base mb-10">
            In-person event: Located at Pine Wellness Studio. Bring your yoga
            mat and comfortable clothing.
          </p>
        </div>
        <div>
          <h3
            class="text-[32px] leading-snug font-semibold text-[#151B26] mb-6"
          >
            Event Hosts
          </h3>
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6 flex flex-col gap-4">
            <div
              class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center"
            >
              <div class="flex gap-3 flex-row items-center">
                <img
                  src="./images/person.png"
                  class="rounded-full w-[56px] h-[56px]"
                />
                <a
                  href="#"
                  class="text-base text-[#151B26] hover:text-[#4E81FA] cursor-pointer duration-300"
                >
                  Cameron Williamson
                </a>
              </div>
              <a href="/follow" class="inline-flex items-center">
                <button
                  type="button"
                  name="follow"
                  class="inline-flex rounded-xl text-base duration-300 bg-[#4E81FA] hover:bg-[#1A48B3] text-white py-4 px-8 hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-90"
                >
                  Follow
                </button>
              </a>
            </div>
            <div class="flex gap-3 justify-start flex-col sm:flex-row mb-6">
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Personal coach
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Yoga trainer
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Fitness trainer
                </p>
              </div>
            </div>
            <p class="text-base text-[#33383C]">
              Cameron Williamson has been living and teaching wellness
              practices for most of his life and has inspired hundreds of
              students over the years both in America and Israel. He made
              aliyah from Florida ten years ago and after living in Pardes
              Hana for ten years he now resides in Efrat. When David is not
              teaching Yoga or Qi Gong he is seeing clients in his clinic in
              Efrat and Pardes Hana for treatments in Acupuncture,
              Craniosacral Therapy and Internal Family Systems.
            </p>
          </div>
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6 flex flex-col gap-4">
            <div
              class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center"
            >
              <div class="flex gap-3 flex-row items-center">
                <img
                  src="./images/person.png"
                  class="rounded-full w-[56px] h-[56px]"
                />
                <a
                  href="#"
                  class="text-base text-[#151B26] hover:text-[#4E81FA] cursor-pointer duration-300"
                >
                  Kathryn Murphy
                </a>
              </div>
              <a href="/follow" class="inline-flex items-center">
                <button
                  type="button"
                  name="follow"
                  class="inline-flex rounded-xl text-base duration-300 bg-[#4E81FA] hover:bg-[#1A48B3] text-white py-4 px-8 hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-90"
                >
                  Follow
                </button>
              </a>
            </div>
            <div class="flex gap-3 justify-start flex-col sm:flex-row mb-6">
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Yoga trainer
                </p>
              </div>
              <div class="py-3 px-4 bg-white rounded-[32px] text-center">
                <p class="text-sm font-semibold text-[#4E81FA]">
                  Fitness trainer
                </p>
              </div>
            </div>
            <p class="text-base text-[#33383C]">
              Kathryn Murphy is a certified yoga trainer with over 10 years of
              experience in mindfulness and holistic wellness practices. Known
              for her calming presence and deep understanding of body-mind
              connections, she has helped countless individuals find balance
              and inner peace through tailored yoga sessions.
            </p>
          </div>
        </div>
        <div>
          <h3
            class="text-[32px] leading-snug font-semibold text-[#151B26] mb-6"
          >
            Related events
          </h3>
          <div class="bg-[#F5F9FE] rounded-2xl p-8 mb-6">
            <h4
              class="text-[24px] font-semibold leading-10 text-[#151B26] mb-6"
            >
              November 2024
            </h4>
          </div>
        </div>
        <div>
          <h3
            class="text-[32px] leading-snug font-semibold text-[#151B26] mb-6"
          >
            Photo from past events
          </h3>
          <div class="grid gap-4 grid-cols-3">
            <img src="./images/event-photo.png" class="rounded-xl" />
            <img src="./images/event-photo.png" class="rounded-xl" />
            <img src="./images/event-photo.png" class="rounded-xl" />
            <img src="./images/event-photo.png" class="rounded-xl" />
            <img src="./images/event-photo.png" class="rounded-xl" />
          </div>
        </div>
      </div>
      <div class="flex flex-col gap-6">
        <div class="p-6 rounded-xl flex flex-col gap-6 bg-[#4E81FA]">
          <h4 class="text-white text-[24px] leading-snug font-semibold">
            Contact Event Host
          </h4>
          <div class="flex flex-col gap-4">
            <div
              class="flex flex-row gap-2 items-center relative duration-300 text-white fill-white hover:text-[#151B26] hover:fill-[#151B26]"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M10.0376 5.31617L10.6866 6.4791C11.2723 7.52858 11.0372 8.90532 10.1147 9.8278C10.1147 9.8278 10.1147 9.8278 10.1147 9.8278C10.1146 9.82792 8.99588 10.9468 11.0245 12.9755C13.0525 15.0035 14.1714 13.8861 14.1722 13.8853C14.1722 13.8853 14.1722 13.8853 14.1722 13.8853C15.0947 12.9628 16.4714 12.7277 17.5209 13.3134L18.6838 13.9624C20.2686 14.8468 20.4557 17.0692 19.0628 18.4622C18.2258 19.2992 17.2004 19.9505 16.0669 19.9934C14.1588 20.0658 10.9183 19.5829 7.6677 16.3323C4.41713 13.0817 3.93421 9.84122 4.00655 7.93309C4.04952 6.7996 4.7008 5.77423 5.53781 4.93723C6.93076 3.54428 9.15317 3.73144 10.0376 5.31617Z"
                />
              </svg>
              <a href="tel:+1 (646) 555-3904" class="text-sm"
                >+1 (646) 555-3904</a
              >
            </div>
            <div
              class="flex flex-row gap-2 items-center relative duration-300 text-white fill-white hover:text-[#151B26] hover:fill-[#151B26]"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M3.17157 5.17157C2 6.34315 2 8.22876 2 12C2 15.7712 2 17.6569 3.17157 18.8284C4.34315 20 6.22876 20 10 20H14C17.7712 20 19.6569 20 20.8284 18.8284C22 17.6569 22 15.7712 22 12C22 8.22876 22 6.34315 20.8284 5.17157C19.6569 4 17.7712 4 14 4H10C6.22876 4 4.34315 4 3.17157 5.17157ZM18.5762 7.51986C18.8413 7.83807 18.7983 8.31099 18.4801 8.57617L16.2837 10.4066C15.3973 11.1452 14.6789 11.7439 14.0448 12.1517C13.3843 12.5765 12.7411 12.8449 12 12.8449C11.2589 12.8449 10.6157 12.5765 9.95518 12.1517C9.32112 11.7439 8.60271 11.1452 7.71636 10.4066L5.51986 8.57617C5.20165 8.31099 5.15866 7.83807 5.42383 7.51986C5.68901 7.20165 6.16193 7.15866 6.48014 7.42383L8.63903 9.22291C9.57199 10.0004 10.2197 10.5384 10.7666 10.8901C11.2959 11.2306 11.6549 11.3449 12 11.3449C12.3451 11.3449 12.7041 11.2306 13.2334 10.8901C13.7803 10.5384 14.428 10.0004 15.361 9.22291L17.5199 7.42383C17.8381 7.15866 18.311 7.20165 18.5762 7.51986Z"
                />
              </svg>
              <a href="mailto:CameronWW@gmail.com" class="text-sm"
                >CameronWW@gmail.com</a
              >
            </div>
            <div
              class="flex flex-row gap-2 items-center relative duration-300 text-white fill-white hover:text-[#151B26] hover:fill-[#151B26]"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M2.02783 11.25C2.41136 6.07745 6.72957 2 12.0001 2C11.1693 2 10.4295 2.36421 9.82093 2.92113C9.21541 3.47525 8.70371 4.24878 8.28983 5.16315C7.87352 6.08292 7.55013 7.15868 7.33126 8.32611C7.1558 9.26194 7.04903 10.2485 7.01344 11.25H2.02783ZM2.02783 12.75H7.01344C7.04903 13.7515 7.1558 14.7381 7.33126 15.6739C7.55013 16.8413 7.87351 17.9171 8.28983 18.8368C8.70371 19.7512 9.21541 20.5247 9.82093 21.0789C10.4295 21.6358 11.1693 22 12.0001 22C6.72957 22 2.41136 17.9226 2.02783 12.75Z"
                />
                <path
                  d="M12.0001 3.39535C11.7251 3.39535 11.3699 3.51236 10.9567 3.89042C10.5406 4.27126 10.1239 4.86815 9.75585 5.68137C9.3902 6.4892 9.09329 7.46441 8.88897 8.55419C8.72806 9.41242 8.62824 10.3222 8.59321 11.25H15.4071C15.372 10.3222 15.2722 9.41242 15.1113 8.5542C14.907 7.46441 14.6101 6.48921 14.2444 5.68137C13.8763 4.86815 13.4597 4.27126 13.0435 3.89042C12.6304 3.51236 12.2751 3.39535 12.0001 3.39535Z"
                />
                <path
                  d="M8.88897 15.4458C9.09329 16.5356 9.3902 17.5108 9.75585 18.3186C10.1239 19.1319 10.5406 19.7287 10.9567 20.1096C11.3698 20.4876 11.7251 20.6047 12.0001 20.6047C12.2751 20.6047 12.6304 20.4876 13.0435 20.1096C13.4597 19.7287 13.8763 19.1319 14.2444 18.3186C14.6101 17.5108 14.907 16.5356 15.1113 15.4458C15.2722 14.5876 15.372 13.6778 15.4071 12.75H8.59321C8.62824 13.6778 8.72806 14.5876 8.88897 15.4458Z"
                />
                <path
                  d="M12.0001 2C12.831 2 13.5708 2.36421 14.1793 2.92113C14.7849 3.47525 15.2966 4.24878 15.7104 5.16315C16.1267 6.08292 16.4501 7.15868 16.669 8.32612C16.8445 9.26194 16.9512 10.2485 16.9868 11.25H21.9724C21.5889 6.07745 17.2707 2 12.0001 2Z"
                />
                <path
                  d="M16.669 15.6739C16.4501 16.8413 16.1267 17.9171 15.7104 18.8368C15.2966 19.7512 14.7849 20.5247 14.1793 21.0789C13.5708 21.6358 12.831 22 12.0001 22C17.2707 22 21.5889 17.9226 21.9724 12.75H16.9868C16.9512 13.7515 16.8445 14.7381 16.669 15.6739Z"
                />
              </svg>
              <a href="www.events.com" class="text-sm">www.events.com</a>
            </div>
          </div>

          <div class="flex flex-row gap-4 items-center">
            <a
              href="https://x.com/ScheduleEvent"
              target="_blank"
              class="w-[44px] h-[44px] rounded-full flex justify-center items-center bg-white hover:bg-[#151B26] duration-300"
            >
              <svg
                width="16"
                height="17"
                viewBox="0 0 16 17"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M13.6141 4.55592C14.2725 4.17092 14.7084 3.64543 14.9218 2.97712C14.2814 3.3008 13.6435 3.52279 13.0063 3.64543C12.4335 3.05862 11.7084 2.76056 10.839 2.76056C9.98922 2.76056 9.27141 3.04465 8.68559 3.59731C8.10303 4.15384 7.8089 4.82992 7.8089 5.61931C7.8089 5.85916 7.84067 6.07339 7.90422 6.25502C5.39799 6.17352 3.32359 5.18309 1.68916 3.27673C1.41214 3.74323 1.27363 4.21827 1.27363 4.7096C1.27363 5.71944 1.72094 6.51971 2.61393 7.10729C2.10469 7.06615 1.65576 6.94352 1.27363 6.74248C1.27363 7.46357 1.49606 8.0659 1.94174 8.59605C2.38905 9.12076 2.96346 9.45531 3.66498 9.59813C3.41077 9.66022 3.14271 9.68972 2.86732 9.68972C2.61311 9.68972 2.43305 9.66954 2.32631 9.62529C2.51371 10.2129 2.86732 10.6895 3.37818 11.0543C3.88741 11.4207 4.47323 11.6139 5.13156 11.631C4.04629 12.4421 2.8054 12.8434 1.40155 12.8434C1.05772 12.8434 0.814099 12.8388 0.666626 12.8139C2.04929 13.6693 3.58839 14.0939 5.29126 14.0939C7.03405 14.0939 8.57397 13.6732 9.91426 12.8318C11.2546 11.995 12.2437 10.9627 12.8792 9.74716C13.518 8.53318 13.8341 7.26952 13.8341 5.94998V5.58671C14.4517 5.14428 14.9512 4.64596 15.3333 4.09563C14.7792 4.32305 14.2065 4.47752 13.6141 4.55592Z"
                  fill="#4E81FA"
                />
              </svg>
            </a>
            <a
              href="https://www.facebook.com/appeventschedule"
              target="_blank"
              class="w-[44px] h-[44px] rounded-full flex justify-center items-center bg-white hover:bg-[#151B26] duration-300"
              ><svg
                width="16"
                height="17"
                viewBox="0 0 16 17"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M11.3398 8.92719L11.7101 6.51416H9.39475V4.94828C9.39475 4.28812 9.71819 3.64463 10.7552 3.64463H11.8078V1.59021C11.8078 1.59021 10.8526 1.42719 9.93928 1.42719C8.03251 1.42719 6.78616 2.58291 6.78616 4.6751V6.51416H4.66663V8.92719H6.78616V14.7605H9.39475V8.92719H11.3398Z"
                  fill="#4E81FA"
                />
              </svg>
            </a>
            <a
              href="https://www.linkedin.com/company/eventschedule/"
              target="_blank"
              class="w-[44px] h-[44px] rounded-full flex justify-center items-center bg-white hover:bg-[#151B26] duration-300"
            >
              <svg
                width="16"
                height="17"
                viewBox="0 0 16 17"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g clip-path="url(#clip0_491_201)">
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M4.36737 14.4537V5.87129H1.51506V14.4537H4.36737V14.4537ZM2.94151 4.69994C3.93586 4.69994 4.55526 4.04035 4.55526 3.21679C4.53646 2.37491 3.93586 1.73395 2.96016 1.73395C1.9843 1.73397 1.34644 2.37494 1.34644 3.21681C1.34644 4.04038 1.9655 4.69996 2.92274 4.69996L2.94151 4.69994ZM5.94602 14.4537C5.94602 14.4537 5.98344 6.67655 5.94602 5.87132H8.79878V7.11595H8.77985C9.15495 6.53028 9.83073 5.66978 11.3694 5.66978C13.2466 5.66978 14.6537 6.89642 14.6537 9.53262V14.4537H11.8014V9.86239C11.8014 8.70871 11.3887 7.92147 10.3562 7.92147C9.56831 7.92147 9.09878 8.4522 8.89258 8.96522C8.81714 9.14783 8.79878 9.40431 8.79878 9.66082V14.4537H5.94602Z"
                    fill="#4E81FA"
                  />
                </g>
                <defs>
                  <clipPath id="clip0_491_201">
                    <rect
                      width="13.3333"
                      height="13.3333"
                      fill="white"
                      transform="translate(1.33337 1.42719)"
                    />
                  </clipPath>
                </defs>
              </svg>
            </a>
            <a
              href="#"
              target="_blank"
              class="w-[44px] h-[44px] rounded-full flex justify-center items-center bg-white hover:bg-[#151B26] duration-300"
            >
              <svg
                width="16"
                height="17"
                viewBox="0 0 16 17"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M10.0749 14.5607C9.53769 14.778 8.95781 14.8693 8.37979 14.8277C7.80317 14.8753 7.22354 14.7837 6.68968 14.5607C6.61607 14.5012 6.55686 14.4258 6.51647 14.3402C6.47608 14.2546 6.45555 14.161 6.45642 14.0664C6.45642 13.8811 6.44968 13.3944 6.44631 12.7468C4.28715 13.2159 3.83157 11.706 3.83157 11.706C3.68881 11.2368 3.38295 10.8342 2.96926 10.5708C2.26526 10.0891 3.02231 10.0992 3.02231 10.0992C3.26833 10.1333 3.50334 10.223 3.7094 10.3617C3.91547 10.5003 4.08715 10.6842 4.21136 10.8992C4.31653 11.0907 4.4586 11.2593 4.62937 11.3955C4.80013 11.5316 4.9962 11.6326 5.20623 11.6925C5.41626 11.7524 5.63608 11.7701 5.85299 11.7445C6.06989 11.7189 6.27957 11.6506 6.46989 11.5434C6.50544 11.1496 6.68044 10.7814 6.96336 10.5051C5.24042 10.3098 3.42821 9.64365 3.42821 6.67018C3.41689 5.89897 3.70274 5.15298 4.22652 4.58681C3.98998 3.91763 4.01739 3.18345 4.30315 2.53376C4.30315 2.53376 4.9541 2.32492 6.43705 3.32955C7.70902 2.98082 9.05139 2.98082 10.3234 3.32955C11.8055 2.32492 12.4556 2.53376 12.4556 2.53376C12.7419 3.18328 12.7696 3.91756 12.533 4.58681C13.0568 5.15292 13.3424 5.89902 13.3305 6.67018C13.3305 9.65123 11.5158 10.3081 9.78694 10.5001C9.97214 10.6881 10.1149 10.9136 10.2057 11.1614C10.2965 11.4092 10.3332 11.6735 10.3133 11.9367C10.3133 12.9742 10.304 13.8121 10.304 14.0664C10.3088 14.1612 10.2905 14.2557 10.2506 14.3419C10.2107 14.428 10.1504 14.5031 10.0749 14.5607Z"
                  fill="#4E81FA"
                />
              </svg>
            </a>
          </div>
        </div>
        <div class="p-6 rounded-xl flex flex-col gap-6 bg-[#F5F9FE]">
          <h4 class="text-[#151B26] text-[24px] leading-snug font-semibold">
            Add to Calendar
          </h4>
          <div class="flex flex-row gap-3">
            <a href="#" target="_blank"
              ><img src="./images/google-meet.svg" class="w-[44px] h-[44px]"
            /></a>
            <a href="#" target="_blank"
              ><img src="./images/teams.svg" class="w-[44px] h-[44px]"
            /></a>
          </div>
        </div>
        <div class="p-6 rounded-xl flex flex-col gap-6 bg-[#F5F9FE]">
          <h4 class="text-[#151B26] text-[24px] leading-snug font-semibold">
            Create your Own ‘Event Schedule’!
          </h4>
          <a href="https://eventschedule.com/login">
            <button
              type="button"
              name="login"
              class="inline-flex items-center justify-center rounded-xl text-base duration-300 bg-transparent border-[1px] border-[#4E81FA] hover:border-[#1A48B3] hover:bg-[#1A48B3] text-[#4E81FA] hover:text-white py-4 px-8 hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-90"
            >
              Create Event
            </button>
          </a>
        </div>
      </div>
    </div>
  </main>

</x-app-guest-layout>