<template>
  <div class="es-support-chat">
    <!-- Greeting: once per browser session, after the cookie banner is out of the way -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-3 scale-95"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-if="greetingVisible && available && !panelOpen"
        class="fixed end-4 z-40 sm:end-6"
        :style="{ bottom: (launcherBottom + (isMobile ? 68 : 72)) + 'px' }"
      >
        <!-- Mobile: one line -->
        <div
          v-if="isMobile"
          class="flex max-w-[calc(100vw-2rem)] items-center gap-2 rounded-2xl border border-gray-200 bg-white py-2 ps-3 pe-2 text-sm text-gray-800 shadow-lg dark:border-white/10 dark:bg-gray-800 dark:text-gray-100"
        >
          <button type="button" class="min-w-0 truncate text-start" @click="openFromGreeting()">
            Hi! Questions? I'm online now.
          </button>
          <button type="button" class="shrink-0 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Dismiss" @click="dismissGreeting">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <!-- Desktop: a card with suggestions -->
        <div
          v-else
          class="relative w-80 rounded-2xl border border-gray-200 bg-white p-4 text-gray-900 shadow-xl dark:border-white/10 dark:bg-gray-800 dark:text-gray-100"
        >
          <button type="button" class="absolute end-2 top-2 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Dismiss" @click="dismissGreeting">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          <button type="button" class="flex w-full items-center gap-3 text-start" @click="openFromGreeting()">
            <span class="relative shrink-0">
              <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-sm font-semibold text-white">
                <img v-if="avatarOk && agent && agent.avatar_url" :src="agent.avatar_url" alt="" class="h-full w-full object-cover" @error="avatarOk = false">
                <span v-else>{{ initials }}</span>
              </span>
              <span class="absolute -bottom-0.5 -end-0.5 h-3.5 w-3.5 rounded-full border-2 border-white bg-green-500 dark:border-gray-800"></span>
            </span>
            <span class="min-w-0 pe-6">
              <span class="block text-sm font-semibold">{{ agentName }}</span>
              <span class="block text-xs text-green-600 dark:text-green-400">Online now</span>
            </span>
          </button>
          <p class="mt-3 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
            Hi! I'm online right now. Any questions about Event Schedule?
          </p>
          <div class="mt-3 flex flex-wrap gap-2">
            <button
              v-for="chip in suggestions"
              :key="chip"
              type="button"
              class="rounded-full border border-[#4E81FA]/30 bg-[#4E81FA]/5 px-3 py-1.5 text-xs font-medium text-[#3565d8] transition-all duration-200 hover:border-[#4E81FA] hover:bg-[#4E81FA]/10 dark:border-[#4E81FA]/40 dark:bg-[#4E81FA]/10 dark:text-[#8fb0ff] dark:hover:bg-[#4E81FA]/20"
              @click="openFromGreeting(chip)"
            >
              {{ chip }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Reply preview while the panel is closed -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-3"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div
        v-if="preview && !panelOpen && !greetingVisible"
        class="fixed end-4 z-40 w-[min(20rem,calc(100vw-2rem))] sm:end-6"
        :style="{ bottom: (launcherBottom + (isMobile ? 68 : 72)) + 'px' }"
      >
        <div class="relative rounded-2xl border border-gray-200 bg-white p-3 pe-9 text-gray-900 shadow-xl dark:border-white/10 dark:bg-gray-800 dark:text-gray-100">
          <button type="button" class="absolute end-2 top-2 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Dismiss" @click="preview = null">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
          <button type="button" class="block w-full text-start" @click="openPanel()">
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400">{{ agentName }} replied</span>
            <span class="mt-0.5 line-clamp-3 block text-sm">{{ preview }}</span>
          </button>
        </div>
      </div>
    </transition>

    <!-- Launcher -->
    <button
      v-show="showLauncher && !(panelOpen && isMobile)"
      ref="launcher"
      type="button"
      class="group fixed end-4 z-40 flex items-center gap-2.5 rounded-full bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-white shadow-lg shadow-[#4E81FA]/30 transition-all duration-200 hover:scale-105 hover:shadow-xl hover:shadow-[#4E81FA]/40 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#4E81FA]/40 motion-reduce:hover:scale-100 sm:end-6"
      :class="[isMobile || panelOpen ? 'h-14 w-14 justify-center' : 'h-14 py-2 ps-2 pe-5', entered ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4']"
      :style="{ bottom: launcherBottom + 'px' }"
      :aria-label="panelOpen ? 'Close chat' : 'Chat with ' + agentName"
      :aria-expanded="panelOpen ? 'true' : 'false'"
      aria-controls="es-support-chat-panel"
      @click="togglePanel"
    >
      <template v-if="panelOpen">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
      </template>
      <template v-else>
        <span class="relative shrink-0">
          <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-white/20 text-sm font-semibold ring-2 ring-white/40">
            <img v-if="avatarOk && agent && agent.avatar_url" :src="agent.avatar_url" alt="" class="h-full w-full object-cover" @error="avatarOk = false">
            <span v-else>{{ initials }}</span>
          </span>
          <span v-if="available" class="absolute -bottom-0.5 -end-0.5 flex h-3.5 w-3.5">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75 motion-reduce:animate-none"></span>
            <span class="relative inline-flex h-3.5 w-3.5 rounded-full border-2 border-white bg-green-500"></span>
          </span>
        </span>
        <span v-if="!isMobile" class="text-start leading-tight">
          <span class="block text-sm font-semibold">Chat with {{ agentName }}</span>
          <span class="block text-xs text-white/85">{{ available ? 'Online now' : 'Leave a message' }}</span>
        </span>
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -end-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white ring-2 ring-white dark:ring-gray-900"
        >{{ unreadCount }}</span>
      </template>
    </button>

    <!-- Panel -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-4 sm:scale-95"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div
        v-if="panelOpen"
        id="es-support-chat-panel"
        ref="panel"
        role="dialog"
        :aria-label="'Chat with ' + agentName"
        class="fixed z-[60] flex flex-col overflow-hidden bg-white text-gray-900 shadow-2xl dark:bg-gray-900 dark:text-gray-100 sm:z-[55] sm:rounded-2xl sm:border sm:border-gray-200 sm:dark:border-white/10"
        :class="isMobile ? 'inset-0' : 'end-6 w-[24rem]'"
        :style="isMobile ? {} : { bottom: (launcherBottom + 72) + 'px', height: 'min(600px, calc(100vh - ' + (launcherBottom + 96) + 'px))' }"
        @keydown.esc="closePanel"
      >
        <!-- Header -->
        <div class="relative shrink-0 bg-gradient-to-br from-[#4E81FA] via-[#3b8ff5] to-[#0EA5E9] px-5 pb-5 pt-4 text-white" :style="isMobile ? { paddingTop: 'max(1rem, env(safe-area-inset-top))' } : {}">
          <div class="flex items-center gap-3">
            <span class="relative shrink-0">
              <span class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-white/20 text-base font-semibold ring-2 ring-white/40">
                <img v-if="avatarOk && agent && agent.avatar_url" :src="agent.avatar_url" alt="" class="h-full w-full object-cover" @error="avatarOk = false">
                <span v-else>{{ initials }}</span>
              </span>
              <span
                class="absolute -bottom-0.5 -end-0.5 h-3.5 w-3.5 rounded-full border-2 border-[#3b8ff5]"
                :class="available ? 'bg-green-400' : 'bg-gray-300'"
              ></span>
            </span>
            <div class="min-w-0 flex-1">
              <div class="truncate text-base font-semibold">{{ agentName }} from Event Schedule</div>
              <div class="text-sm text-white/85">
                {{ available ? 'Online now · replies in minutes' : 'Away now · replies by email' }}
              </div>
            </div>
            <button type="button" class="shrink-0 rounded-lg p-1.5 text-white/90 transition-colors hover:bg-white/20" aria-label="Close chat" @click="closePanel">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path v-if="isMobile" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Messages -->
        <div ref="scroller" class="flex-1 space-y-3 overflow-y-auto bg-gray-50 px-4 py-4 dark:bg-black/20">
          <!-- Intro, always first -->
          <div class="flex items-end gap-2">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-[11px] font-semibold text-white">
              <img v-if="avatarOk && agent && agent.avatar_url" :src="agent.avatar_url" alt="" class="h-full w-full object-cover" @error="avatarOk = false">
              <span v-else>{{ initials }}</span>
            </span>
            <div class="max-w-[80%] rounded-2xl rounded-bl-md bg-white px-4 py-2.5 text-sm shadow-sm ring-1 ring-gray-200/70 dark:bg-gray-800 dark:ring-white/5">
              Hi! I'm {{ agentName }} from Event Schedule. Ask me anything, I'm happy to help.
            </div>
          </div>

          <div v-if="!allMessages.length" class="flex flex-wrap gap-2 ps-9">
            <button
              v-for="chip in suggestions"
              :key="chip"
              type="button"
              class="rounded-full border border-[#4E81FA]/30 bg-white px-3 py-1.5 text-xs font-medium text-[#3565d8] transition-all duration-200 hover:border-[#4E81FA] hover:bg-[#4E81FA]/5 dark:border-[#4E81FA]/40 dark:bg-gray-800 dark:text-[#8fb0ff] dark:hover:bg-[#4E81FA]/15"
              @click="send(chip)"
            >
              {{ chip }}
            </button>
          </div>

          <div aria-live="polite" class="space-y-3">
            <template v-for="(msg, idx) in allMessages" :key="msg.key">
              <div v-if="showTimestamp(idx)" class="py-1 text-center text-xs text-gray-400 dark:text-gray-500">{{ formatGroupTime(msg.created_at) }}</div>
              <div v-if="msg.is_from_admin" class="flex items-end gap-2">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-[11px] font-semibold text-white" aria-hidden="true">
                  <img v-if="avatarOk && agent && agent.avatar_url" :src="agent.avatar_url" alt="" class="h-full w-full object-cover" @error="avatarOk = false">
                  <span v-else>{{ initials }}</span>
                </span>
                <div class="max-w-[80%] whitespace-pre-wrap break-words rounded-2xl rounded-bl-md bg-white px-4 py-2.5 text-sm shadow-sm ring-1 ring-gray-200/70 dark:bg-gray-800 dark:ring-white/5">
                  <template v-for="(seg, sIdx) in segments(msg.body)" :key="sIdx">
                    <a v-if="seg.href" :href="seg.href" target="_blank" rel="noopener noreferrer nofollow" class="text-[#3565d8] underline dark:text-[#8fb0ff]">{{ seg.text }}</a>
                    <template v-else>{{ seg.text }}</template>
                  </template>
                </div>
              </div>
              <div v-else class="flex flex-col items-end">
                <component
                  :is="msg.failed ? 'button' : 'div'"
                  :type="msg.failed ? 'button' : undefined"
                  class="max-w-[80%] whitespace-pre-wrap break-words rounded-2xl rounded-br-md bg-gradient-to-br from-[#4E81FA] to-[#3b8ff5] px-4 py-2.5 text-start text-sm text-white shadow-sm"
                  :class="{ 'opacity-60': msg.pending, 'ring-2 ring-red-400': msg.failed }"
                  @click="msg.failed && retry(msg.key)"
                >
                  <template v-for="(seg, sIdx) in segments(msg.body)" :key="sIdx">
                    <a v-if="seg.href && !msg.failed" :href="seg.href" target="_blank" rel="noopener noreferrer nofollow" class="underline">{{ seg.text }}</a>
                    <template v-else>{{ seg.text }}</template>
                  </template>
                </component>
                <div v-if="msg.failed" class="mt-1 text-[11px] text-red-500">Not sent. Tap to retry.</div>
                <div v-else-if="msg.pending" class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Sending...</div>
                <div v-else-if="msg.key === lastVisitorKey && msg.read" class="mt-1 text-[11px] text-gray-400 dark:text-gray-500">Seen</div>
              </div>
            </template>
          </div>

          <!-- Typing -->
          <div v-if="agentTyping" class="flex items-end gap-2" aria-live="polite" :aria-label="agentName + ' is typing'">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-[11px] font-semibold text-white" aria-hidden="true">
              <img v-if="avatarOk && agent && agent.avatar_url" :src="agent.avatar_url" alt="" class="h-full w-full object-cover" @error="avatarOk = false">
              <span v-else>{{ initials }}</span>
            </span>
            <div class="flex items-center gap-1 rounded-2xl rounded-bl-md bg-white px-4 py-3.5 shadow-sm ring-1 ring-gray-200/70 dark:bg-gray-800 dark:ring-white/5">
              <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400 motion-reduce:animate-none"></span>
              <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400 [animation-delay:150ms] motion-reduce:animate-none"></span>
              <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-gray-400 [animation-delay:300ms] motion-reduce:animate-none"></span>
            </div>
          </div>

          <!-- Setting expectations after the first message -->
          <p v-if="awaitingFirstReply && !agentTyping" class="px-2 text-center text-xs text-gray-500 dark:text-gray-400">
            {{ available ? 'Thanks! ' + agentName + ' usually replies within a few minutes.' : 'Thanks! ' + agentName + ' will reply by email.' }}
          </p>

          <!-- Stepped away mid-chat -->
          <div v-if="!available && allMessages.length" class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:border-amber-700/60 dark:bg-amber-900/20 dark:text-amber-200">
            {{ agentName }} stepped away. {{ hasEmail ? "You'll get the reply by email." : "Leave your email below and you'll get the reply there." }}
          </div>

          <!-- Where to send the reply -->
          <div v-if="emailMode === 'subtle' && !emailFormOpen" class="text-center">
            <button type="button" class="text-xs font-medium text-[#3565d8] underline-offset-2 hover:underline dark:text-[#8fb0ff]" @click="emailFormOpen = true">
              Get the reply by email
            </button>
          </div>
          <form
            v-if="emailMode === 'required' || emailMode === 'prominent' || (emailMode === 'subtle' && emailFormOpen)"
            class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-200/70 dark:bg-gray-800 dark:ring-white/5"
            novalidate
            @submit.prevent="saveContact"
          >
            <p class="text-sm font-medium">
              {{ emailMode === 'required' ? 'Where should ' + agentName + ' send the reply?' : emailMode === 'prominent' ? "Don't miss " + agentName + "'s reply. Where should it go?" : 'Where should we send the reply if you step away?' }}
            </p>
            <div class="mt-3 space-y-2">
              <input
                v-model="nameInput"
                type="text"
                autocomplete="name"
                maxlength="100"
                placeholder="Your name (optional)"
                aria-label="Your name"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4E81FA] dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 sm:text-sm"
              >
              <input
                ref="emailField"
                v-model="emailInput"
                type="email"
                autocomplete="email"
                maxlength="255"
                placeholder="you@example.com"
                aria-label="Your email"
                required
                class="w-full rounded-lg border bg-white px-3 py-2 text-base text-gray-900 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4E81FA] dark:bg-gray-900 dark:text-gray-100 sm:text-sm"
                :class="emailError ? 'border-red-400' : 'border-gray-300 dark:border-gray-600'"
              >
              <p v-if="emailError" class="text-xs text-red-500">{{ emailError }}</p>
            </div>
            <button
              v-if="token"
              type="submit"
              :disabled="savingContact"
              class="mt-3 w-full rounded-lg bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] px-4 py-2 text-sm font-semibold text-white transition-all duration-200 hover:opacity-95 disabled:opacity-60"
            >
              Save
            </button>
          </form>
          <p v-if="contactSaved" class="text-center text-xs text-green-600 dark:text-green-400">
            Got it. If you step away, the reply will go to your email.
          </p>
        </div>

        <!-- Composer -->
        <div class="relative shrink-0 border-t border-gray-100 bg-white px-3 pb-2 pt-3 dark:border-white/5 dark:bg-gray-900" :style="isMobile ? { paddingBottom: 'max(0.5rem, env(safe-area-inset-bottom))' } : {}">
          <!-- Honeypot: always in the DOM, invisible to people, irresistible to form-filling bots.
               Its value goes out with every POST (see post() and saveContact()). -->
          <input
            v-model="honeypot"
            type="text"
            :name="config.honeypotField"
            tabindex="-1"
            autocomplete="off"
            aria-hidden="true"
            class="absolute -left-[10000px] h-px w-px overflow-hidden opacity-0"
          >
          <p v-if="sendError" class="mb-2 text-center text-xs text-red-500" role="alert">{{ sendError }}</p>
          <div class="flex items-end gap-2">
            <textarea
              ref="input"
              v-model="inputText"
              rows="1"
              maxlength="2000"
              :placeholder="allMessages.length ? 'Write a message...' : 'Ask ' + agentName + ' anything...'"
              aria-label="Message"
              class="max-h-32 min-h-[2.75rem] flex-1 resize-none rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-base text-gray-900 placeholder-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#4E81FA] dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 sm:text-sm"
              @input="autosize"
              @keydown.enter.exact.prevent="send()"
            ></textarea>
            <button
              type="button"
              :disabled="!inputText.trim()"
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-white shadow-sm transition-all duration-200 hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-40"
              aria-label="Send"
              @click="send()"
            >
              <svg class="h-5 w-5 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </button>
          </div>
          <div class="mt-2 text-center text-[11px] text-gray-400 dark:text-gray-500">
            A real person, not a bot ·
            <a :href="config.privacyUrl" target="_blank" rel="noopener" class="underline-offset-2 hover:underline">Privacy</a>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { ACTIVE_KEY, TOKEN_KEY, storageGet, storageSet } from '../support-chat-boot';

const OPEN_KEY = 'es_support_chat_open';
const GREETED_KEY = 'es_support_chat_greeted';
const PREVIEWED_KEY = 'es_support_chat_previewed';
const GREETING_DELAY_MS = 10000;
const NUDGE_AFTER_MS = 3 * 60 * 1000;
const ACTIVE_WINDOW_MS = 30 * 60 * 1000;
const URL_PATTERN = /(https?:\/\/[^\s<]+|www\.[^\s<]+)/gi;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default {
  name: 'SupportChatWidget',
  props: {
    config: { type: Object, required: true },
    initialAvailable: { type: Boolean, default: false },
    initialAgent: { type: Object, default: null },
    openOnMount: { type: Boolean, default: false },
  },
  data() {
    return {
      available: this.initialAvailable,
      agent: this.initialAgent,
      avatarOk: true,
      token: storageGet('localStorage', TOKEN_KEY),
      panelOpen: false,
      entered: false,
      messages: [],
      pending: [],
      pendingSeq: 0,
      agentTyping: false,
      hasEmail: false,
      unreadCount: 0,
      inputText: '',
      greetingVisible: false,
      preview: null,
      // Per session, so a reply already previewed (or dismissed) does not pop up on every page.
      lastPreviewId: parseInt(storageGet('sessionStorage', PREVIEWED_KEY) || '0', 10),
      emailFormOpen: false,
      emailInput: '',
      nameInput: '',
      honeypot: '',
      sendError: '',
      // Sends made before a token exists wait for the first one, or two POSTs without a token
      // would open two conversations and the second token would orphan the first.
      firstSend: null,
      // Bumped on every successful send; a poll issued before it is stale and must not replace
      // the list, or the just-sent message would vanish until the next poll.
      sendSeq: 0,
      emailError: '',
      forceEmail: false,
      savingContact: false,
      contactSaved: false,
      firstSentAt: null,
      now: Date.now(),
      isMobile: window.innerWidth < 640,
      bannerOffset: 0,
      pollTimer: null,
      greetingTimer: null,
      clockTimer: null,
      flashTimer: null,
      originalTitle: document.title,
      bannerObserver: null,
    };
  },
  computed: {
    agentName() {
      return (this.agent && this.agent.name) || 'Event Schedule';
    },
    initials() {
      return (this.agent && this.agent.initials) || 'ES';
    },
    suggestions() {
      return ['How does pricing work?', 'Can I sell tickets?', 'How do I get started?'];
    },
    showLauncher() {
      return this.available || !!this.token;
    },
    launcherBottom() {
      return (this.isMobile ? 16 : 24) + this.bannerOffset;
    },
    allMessages() {
      const server = this.messages.map((m) => ({ ...m, key: 's' + m.id }));
      return server.concat(this.pending);
    },
    lastVisitorKey() {
      for (let i = this.allMessages.length - 1; i >= 0; i--) {
        if (!this.allMessages[i].is_from_admin) {
          return this.allMessages[i].key;
        }
      }
      return null;
    },
    lastMessageAt() {
      const last = this.messages[this.messages.length - 1];
      return last ? new Date(last.created_at).getTime() : 0;
    },
    awaitingFirstReply() {
      return this.messages.some((m) => !m.is_from_admin) && !this.messages.some((m) => m.is_from_admin);
    },
    // Nobody is there to answer live, so a message needs somewhere for the reply to go.
    needsEmail() {
      return !this.available && !this.hasEmail && !this.contactSaved;
    },
    // How hard to ask where the reply should go. Only ever once they have said something,
    // and 'required' when nobody is there to answer live.
    emailMode() {
      if (this.hasEmail || this.contactSaved) {
        return 'none';
      }
      if (this.forceEmail || this.needsEmail) {
        return this.allMessages.length || this.forceEmail ? 'required' : 'none';
      }
      if (!this.messages.some((m) => !m.is_from_admin)) {
        return 'none';
      }
      const lastAdmin = [...this.messages].reverse().find((m) => m.is_from_admin);
      const waitingSince = this.firstSentAt || this.firstVisitorMessageAt();
      if (!lastAdmin && waitingSince && this.now - waitingSince >= NUDGE_AFTER_MS) {
        return 'prominent';
      }
      return 'subtle';
    },
  },
  watch: {
    panelOpen(open) {
      storageSet('sessionStorage', OPEN_KEY, open ? '1' : null);
      this.schedulePoll();
      if (open) {
        this.preview = null;
        this.greetingVisible = false;
        this.stopFlash();
        this.fetchMessages();
        this.$nextTick(() => {
          this.scrollToBottom();
          // Not on a phone: the keyboard would come up over the thread they came to read.
          if (this.$refs.input && !this.isMobile) {
            this.$refs.input.focus({ preventScroll: true });
          }
        });
      } else {
        this.$nextTick(() => {
          if (this.$refs.launcher && document.activeElement === document.body) {
            this.$refs.launcher.focus({ preventScroll: true });
          }
        });
      }
    },
    'allMessages.length'() {
      this.$nextTick(() => this.scrollToBottom());
    },
    emailMode(mode) {
      if (mode === 'required' || mode === 'prominent') {
        this.$nextTick(() => this.scrollToBottom());
      }
    },
  },
  mounted() {
    requestAnimationFrame(() => { this.entered = true; });

    this.watchCookieBanner();
    window.addEventListener('resize', this.onResize);
    document.addEventListener('visibilitychange', this.onVisibility);
    window.addEventListener('focus', this.stopFlash);
    this.clockTimer = setInterval(() => { this.now = Date.now(); }, 15000);

    if (this.token) {
      this.fetchMessages();
    }

    // Reopening on the next page is right on a desktop, where the panel is a corner card. On a
    // phone it is full-screen, and would cover every page the visitor navigates to.
    if (this.openOnMount || (!this.isMobile && storageGet('sessionStorage', OPEN_KEY) === '1')) {
      this.panelOpen = true;
    } else {
      this.scheduleGreeting();
    }

    this.schedulePoll();
  },
  beforeUnmount() {
    clearTimeout(this.pollTimer);
    clearTimeout(this.greetingTimer);
    clearInterval(this.clockTimer);
    this.stopFlash();
    if (this.bannerObserver) {
      this.bannerObserver.disconnect();
    }
    window.removeEventListener('resize', this.onResize);
    document.removeEventListener('visibilitychange', this.onVisibility);
    window.removeEventListener('focus', this.stopFlash);
  },
  methods: {
    headers(json) {
      const headers = { Accept: 'application/json' };
      if (json) {
        headers['Content-Type'] = 'application/json';
      }
      if (this.token) {
        headers['X-Support-Chat-Token'] = this.token;
      }
      return headers;
    },

    // ── Polling ──

    schedulePoll() {
      clearTimeout(this.pollTimer);
      let delay = null;
      if (this.panelOpen && !document.hidden) {
        delay = 3000;
      } else if (this.token && !document.hidden) {
        delay = 30000;
      } else if (this.token && Date.now() - this.lastMessageAt < ACTIVE_WINDOW_MS) {
        // A background tab in an active conversation keeps listening, so its title can say
        // a reply has arrived.
        delay = 30000;
      } else if (!this.token && !document.hidden) {
        delay = 120000;
      }
      if (delay !== null) {
        this.pollTimer = setTimeout(() => this.poll(), delay);
      }
    },
    poll() {
      const done = () => this.schedulePoll();
      if (this.token) {
        this.fetchMessages().finally(done);
      } else {
        this.fetchStatus().finally(done);
      }
    },
    fetchStatus() {
      return fetch(this.config.statusUrl, { headers: this.headers(false), credentials: 'same-origin' })
        .then((r) => (r.ok ? r.json() : null))
        .then((data) => {
          if (data) {
            this.available = !!data.available;
            if (data.agent) {
              this.agent = data.agent;
            }
          }
        })
        .catch(() => {});
    },
    fetchMessages() {
      if (!this.token) {
        return Promise.resolve();
      }
      const visible = !document.hidden;
      const seqAtStart = this.sendSeq;
      const url = this.config.messagesUrl
        + '?visible=' + (visible ? '1' : '0')
        + '&page=' + encodeURIComponent(window.location.pathname);

      return fetch(url, { headers: this.headers(false), credentials: 'same-origin' })
        .then((r) => {
          if (r.status === 404) {
            // The conversation is gone (or the token was never valid): start fresh.
            this.forgetConversation();
            return null;
          }
          return r.ok ? r.json() : null;
        })
        .then((data) => {
          if (!data || seqAtStart !== this.sendSeq) {
            return;
          }
          this.messages = data.messages || [];
          this.available = !!data.available;
          if (data.agent) {
            this.agent = data.agent;
          }
          this.agentTyping = !!data.agent_typing;
          this.hasEmail = !!data.has_email;
          this.unreadCount = data.unread_count || 0;

          if (this.unreadCount > 0) {
            if (this.panelOpen && visible) {
              this.markRead();
            } else {
              this.announce();
            }
          }
        })
        .catch(() => {});
    },
    markRead() {
      this.unreadCount = 0;
      fetch(this.config.readUrl, { method: 'POST', headers: this.headers(false), credentials: 'same-origin' }).catch(() => {});
    },
    // A reply arrived while they were not looking at the panel.
    announce() {
      const latest = [...this.messages].reverse().find((m) => m.is_from_admin);
      if (!latest || latest.id <= this.lastPreviewId) {
        return;
      }
      this.lastPreviewId = latest.id;
      storageSet('sessionStorage', PREVIEWED_KEY, String(latest.id));
      this.preview = latest.body.length > 160 ? latest.body.slice(0, 157) + '...' : latest.body;
      this.greetingVisible = false;
      if (document.hidden) {
        this.startFlash();
      }
    },
    forgetConversation() {
      this.token = null;
      this.messages = [];
      this.hasEmail = false;
      this.unreadCount = 0;
      storageSet('localStorage', TOKEN_KEY, null);
      storageSet('sessionStorage', ACTIVE_KEY, null);
    },

    // ── Sending ──

    send(text) {
      const body = (text === undefined ? this.inputText : text).trim();
      if (!body) {
        return;
      }

      const email = this.emailInput.trim();
      if (email && !EMAIL_PATTERN.test(email)) {
        this.emailError = 'That email does not look right.';
        return;
      }
      if (this.needsEmail && !email) {
        this.forceEmail = true;
        this.emailError = 'Add your email so the reply can reach you.';
        this.$nextTick(() => this.$refs.emailField && this.$refs.emailField.focus());
        return;
      }

      if (text === undefined) {
        this.inputText = '';
        this.$nextTick(() => this.autosize());
      }

      const key = 'p' + (++this.pendingSeq);
      this.sendError = '';
      this.pending.push({
        key,
        body,
        is_from_admin: false,
        pending: true,
        failed: false,
        created_at: new Date().toISOString(),
      });
      this.post(key);
    },
    // Pending items are looked up by key every time: the array holds reactive proxies, so a
    // reference kept from before the push would neither update the view nor compare equal.
    pendingItem(key) {
      return this.pending.find((p) => p.key === key);
    },
    dropPending(key) {
      this.pending = this.pending.filter((p) => p.key !== key);
    },
    retry(key) {
      const item = this.pendingItem(key);
      if (!item) {
        return;
      }
      item.failed = false;
      item.pending = true;
      this.post(key);
    },
    post(key) {
      if (!this.token && this.firstSend) {
        // A token-less POST is already out: wait for its token, then send with it.
        this.firstSend.then(() => this.post(key));
        return;
      }
      const item = this.pendingItem(key);
      if (!item) {
        return;
      }
      const payload = {
        body: item.body,
        page: window.location.pathname,
        [this.config.honeypotField]: this.honeypot,
      };
      if (this.emailInput.trim()) {
        payload.email = this.emailInput.trim();
      }
      if (this.nameInput.trim()) {
        payload.name = this.nameInput.trim();
      }

      const request = fetch(this.config.messagesUrl, {
        method: 'POST',
        headers: this.headers(true),
        credentials: 'same-origin',
        body: JSON.stringify(payload),
      })
        .then((r) => r.json().then((data) => ({ ok: r.ok, status: r.status, data })))
        .then(({ ok, status, data }) => {
          if (!ok) {
            if (status === 429 && data.error === 'too_many_conversations') {
              this.dropPending(key);
              this.inputText = item.body;
              this.sendError = data.message;
              return;
            }
            if (status === 422 && data.errors && data.errors.email) {
              // Nobody is there to answer live: ask where the reply should go, and give
              // them their message back to send again.
              this.dropPending(key);
              this.inputText = item.body;
              this.forceEmail = true;
              this.emailError = data.errors.email[0];
              return;
            }
            throw new Error('send failed');
          }
          if (data.token) {
            this.token = data.token;
            storageSet('localStorage', TOKEN_KEY, data.token);
          }
          storageSet('sessionStorage', ACTIVE_KEY, '1');
          this.sendSeq++;
          this.hasEmail = !!data.has_email;
          if (this.hasEmail) {
            this.forceEmail = false;
            this.emailError = '';
          }
          if (!this.firstSentAt) {
            this.firstSentAt = Date.now();
          }
          this.dropPending(key);
          if (data.message && !this.messages.some((m) => m.id === data.message.id)) {
            this.messages.push(data.message);
          }
          this.schedulePoll();
        })
        .catch(() => {
          const failed = this.pendingItem(key);
          if (failed) {
            failed.pending = false;
            failed.failed = true;
          }
        });

      if (!this.token) {
        this.firstSend = request.finally(() => {
          this.firstSend = null;
        });
      }
    },
    saveContact() {
      const email = this.emailInput.trim();
      if (!EMAIL_PATTERN.test(email)) {
        this.emailError = 'That email does not look right.';
        return;
      }
      this.emailError = '';
      if (!this.token) {
        // Goes out with their first message instead.
        return;
      }
      this.savingContact = true;
      fetch(this.config.contactUrl, {
        method: 'POST',
        headers: this.headers(true),
        credentials: 'same-origin',
        body: JSON.stringify({ email, name: this.nameInput.trim() || null, [this.config.honeypotField]: this.honeypot }),
      })
        .then((r) => {
          if (!r.ok) {
            throw r;
          }
          this.hasEmail = true;
          this.forceEmail = false;
          this.contactSaved = true;
          this.emailFormOpen = false;
        })
        .catch(() => {
          this.emailError = 'Could not save that. Please try again.';
        })
        .finally(() => {
          this.savingContact = false;
        });
    },

    // ── Panel and greeting ──

    openPanel() {
      this.panelOpen = true;
    },
    closePanel() {
      this.panelOpen = false;
    },
    togglePanel() {
      this.panelOpen = !this.panelOpen;
    },
    openFromGreeting(chip) {
      this.greetingVisible = false;
      this.panelOpen = true;
      if (chip) {
        this.$nextTick(() => this.send(chip));
      }
    },
    dismissGreeting() {
      this.greetingVisible = false;
    },
    scheduleGreeting() {
      if (!this.config.greet || this.token || storageGet('sessionStorage', GREETED_KEY) === '1') {
        return;
      }
      this.greetingTimer = setTimeout(() => this.tryGreeting(), GREETING_DELAY_MS);
    },
    tryGreeting() {
      if (!this.available || this.panelOpen || this.token || document.hidden) {
        return;
      }
      // Never stack two pop-ups: wait for the cookie banner to be answered first.
      if (this.bannerOffset > 0) {
        this.greetingTimer = setTimeout(() => this.tryGreeting(), 2000);
        return;
      }
      storageSet('sessionStorage', GREETED_KEY, '1');
      this.greetingVisible = true;
    },

    // ── Layout ──

    watchCookieBanner() {
      const banner = document.querySelector('[data-cookie-consent]');
      if (!banner) {
        return;
      }
      const update = () => {
        this.bannerOffset = banner.hidden ? 0 : banner.getBoundingClientRect().height + 12;
      };
      update();
      this.bannerObserver = new MutationObserver(update);
      this.bannerObserver.observe(banner, { attributes: true, attributeFilter: ['hidden', 'data-state'] });
    },
    onResize() {
      this.isMobile = window.innerWidth < 640;
    },
    onVisibility() {
      if (!document.hidden) {
        this.stopFlash();
        if (this.token) {
          this.fetchMessages();
        }
      }
      this.schedulePoll();
    },
    autosize() {
      const el = this.$refs.input;
      if (!el) {
        return;
      }
      el.style.height = 'auto';
      el.style.height = Math.min(el.scrollHeight, 128) + 'px';
    },
    scrollToBottom() {
      const el = this.$refs.scroller;
      if (el) {
        el.scrollTop = el.scrollHeight;
      }
    },
    startFlash() {
      if (this.flashTimer) {
        return;
      }
      let on = false;
      this.flashTimer = setInterval(() => {
        on = !on;
        document.title = on ? '(1) New message' : this.originalTitle;
      }, 1200);
    },
    stopFlash() {
      if (!this.flashTimer) {
        return;
      }
      clearInterval(this.flashTimer);
      this.flashTimer = null;
      document.title = this.originalTitle;
    },

    // ── Formatting ──

    // Text and link runs, rendered by the template - never v-html.
    segments(text) {
      const out = [];
      const value = String(text || '');
      let last = 0;
      value.replace(URL_PATTERN, (match, _p, offset) => {
        let url = match;
        let trailing = '';
        const punct = url.match(/[.,!?)\]]+$/);
        if (punct) {
          trailing = punct[0];
          url = url.slice(0, url.length - trailing.length);
        }
        if (offset > last) {
          out.push({ text: value.slice(last, offset) });
        }
        out.push({ text: url, href: /^www\./i.test(url) ? 'https://' + url : url });
        if (trailing) {
          out.push({ text: trailing });
        }
        last = offset + match.length;
        return match;
      });
      if (last < value.length) {
        out.push({ text: value.slice(last) });
      }
      return out;
    },
    firstVisitorMessageAt() {
      const first = this.messages.find((m) => !m.is_from_admin);
      return first ? new Date(first.created_at).getTime() : null;
    },
    showTimestamp(idx) {
      if (idx === 0) {
        return true;
      }
      const curr = new Date(this.allMessages[idx].created_at);
      const prev = new Date(this.allMessages[idx - 1].created_at);
      return curr - prev > 300000;
    },
    formatGroupTime(iso) {
      const d = new Date(iso);
      const now = new Date();
      const time = d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
      if (d.toDateString() === now.toDateString()) {
        return 'Today ' + time;
      }
      const yesterday = new Date(now);
      yesterday.setDate(yesterday.getDate() - 1);
      if (d.toDateString() === yesterday.toDateString()) {
        return 'Yesterday ' + time;
      }
      return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) + ' ' + time;
    },
  },
};
</script>
