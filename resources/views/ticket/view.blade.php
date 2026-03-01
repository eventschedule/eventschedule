<x-app-layout>

    <x-slot name="head">
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style {!! nonce_attr() !!}>
            /* Animations */
            @keyframes pulse-slow {
                0%, 100% { opacity: 0.6; transform: scale(1); }
                50% { opacity: 0.3; transform: scale(1.1); }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            .animate-pulse-slow {
                animation: pulse-slow 8s ease-in-out infinite;
            }
            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
            .animate-float-delayed {
                animation: float 6s ease-in-out infinite;
                animation-delay: -3s;
            }

            /* Glass effect */
            .glass {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }
            .glass-strong {
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(30px);
                -webkit-backdrop-filter: blur(30px);
                border: 1px solid rgba(255, 255, 255, 0.15);
            }

            /* Gradient text */
            .text-gradient {
                background: linear-gradient(135deg, #a78bfa 0%, #c084fc 50%, #f0abfc 100%);
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            /* Header gradient */
            .header-gradient {
                background: linear-gradient(135deg, rgba(139, 92, 246, 0.3) 0%, rgba(192, 132, 252, 0.2) 50%, rgba(240, 171, 252, 0.1) 100%);
            }

            /* Ticket stub cutouts */
            .ticket-cutout-left {
                position: absolute;
                left: -12px;
                top: 50%;
                transform: translateY(-50%);
                width: 24px;
                height: 24px;
                background: #0a0a0f;
                border-radius: 50%;
            }
            .ticket-cutout-right {
                position: absolute;
                right: -12px;
                top: 50%;
                transform: translateY(-50%);
                width: 24px;
                height: 24px;
                background: #0a0a0f;
                border-radius: 50%;
            }

            /* Print styles */
            @media print {
                body, html {
                    background: white !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                .glass, .glass-strong {
                    background: #f8fafc !important;
                    backdrop-filter: none !important;
                    -webkit-backdrop-filter: none !important;
                    border: 1px solid #e2e8f0 !important;
                }
                .text-gradient {
                    background: none !important;
                    -webkit-text-fill-color: #6366f1 !important;
                    color: #6366f1 !important;
                }
                .header-gradient {
                    background: #f1f5f9 !important;
                }
                .ticket-cutout-left,
                .ticket-cutout-right {
                    background: white !important;
                    border: 1px solid #e2e8f0 !important;
                }
                .print-hidden {
                    display: none !important;
                }
                .print-bg-white {
                    background: white !important;
                }
                .print-text-dark {
                    color: #1e293b !important;
                    -webkit-text-fill-color: #1e293b !important;
                }
                .print-text-gray {
                    color: #64748b !important;
                }
                .print-border {
                    border-color: #e2e8f0 !important;
                }
            }
        </style>
    </x-slot>

    {{-- Dark background with gradient orbs --}}
    <main
      class="font-['Manrope'] text-[15px] font-normal leading-[1.75em] flex flex-col gap-[16px] flex-1 relative z-0 overflow-y-auto p-[16px] sm:p-[24px] focus:outline-none min-h-screen bg-[#0a0a0f] print:bg-white"
      tabindex="0"
    >
      {{-- Animated gradient orbs (hidden in print) --}}
      <div class="fixed inset-0 overflow-hidden pointer-events-none print-hidden" aria-hidden="true">
        <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[400px] h-[400px] bg-fuchsia-600/20 rounded-full blur-[100px] animate-pulse-slow animate-float"></div>
        <div class="absolute top-[40%] right-[20%] w-[300px] h-[300px] bg-indigo-600/15 rounded-full blur-[80px] animate-float-delayed"></div>
      </div>

      {{-- Ticket Card Container --}}
      <div class="relative z-10 w-full max-w-[440px] mx-auto">

        {{-- Header Section --}}
        <div class="glass-strong header-gradient rounded-t-[24px] p-[24px] sm:p-[32px] text-center print:bg-slate-100">
          @if ($role && $role->profile_image_url)
            <div class="mb-[20px]">
              <img
                class="w-[100px] h-[100px] mx-auto rounded-2xl object-cover shadow-lg shadow-violet-500/20 print:shadow-none"
                src="{{ $role->profile_image_url }}"
                alt="Logo"
              />
            </div>
          @endif
          <h1 class="text-[28px] sm:text-[32px] font-extrabold leading-[1.1] text-gradient print-text-dark">
            {{ $event->name }}
          </h1>
          @if ($event->event_url || $event->venue)
            <p class="mt-[12px] text-[13px] text-white/60 print-text-gray">
              @if ($event->event_url)
                <a href="{{ $event->event_url }}" target="_blank" class="hover:text-white/80 transition-colors print:text-slate-600">
                  {{ \App\Utils\UrlUtils::clean($event->event_url) }}
                </a>
              @elseif ($event->venue)
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->venue->bestAddress()) }}" target="_blank" class="hover:text-white/80 transition-colors print:text-slate-600">
                  {{ $event->venue->shortAddress() }}
                </a>
              @endif
            </p>
          @endif
        </div>

        {{-- Main Details Section --}}
        <div class="glass p-[20px] sm:p-[24px] relative print:bg-slate-50">
          {{-- Status watermark for unpaid/cancelled --}}
          @if ($sale->status !== 'paid')
            <div class="absolute inset-0 flex items-center justify-center overflow-hidden pointer-events-none rounded-none">
              <div class="text-white/10 print:text-gray-300/40 text-[48px] sm:text-[60px] font-extrabold rotate-[-30deg] whitespace-nowrap tracking-wider">
                {{ strtoupper($sale->status) }}
              </div>
            </div>
          @endif

          <div class="grid grid-cols-[1fr,auto] gap-[20px] items-start">
            {{-- Left: Info badges --}}
            <div class="space-y-[12px]">
              {{-- Date --}}
              <div class="flex items-center gap-[12px]">
                <div class="w-[40px] h-[40px] rounded-xl bg-violet-500/20 print:bg-violet-100 flex items-center justify-center flex-shrink-0">
                  <svg width="20" height="20" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.17194 0.665383C5.17194 0.298036 4.87019 0 4.49826 0C4.12633 0 3.82458 0.298036 3.82458 0.665383V2.07239C2.52633 2.17636 1.67721 2.42588 1.05265 3.04274C0.428085 3.65961 0.175454 4.49827 0.0701904 5.78052H17.9298C17.8246 4.49827 17.5719 3.65961 16.9474 3.04274C16.3228 2.42588 15.4737 2.17636 14.1755 2.07239V0.665383C14.1755 0.298036 13.8737 0 13.5018 0C13.1298 0 12.8281 0.298036 12.8281 0.665383V2.01001C12.2316 1.99615 11.5579 1.99615 10.8 1.99615H7.20002C6.44212 1.99615 5.77545 1.99615 5.17194 2.01001V0.665383Z" class="fill-violet-400 print:fill-violet-600"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 9.11436C0 8.36581 0 7.70735 0.0140351 7.11128H17.993C18.007 7.70042 18.007 8.36581 18.007 9.11436V10.8956C18.007 14.2503 18.007 15.9276 16.9544 16.9673C15.9018 18.0069 14.2035 18.0069 10.807 18.0069H7.20702C3.81053 18.0069 2.11228 18.0069 1.05965 16.9673C0.00701754 15.9276 0.00701754 14.2503 0.00701754 10.8956V9.11436H0ZM13.5018 10.8887C14 10.8887 14.4 10.4936 14.4 10.0015C14.4 9.50943 14 9.11436 13.5018 9.11436C13.0035 9.11436 12.6035 9.50943 12.6035 10.0015C12.6035 10.4936 13.0035 10.8887 13.5018 10.8887ZM13.5018 14.4444C14 14.4444 14.4 14.0493 14.4 13.5572C14.4 13.0651 14 12.67 13.5018 12.67C13.0035 12.67 12.6035 13.0651 12.6035 13.5572C12.6035 14.0493 13.0035 14.4444 13.5018 14.4444ZM9.90175 10.0015C9.90175 10.4936 9.50175 10.8887 9.00351 10.8887C8.50526 10.8887 8.10526 10.4936 8.10526 10.0015C8.10526 9.50943 8.50526 9.11436 9.00351 9.11436C9.50175 9.11436 9.90175 9.50943 9.90175 10.0015ZM9.90175 13.5572C9.90175 14.0493 9.50175 14.4444 9.00351 14.4444C8.50526 14.4444 8.10526 14.0493 8.10526 13.5572C8.10526 13.0651 8.50526 12.67 9.00351 12.67C9.50175 12.67 9.90175 13.0651 9.90175 13.5572ZM4.49825 10.8887C4.99649 10.8887 5.39649 10.4936 5.39649 10.0015C5.39649 9.50943 4.99649 9.11436 4.49825 9.11436C4 9.11436 3.6 9.50943 3.6 10.0015C3.6 10.4936 4 10.8887 4.49825 10.8887ZM4.49825 14.4444C4.99649 14.4444 5.39649 14.0493 5.39649 13.5572C5.39649 13.0651 4.99649 12.67 4.49825 12.67C4 12.67 3.6 13.0651 3.6 13.5572C3.6 14.0493 4 14.4444 4.49825 14.4444Z" class="fill-violet-400 print:fill-violet-600"/>
                  </svg>
                </div>
                <div>
                  <p class="text-[10px] text-white/50 print-text-gray uppercase tracking-wide font-medium">{{ __('messages.date') }}</p>
                  <p class="text-[13px] text-white print-text-dark font-semibold">{{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}</p>
                </div>
              </div>

              {{-- Time --}}
              <div class="flex items-center gap-[12px]">
                <div class="w-[40px] h-[40px] rounded-xl bg-fuchsia-500/20 print:bg-fuchsia-100 flex items-center justify-center flex-shrink-0">
                  <svg width="20" height="20" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M0 9C0 4.02667 4.02667 0 9 0C13.9733 0 18 4.02667 18 9C18 13.9733 13.9733 18 9 18C4.02667 18 0 13.9733 0 9ZM9.67333 5.4C9.67333 5.02667 9.37333 4.72667 9 4.72667C8.62667 4.72667 8.32667 5.02667 8.32667 5.4V9C8.32667 9.18 8.4 9.35333 8.52667 9.48L10.7733 11.7267C11.04 11.9933 11.4667 11.9933 11.7267 11.7267C11.9933 11.46 11.9933 11.0333 11.7267 10.7733L9.67333 8.72V5.4Z" class="fill-fuchsia-400 print:fill-fuchsia-600"/>
                  </svg>
                </div>
                <div>
                  <p class="text-[10px] text-white/50 print-text-gray uppercase tracking-wide font-medium">{{ __('messages.time') }}</p>
                  <p class="text-[13px] text-white print-text-dark font-semibold">{{ $event->getStartEndTime($sale->event_date) }}</p>
                </div>
              </div>

              {{-- Attendee --}}
              <div class="flex items-center gap-[12px]">
                <div class="w-[40px] h-[40px] rounded-xl bg-emerald-500/20 print:bg-emerald-100 flex items-center justify-center flex-shrink-0">
                  <svg width="20" height="20" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9 0C6.51472 0 4.5 2.01472 4.5 4.5C4.5 6.98528 6.51472 9 9 9C11.4853 9 13.5 6.98528 13.5 4.5C13.5 2.01472 11.4853 0 9 0Z" class="fill-emerald-400 print:fill-emerald-600"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M9 10.5C5.27208 10.5 2.25 12.1863 2.25 14.25C2.25 16.3137 2.25 18 9 18C15.75 18 15.75 16.3137 15.75 14.25C15.75 12.1863 12.7279 10.5 9 10.5Z" class="fill-emerald-400 print:fill-emerald-600"/>
                  </svg>
                </div>
                <div>
                  <p class="text-[10px] text-white/50 print-text-gray uppercase tracking-wide font-medium">{{ __('messages.attendee') }}</p>
                  <p class="text-[13px] text-white print-text-dark font-semibold">{{ $sale->name }}</p>
                </div>
              </div>

              {{-- Number of guests --}}
              <div class="flex items-center gap-[12px]">
                <div class="w-[40px] h-[40px] rounded-xl bg-amber-500/20 print:bg-amber-100 flex items-center justify-center flex-shrink-0">
                  <svg width="20" height="20" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 9C7.65685 9 9 7.65685 9 6C9 4.34315 7.65685 3 6 3C4.34315 3 3 4.34315 3 6C3 7.65685 4.34315 9 6 9Z" class="fill-amber-400 print:fill-amber-600"/>
                    <path d="M12 9C13.1046 9 14 7.88071 14 6.5C14 5.11929 13.1046 4 12 4C10.8954 4 10 5.11929 10 6.5C10 7.88071 10.8954 9 12 9Z" class="fill-amber-400 print:fill-amber-600"/>
                    <path d="M6 10.5C2.68629 10.5 0 12.1863 0 14.25V15.75C0 16.1642 0.335786 16.5 0.75 16.5H11.25C11.6642 16.5 12 16.1642 12 15.75V14.25C12 12.1863 9.31371 10.5 6 10.5Z" class="fill-amber-400 print:fill-amber-600"/>
                    <path d="M13.5 11.25C12.8643 11.25 12.2554 11.3571 11.6952 11.5506C13.0516 12.5047 14 13.8397 14 15.375V15.75C14 15.8372 13.9916 15.9224 13.9755 16.0051C13.9916 16.0018 14.0079 16 14.025 16H17.25C17.6642 16 18 15.6642 18 15.25V14C18 12.4812 16.0212 11.25 13.5 11.25Z" class="fill-amber-400 print:fill-amber-600"/>
                  </svg>
                </div>
                <div>
                  <p class="text-[10px] text-white/50 print-text-gray uppercase tracking-wide font-medium">{{ __('messages.guests') }}</p>
                  <p class="text-[13px] text-white print-text-dark font-semibold">{{ $sale->isRsvp() ? 1 : $sale->quantity() }}</p>
                </div>
              </div>
            </div>

            {{-- Right: QR Code --}}
            <div class="flex flex-col items-center">
              <div class="bg-white rounded-2xl p-[8px] shadow-lg shadow-black/20 print:shadow-md">
                <img class="w-[120px] h-[120px]" src="{{ route('ticket.qr_code', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}" alt="QR Code" />
              </div>
              <p class="text-[10px] text-white/40 print-text-gray mt-[8px] text-center font-medium">{{ __('messages.scan_for_entry') }}</p>
            </div>
          </div>
        </div>

        @if ($sale->isRsvp())
        {{-- RSVP Confirmation --}}
        <div class="glass p-[20px] sm:p-[24px] print:bg-slate-50">
          <div class="flex items-center gap-[8px]">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" class="fill-emerald-400 print:fill-emerald-600"/>
            </svg>
            <span class="text-[14px] text-emerald-400 print:text-emerald-600 font-semibold">{{ __('messages.registered') }}</span>
          </div>
        </div>
        @else
        {{-- Ticket Stub Divider --}}
        <div class="relative h-[1px] bg-transparent">
          <div class="ticket-cutout-left print:bg-white"></div>
          <div class="ticket-cutout-right print:bg-white"></div>
          <div class="absolute inset-x-[20px] top-1/2 border-t border-dashed border-white/20 print:border-slate-300"></div>
        </div>

        {{-- Ticket Types Section --}}
        @if ($sale->saleTickets->count() > 0)
        <div class="glass p-[20px] sm:p-[24px] print:bg-slate-50">
          <h2 class="text-[11px] uppercase tracking-wider text-white/50 print-text-gray font-semibold mb-[12px]">{{ __('messages.tickets') }}</h2>
          <div class="space-y-[8px]">
            @foreach ($sale->saleTickets as $saleTicket)
              <div class="flex items-center justify-between">
                <span class="text-[14px] text-white print-text-dark font-medium">{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</span>
                <span class="px-[12px] py-[4px] rounded-full bg-violet-500/20 print:bg-violet-100 text-violet-300 print:text-violet-700 text-[12px] font-semibold">
                  x{{ $saleTicket->quantity }}
                </span>
              </div>
            @endforeach
          </div>
          @if ($sale->discount_amount > 0)
            <div class="mt-[12px] pt-[12px] border-t border-white/10 print:border-slate-200">
              <div class="flex items-center justify-between">
                <span class="text-[13px] text-emerald-400 print:text-emerald-600 font-medium">{{ __('messages.discount') }}@if ($sale->promoCode) ({{ $sale->promoCode->code }})@endif</span>
                <span class="text-[13px] text-emerald-400 print:text-emerald-600 font-medium">-{{ number_format($sale->discount_amount, 2) }} {{ $event->ticket_currency_code }}</span>
              </div>
            </div>
          @endif
        </div>
        @endif
        @endif

        {{-- Custom Fields Section --}}
        @php
          $hasEventCustomFields = $event->custom_fields && count($event->custom_fields) > 0;
          $hasTicketCustomFields = false;
          foreach ($sale->saleTickets as $st) {
            if ($st->ticket->custom_fields && count($st->ticket->custom_fields) > 0) {
              $hasTicketCustomFields = true;
              break;
            }
          }
        @endphp
        @if ($hasEventCustomFields || $hasTicketCustomFields)
          <div class="glass p-[20px] sm:p-[24px] print:bg-slate-50">
            <h2 class="text-[11px] uppercase tracking-wider text-white/50 print-text-gray font-semibold mb-[12px]">{{ __('messages.details') }}</h2>

            {{-- Event-level Custom Fields --}}
            @if ($hasEventCustomFields)
              @php $eventFallbackIndex = 1; @endphp
              @foreach ($event->custom_fields as $fieldKey => $fieldConfig)
                @php
                  $index = $fieldConfig['index'] ?? $eventFallbackIndex;
                  $eventFallbackIndex++;
                @endphp
                @if ($index >= 1 && $index <= 10 && $sale->{"custom_value{$index}"})
                  <div class="flex gap-[8px] items-start mb-[8px]">
                    <span class="text-[12px] text-white/60 print-text-gray font-medium">{{ $fieldConfig['name'] }}:</span>
                    <span class="text-[12px] text-white print-text-dark">{{ $sale->{"custom_value{$index}"} }}</span>
                  </div>
                @endif
              @endforeach
            @endif

            {{-- Ticket-level Custom Fields --}}
            @foreach ($sale->saleTickets as $saleTicket)
              @if ($saleTicket->ticket->custom_fields && count($saleTicket->ticket->custom_fields) > 0)
                <div class="mt-[12px] pt-[12px] border-t border-white/10 print:border-slate-200">
                  <p class="text-[11px] text-violet-400 print:text-violet-600 font-semibold mb-[8px]">{{ $saleTicket->ticket->type ?: __('messages.ticket') }}</p>
                  @php $ticketFallbackIndex = 1; @endphp
                  @foreach ($saleTicket->ticket->custom_fields as $fieldKey => $fieldConfig)
                    @php
                      $index = $fieldConfig['index'] ?? $ticketFallbackIndex;
                      $ticketFallbackIndex++;
                    @endphp
                    @if ($index >= 1 && $index <= 10 && $saleTicket->{"custom_value{$index}"})
                      <div class="flex gap-[8px] items-start mb-[4px] ml-[12px]">
                        <span class="text-[12px] text-white/60 print-text-gray font-medium">{{ $fieldConfig['name'] }}:</span>
                        <span class="text-[12px] text-white print-text-dark">{{ $saleTicket->{"custom_value{$index}"} }}</span>
                      </div>
                    @endif
                  @endforeach
                </div>
              @endif
            @endforeach
          </div>
        @endif

        {{-- Cancel Registration --}}
        @if ($sale->isRsvp() && $sale->status === 'paid')
        <div class="glass p-[20px] sm:p-[24px] print:bg-slate-50 print:hidden">
          <form action="{{ route('rsvp.cancel', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}" method="POST"
                onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
            @csrf
            <input type="hidden" name="secret" value="{{ $sale->secret }}">
            <button type="submit" class="text-[13px] text-red-400 print:text-red-600 hover:text-red-300 transition-colors font-medium">
              {{ __('messages.cancel_registration') }}
            </button>
          </form>
        </div>
        @endif

        {{-- Footer Section --}}
        <div class="glass rounded-b-[24px] p-[20px] sm:p-[24px] print:bg-slate-50">
          {{-- Notes --}}
          @if ($event->ticket_notes_html)
            <div class="mb-[16px] pb-[16px] border-b border-white/10 print:border-slate-200">
              <h3 class="text-[11px] uppercase tracking-wider text-violet-400 print:text-violet-600 font-semibold mb-[8px]">{{ __('messages.notes') }}</h3>
              <div class="text-[12px] text-white/80 print-text-dark custom-content leading-relaxed">
                {!! \App\Utils\UrlUtils::convertUrlsToLinks($event->ticket_notes_html) !!}
              </div>
            </div>
          @endif

          {{-- Terms & Support --}}
          <div class="grid grid-cols-2 gap-[16px]">
            <div>
              <h3 class="text-[11px] uppercase tracking-wider text-violet-400 print:text-violet-600 font-semibold mb-[6px]">{{ __('messages.terms_and_conditions') }}</h3>
              @php
                $termsUrl = $event->terms_url ?: (config('app.hosted')
                  ? marketing_url('/terms-of-service')
                  : marketing_url('/self-hosting-terms-of-service'));
                $termsDisplay = $event->terms_url
                  ? preg_replace('#^https?://(www\.)?#', '', $event->terms_url)
                  : marketing_domain() . '/terms';
              @endphp
              <a href="{{ $termsUrl }}" target="_blank" class="text-[11px] text-white/60 print-text-gray hover:text-white/80 transition-colors break-all">
                {{ Str::limit($termsDisplay, 30) }}
              </a>
            </div>
            <div>
              <h3 class="text-[11px] uppercase tracking-wider text-violet-400 print:text-violet-600 font-semibold mb-[6px]">{{ __('messages.event_support_contact') }}</h3>
              <a href="mailto:{{ $event->user->email }}" target="_blank" class="text-[11px] text-white/60 print-text-gray hover:text-white/80 transition-colors break-all">
                {{ $event->user->email }}
              </a>
            </div>
          </div>
        </div>

      </div>
    </main>

</x-app-layout>
