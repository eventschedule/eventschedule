<template>
    <div class="lg:grid lg:grid-cols-2 lg:gap-6">

        <!-- Left: Tabs + Sections + Action buttons -->
        <div>
            <!-- Tab bar -->
            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                <button type="button" @click="showSection('content')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                    :class="activeSection === 'content' ? 'border-[#4E81FA] text-[#4E81FA] font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                    {{ t.content }}
                </button>
                <button type="button" @click="showSection('style')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                    :class="activeSection === 'style' ? 'border-[#4E81FA] text-[#4E81FA] font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                    {{ t.style }}
                </button>
                <button type="button" @click="showSection('settings')"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                    :class="activeSection === 'settings' ? 'border-[#4E81FA] text-[#4E81FA] font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'">
                    {{ t.settings }}
                </button>
                <button type="button" @click="openPreviewInNewTab()"
                    class="lg:hidden px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                    {{ t.preview }}
                </button>
            </div>

            <div class="space-y-2">
                    <div v-show="activeSection === 'content'" class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">

                        <!-- Subject -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 px-5 py-4">{{ t.subject }} *</h3>
                            <div class="px-5 pb-5">
                                <input id="subject" name="subject" type="text"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                    v-model="subject" @input="onSubjectInput" required />
                            </div>
                        </div>

                        <!-- Blocks section -->
                        <div>
                            <div class="flex items-center justify-between px-5 py-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ t.blocks }} ({{ blocks.length }})
                                </h3>
                                <button type="button" @click="toggleBlockPalette()"
                                    :class="showBlockPalette ? 'text-sm px-3 py-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 font-medium' : 'text-sm px-3 py-1 bg-[#4E81FA] text-white rounded-md hover:bg-blue-600 font-medium'">
                                    {{ showBlockPalette ? t.done : t.add_block }}
                                </button>
                            </div>
                            <div :class="showBlockPalette ? 'grid grid-cols-2 gap-4' : ''">
                                <!-- Left: Block palette (only when visible) -->
                                <div v-show="showBlockPalette" class="px-4 pb-4">
                                    <div ref="blockPalette" class="grid grid-cols-2 gap-2">
                                        <div v-for="bt in blockTypes" :key="bt.type"
                                            class="palette-item border border-gray-200 dark:border-gray-600 rounded-lg p-2 text-center cursor-grab hover:border-[#4E81FA] hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors select-none"
                                            :data-block-type="bt.type"
                                            @click="addBlockFromPalette(bt)">
                                            <div class="text-lg mb-1">{{ bt.icon }}</div>
                                            <div class="text-xs text-gray-700 dark:text-gray-300">{{ bt.label }}</div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Right: Block canvas (always visible) -->
                            <div ref="blockCanvas" class="px-4 pb-4 min-h-[100px] space-y-2">
                                <div v-for="block in blocks" :key="block.id"
                                    class="block-item group relative border rounded-lg transition-colors"
                                    :data-block-id="block.id"
                                    :class="selectedBlockId === block.id ? 'border-[#4E81FA] bg-blue-50 dark:bg-blue-900/20 ring-2 ring-[#4E81FA]' : 'border-gray-200 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-400'">

                                    <!-- Block header: drag handle + label + actions -->
                                    <div class="flex items-center justify-between px-3 py-2 cursor-pointer" @click="!showBlockPalette && selectBlock(block.id)">
                                        <div class="flex items-center gap-2">
                                            <span class="cursor-grab text-gray-400 drag-handle">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                            </span>
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ blockIcon(block.type) }} {{ blockLabel(block.type) }}</span>
                                        </div>
                                        <div v-show="!showBlockPalette" class="flex gap-1 sm:opacity-0 sm:group-hover:opacity-100 focus-within:opacity-100 transition-opacity">
                                            <button type="button" @click.stop="cloneBlock(block.id)" class="p-1 text-gray-400 hover:text-[#4E81FA]" :title="t.clone_block">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            </button>
                                            <button type="button" @click.stop="removeBlock(block.id)" class="p-1 text-gray-400 hover:text-red-500" :title="t.remove_block">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Compact snippet: shown when NOT selected -->
                                    <div v-show="selectedBlockId !== block.id && !showBlockPalette" class="px-3 pb-2 text-sm text-gray-600 dark:text-gray-300 truncate cursor-pointer" @click="selectBlock(block.id)">
                                        <span v-if="block.type === 'heading'">
                                            <template v-if="block.data.text">{{ block.data.text }}</template>
                                            <span v-else class="italic text-gray-400 dark:text-gray-500">{{ t.no_content }}</span>
                                        </span>
                                        <span v-else-if="block.type === 'text'">
                                            <template v-if="block.data.content">{{ block.data.content.substring(0, 80) }}</template>
                                            <span v-else class="italic text-gray-400 dark:text-gray-500">{{ t.no_content }}</span>
                                        </span>
                                        <span v-else-if="block.type === 'events'">{{ block.data.useAllEvents ? t.all_upcoming_events : (block.data.eventIds ? block.data.eventIds.length + ' ' + t.events : t.events) }}</span>
                                        <span v-else-if="block.type === 'button'">
                                            <template v-if="block.data.text">{{ block.data.text }}</template>
                                            <span v-else class="italic text-gray-400 dark:text-gray-500">{{ t.no_content }}</span>
                                        </span>
                                        <span v-else-if="block.type === 'image'">{{ block.data.url ? (block.data.alt || block.data.url.substring(0, 50)) : t.image_url + '...' }}</span>
                                        <span v-else-if="block.type === 'social_links'">{{ (block.data.links || []).length + ' ' + t.links }}</span>
                                        <span v-else-if="block.type === 'divider'" class="text-gray-400">---</span>
                                        <span v-else-if="block.type === 'spacer'" class="text-gray-400">{{ (block.data.height || 20) + 'px' }}</span>
                                        <span v-else-if="block.type === 'offer'">
                                            <template v-if="block.data.title">{{ block.data.title }}<template v-if="block.data.salePrice"> - {{ block.data.salePrice }}</template></template>
                                            <span v-else class="italic text-gray-400 dark:text-gray-500">{{ t.no_content }}</span>
                                        </span>
                                        <span v-else-if="block.type === 'profile_image'" class="text-gray-400">{{ t.profile_image }}</span>
                                        <span v-else-if="block.type === 'header_banner'" class="text-gray-400">{{ t.header_image }}</span>
                                    </div>

                                    <!-- Inline settings: shown when selected -->
                                    <div v-show="selectedBlockId === block.id && !showBlockPalette" class="px-3 pb-3 border-t border-gray-100 dark:border-gray-700 mt-1 pt-3">

                                        <!-- Heading block settings -->
                                        <div v-if="block.type === 'heading'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.heading_text }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                    :value="block.data.text"
                                                    @input="onHeadingInput(block.id, $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.heading_level }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] rounded-md shadow-sm"
                                                    :value="block.data.level"
                                                    @change="updateBlockData(block.id, 'level', $event.target.value)">
                                                    <option value="h1">{{ t.heading_header }}</option>
                                                    <option value="h2">{{ t.heading_subheader }}</option>
                                                    <option value="h3">{{ t.heading_section }}</option>
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

                                        <!-- Offer block settings -->
                                        <div v-if="block.type === 'offer'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.offer_title }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                    :value="block.data.title"
                                                    @input="updateBlockData(block.id, 'title', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.offer_description }}</label>
                                                <textarea class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm" rows="3"
                                                    :value="block.data.description"
                                                    @input="updateBlockData(block.id, 'description', $event.target.value)"></textarea>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.original_price }}</label>
                                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                        :value="block.data.originalPrice"
                                                        @input="updateBlockData(block.id, 'originalPrice', $event.target.value)" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.sale_price }}</label>
                                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                        :value="block.data.salePrice"
                                                        @input="updateBlockData(block.id, 'salePrice', $event.target.value)" />
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.coupon_code_label }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                    :value="block.data.couponCode"
                                                    @input="updateBlockData(block.id, 'couponCode', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_text }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                    :value="block.data.buttonText"
                                                    @input="updateBlockData(block.id, 'buttonText', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_url }}</label>
                                                <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                                    :value="block.data.buttonUrl"
                                                    @input="updateBlockData(block.id, 'buttonUrl', $event.target.value)" />
                                            </div>
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

                    </div>

                    <!-- STYLE SECTION -->
                    <div v-show="activeSection === 'style'" class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">

                        <!-- Template -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 px-5 py-4">{{ t.template }}</h3>
                            <div class="px-5 pb-5">
                                <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
                                    <label v-for="tmpl in templateNames" :key="tmpl" class="cursor-pointer"
                                        :class="template === tmpl ? 'ring-2 ring-[#4E81FA] rounded-lg' : ''">
                                        <input type="radio" name="template_selector" :value="tmpl" v-model="template" @change="onTemplateChange(tmpl)" class="sr-only" />
                                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center hover:border-[#4E81FA] transition-colors"
                                            :class="template === tmpl ? 'border-[#4E81FA] bg-blue-50 dark:bg-blue-900/20' : ''">
                                            <div class="h-16 mb-2 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                                <!-- Modern: filled rounded card with accent band -->
                                                <div v-if="tmpl === 'modern'" class="w-full h-full flex flex-col items-center justify-center gap-1">
                                                    <div class="w-10 h-2.5 rounded bg-[#4E81FA]"></div>
                                                    <div class="w-8 h-1 rounded bg-gray-300 dark:bg-gray-500"></div>
                                                    <div class="w-6 h-1 rounded bg-gray-300 dark:bg-gray-500"></div>
                                                    <div class="w-5 h-2 rounded bg-[#4E81FA]"></div>
                                                </div>
                                                <!-- Classic: underlined heading, outlined button -->
                                                <div v-else-if="tmpl === 'classic'" class="w-full h-full flex flex-col items-center justify-center gap-1 bg-[#faf9f6] dark:bg-gray-700">
                                                    <div class="w-6 h-1 bg-[#8B4513]"></div>
                                                    <div class="w-4 h-px bg-[#8B4513]"></div>
                                                    <div class="w-8 h-1 rounded bg-gray-300 dark:bg-gray-500"></div>
                                                    <div class="w-5 h-2 rounded border border-[#8B4513]"></div>
                                                </div>
                                                <!-- Minimal: stark, uppercase, text link -->
                                                <div v-else-if="tmpl === 'minimal'" class="w-full h-full flex flex-col items-center justify-center gap-0.5">
                                                    <div class="w-7 h-0.5 bg-gray-400 dark:bg-gray-500"></div>
                                                    <div class="w-8 h-px bg-gray-200 dark:bg-gray-600"></div>
                                                    <div class="w-6 h-0.5 bg-gray-300 dark:bg-gray-500"></div>
                                                    <div class="w-4 h-px bg-gray-200 dark:bg-gray-600"></div>
                                                    <div class="text-[6px] text-gray-400 dark:text-gray-500 underline mt-0.5">link</div>
                                                </div>
                                                <!-- Bold: dark bg, large accent elements -->
                                                <div v-else-if="tmpl === 'bold'" class="w-full h-full flex flex-col items-center justify-center gap-1 bg-[#16213e]">
                                                    <div class="w-full h-3 bg-[#e94560]"></div>
                                                    <div class="w-6 h-0.5 bg-gray-400"></div>
                                                    <div class="w-8 h-0.5 bg-gray-500"></div>
                                                    <div class="w-6 h-2.5 rounded bg-[#e94560]"></div>
                                                </div>
                                                <!-- Compact: dense, left-aligned, small -->
                                                <div v-else-if="tmpl === 'compact'" class="w-full h-full flex flex-col items-start justify-center gap-0.5 pl-2">
                                                    <div class="flex items-center gap-0.5"><div class="w-0.5 h-2 bg-[#2d6a4f]"></div><div class="w-6 h-1 bg-gray-400 dark:bg-gray-500"></div></div>
                                                    <div class="w-9 h-0.5 bg-gray-300 dark:bg-gray-500 ml-1.5"></div>
                                                    <div class="w-7 h-0.5 bg-gray-300 dark:bg-gray-500 ml-1.5"></div>
                                                    <div class="w-4 h-1.5 bg-[#2d6a4f] ml-1.5"></div>
                                                </div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 capitalize">{{ tmpl }}</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Style Settings -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 px-5 py-4">{{ t.style_settings }}</h3>
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

                    <!-- SETTINGS SECTION -->
                    <div v-show="activeSection === 'settings'" class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg divide-y divide-gray-200 dark:divide-gray-700">

                        <!-- Recipients -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 px-5 py-4">{{ t.recipients }}</h3>
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

                        <!-- A/B Testing -->
                        <div v-if="newsletter && newsletter.exists">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 px-5 py-4">{{ t.ab_testing }}</h3>
                            <div class="px-5 pb-5" v-html="abTestHtml"></div>
                        </div>

                        <!-- Footer -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 px-5 py-4">{{ t.footer_text }}</h3>
                            <div class="px-5 pb-5">
                                <textarea v-model="styleSettings.footerText" rows="2"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] focus:ring-[#4E81FA] shadow-sm"
                                    :placeholder="roleName"></textarea>
                            </div>
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
            <input type="hidden" name="style_settings[footerText]" :value="styleSettings.footerText" />
            <input type="hidden" v-for="segmentId in selectedSegmentIds" :key="'seg_' + segmentId" name="segment_ids[]" :value="segmentId" />

            <!-- Action Buttons -->
            <div class="mt-4 flex flex-wrap gap-3 justify-between">
                <div>
                    <button v-if="newsletter && newsletter.exists" type="button" @click="showTestSend = true" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                        {{ t.send_a_test }}
                    </button>
                </div>
                <div class="flex flex-wrap gap-3">
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
        </div>

        <!-- Right: Always-visible live preview -->
        <div class="hidden lg:block">
            <div class="lg:sticky lg:top-20">
                <div class="bg-white dark:bg-gray-800 shadow-md sm:rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ t.preview }}</h3>
                    </div>
                    <div class="relative">
                        <div v-show="previewLoading" class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 flex items-center justify-center z-10">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="animate-spin h-6 w-6 text-[#4E81FA]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <iframe ref="livePreviewFrame" class="w-full border-0 bg-white"
                                style="height: calc(100vh - 14rem); min-height: 400px;"
                                srcdoc="<html><body style='display:flex;align-items:center;justify-content:center;height:100vh;color:#999;font-family:sans-serif'>Loading preview...</body></html>">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Send Modal -->
        <div v-if="newsletter && newsletter.exists" v-show="showTestSend" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showTestSend = false">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ t.test_send }}</h3>
                <form method="POST" :action="routes.test_send">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ t.test_email_sent_to.replace(':email', roleEmail) }}</p>
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
    roleEmail: { type: String, default: '' },
    abTestHtml: { type: String, default: '' },
    roleSocialLinks: { type: Array, default: () => [] },
    roleName: { type: String, default: '' },
    availableBlockTypes: { type: Array, default: null },
});

const t = props.translations;

const blocks = ref(JSON.parse(JSON.stringify(props.initialBlocks)));
const selectedBlockId = ref(null);
const template = ref(props.initialTemplate);
const subject = ref(props.initialSubject);
const styleSettings = reactive({ ...props.initialStyleSettings });
const selectedSegmentIds = ref([...props.initialSegmentIds]);
const showBlockPalette = ref(false);
const showTestSend = ref(false);
const showSchedule = ref(false);
const previewLoading = ref(false);

const activeSection = ref('content');

// Track whether the first heading is auto-synced from the subject field
const subjectSyncActive = ref((() => {
    const firstHeading = props.initialBlocks.find(b => b.type === 'heading');
    return !firstHeading || !firstHeading.data.text || firstHeading.data.text === props.initialSubject;
})());

function showSection(id) {
    activeSection.value = id;
    if (id) {
        history.replaceState(null, '', '#' + id);
    } else {
        history.replaceState(null, '', window.location.pathname + window.location.search);
    }
}

const templateNames = ['modern', 'classic', 'minimal', 'bold', 'compact'];

const allBlockTypes = [
    { type: 'heading', label: t.block_heading, icon: 'H', defaultData: { text: '', level: 'h1', align: 'center' } },
    { type: 'text', label: t.block_text, icon: '\u00b6', defaultData: { content: '' } },
    { type: 'events', label: t.block_events, icon: '\ud83d\udcc5', defaultData: { layout: 'cards', useAllEvents: true, eventIds: [] } },
    { type: 'button', label: t.block_button, icon: '\u25a2', defaultData: { text: '', url: '', align: 'center' } },
    { type: 'image', label: t.block_image, icon: '\ud83d\uddbc', defaultData: { url: '', alt: '', width: '100%', align: 'center' } },
    { type: 'divider', label: t.block_divider, icon: '\u2014', defaultData: { style: 'solid' } },
    { type: 'spacer', label: t.block_spacer, icon: '\u2195', defaultData: { height: 20 } },
    { type: 'social_links', label: t.block_social_links, icon: '@', defaultData: { links: props.roleSocialLinks.length ? JSON.parse(JSON.stringify(props.roleSocialLinks)) : [{ platform: 'website', url: '' }] } },
    { type: 'profile_image', label: t.block_profile_image, icon: '\ud83d\udc64', defaultData: {} },
    { type: 'header_banner', label: t.block_header_banner, icon: '\ud83c\udff3', defaultData: {} },
    { type: 'offer', label: t.block_offer, icon: '\ud83c\udff7', defaultData: { title: '', description: '', originalPrice: '', salePrice: '', couponCode: '', buttonText: '', buttonUrl: '', align: 'center' } },
];

const blockTypes = props.availableBlockTypes
    ? allBlockTypes.filter(bt => props.availableBlockTypes.includes(bt.type))
    : allBlockTypes;

const blockPalette = ref(null);
const blockCanvas = ref(null);
const livePreviewFrame = ref(null);

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

    const t = window.editorTranslations || {};
    const instance = new EasyMDE({
        element: el,
        toolbar: [
            { name: "bold", action: EasyMDE.toggleBold, className: "editor-button-text", title: t.bold || "Bold", text: "B" },
            { name: "italic", action: EasyMDE.toggleItalic, className: "editor-button-text", title: t.italic || "Italic", text: "I" },
            { name: "heading", action: EasyMDE.toggleHeadingSmaller, className: "editor-button-text", title: t.heading || "Heading", text: "H" },
            "|",
            { name: "link", action: function(editor) { EasyMDE.drawLink(editor); }, className: "editor-button-text", title: t.link || "Link", text: "\ud83d\udd17" },
            { name: "quote", action: EasyMDE.toggleBlockquote, className: "editor-button-text", title: t.quote || "Quote", text: "\"" },
            { name: "unordered-list", action: EasyMDE.toggleUnorderedList, className: "editor-button-text", title: t.unorderedList || "Unordered List", text: "UL" },
            { name: "ordered-list", action: EasyMDE.toggleOrderedList, className: "editor-button-text", title: t.orderedList || "Ordered List", text: "OL" },
            "|",
            { name: "preview", action: EasyMDE.togglePreview, className: "editor-button-text no-disable", title: t.preview || "Toggle Preview", text: "\ud83d\udc41" },
            { name: "guide", action: "https://www.markdownguide.org/basic-syntax/", className: "editor-button-text", title: t.guide || "Markdown Guide", text: "?" },
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
    const bt = allBlockTypes.find(b => b.type === type);
    return bt ? bt.label : type;
}

function blockIcon(type) {
    const bt = allBlockTypes.find(b => b.type === type);
    return bt ? bt.icon : '';
}

function addBlockFromPalette(bt) {
    const newBlock = {
        id: generateId(),
        type: bt.type,
        data: JSON.parse(JSON.stringify(bt.defaultData)),
    };
    blocks.value.push(newBlock);
    showBlockPalette.value = false;
    selectBlock(newBlock.id);
}

function toggleBlockPalette() {
    showBlockPalette.value = !showBlockPalette.value;
    if (showBlockPalette.value) {
        destroyAllEasyMDE();
        selectedBlockId.value = null;
        nextTick(() => initSortable());
    }
}

function selectBlock(blockId) {
    const prevId = selectedBlockId.value;
    selectedBlockId.value = selectedBlockId.value === blockId ? null : blockId;

    // Destroy EasyMDE on previously selected block
    if (prevId) {
        destroyEasyMDE(prevId);
    }

    // Init EasyMDE on newly selected text block, and focus primary input
    const newId = selectedBlockId.value;
    if (newId) {
        const block = blocks.value.find(b => b.id === newId);
        if (block && block.type === 'text') {
            nextTick(() => {
                initEasyMDE(newId);
                if (easyMDEInstances[newId]) {
                    easyMDEInstances[newId].codemirror.focus();
                }
            });
        } else if (block) {
            nextTick(() => {
                const blockEl = document.querySelector(`[data-block-id="${newId}"]`);
                const input = blockEl?.querySelector('input[type=text], input[type=url], input[type=number]');
                if (input) {
                    input.focus();
                }
            });
        }
    }
}

function onSubjectInput() {
    const firstHeading = blocks.value.find(b => b.type === 'heading');
    if (firstHeading && subjectSyncActive.value) {
        firstHeading.data.text = subject.value;
    }
}

function onHeadingInput(blockId, value) {
    const firstHeading = blocks.value.find(b => b.type === 'heading');
    if (firstHeading && firstHeading.id === blockId) {
        subjectSyncActive.value = false;
    }
    updateBlockData(blockId, 'text', value);
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

function cloneBlock(blockId) {
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
        const preservedLayout = styleSettings.eventLayout;
        const preservedFooterText = styleSettings.footerText;
        Object.assign(styleSettings, props.templateDefaults[tmpl]);
        styleSettings.eventLayout = preservedLayout;
        styleSettings.footerText = preservedFooterText;
    }
}

function debouncedPreview() {
    clearTimeout(previewDebounceTimer);
    previewDebounceTimer = setTimeout(() => fetchPreview(), 800);
}

function fetchPreview() {
    if (previewAbortController) previewAbortController.abort();
    previewAbortController = new AbortController();

    const form = document.querySelector('#newsletter-builder')?.closest('form');
    if (!form) return;

    previewLoading.value = true;

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

function openPreviewInNewTab() {
    const form = document.querySelector('#newsletter-builder')?.closest('form');
    if (!form) return;
    const formData = new FormData(form);
    formData.delete('_method');
    formData.append('_token', props.csrfToken);
    fetch(props.previewUrl, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            const w = window.open('', '_blank');
            if (w) { w.document.write(data.html); w.document.close(); }
        });
}

function confirmSend() {
    if (confirm(t.newsletter_send_confirm)) {
        const form = document.querySelector('#newsletter-builder')?.closest('form');
        if (!form) return;

        // Save content via fetch, then POST to send
        const formData = new FormData(form);
        formData.delete('_method');

        fetch(form.action, {
            method: 'POST',
            body: formData,
        }).then(() => {
            const sendForm = document.createElement('form');
            sendForm.method = 'POST';
            sendForm.action = props.routes.send;
            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = props.csrfToken;
            sendForm.appendChild(token);
            document.body.appendChild(sendForm);
            sendForm.submit();
        });
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
                    showBlockPalette.value = false;
                    selectBlock(newBlock.id);
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

function onHashChange() {
    const hash = window.location.hash.replace('#', '');
    if (['content', 'style', 'settings'].includes(hash)) {
        activeSection.value = hash;
    }
}

// Watch for changes and trigger debounced preview
watch(
    [blocks, () => template.value, () => subject.value, () => styleSettings.backgroundColor, () => styleSettings.accentColor, () => styleSettings.textColor, () => styleSettings.fontFamily, () => styleSettings.buttonRadius, () => styleSettings.eventLayout, () => styleSettings.footerText],
    () => {
        debouncedPreview();
    },
    { deep: true }
);

onMounted(() => {
    // Restore section from URL hash
    onHashChange();
    window.addEventListener('hashchange', onHashChange);

    nextTick(() => {
        initSortable();
        fetchPreview();
    });
});

onBeforeUnmount(() => {
    destroyAllEasyMDE();
    window.removeEventListener('hashchange', onHashChange);
    clearTimeout(previewDebounceTimer);
    if (previewAbortController) previewAbortController.abort();
});
</script>
