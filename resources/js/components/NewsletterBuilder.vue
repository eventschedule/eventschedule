<template>
    <div class="lg:grid lg:grid-cols-[7fr_5fr] lg:gap-6">

        <!-- Left: Block palette + Block list with inline settings + Accordions -->
        <div>
            <!-- Trigger live preview updates (via watcher in script) -->

            <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">

                <!-- Block Palette -->
                <div>
                    <button type="button" @click="accordionOpen.blocks = !accordionOpen.blocks"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.add_block }}</h3>
                        <svg :class="accordionOpen.blocks ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.blocks">
                        <div class="px-5 pb-5">
                            <div ref="blockPalette" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <div v-for="bt in blockTypes" :key="bt.type"
                                    class="palette-item border border-gray-200 dark:border-gray-600 rounded-lg p-2 text-center cursor-grab hover:border-[#4E81FA] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors select-none"
                                    :data-block-type="bt.type">
                                    <div class="text-lg mb-1">{{ bt.icon }}</div>
                                    <div class="text-xs text-gray-700 dark:text-gray-300">{{ bt.label }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Block Canvas with inline settings -->
                <div>
                    <button type="button" @click="accordionOpen.canvas = !accordionOpen.canvas"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.blocks }} ({{ blocks.length }})</h3>
                        <svg :class="accordionOpen.canvas ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.canvas">
                        <div ref="blockCanvas" class="px-4 pb-4 min-h-[100px] space-y-2">
                            <div v-for="block in blocks" :key="block.id"
                                class="block-item group relative border rounded-lg transition-colors"
                                :data-block-id="block.id"
                                :class="selectedBlockId === block.id ? 'border-[#4E81FA] bg-blue-50 dark:bg-blue-900/20 ring-2 ring-[#4E81FA]' : 'border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-400'">

                                <!-- Block header: drag handle + label + actions -->
                                <div class="flex items-center justify-between px-3 py-2 cursor-pointer" @click="selectBlock(block.id)">
                                    <div class="flex items-center gap-2">
                                        <span class="cursor-grab text-gray-400 drag-handle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                        </span>
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ blockLabel(block.type) }}</span>
                                    </div>
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button" @click.stop="duplicateBlock(block.id)" class="p-1 text-gray-400 hover:text-[#4E81FA]" :title="t.duplicate_block">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </button>
                                        <button type="button" @click.stop="removeBlock(block.id)" class="p-1 text-gray-400 hover:text-red-500" :title="t.remove_block">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Compact snippet: shown when NOT selected -->
                                <div v-show="selectedBlockId !== block.id" class="px-3 pb-2 text-sm text-gray-600 dark:text-gray-300 truncate">
                                    <span v-if="block.type === 'heading'">{{ block.data.text || t.heading_text + '...' }}</span>
                                    <span v-else-if="block.type === 'text'">{{ (block.data.content || t.content + '...').substring(0, 80) }}</span>
                                    <span v-else-if="block.type === 'events'">{{ block.data.useAllEvents ? t.all_upcoming_events : (block.data.eventIds ? block.data.eventIds.length + ' ' + t.events : t.events) }}</span>
                                    <span v-else-if="block.type === 'button'">{{ block.data.text || t.button_text + '...' }}</span>
                                    <span v-else-if="block.type === 'image'">{{ block.data.url ? (block.data.alt || block.data.url.substring(0, 50)) : t.image_url + '...' }}</span>
                                    <span v-else-if="block.type === 'social_links'">{{ (block.data.links || []).length + ' ' + t.links }}</span>
                                    <span v-else-if="block.type === 'divider'" class="text-gray-400">---</span>
                                    <span v-else-if="block.type === 'spacer'" class="text-gray-400">{{ (block.data.height || 20) + 'px' }}</span>
                                    <span v-else-if="block.type === 'profile_image'" class="text-gray-400">{{ t.profile_image }}</span>
                                    <span v-else-if="block.type === 'header_banner'" class="text-gray-400">{{ t.header_image }}</span>
                                </div>

                                <!-- Inline settings: shown when selected -->
                                <div v-show="selectedBlockId === block.id" class="px-3 pb-3 border-t border-gray-100 dark:border-gray-700 mt-1 pt-3">

                                    <!-- Heading block settings -->
                                    <div v-if="block.type === 'heading'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.heading_text }}</label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                :value="block.data.text"
                                                @input="updateBlockData(block.id, 'text', $event.target.value)" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.heading_level }}</label>
                                            <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                                                :value="block.data.level"
                                                @change="updateBlockData(block.id, 'level', $event.target.value)">
                                                <option value="h1">H1</option>
                                                <option value="h2">H2</option>
                                                <option value="h3">H3</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.alignment }}</label>
                                            <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                                                :value="block.data.align"
                                                @change="updateBlockData(block.id, 'align', $event.target.value)">
                                                <option value="left">{{ t.left }}</option>
                                                <option value="center">{{ t.center }}</option>
                                                <option value="right">{{ t.right }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Text block settings -->
                                    <div v-if="block.type === 'text'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.content }}</label>
                                            <textarea :ref="el => setTextBlockRef(block.id, el)" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm" rows="6"
                                                :value="block.data.content"
                                                @input="updateBlockData(block.id, 'content', $event.target.value)"></textarea>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t.markdown_supported }}</p>
                                        </div>
                                    </div>

                                    <!-- Events block settings -->
                                    <div v-if="block.type === 'events'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.event_layout }}</label>
                                            <div class="flex gap-4 mt-2">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" value="cards"
                                                        :checked="block.data.layout === 'cards'"
                                                        @change="updateBlockData(block.id, 'layout', 'cards')"
                                                        class="border-gray-300 dark:border-gray-700 text-[#4E81FA] focus:ring-[#4E81FA]" />
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.cards }}</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" value="list"
                                                        :checked="block.data.layout === 'list'"
                                                        @change="updateBlockData(block.id, 'layout', 'list')"
                                                        class="border-gray-300 dark:border-gray-700 text-[#4E81FA] focus:ring-[#4E81FA]" />
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.list }}</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox"
                                                    :checked="block.data.useAllEvents"
                                                    @change="updateBlockData(block.id, 'useAllEvents', $event.target.checked)"
                                                    class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA]" />
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.all_upcoming_events }}</span>
                                            </label>
                                        </div>
                                        <div v-show="!block.data.useAllEvents">
                                            <div v-if="events.length" class="space-y-2 max-h-60 overflow-y-auto">
                                                <div v-for="evt in events" :key="evt.id" class="flex items-center gap-3 p-2 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                    <label class="flex items-center gap-2 cursor-pointer flex-1">
                                                        <input type="checkbox"
                                                            :checked="block.data.eventIds && block.data.eventIds.includes(evt.id)"
                                                            @change="toggleBlockEvent(block.id, evt.id)"
                                                            class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA]" />
                                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ evt.name }}</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ evt.date }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ t.no_upcoming_events }}</p>
                                        </div>
                                    </div>

                                    <!-- Button block settings -->
                                    <div v-if="block.type === 'button'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_text }}</label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                :value="block.data.text"
                                                @input="updateBlockData(block.id, 'text', $event.target.value)" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_url }}</label>
                                            <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                :value="block.data.url"
                                                @input="updateBlockData(block.id, 'url', $event.target.value)" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.alignment }}</label>
                                            <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                                                :value="block.data.align"
                                                @change="updateBlockData(block.id, 'align', $event.target.value)">
                                                <option value="left">{{ t.left }}</option>
                                                <option value="center">{{ t.center }}</option>
                                                <option value="right">{{ t.right }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Divider block settings -->
                                    <div v-if="block.type === 'divider'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.divider_style }}</label>
                                            <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                                                :value="block.data.style"
                                                @change="updateBlockData(block.id, 'style', $event.target.value)">
                                                <option value="solid">{{ t.solid }}</option>
                                                <option value="dashed">{{ t.dashed }}</option>
                                                <option value="dotted">{{ t.dotted }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Spacer block settings -->
                                    <div v-if="block.type === 'spacer'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.spacer_height }}</label>
                                            <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" min="5" max="200"
                                                :value="block.data.height"
                                                @input="updateBlockData(block.id, 'height', parseInt($event.target.value) || 20)" />
                                        </div>
                                    </div>

                                    <!-- Image block settings -->
                                    <div v-if="block.type === 'image'" class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.image_url }}</label>
                                            <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                :value="block.data.url"
                                                @input="updateBlockData(block.id, 'url', $event.target.value)" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.image_alt }}</label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                :value="block.data.alt"
                                                @input="updateBlockData(block.id, 'alt', $event.target.value)" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.width }}</label>
                                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" placeholder="100%"
                                                :value="block.data.width"
                                                @input="updateBlockData(block.id, 'width', $event.target.value)" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.alignment }}</label>
                                            <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                                                :value="block.data.align"
                                                @change="updateBlockData(block.id, 'align', $event.target.value)">
                                                <option value="left">{{ t.left }}</option>
                                                <option value="center">{{ t.center }}</option>
                                                <option value="right">{{ t.right }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Social links block settings -->
                                    <div v-if="block.type === 'social_links'" class="space-y-3">
                                        <div v-for="(link, linkIdx) in block.data.links" :key="linkIdx" class="flex gap-2 items-end">
                                            <div class="flex-1">
                                                <label v-if="linkIdx === 0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.platform }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm text-sm"
                                                    :value="link.platform"
                                                    @change="updateSocialLink(block.id, linkIdx, 'platform', $event.target.value)">
                                                    <option value="website">Website</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="twitter">X / Twitter</option>
                                                    <option value="youtube">YouTube</option>
                                                    <option value="tiktok">TikTok</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                </select>
                                            </div>
                                            <div class="flex-[2]">
                                                <label v-if="linkIdx === 0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL</label>
                                                <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm text-sm"
                                                    :value="link.url"
                                                    @input="updateSocialLink(block.id, linkIdx, 'url', $event.target.value)" />
                                            </div>
                                            <button type="button" @click="removeSocialLink(block.id, linkIdx)"
                                                class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-300 mb-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <button type="button" @click="addSocialLink(block.id)"
                                            class="text-sm text-[#4E81FA] hover:text-blue-700">+ {{ t.add_link }}</button>
                                    </div>

                                    <!-- Profile image / header banner: no settings -->
                                    <div v-if="block.type === 'profile_image' || block.type === 'header_banner'">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ t.auto_populated_from_schedule }}</p>
                                    </div>
                                </div>
                            </div>

                            <div v-show="blocks.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-500">
                                <p class="text-sm">{{ t.drag_blocks_here }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Section -->
                <div>
                    <button type="button" @click="accordionOpen.template = !accordionOpen.template"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.template }}</h3>
                        <svg :class="accordionOpen.template ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.template">
                        <div class="px-5 pb-5">
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                                <label v-for="tmpl in templateNames" :key="tmpl" class="cursor-pointer"
                                    :class="template === tmpl ? 'ring-2 ring-[#4E81FA] rounded-lg' : ''">
                                    <input type="radio" name="template_selector" :value="tmpl" v-model="template" @change="onTemplateChange(tmpl)" class="sr-only" />
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center hover:border-[#4E81FA] transition-colors"
                                        :class="template === tmpl ? 'border-[#4E81FA] bg-blue-50 dark:bg-blue-900/20' : ''">
                                        <div class="h-16 mb-2 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <div v-if="tmpl === 'modern'" class="w-10 h-10 rounded-md bg-[#4E81FA]"></div>
                                            <div v-else-if="tmpl === 'classic'" class="w-10 h-10 rounded border-2 border-[#8B4513]"></div>
                                            <div v-else-if="tmpl === 'minimal'" class="w-10 h-10 border border-gray-300 dark:border-gray-500"></div>
                                            <div v-else-if="tmpl === 'bold'" class="w-10 h-10 rounded-md bg-[#e94560]"></div>
                                            <div v-else-if="tmpl === 'compact'" class="w-10 h-8 rounded bg-[#2d6a4f] mt-1"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300 capitalize">{{ tmpl }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject Section -->
                <div>
                    <button type="button" @click="accordionOpen.subject = !accordionOpen.subject"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.subject }}</h3>
                        <svg :class="accordionOpen.subject ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.subject">
                        <div class="px-5 pb-5">
                            <input id="subject" name="subject" type="text"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                v-model="subject" required />
                        </div>
                    </div>
                </div>

                <!-- Style Section -->
                <div>
                    <button type="button" @click="accordionOpen.style = !accordionOpen.style"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.style_settings }}</h3>
                        <svg :class="accordionOpen.style ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.style">
                        <div class="px-5 pb-5">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.background_color }}</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="color" v-model="styleSettings.backgroundColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                                            <input type="text" v-model="styleSettings.backgroundColor" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.accent_color }}</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="color" v-model="styleSettings.accentColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                                            <input type="text" v-model="styleSettings.accentColor" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.text_color }}</label>
                                        <div class="flex items-center gap-2 mt-1">
                                            <input type="color" v-model="styleSettings.textColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                                            <input type="text" v-model="styleSettings.textColor" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" />
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.font_family }}</label>
                                        <select v-model="styleSettings.fontFamily" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm">
                                            <option value="Arial">Arial</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Verdana">Verdana</option>
                                            <option value="Trebuchet MS">Trebuchet MS</option>
                                            <option value="Courier New">Courier New</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_style }}</label>
                                        <div class="flex gap-4 mt-2">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" v-model="styleSettings.buttonRadius" value="rounded" class="border-gray-300 dark:border-gray-700 text-[#4E81FA] focus:ring-[#4E81FA]" />
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.rounded }}</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" v-model="styleSettings.buttonRadius" value="square" class="border-gray-300 dark:border-gray-700 text-[#4E81FA] focus:ring-[#4E81FA]" />
                                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.square }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recipients Section -->
                <div>
                    <button type="button" @click="accordionOpen.recipients = !accordionOpen.recipients"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.recipients }}</h3>
                        <svg :class="accordionOpen.recipients ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.recipients">
                        <div class="px-5 pb-5">
                            <div v-if="segments.length" class="space-y-3">
                                <label v-for="segment in segments" :key="segment.id"
                                    class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:border-[#4E81FA] transition-colors"
                                    :class="selectedSegmentIds.includes(segment.id) ? 'bg-blue-50 dark:bg-blue-900/20 border-[#4E81FA]' : ''">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox"
                                            :checked="selectedSegmentIds.includes(segment.id)"
                                            @change="toggleSegment(segment.id)"
                                            class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA]" />
                                        <div>
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ segment.name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ms-2">({{ segment.type_label }})</span>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ segment.count }}</span>
                                </label>
                            </div>
                            <div v-else>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ t.no_segments }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ t.default_all_followers }}</p>
                            </div>
                            <div class="mt-3">
                                <a :href="routes.manage_segments" class="text-sm text-[#4E81FA] hover:text-blue-700">{{ t.manage_segments }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- A/B Testing Section -->
                <div v-if="newsletter && newsletter.exists">
                    <button type="button" @click="accordionOpen.abTest = !accordionOpen.abTest"
                            class="flex justify-between items-center w-full text-left px-5 py-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ t.ab_testing }}</h3>
                        <svg :class="accordionOpen.abTest ? 'rotate-180' : ''" class="w-5 h-5 text-gray-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-show="accordionOpen.abTest">
                        <div class="px-5 pb-5" v-html="abTestHtml"></div>
                    </div>
                </div>

            </div>

            <!-- Hidden fields -->
            <input type="hidden" name="blocks" :value="JSON.stringify(blocks)" />
            <input type="hidden" name="template" :value="template" />
            <input type="hidden" name="style_settings[backgroundColor]" :value="styleSettings.backgroundColor" />
            <input type="hidden" name="style_settings[accentColor]" :value="styleSettings.accentColor" />
            <input type="hidden" name="style_settings[textColor]" :value="styleSettings.textColor" />
            <input type="hidden" name="style_settings[fontFamily]" :value="styleSettings.fontFamily" />
            <input type="hidden" name="style_settings[buttonRadius]" :value="styleSettings.buttonRadius" />
            <input type="hidden" name="style_settings[eventLayout]" :value="styleSettings.eventLayout" />
            <input type="hidden" v-for="segmentId in selectedSegmentIds" :key="'seg_' + segmentId" name="segment_ids[]" :value="segmentId" />

            <!-- Action Buttons -->
            <div class="mt-4 flex flex-wrap gap-3 justify-end">
                <!-- Preview: mobile only -->
                <button type="button" @click="openPreview()" class="lg:hidden inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ t.preview }}
                </button>

                <button v-if="newsletter && newsletter.exists" type="button" @click="showTestSend = true" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ t.test_send }}
                </button>
                <button v-if="newsletter && newsletter.exists" type="button" @click="showSchedule = true" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-yellow-600">
                    {{ t.schedule_newsletter }}
                </button>

                <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600">
                    {{ t.save }}
                </button>

                <button v-if="newsletter && newsletter.exists && newsletter.canSend" type="button" @click="confirmSend()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-green-700">
                    {{ t.send_now }}
                </button>
            </div>
        </div>

        <!-- Right: Always-visible live preview -->
        <div class="hidden lg:block">
            <div class="lg:sticky lg:top-20">
                <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ t.preview }}</h3>
                        <span v-show="previewLoading" class="text-xs text-gray-400 flex items-center gap-1">
                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                    <iframe ref="livePreviewFrame" class="w-full border-0 bg-white"
                            style="height: calc(100vh - 10rem); min-height: 500px;"
                            srcdoc="<html><body style='display:flex;align-items:center;justify-content:center;height:100vh;color:#999;font-family:sans-serif'>Loading preview...</body></html>">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Preview Modal (mobile) -->
        <div v-show="showPreview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showPreview = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ t.preview }}</h3>
                    <button @click="showPreview = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-4">
                    <iframe ref="previewFrame" class="w-full border-0" style="height: 600px;"></iframe>
                </div>
            </div>
        </div>

        <!-- Test Send Modal -->
        <div v-if="newsletter && newsletter.exists" v-show="showTestSend" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showTestSend = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t.test_send }}</h3>
                <form method="POST" :action="routes.test_send">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.email }}</label>
                    <input id="test_email" name="email" type="email"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                        :value="userEmail" required />
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" @click="showTestSend = false" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-200">{{ t.cancel }}</button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#4E81FA] border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-600">{{ t.send }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Modal -->
        <div v-if="newsletter && newsletter.exists" v-show="showSchedule" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showSchedule = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t.schedule_newsletter }}</h3>
                <form method="POST" :action="routes.schedule">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.send_at }}</label>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" required />
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" @click="showSchedule = false" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-200">{{ t.cancel }}</button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-yellow-600">{{ t.schedule }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';

const props = defineProps({
    initialBlocks: { type: Array, default: () => [] },
    initialTemplate: { type: String, default: 'modern' },
    initialSubject: { type: String, default: '' },
    initialStyleSettings: { type: Object, default: () => ({}) },
    initialSegmentIds: { type: Array, default: () => [] },
    templateDefaults: { type: Object, default: () => ({}) },
    events: { type: Array, default: () => [] },
    segments: { type: Array, default: () => [] },
    previewUrl: { type: String, default: '' },
    csrfToken: { type: String, default: '' },
    translations: { type: Object, default: () => ({}) },
    newsletter: { type: Object, default: null },
    routes: { type: Object, default: () => ({}) },
    userEmail: { type: String, default: '' },
    abTestHtml: { type: String, default: '' },
});

const t = props.translations;

const blocks = ref(JSON.parse(JSON.stringify(props.initialBlocks)));
const selectedBlockId = ref(null);
const template = ref(props.initialTemplate);
const subject = ref(props.initialSubject);
const styleSettings = reactive({ ...props.initialStyleSettings });
const selectedSegmentIds = ref([...props.initialSegmentIds]);
const showPreview = ref(false);
const showTestSend = ref(false);
const showSchedule = ref(false);
const previewLoading = ref(false);

const accordionOpen = reactive({
    blocks: true,
    canvas: true,
    template: false,
    subject: true,
    style: false,
    recipients: false,
    abTest: false,
});

const templateNames = ['modern', 'classic', 'minimal', 'bold', 'compact'];

const blockTypes = [
    { type: 'heading', label: t.block_heading, icon: 'H', defaultData: { text: '', level: 'h1', align: 'center' } },
    { type: 'text', label: t.block_text, icon: '\u00b6', defaultData: { content: '' } },
    { type: 'events', label: t.block_events, icon: '\ud83d\udcc5', defaultData: { layout: 'cards', useAllEvents: true, eventIds: [] } },
    { type: 'button', label: t.block_button, icon: '\u25a2', defaultData: { text: '', url: '', align: 'center' } },
    { type: 'image', label: t.block_image, icon: '\ud83d\uddbc', defaultData: { url: '', alt: '', width: '100%', align: 'center' } },
    { type: 'divider', label: t.block_divider, icon: '\u2014', defaultData: { style: 'solid' } },
    { type: 'spacer', label: t.block_spacer, icon: '\u2195', defaultData: { height: 20 } },
    { type: 'social_links', label: t.block_social_links, icon: '@', defaultData: { links: [{ platform: 'website', url: '' }] } },
    { type: 'profile_image', label: t.block_profile_image, icon: '\ud83d\udc64', defaultData: {} },
    { type: 'header_banner', label: t.block_header_banner, icon: '\ud83c\udff3', defaultData: {} },
];

const blockPalette = ref(null);
const blockCanvas = ref(null);
const livePreviewFrame = ref(null);
const previewFrame = ref(null);

const textBlockRefs = {};
const easyMDEInstances = {};

let previewDebounceTimer = null;
let previewAbortController = null;

function setTextBlockRef(blockId, el) {
    if (el) {
        textBlockRefs[blockId] = el;
    } else {
        delete textBlockRefs[blockId];
    }
}

function initEasyMDE(blockId) {
    destroyEasyMDE(blockId);
    const el = textBlockRefs[blockId];
    if (!el) return;
    const block = blocks.value.find(b => b.id === blockId);
    if (!block) return;

    const instance = new EasyMDE({
        element: el,
        toolbar: [
            { name: "bold", action: EasyMDE.toggleBold, className: "editor-button-text", title: "Bold", text: "B" },
            { name: "italic", action: EasyMDE.toggleItalic, className: "editor-button-text", title: "Italic", text: "I" },
            { name: "heading", action: EasyMDE.toggleHeadingSmaller, className: "editor-button-text", title: "Heading", text: "H" },
            "|",
            { name: "link", action: function(editor) { EasyMDE.drawLink(editor); }, className: "editor-button-text", title: "Link", text: "\ud83d\udd17" },
            { name: "quote", action: EasyMDE.toggleBlockquote, className: "editor-button-text", title: "Quote", text: "\"" },
            { name: "unordered-list", action: EasyMDE.toggleUnorderedList, className: "editor-button-text", title: "Unordered List", text: "UL" },
            { name: "ordered-list", action: EasyMDE.toggleOrderedList, className: "editor-button-text", title: "Ordered List", text: "OL" },
            "|",
            { name: "preview", action: EasyMDE.togglePreview, className: "editor-button-text no-disable", title: "Toggle Preview", text: "\ud83d\udc41" },
            { name: "guide", action: "https://www.markdownguide.org/basic-syntax/", className: "editor-button-text", title: "Markdown Guide", text: "?" },
        ],
        initialValue: block.data.content || '',
        minHeight: "200px",
        spellChecker: true,
        nativeSpellcheck: true,
        status: false,
    });

    instance.codemirror.on('change', () => {
        updateBlockData(blockId, 'content', instance.value());
    });

    easyMDEInstances[blockId] = instance;
}

function destroyEasyMDE(blockId) {
    if (easyMDEInstances[blockId]) {
        easyMDEInstances[blockId].toTextArea();
        delete easyMDEInstances[blockId];
    }
}

function destroyAllEasyMDE() {
    Object.keys(easyMDEInstances).forEach(id => destroyEasyMDE(id));
}

function generateId() {
    return 'b_' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
}

function blockLabel(type) {
    const bt = blockTypes.find(b => b.type === type);
    return bt ? bt.label : type;
}

function selectBlock(blockId) {
    const prevId = selectedBlockId.value;
    selectedBlockId.value = selectedBlockId.value === blockId ? null : blockId;

    // Destroy EasyMDE on previously selected block
    if (prevId) {
        destroyEasyMDE(prevId);
    }

    // Init EasyMDE on newly selected text block
    const newId = selectedBlockId.value;
    if (newId) {
        const block = blocks.value.find(b => b.id === newId);
        if (block && block.type === 'text') {
            nextTick(() => initEasyMDE(newId));
        }
    }
}

function updateBlockData(blockId, key, value) {
    const block = blocks.value.find(b => b.id === blockId);
    if (block) {
        block.data[key] = value;
    }
}

function toggleBlockEvent(blockId, eventId) {
    const block = blocks.value.find(b => b.id === blockId);
    if (!block) return;
    if (!block.data.eventIds) block.data.eventIds = [];
    const idx = block.data.eventIds.indexOf(eventId);
    if (idx > -1) {
        block.data.eventIds.splice(idx, 1);
    } else {
        block.data.eventIds.push(eventId);
    }
}

function updateSocialLink(blockId, linkIdx, key, value) {
    const block = blocks.value.find(b => b.id === blockId);
    if (block && block.data.links && block.data.links[linkIdx]) {
        block.data.links[linkIdx][key] = value;
    }
}

function addSocialLink(blockId) {
    const block = blocks.value.find(b => b.id === blockId);
    if (block) {
        if (!block.data.links) block.data.links = [];
        block.data.links.push({ platform: 'website', url: '' });
    }
}

function removeSocialLink(blockId, linkIdx) {
    const block = blocks.value.find(b => b.id === blockId);
    if (block && block.data.links) {
        block.data.links.splice(linkIdx, 1);
    }
}

function duplicateBlock(blockId) {
    const idx = blocks.value.findIndex(b => b.id === blockId);
    if (idx === -1) return;
    const original = blocks.value[idx];
    const copy = {
        id: generateId(),
        type: original.type,
        data: JSON.parse(JSON.stringify(original.data)),
    };
    blocks.value.splice(idx + 1, 0, copy);
    selectedBlockId.value = copy.id;
}

function removeBlock(blockId) {
    destroyEasyMDE(blockId);
    blocks.value = blocks.value.filter(b => b.id !== blockId);
    if (selectedBlockId.value === blockId) {
        selectedBlockId.value = null;
    }
}

function toggleSegment(segmentId) {
    const idx = selectedSegmentIds.value.indexOf(segmentId);
    if (idx > -1) {
        selectedSegmentIds.value.splice(idx, 1);
    } else {
        selectedSegmentIds.value.push(segmentId);
    }
}

function onTemplateChange(tmpl) {
    if (props.templateDefaults[tmpl]) {
        Object.assign(styleSettings, props.templateDefaults[tmpl]);
    }
}

function debouncedPreview() {
    clearTimeout(previewDebounceTimer);
    previewDebounceTimer = setTimeout(() => fetchPreview(), 800);
}

function fetchPreview() {
    if (previewAbortController) previewAbortController.abort();
    previewAbortController = new AbortController();
    previewLoading.value = true;

    const form = document.querySelector('#newsletter-builder')?.closest('form');
    if (!form) return;

    const formData = new FormData(form);
    formData.delete('_method');

    fetch(props.previewUrl, {
        method: 'POST',
        body: formData,
        signal: previewAbortController.signal,
    })
    .then(r => r.json())
    .then(data => {
        if (livePreviewFrame.value) livePreviewFrame.value.srcdoc = data.html;
        previewLoading.value = false;
    })
    .catch(e => { if (e.name !== 'AbortError') previewLoading.value = false; });
}

function openPreview() {
    showPreview.value = true;
    const form = document.querySelector('#newsletter-builder')?.closest('form');
    if (!form) return;

    const formData = new FormData(form);
    formData.delete('_method');
    formData.append('_token', props.csrfToken);

    fetch(props.previewUrl, {
        method: 'POST',
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (previewFrame.value) previewFrame.value.srcdoc = data.html;
    });
}

function confirmSend() {
    if (confirm(t.newsletter_send_confirm)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = props.routes.send;
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = props.csrfToken;
        form.appendChild(token);
        document.body.appendChild(form);
        form.submit();
    }
}

function initSortable() {
    if (typeof Sortable === 'undefined') return;

    if (blockPalette.value) {
        new Sortable(blockPalette.value, {
            group: { name: 'blocks', pull: 'clone', put: false },
            sort: false,
            draggable: '.palette-item',
            ghostClass: 'opacity-50',
        });
    }

    if (blockCanvas.value) {
        new Sortable(blockCanvas.value, {
            group: { name: 'blocks', pull: false, put: true },
            animation: 150,
            handle: '.drag-handle',
            draggable: '.block-item',
            ghostClass: 'opacity-50',
            onAdd(evt) {
                const blockType = evt.item.getAttribute('data-block-type');
                const bt = blockTypes.find(b => b.type === blockType);
                if (bt) {
                    const newBlock = {
                        id: generateId(),
                        type: bt.type,
                        data: JSON.parse(JSON.stringify(bt.defaultData)),
                    };
                    evt.item.remove();
                    blocks.value.splice(evt.newIndex, 0, newBlock);
                    selectedBlockId.value = newBlock.id;
                }
            },
            onEnd(evt) {
                if (evt.from === evt.to && evt.oldIndex !== evt.newIndex) {
                    const movedBlock = blocks.value.splice(evt.oldIndex, 1)[0];
                    blocks.value.splice(evt.newIndex, 0, movedBlock);
                }
            },
        });
    }
}

// Watch for changes and trigger debounced preview
watch(
    [blocks, () => template.value, () => subject.value, () => styleSettings.backgroundColor, () => styleSettings.accentColor, () => styleSettings.textColor, () => styleSettings.fontFamily, () => styleSettings.buttonRadius, () => styleSettings.eventLayout],
    () => {
        debouncedPreview();
    },
    { deep: true }
);

onMounted(() => {
    nextTick(() => {
        initSortable();
        fetchPreview();
    });
});

onBeforeUnmount(() => {
    destroyAllEasyMDE();
});
</script>
