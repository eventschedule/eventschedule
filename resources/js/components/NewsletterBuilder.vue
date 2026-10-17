<template>
    <!-- The builder: what is written on the left, the mail as it will arrive on the right, and a
         bar along the foot that saves it and sends it. Its look is the portal's page kit
         (form-kit-styles: the tabs, the save bar) plus newsletter/partials/_builder-styles, which
         styles the nb-* classes below. -->
    <div class="nb" :class="{ 'is-template': isTemplateMode }">
        <div class="nb-grid">

            <!-- Left: the tabs and what each one holds -->
            <div class="nb-edit">
                <div class="ap-tabs-wrap nb-tabs">
                    <div class="ap-tabs" role="tablist">
                        <button v-for="tab in tabs" :key="tab.id" type="button" class="ap-tab" role="tab"
                            :id="'nb-tab-' + tab.id"
                            :aria-selected="activeSection === tab.id ? 'true' : 'false'"
                            :aria-controls="'nb-pane-' + tab.id"
                            @click="showSection(tab.id)">{{ tab.label }}</button>
                        <!-- A phone has no room for the preview beside the editor: it opens over it. -->
                        <button type="button" class="ap-tab nb-tab-preview" @click="openPhonePreview()">{{ t.preview }}</button>
                    </div>
                </div>

                <!-- CONTENT -->
                <div v-show="activeSection === 'content'" id="nb-pane-content" role="tabpanel" aria-labelledby="nb-tab-content" class="ap-card nb-card">

                    <div v-if="!isTemplateMode" class="nb-section">
                        <label for="subject" class="nb-label">{{ t.subject }} <span aria-hidden="true">*</span></label>
                        <input id="subject" name="subject" type="text" maxlength="255" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                            v-model="subject" @input="onSubjectInput" required />
                        <!-- Who it goes to, said where it is written; the choice itself is on Settings. -->
                        <p class="nb-audience">
                            <span class="nb-audience-to">{{ t.to }}</span>
                            <bdi class="nb-audience-who">{{ audienceLine }}</bdi>
                            <button v-if="!(abTest && abTest.variant === 'B')" type="button" class="event-link" @click="goToRecipients()">{{ t.change }}</button>
                        </p>
                    </div>

                    <!-- Preview text: the line an inbox shows after the subject. Posted by the
                         hidden style_settings[previewText] field below. -->
                    <div v-if="!isTemplateMode" class="nb-section">
                        <label for="newsletter_preview_text" class="nb-label">{{ t.preview_text }}</label>
                        <input id="newsletter_preview_text" type="text" maxlength="150" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                            v-model="styleSettings.previewText" />
                        <p class="nb-hint">{{ t.preview_text_help }}</p>
                    </div>

                    <!-- The blocks, in the order the mail has them -->
                    <div class="nb-section is-blocks">
                        <div class="nb-section-head">
                            <h3 class="nb-title">{{ t.blocks }} <span class="nb-count">{{ blocks.length }}</span></h3>
                            <button type="button" class="page-tool nb-add" :aria-expanded="showBlockPalette ? 'true' : 'false'" aria-controls="nb-palette" @click="toggleBlockPalette()">
                                <svg v-if="!showBlockPalette" class="nb-icon is-sm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" /></svg>
                                {{ showBlockPalette ? t.done : t.add_block }}
                            </button>
                        </div>

                        <!-- What can be added. A click puts it after the open block (or at the end);
                             it can also be dragged to a place in the list. -->
                        <div v-show="showBlockPalette" id="nb-palette" class="nb-palette">
                            <p class="nb-palette-where">{{ paletteWhere }}</p>
                            <div ref="blockPalette" class="nb-palette-grid">
                                <div v-for="bt in blockTypes" :key="bt.type" role="button" tabindex="0"
                                    class="palette-item nb-palette-item" :data-block-type="bt.type"
                                    @click="addBlockFromPalette(bt)"
                                    @keydown.enter.prevent="addBlockFromPalette(bt)"
                                    @keydown.space.prevent="addBlockFromPalette(bt)">
                                    <svg class="nb-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="blockIconPath(bt.type)" /></svg>
                                    <span>{{ bt.label }}</span>
                                </div>
                            </div>
                        </div>

                        <div ref="blockCanvas" class="nb-canvas">
                            <div v-for="(block, index) in blocks" :key="block.id"
                                class="block-item nb-block" :class="{ 'is-open': selectedBlockId === block.id }"
                                :data-block-id="block.id">

                                <!-- The block's line: drag handle, what it is and holds, its tools -->
                                <div class="nb-block-head">
                                    <span class="drag-handle nb-handle" aria-hidden="true">
                                        <svg class="nb-icon is-sm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" /></svg>
                                    </span>
                                    <button type="button" class="nb-block-toggle" :aria-expanded="selectedBlockId === block.id ? 'true' : 'false'" @click="selectBlock(block.id)">
                                        <svg class="nb-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" :d="blockIconPath(block.type)" /></svg>
                                        <span class="nb-block-name">{{ blockLabel(block.type) }}</span>
                                        <span v-if="selectedBlockId !== block.id && blockSnippet(block)" class="nb-block-snippet"><bdi>{{ blockSnippet(block) }}</bdi></span>
                                        <span v-else-if="selectedBlockId !== block.id && blockWantsContent(block)" class="nb-block-snippet is-empty">{{ t.no_content }}</span>
                                    </button>
                                    <div class="nb-block-tools">
                                        <button type="button" class="nb-tool" :disabled="index === 0" :title="t.move_up" :aria-label="t.move_up" @click="moveBlock(block.id, -1)">
                                            <svg class="nb-icon is-sm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                        </button>
                                        <button type="button" class="nb-tool" :disabled="index === blocks.length - 1" :title="t.move_down" :aria-label="t.move_down" @click="moveBlock(block.id, 1)">
                                            <svg class="nb-icon is-sm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                        <button type="button" class="nb-tool" :title="t.clone_block" :aria-label="t.clone_block" @click="cloneBlock(block.id)">
                                            <svg class="nb-icon is-sm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                        </button>
                                        <button type="button" class="nb-tool is-danger" :title="t.remove_block" :aria-label="t.remove_block" @click="removeBlock(block.id)">
                                            <svg class="nb-icon is-sm" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- What the open block holds -->
                                <div v-show="selectedBlockId === block.id" class="nb-block-body">

                                        <!-- Heading block settings -->
                                        <div v-if="block.type === 'heading'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.heading_text }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.text"
                                                    @input="onHeadingInput(block.id, $event.target.value)" />
                                                <p v-if="!isTemplateMode && subjectSyncActive && isFirstHeading(block.id)" class="nb-hint">{{ t.newsletter_heading_follows_subject }}</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.heading_level }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm"
                                                    :value="block.data.level"
                                                    @change="updateBlockData(block.id, 'level', $event.target.value)">
                                                    <option value="h1">{{ t.heading_header }}</option>
                                                    <option value="h2">{{ t.heading_subheader }}</option>
                                                    <option value="h3">{{ t.heading_section }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.alignment }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm"
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
                                                <textarea :ref="el => setTextBlockRef(block.id, el)" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm" rows="6"
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
                                                            class="border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.cards }}</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" value="list"
                                                            :checked="block.data.layout === 'list'"
                                                            @change="updateBlockData(block.id, 'layout', 'list')"
                                                            class="border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.list }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        :checked="block.data.useAllEvents"
                                                        @change="updateBlockData(block.id, 'useAllEvents', $event.target.checked)"
                                                        class="rounded border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)]" />
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
                                                                class="rounded border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)]" />
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
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.text"
                                                    @input="updateBlockData(block.id, 'text', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_url }}</label>
                                                <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.url"
                                                    @input="updateBlockData(block.id, 'url', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.alignment }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm"
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
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm"
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
                                                <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" min="5" max="200"
                                                    :value="block.data.height"
                                                    @input="updateBlockData(block.id, 'height', parseInt($event.target.value) || 20)" />
                                            </div>
                                        </div>

                                        <!-- Image block settings -->
                                        <div v-if="block.type === 'image'" class="space-y-3">
                                            <!-- Layout selector (only when multiple images) -->
                                            <div v-if="block.data.images && block.data.images.length > 1">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.layout }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm"
                                                    :value="block.data.layout"
                                                    @change="updateBlockData(block.id, 'layout', $event.target.value)">
                                                    <option value="column">{{ t.layout_column }}</option>
                                                    <option value="row">{{ t.layout_row }}</option>
                                                    <option value="grid">{{ t.layout_grid }}</option>
                                                </select>
                                            </div>

                                            <!-- Width (for column layout or single image) -->
                                            <div v-if="!block.data.images || block.data.images.length <= 1 || block.data.layout === 'column'">
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.width }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" placeholder="100%"
                                                    :value="block.data.width"
                                                    @input="updateBlockData(block.id, 'width', $event.target.value)" />
                                            </div>

                                            <!-- Alignment -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.alignment }}</label>
                                                <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm"
                                                    :value="block.data.align"
                                                    @change="updateBlockData(block.id, 'align', $event.target.value)">
                                                    <option value="left">{{ t.left }}</option>
                                                    <option value="center">{{ t.center }}</option>
                                                    <option value="right">{{ t.right }}</option>
                                                </select>
                                            </div>

                                            <!-- Per-image fields -->
                                            <div v-for="(img, imgIdx) in (block.data.images || [])" :key="img._id"
                                                class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 space-y-2">

                                                <!-- Header with image number and remove button -->
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        {{ t.image }}{{ block.data.images.length > 1 ? ' ' + (imgIdx + 1) : '' }}
                                                    </span>
                                                    <button v-if="block.data.images.length > 1" type="button"
                                                        @click="removeImageItem(block.id, img._id)"
                                                        class="p-1 text-red-400 hover:text-red-600 dark:hover:text-red-300">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>

                                                <!-- Upload button -->
                                                <div v-if="canUploadImages">
                                                    <input type="file" :id="'image-upload-' + img._id" class="hidden"
                                                        accept="image/jpeg,image/png,image/gif,image/webp"
                                                        @change="uploadImageItem(block.id, img._id, $event.target.files[0]); $event.target.value = ''" />
                                                    <div class="flex items-center gap-3">
                                                        <button type="button"
                                                            @click="openImagePicker(img._id)"
                                                            :disabled="imageUploading[img._id]"
                                                            class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600 disabled:opacity-50">
                                                            <svg class="w-4 h-4 me-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                            <span v-if="imageUploading[img._id]">{{ t.uploading }}...</span>
                                                            <span v-else>{{ t.choose_file }}</span>
                                                        </button>
                                                    </div>
                                                    <div v-if="img.url" class="mt-2">
                                                        <img :src="img.url" :alt="img.alt" class="max-h-16 rounded border border-gray-200 dark:border-gray-600" />
                                                    </div>
                                                </div>

                                                <!-- URL -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ canUploadImages ? t.or_enter_url : t.image_url }}</label>
                                                    <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                        :value="img.url"
                                                        @input="updateImageItem(block.id, img._id, 'url', $event.target.value)" />
                                                </div>

                                                <!-- Alt -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.image_alt }}</label>
                                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                        :value="img.alt"
                                                        @input="updateImageItem(block.id, img._id, 'alt', $event.target.value)" />
                                                </div>

                                                <!-- Caption -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.image_caption }}</label>
                                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                        :value="img.caption"
                                                        @input="updateImageItem(block.id, img._id, 'caption', $event.target.value)" />
                                                </div>

                                                <!-- Link -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.image_link }}</label>
                                                    <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                        :value="img.link"
                                                        @input="updateImageItem(block.id, img._id, 'link', $event.target.value)" />
                                                </div>
                                            </div>

                                            <!-- Add image button -->
                                            <button v-if="(block.data.images || []).length < 4" type="button"
                                                @click="addImageItem(block.id)"
                                                class="text-sm text-[var(--brand-blue)] hover:text-blue-700">
                                                + {{ t.add_image }}
                                            </button>
                                        </div>

                                        <!-- Social links block settings -->
                                        <div v-if="block.type === 'social_links'" class="space-y-3">
                                            <div v-for="(link, linkIdx) in block.data.links" :key="linkIdx" class="flex gap-2 items-end">
                                                <div class="flex-1">
                                                    <label v-if="linkIdx === 0" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.platform }}</label>
                                                    <select class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm text-sm"
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
                                                    <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm text-sm"
                                                        :value="link.url"
                                                        @input="updateSocialLink(block.id, linkIdx, 'url', $event.target.value)" />
                                                </div>
                                                <button type="button" @click="removeSocialLink(block.id, linkIdx)"
                                                    class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-300 mb-0.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <button type="button" @click="addSocialLink(block.id)"
                                                class="text-sm text-[var(--brand-blue)] hover:text-blue-700">+ {{ t.add_link }}</button>
                                        </div>

                                        <!-- Offer block settings -->
                                        <div v-if="block.type === 'offer'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.offer_title }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.title"
                                                    @input="updateBlockData(block.id, 'title', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.offer_description }}</label>
                                                <textarea class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" rows="3"
                                                    :value="block.data.description"
                                                    @input="updateBlockData(block.id, 'description', $event.target.value)"></textarea>
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.original_price }}</label>
                                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                        :value="block.data.originalPrice"
                                                        @input="updateBlockData(block.id, 'originalPrice', $event.target.value)" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.sale_price }}</label>
                                                    <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                        :value="block.data.salePrice"
                                                        @input="updateBlockData(block.id, 'salePrice', $event.target.value)" />
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.coupon_code_label }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.couponCode"
                                                    @input="updateBlockData(block.id, 'couponCode', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_text }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.buttonText"
                                                    @input="updateBlockData(block.id, 'buttonText', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.button_url }}</label>
                                                <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.buttonUrl"
                                                    @input="updateBlockData(block.id, 'buttonUrl', $event.target.value)" />
                                            </div>
                                        </div>

                                        <!-- Video block settings -->
                                        <div v-if="block.type === 'video'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.youtube_url }}</label>
                                                <input type="url" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.url"
                                                    @input="updateBlockData(block.id, 'url', $event.target.value)"
                                                    placeholder="https://www.youtube.com/watch?v=..." />
                                            </div>
                                        </div>

                                        <!-- Quote block settings -->
                                        <div v-if="block.type === 'quote'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.quote_text }}</label>
                                                <textarea class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" rows="3"
                                                    :value="block.data.text"
                                                    @input="updateBlockData(block.id, 'text', $event.target.value)"></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.quote_author }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.author"
                                                    @input="updateBlockData(block.id, 'author', $event.target.value)" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.quote_author_title }}</label>
                                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm"
                                                    :value="block.data.title"
                                                    @input="updateBlockData(block.id, 'title', $event.target.value)" />
                                            </div>
                                        </div>

                                        <!-- Sponsors block settings -->
                                        <div v-if="block.type === 'sponsors'" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.sponsor_source }}</label>
                                                <div class="flex gap-4 mt-2">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" value="schedule"
                                                            :name="'sponsor_source_' + block.id"
                                                            :checked="block.data.source === 'schedule'"
                                                            @change="updateBlockData(block.id, 'source', 'schedule')"
                                                            class="border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.sponsor_source_schedule }}</span>
                                                    </label>
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="radio" value="first_event"
                                                            :name="'sponsor_source_' + block.id"
                                                            :checked="block.data.source === 'first_event'"
                                                            @change="updateBlockData(block.id, 'source', 'first_event')"
                                                            class="border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.sponsor_source_first_event }}</span>
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ t.sponsor_source_help }}</p>
                                            </div>
                                        </div>

                                        <!-- Poll block: auto-populated -->
                                        <div v-if="block.type === 'poll'">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t.poll_auto_description }}</p>
                                        </div>

                                        <!-- Profile image / header banner: no settings -->
                                        <div v-if="block.type === 'profile_image' || block.type === 'header_banner'">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ t.auto_populated_from_schedule }}</p>
                                        </div>
                                </div>
                            </div>

                            <div v-show="blocks.length === 0" class="nb-empty">{{ t.drag_blocks_here }}</div>
                        </div>
                    </div>
                </div>

                <!-- STYLE -->
                <div v-show="activeSection === 'style'" id="nb-pane-style" role="tabpanel" aria-labelledby="nb-tab-style" class="ap-card nb-card">

                    <!-- The design: one of the five presets. "Design", because the section's
                         Templates tab is something else (saved newsletters to start from). -->
                    <div class="nb-section">
                        <h3 class="nb-title">{{ t.design }}</h3>
                        <div class="nb-designs" role="radiogroup" :aria-label="t.design">
                            <label v-for="tmpl in templateNames" :key="tmpl" class="nb-design" :class="{ 'is-on': template === tmpl }">
                                <input type="radio" name="template_selector" :value="tmpl" :checked="template === tmpl" @change="chooseDesign(tmpl)" class="sr-only" />
                                <!-- Each preset drawn as the design it is (the sheet, the title, an event entry), in the
                                     colours it arrives with. The email's own colours, so they do not follow the app's theme. -->
                                <span class="nb-design-thumb">
                                                <svg v-if="tmpl === 'modern'" viewBox="0 0 96 64" class="block w-full h-full" aria-hidden="true">
                                                    <rect width="96" height="64" fill="#f1f2f4" /><rect x="20" y="6" width="56" height="58" rx="5" fill="#fff" stroke="#e3e5e8" />
                                                    <path d="M20 11a5 5 0 0 1 5-5h46a5 5 0 0 1 5 5v11H20z" :fill="presetAccent(tmpl)" /><rect x="34" y="12.5" width="28" height="3.5" rx="1.75" fill="#fff" />
                                                    <rect x="26" y="27" width="44" height="2" rx="1" fill="#c9ccd1" /><rect x="26" y="31.5" width="34" height="2" rx="1" fill="#c9ccd1" />
                                                    <rect x="26" y="38" width="44" height="12" rx="3" fill="#f7f7f8" stroke="#e3e5e8" /><rect x="28.5" y="40.5" width="9" height="7" rx="1.5" fill="#b9bdc4" />
                                                    <rect x="40" y="41.5" width="12" height="1.6" rx=".8" :fill="presetAccent(tmpl)" /><rect x="40" y="45" width="22" height="2" rx="1" fill="#55595f" />
                                                    <rect x="26" y="53" width="44" height="11" rx="3" fill="#f7f7f8" stroke="#e3e5e8" />
                                                </svg>
                                                <svg v-else-if="tmpl === 'classic'" viewBox="0 0 96 64" class="block w-full h-full" aria-hidden="true">
                                                    <rect width="96" height="64" fill="#efede8" /><rect x="20" y="6" width="56" height="58" fill="#faf9f6" stroke="#dedbd3" />
                                                    <rect x="40" y="11" width="16" height="1.6" fill="#6b6257" /><path d="M26 16.5h44M26 18h44" stroke="#bdb8ae" stroke-width=".7" />
                                                    <rect x="31" y="23" width="34" height="3.4" :fill="presetAccent(tmpl)" /><rect x="44" y="29" width="8" height="1" :fill="presetAccent(tmpl)" />
                                                    <rect x="26" y="34" width="44" height="1.8" fill="#c8c3b9" /><rect x="26" y="38" width="36" height="1.8" fill="#c8c3b9" />
                                                    <rect x="26" y="44" width="44" height="13" fill="#f3f1ec" stroke="#dedbd3" /><rect x="29" y="46.5" width="12" height="1.4" :fill="presetAccent(tmpl)" />
                                                    <rect x="29" y="50" width="22" height="2.2" fill="#4a443c" /><rect x="58" y="46.5" width="9.5" height="8" fill="#b7b0a4" />
                                                </svg>
                                                <svg v-else-if="tmpl === 'minimal'" viewBox="0 0 96 64" class="block w-full h-full" aria-hidden="true">
                                                    <rect width="96" height="64" fill="#fff" /><rect x="45.5" y="6" width="5" height="5" rx="1.2" fill="#9aa0a6" /><rect x="41" y="13" width="14" height="1.6" rx=".8" fill="#3c4043" />
                                                    <path d="M18 19h60" stroke="#e3e5e8" stroke-width=".8" /><rect x="28" y="24" width="40" height="4.4" rx="1" fill="#202124" />
                                                    <rect x="18" y="33" width="60" height="1.8" rx=".9" fill="#c9ccd1" /><rect x="18" y="37" width="48" height="1.8" rx=".9" fill="#c9ccd1" />
                                                    <rect x="18" y="44.5" width="14" height="1.3" rx=".65" fill="#9aa0a6" /><rect x="18" y="48" width="30" height="2.6" rx="1" fill="#202124" />
                                                    <path d="M18 55.5h60" stroke="#e3e5e8" stroke-width=".8" /><rect x="18" y="59" width="14" height="1.3" rx=".65" fill="#9aa0a6" />
                                                </svg>
                                                <svg v-else-if="tmpl === 'bold'" viewBox="0 0 96 64" class="block w-full h-full" aria-hidden="true">
                                                    <rect width="96" height="64" fill="#1a1a2e" /><rect x="20" y="8" width="56" height="17" :fill="presetAccent(tmpl)" /><rect x="29" y="14.4" width="38" height="4.6" rx="1" fill="#fff" />
                                                    <rect x="26" y="30" width="44" height="1.8" rx=".9" fill="#8c8fa3" /><rect x="26" y="34" width="34" height="1.8" rx=".9" fill="#8c8fa3" />
                                                    <rect x="26" y="40" width="44" height="20" rx="4" fill="#2a2a3f" stroke="#3a3a52" /><rect x="29" y="43" width="14" height="14" rx="2.5" fill="#6d7087" />
                                                    <rect x="46" y="44" width="12" height="1.5" rx=".75" :fill="presetAccent(tmpl)" /><rect x="46" y="47.5" width="20" height="2.6" rx="1" fill="#fff" />
                                                    <rect x="46" y="52.5" width="12" height="4.5" rx="1.6" :fill="presetAccent(tmpl)" />
                                                </svg>
                                                <svg v-else viewBox="0 0 96 64" class="block w-full h-full" aria-hidden="true">
                                                    <rect width="96" height="64" fill="#f5f5f5" /><rect x="24" y="6" width="48" height="1.8" :fill="presetAccent(tmpl)" />
                                                    <rect x="24" y="11" width="4" height="4" rx="1" fill="#9aa0a6" /><rect x="30" y="12.1" width="13" height="1.7" rx=".85" fill="#3c4043" />
                                                    <rect x="24" y="19" width="26" height="2.8" rx="1" fill="#202124" /><rect x="24" y="24.5" width="48" height="1.5" rx=".75" fill="#bfc3c8" />
                                                    <g v-for="y in [30, 39, 48, 57]" :key="y">
                                                        <rect x="24" :y="y" width="48" height="7.4" rx="1.6" fill="#fff" stroke="#e1e3e6" stroke-width=".7" /><rect x="26.5" :y="y + 2.2" width="4" height="3" rx=".6" :fill="presetAccent(tmpl)" />
                                                        <rect x="33.5" :y="y + 1.9" width="20" height="1.6" rx=".8" fill="#202124" /><rect x="33.5" :y="y + 4.4" width="13" height="1.2" rx=".6" fill="#9aa0a6" />
                                                    </g>
                                                </svg>
                                </span>
                                <span class="nb-design-name">{{ designName(tmpl) }}</span>
                            </label>
                        </div>
                        <p v-if="designUndo" class="nb-note" role="status">
                            <span>{{ t.newsletter_design_reset.replace(':design', designName(template)) }}</span>
                            <button type="button" class="event-link" @click="undoDesign()">{{ t.undo }}</button>
                        </p>
                    </div>

                    <!-- Colours, type and buttons -->
                    <div class="nb-section">
                        <h3 class="nb-title">{{ t.style_settings }}</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.background_color }}</label>
                                            <div class="flex items-center gap-2 mt-1">
                                                <input type="color" v-model="styleSettings.backgroundColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                                                <input type="text" v-model="styleSettings.backgroundColor" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.accent_color }}</label>
                                            <div class="flex items-center gap-2 mt-1">
                                                <input type="color" v-model="styleSettings.accentColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                                                <input type="text" v-model="styleSettings.accentColor" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" />
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.text_color }}</label>
                                            <div class="flex items-center gap-2 mt-1">
                                                <input type="color" v-model="styleSettings.textColor" class="h-10 w-14 rounded cursor-pointer border border-gray-300 dark:border-gray-600" />
                                                <input type="text" v-model="styleSettings.textColor" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ t.font_family }}</label>
                                            <select v-model="styleSettings.fontFamily" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-md shadow-sm">
                                                <option value="System">{{ t.system }}</option>
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
                                                    <input type="radio" v-model="styleSettings.buttonRadius" value="rounded" class="border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.rounded }}</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" v-model="styleSettings.buttonRadius" value="square" class="border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ t.square }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    </div>

                    <!-- Kept for the next newsletter: the design and the blocks, under a name. -->
                    <div v-if="!isTemplateMode" class="nb-section">
                        <h3 class="nb-title">{{ t.templates }}</h3>
                        <p class="nb-hint is-lead">{{ t.newsletter_save_as_template_lead }}</p>
                        <button type="button" class="page-tool" @click="openSaveAsTemplate()">{{ t.save_as_template }}</button>
                    </div>
                </div>

                <!-- SETTINGS -->
                <div v-if="!isTemplateMode" v-show="activeSection === 'settings'" id="nb-pane-settings" role="tabpanel" aria-labelledby="nb-tab-settings" class="ap-card nb-card">

                    <!-- Recipients: everyone is a choice of its own, so it is always said who that is. -->
                    <div id="nb-recipients" class="nb-section">
                        <h3 class="nb-title">{{ t.recipients }}</h3>
                        <p v-if="abTest && abTest.variant === 'B'" class="nb-hint is-lead">{{ t.ab_follows_variant_a }}</p>
                        <template v-else>
                            <div class="nb-choices">
                                <label class="nb-choice">
                                    <input type="radio" name="nb_audience" value="everyone" :checked="audienceMode === 'everyone'" @change="useEveryone()" />
                                    <span><bdi>{{ everyoneLabel }}</bdi></span>
                                </label>
                                <label v-if="segments.length" class="nb-choice">
                                    <input type="radio" name="nb_audience" value="segments" :checked="audienceMode === 'segments'" @change="audienceMode = 'segments'" />
                                    <span>{{ t.newsletter_audience_chosen }}</span>
                                </label>
                            </div>
                            <div v-if="segments.length" v-show="audienceMode === 'segments'" class="nb-choice-body">
                                <label v-for="segment in segments" :key="segment.id" class="nb-segment" :class="{ 'is-on': selectedSegmentIds.includes(segment.id) }">
                                    <input type="checkbox" :checked="selectedSegmentIds.includes(segment.id)" @change="toggleSegment(segment.id)" />
                                    <span class="nb-segment-name"><bdi>{{ segment.name }}</bdi> <span class="nb-segment-type">{{ segment.type_label }}</span></span>
                                    <span class="nb-segment-count">{{ segment.count }}</span>
                                </label>
                                <p v-if="!selectedSegmentIds.length" class="nb-hint is-warn">{{ t.newsletter_audience_none_ticked }}</p>
                            </div>
                            <p v-if="!segments.length" class="nb-hint">{{ t.no_segments }}</p>
                            <a :href="routes.manage_segments" class="event-link nb-manage">{{ t.manage_segments }}</a>
                        </template>
                        <p v-if="audience" class="nb-audience is-total">
                            <span class="nb-audience-to">{{ t.to }}</span>
                            <bdi class="nb-audience-who">{{ audienceLine }}</bdi>
                        </p>
                    </div>

                    <!-- A/B test: two versions, a sample of each, and the better one to everyone else. -->
                    <div v-if="!isAdmin" class="nb-section">
                        <h3 class="nb-title">{{ t.ab_testing }}</h3>
                        <template v-if="abTest">
                            <dl class="nb-kv">
                                <div><dt>{{ t.variant }}</dt><dd><span class="event-chip">{{ abTest.variant }}</span></dd></div>
                                <div><dt>{{ t.test_field }}</dt><dd>{{ abTest.field }}</dd></div>
                                <div><dt>{{ t.sample_percentage }}</dt><dd>{{ abTest.sample }}%</dd></div>
                                <div><dt>{{ t.wait_hours }}</dt><dd>{{ abTest.hours }}</dd></div>
                                <div><dt>{{ t.winner_criteria }}</dt><dd>{{ abTest.criteria }}</dd></div>
                                <div v-if="abTest.winner"><dt>{{ t.winner }}</dt><dd>{{ abTest.winner }}</dd></div>
                            </dl>
                            <p v-if="abTest.pending" class="nb-hint">{{ t.ab_test_pending_note }}</p>
                            <a v-if="abTest.other_url" :href="abTest.other_url" class="event-link">{{ t.ab_test_edit_other.replace(':variant', abTest.other_variant) }}</a>
                        </template>
                        <template v-else>
                            <p class="nb-hint is-lead">{{ t.ab_test_description }}</p>
                            <button v-if="!abFormOpen" type="button" class="page-tool" @click="abFormOpen = true">{{ t.create_ab_test }}</button>
                            <div v-else class="nb-ab-form" @keydown.enter="onDialogEnter($event)">
                                <div class="nb-field">
                                    <label for="nb-ab-field" class="nb-label">{{ t.test_field }}</label>
                                    <select id="nb-ab-field" v-model="abForm.test_field" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm">
                                        <option value="subject">{{ t.subject }}</option>
                                        <option value="blocks">{{ t.content_above_events }}</option>
                                    </select>
                                </div>
                                <div class="nb-field-row">
                                    <div class="nb-field">
                                        <label for="nb-ab-sample" class="nb-label">{{ t.sample_percentage }}</label>
                                        <input id="nb-ab-sample" type="number" min="5" max="50" v-model.number="abForm.sample_percentage" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" />
                                    </div>
                                    <div class="nb-field">
                                        <label for="nb-ab-hours" class="nb-label">{{ t.wait_hours }}</label>
                                        <input id="nb-ab-hours" type="number" min="1" max="72" v-model.number="abForm.winner_wait_hours" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" />
                                    </div>
                                </div>
                                <div class="nb-field">
                                    <label for="nb-ab-criteria" class="nb-label">{{ t.winner_criteria }}</label>
                                    <select id="nb-ab-criteria" v-model="abForm.winner_criteria" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm">
                                        <option value="open_rate">{{ t.open_rate }}</option>
                                        <option value="click_rate">{{ t.click_rate }}</option>
                                    </select>
                                </div>
                                <p class="nb-hint">{{ t.ab_test_create_note }}</p>
                                <div class="nb-actions">
                                    <button type="button" class="nb-btn" @click="abFormOpen = false">{{ t.cancel }}</button>
                                    <button type="button" :class="brandClasses" :disabled="busy" @click="createAbTest()">{{ t.create_ab_test }}</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="nb-section">
                        <label for="nb-footer-text" class="nb-title nb-label-title">{{ t.footer_text }}</label>
                        <textarea id="nb-footer-text" v-model="styleSettings.footerText" rows="2" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" :placeholder="roleName"></textarea>
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
                <input type="hidden" name="style_settings[previewText]" :value="styleSettings.previewText" />
                <input type="hidden" v-for="segmentId in selectedSegmentIds" :key="'seg_' + segmentId" name="segment_ids[]" :value="segmentId" />
            </div>

            <!-- Right: the mail as it will arrive. On a phone it opens over the editor. -->
            <div class="nb-preview-col" :class="{ 'is-sheet': phonePreviewOpen }">
                <div class="nb-preview-stick">
                    <div class="ap-card nb-preview" role="region" :aria-label="t.preview">
                        <div class="nb-preview-head">
                            <h3 class="nb-title">{{ t.preview }}</h3>
                            <span v-if="previewReady && previewLoading" class="nb-updating" role="status">{{ t.preview_updating }}</span>
                            <!-- Most mail is opened on a phone: the same preview at 390px. The house
                                 segmented control: a sunken group, the chosen side pressed in. -->
                            <div class="nb-seg" role="group" :aria-label="t.preview">
                                <button v-for="width in ['desktop', 'mobile']" :key="width" type="button" @click="previewWidth = width"
                                    :aria-pressed="previewWidth === width ? 'true' : 'false'" class="nb-seg-btn">{{ t[width] }}</button>
                            </div>
                            <button v-if="phonePreviewOpen" type="button" class="nb-tool nb-sheet-close" :aria-label="t.close" :title="t.close" @click="closePhonePreview()">
                                <svg class="nb-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <!--
                            Deliberately a banner and not a reload: the builder holds an unsaved draft,
                            so bouncing to the confirm-password page would throw the work away. Say what
                            happened and let them re-confirm in another tab.
                        -->
                        <div v-if="previewNeedsReauth" class="nb-warn is-flush">
                            <svg class="nb-warn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>{{ t.preview_session_expired }}</span>
                        </div>
                        <!-- The mail is laid out at a real width and scaled to this column
                             (previewFrameStyle): the column is 456px at a 1280 window, under the
                             mail's 620px breakpoint, so "Desktop" used to show the phone layout. -->
                        <div ref="previewPane" class="nb-pane" :class="{ 'is-phone': previewWidth === 'mobile' }" :style="{ height: 'calc(' + paneHeight + ')' }">
                            <div v-show="!previewReady" class="nb-pane-loading">
                                <svg class="animate-spin nb-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <!-- Same origin and no scripts: nothing in the mail can run, and the page can
                                 keep the frame's place across a refresh and point at the open block. -->
                            <iframe ref="livePreviewFrame" class="nb-frame" sandbox="allow-same-origin" :title="t.preview"
                                :style="previewFrameStyle" @load="onPreviewLoad"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- The bar along the foot: what has happened, then Send a test, Save, and Review and send. -->
        <div class="event-save-spacer"></div>
        <div class="event-save-bar nb-bar">
            <div class="event-save-bar-inner">
                <div class="event-save-status" aria-live="polite">
                    <span v-if="removed" class="event-save-pair">
                        <span>{{ t.newsletter_block_removed.replace(':block', blockLabel(removed.block.type)) }}</span>
                        <button type="button" class="event-link" @click="undoRemove()">{{ t.undo }}</button>
                    </span>
                    <span v-else-if="barError" class="event-save-strong nb-bar-error">{{ barError }}</span>
                    <span v-else-if="saving">{{ t.saving }}</span>
                    <span v-else-if="dirty" class="event-save-strong">{{ t.unsaved_changes }}</span>
                    <span v-else-if="justSaved">{{ t.saved }}</span>
                </div>
                <div class="event-save-actions">
                    <button v-if="canSend" type="button" class="event-bar-text" @click="openTest()">{{ t.send_a_test }}</button>
                    <button type="button" :class="canSend ? 'event-bar-quiet' : ['event-bar-save', brandClasses]" :disabled="saving" @click="save()">{{ t.save }}</button>
                    <button v-if="canSend" type="button" :class="['event-bar-save', brandClasses]" :disabled="saving || busy" @click="openReview()">{{ abPending ? t.send_ab_test : t.review_and_send }}</button>
                </div>
            </div>
        </div>

        <!-- Review and send: what goes, to whom, and when, before anything goes. -->
        <div v-if="showReview" class="nb-dialog-backdrop" @click.self="closeDialogs()">
            <div class="nb-dialog" role="dialog" aria-modal="true" aria-labelledby="nb-review-title" @keydown.enter="onDialogEnter($event)">
                <h2 id="nb-review-title" class="nb-dialog-title">{{ abPending ? t.send_ab_test : t.review_and_send }}</h2>

                <div v-if="reviewFailed" class="nb-warn" role="alert">
                    <svg class="nb-warn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <p>{{ previewNeedsReauth ? t.preview_session_expired : t.something_went_wrong }}</p>
                </div>
                <div v-else-if="!audience" class="nb-dialog-loading">
                    <svg class="animate-spin nb-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                </div>
                <template v-else>
                    <dl class="nb-kv">
                        <div><dt>{{ t.subject }}</dt><dd><bdi>{{ subject }}</bdi></dd></div>
                        <div v-if="styleSettings.previewText"><dt>{{ t.preview_text }}</dt><dd><bdi>{{ styleSettings.previewText }}</bdi></dd></div>
                        <div><dt>{{ t.to }}</dt><dd><bdi>{{ audience.label }}</bdi> <span class="nb-num">({{ fmt(audience.count) }})</span></dd></div>
                        <div v-if="audience.allowance"><dt>{{ t.newsletter_allowance }}</dt><dd>{{ allowanceText }}</dd></div>
                    </dl>

                    <div v-if="audience.refusal" class="nb-warn" role="alert">
                        <svg class="nb-warn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div>
                            <p>{{ audience.refusal.message }}</p>
                            <p v-if="audience.refusal.links.length" class="nb-warn-links">
                                <a v-for="link in audience.refusal.links" :key="link.url" :href="link.url" class="event-link">{{ link.label }}</a>
                            </p>
                        </div>
                    </div>
                    <p v-else-if="audience.ab" class="nb-lead">{{ abExplain }}</p>
                    <template v-else>
                        <div class="nb-choices" role="radiogroup" :aria-label="t.review_and_send">
                            <label class="nb-choice"><input type="radio" name="nb_when" value="now" v-model="reviewWhen" /> <span>{{ t.send_now }}</span></label>
                            <label class="nb-choice"><input type="radio" name="nb_when" value="later" v-model="reviewWhen" /> <span>{{ t.schedule_for_later }}</span></label>
                        </div>
                        <div v-show="reviewWhen === 'later'" class="nb-choice-body">
                            <label for="nb-scheduled-at" class="sr-only">{{ t.send_at }}</label>
                            <input id="nb-scheduled-at" ref="scheduleInput" type="text" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" :placeholder="t.send_at" @change="scheduleValue = $event.target.value" />
                            <p class="nb-hint">{{ t.newsletter_times_in.replace(':timezone', audience.timezone) }}</p>
                        </div>
                        <div v-if="reviewWhen === 'now' && audience.now_refusal" class="nb-warn" role="alert">
                            <svg class="nb-warn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <p>{{ audience.now_refusal }}</p>
                        </div>
                    </template>

                    <p class="nb-test-first"><button type="button" class="event-link" @click="fromReviewToTest()">{{ t.newsletter_send_test_first }}</button></p>
                </template>

                <div class="nb-actions">
                    <button type="button" class="nb-btn" @click="closeDialogs()">{{ t.cancel }}</button>
                    <button type="button" :class="brandClasses" :disabled="!reviewCanGo || busy" @click="confirmReview()">{{ reviewButtonLabel }}</button>
                </div>
            </div>
        </div>

        <!-- Send a test -->
        <div v-if="showTest" class="nb-dialog-backdrop" @click.self="closeDialogs()">
            <div class="nb-dialog" role="dialog" aria-modal="true" aria-labelledby="nb-test-title" @keydown.enter="onDialogEnter($event)">
                <h2 id="nb-test-title" class="nb-dialog-title">{{ t.send_a_test }}</h2>
                <p class="nb-lead">{{ t.newsletter_test_lead }}</p>
                <div v-if="testRecipients.length > 1" class="nb-choices" role="radiogroup" :aria-label="t.send_a_test">
                    <label v-for="choice in testRecipients" :key="choice.value" class="nb-choice">
                        <input type="radio" name="nb_test_to" :value="choice.value" v-model="testTo" />
                        <span>{{ choice.label }} <bdi class="nb-quiet">{{ choice.email }}</bdi></span>
                    </label>
                </div>
                <p v-else-if="testRecipients.length" class="nb-audience"><span class="nb-audience-to">{{ t.to }}</span> <bdi>{{ testRecipients[0].email }}</bdi></p>
                <div v-if="testResult" :class="testResult.ok ? 'nb-ok' : 'nb-warn'" role="status"><p>{{ testResult.message }}</p></div>
                <div class="nb-actions">
                    <button type="button" class="nb-btn" @click="closeDialogs()">{{ testResult && testResult.ok ? t.close : t.cancel }}</button>
                    <button type="button" :class="brandClasses" :disabled="busy || !testRecipients.length" @click="sendTest()">{{ busy ? t.sending : t.send }}</button>
                </div>
            </div>
        </div>

        <!-- Save as template -->
        <div v-if="showSaveAsTemplate" class="nb-dialog-backdrop" @click.self="closeDialogs()">
            <div class="nb-dialog" role="dialog" aria-modal="true" aria-labelledby="nb-template-title" @keydown.enter="onDialogEnter($event, saveAsTemplate)">
                <h2 id="nb-template-title" class="nb-dialog-title">{{ t.save_as_template }}</h2>
                <label for="nb-template-name" class="nb-label">{{ t.template_name }}</label>
                <input id="nb-template-name" ref="templateNameInput" type="text" maxlength="255" class="nb-input block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm" v-model="templateName" :placeholder="t.template_name_placeholder" />
                <div class="nb-actions">
                    <button type="button" class="nb-btn" @click="closeDialogs()">{{ t.cancel }}</button>
                    <button type="button" :class="brandClasses" :disabled="busy || !templateName.trim()" @click="saveAsTemplate()">{{ t.save }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import EasyMDE from 'easymde';
import 'easymde/dist/easymde.min.css';
import {
    applyEditorDirection,
    attachDirectionShortcut,
    attachEditorObserver,
    attachPasteSanitizer,
    resolveEditorDirection,
    toggleEditorDirection,
    unlockEditorDirection,
} from '../editor-helpers';

const props = defineProps({
    initialBlocks: { type: Array, default: () => [] },
    initialTemplate: { type: String, default: 'modern' },
    initialSubject: { type: String, default: '' },
    initialStyleSettings: { type: Object, default: () => ({}) },
    initialSegmentIds: { type: Array, default: () => [] },
    templateDefaults: { type: Object, default: () => ({}) },
    events: { type: Array, default: () => [] },
    segments: { type: Array, default: () => [] },
    csrfToken: { type: String, default: '' },
    translations: { type: Object, default: () => ({}) },
    // { exists, canSend, hash } of the newsletter being edited; null on the create page.
    newsletter: { type: Object, default: null },
    // NewsletterBuilderRoutes::for(): the addresses this builder works with.
    routes: { type: Object, default: () => ({}) },
    // The addresses a test may go to: [{ value, label, email }].
    testRecipients: { type: Array, default: () => [] },
    // Who "everyone" is, by name: the schedule's followers, or the platform's users.
    everyoneLabel: { type: String, default: '' },
    // This newsletter's A/B test, when it is one of the two variants.
    abTest: { type: Object, default: null },
    // A scheduled newsletter's time, in the clock it was typed in ('Y-m-d H:i').
    scheduledAt: { type: String, default: '' },
    use24Hour: { type: Boolean, default: false },
    roleSocialLinks: { type: Array, default: () => [] },
    roleName: { type: String, default: '' },
    availableBlockTypes: { type: Array, default: null },
    isTemplateMode: { type: Boolean, default: false },
    isAdmin: { type: Boolean, default: false },
    canUploadImages: { type: Boolean, default: false },
});

const t = props.translations;

// The house brand button (components/brand-button), for the buttons a Vue template draws.
const brandClasses = 'inline-flex items-center justify-center px-4 py-3 text-base bg-gradient-to-b from-[var(--brand-button-bg-light)] to-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-white shadow-sm transition-all duration-200 hover:from-[var(--brand-button-bg)] hover:to-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:cursor-not-allowed disabled:opacity-50';

function newImageItem(url, alt, caption, link) {
    return { _id: generateId(), url: url || '', alt: alt || '', caption: caption || '', link: link || '' };
}

function normalizeImageBlock(block) {
    if (block.type === 'image') {
        if (!block.data.images) {
            block.data.images = [newImageItem(block.data.url, block.data.alt)];
            delete block.data.url;
            delete block.data.alt;
        } else {
            block.data.images.forEach(img => { if (!img._id) img._id = generateId(); });
        }
        if (!block.data.layout) block.data.layout = 'column';
    }
    return block;
}

const blocks = ref(JSON.parse(JSON.stringify(props.initialBlocks)).map(b => normalizeImageBlock(b)));
const selectedBlockId = ref(null);
const template = ref(props.initialTemplate);
const subject = ref(props.initialSubject);
const styleSettings = reactive({ ...props.initialStyleSettings });
const selectedSegmentIds = ref([...props.initialSegmentIds]);
const showBlockPalette = ref(false);
const imageUploading = ref({});

// The newsletter as the server knows it, and the addresses that go with it. Both change when a
// new newsletter is saved for the first time, without leaving the page.
const newsletterState = reactive({
    exists: !!(props.newsletter && props.newsletter.exists),
    canSend: props.newsletter ? props.newsletter.canSend !== false : true,
    hash: (props.newsletter && props.newsletter.hash) || '',
});
const routes = reactive({ ...props.routes });

const canSend = computed(() => !props.isTemplateMode && newsletterState.canSend);
const abPending = computed(() => !!(props.abTest && props.abTest.pending));

// ── Saving ────────────────────────────────────────────────────────────

const saving = ref(false);
const justSaved = ref(false);
const barError = ref('');
const busy = ref(false);
let leaving = false;
let justSavedTimer = null;

// What was last saved, to tell whether anything has changed since.
function snapshot() {
    return JSON.stringify([subject.value, blocks.value, template.value, styleSettings, selectedSegmentIds.value]);
}
const savedSnapshot = ref('');
const dirty = computed(() => savedSnapshot.value !== '' && snapshot() !== savedSnapshot.value);

function formEl() {
    return document.querySelector('#newsletter-builder')?.closest('form') || null;
}

function firstError(data) {
    if (data && data.errors) {
        const first = Object.values(data.errors)[0];
        if (Array.isArray(first) && first.length) return first[0];
    }
    return (data && data.message) || t.something_went_wrong;
}

/**
 * Saves without leaving the page: the tab, the open block and the preview stay where they are.
 * A new newsletter becomes a saved one, and the builder carries on with its addresses.
 */
async function save() {
    const form = formEl();
    if (!form) return false;

    // A template is kept by its own form, which goes back to the list of templates. Asked
    // through requestSubmit(), so the browser checks the template's name first.
    if (props.isTemplateMode) {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            leaving = true;
            form.submit();
        }
        return true;
    }

    saving.value = true;
    barError.value = '';
    clearTimeout(justSavedTimer);
    justSaved.value = false;

    const formData = new FormData(form);
    if (newsletterState.exists) {
        formData.set('_method', 'PUT');
    } else {
        formData.delete('_method');
    }
    const sentSnapshot = snapshot();

    try {
        const response = await fetch(newsletterState.exists ? routes.update : routes.store, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-Save-Before-Action': '1',
            },
        });
        if (response.status === 423) {
            previewNeedsReauth.value = true;
            barError.value = t.preview_session_expired;
            return false;
        }
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.saved !== true) {
            barError.value = firstError(data);
            if (data.errors && data.errors.subject) showSection('content');
            return false;
        }

        if (data.routes) {
            Object.assign(routes, data.routes);
            newsletterState.exists = true;
            newsletterState.canSend = true;
            newsletterState.hash = data.hash || '';
            if (routes.update) form.action = routes.update;
            if (routes.edit) history.replaceState(null, '', routes.edit + window.location.hash);
        }

        savedSnapshot.value = sentSnapshot;
        justSaved.value = true;
        justSavedTimer = setTimeout(() => { justSaved.value = false; }, 4000);
        return true;
    } catch {
        barError.value = t.something_went_wrong;
        return false;
    } finally {
        saving.value = false;
    }
}

async function ensureSaved() {
    if (newsletterState.exists && !dirty.value) return true;
    return save();
}

function onFormSubmit(e) {
    if (props.isTemplateMode) {
        leaving = true;
        return;
    }
    // Enter in a field, or anything else that submits the form: save in place.
    e.preventDefault();
    save();
}

function onBeforeUnload(e) {
    if (!leaving && dirty.value) {
        e.preventDefault();
        e.returnValue = '';
    }
}

function submitFormTo(action, extraFields = {}) {
    leaving = true;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;
    const fields = { _token: props.csrfToken, ...extraFields };
    for (const [name, value] of Object.entries(fields)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
}

// ── Who it goes to ────────────────────────────────────────────────────

const audience = ref(null);
const audienceMode = ref(selectedSegmentIds.value.length ? 'segments' : 'everyone');
let audienceSeq = 0;
let audienceTimer = null;

async function loadAudience() {
    if (props.isTemplateMode || !routes.audience) return null;

    const params = new URLSearchParams();
    selectedSegmentIds.value.forEach(id => params.append('segment_ids[]', id));
    if (newsletterState.hash) params.set('newsletter', newsletterState.hash);
    const url = routes.audience + (routes.audience.includes('?') ? '&' : '?') + params.toString();
    const seq = ++audienceSeq;

    try {
        const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        if (response.status === 423) {
            previewNeedsReauth.value = true;
            return null;
        }
        if (!response.ok) return null;
        const data = await response.json();
        if (seq === audienceSeq) audience.value = data;
        return data;
    } catch {
        return null;
    }
}

function fmt(n) {
    try {
        return new Intl.NumberFormat(document.documentElement.lang || undefined).format(n);
    } catch {
        return String(n);
    }
}

const audienceLine = computed(() => {
    if (audience.value) {
        return audience.value.label + ' (' + fmt(audience.value.count) + ')';
    }
    if (selectedSegmentIds.value.length) {
        return props.segments.filter(s => selectedSegmentIds.value.includes(s.id)).map(s => s.name).join(', ');
    }
    return props.everyoneLabel;
});

const allowanceText = computed(() => {
    const allowance = audience.value && audience.value.allowance;
    if (!allowance) return '';
    return t.newsletter_allowance_after
        .replace(':left', fmt(allowance.after))
        .replace(':limit', fmt(allowance.limit));
});

function useEveryone() {
    audienceMode.value = 'everyone';
    selectedSegmentIds.value = [];
}

function toggleSegment(segmentId) {
    const idx = selectedSegmentIds.value.indexOf(segmentId);
    if (idx > -1) {
        selectedSegmentIds.value.splice(idx, 1);
    } else {
        selectedSegmentIds.value.push(segmentId);
    }
}

function goToRecipients() {
    showSection('settings');
    nextTick(() => document.getElementById('nb-recipients')?.scrollIntoView({ block: 'start', behavior: 'smooth' }));
}

watch(selectedSegmentIds, () => {
    clearTimeout(audienceTimer);
    audienceTimer = setTimeout(loadAudience, 400);
}, { deep: true });

// ── Review and send ───────────────────────────────────────────────────

const showReview = ref(false);
const showTest = ref(false);
const showSaveAsTemplate = ref(false);
const reviewWhen = ref(props.scheduledAt ? 'later' : 'now');
const scheduleInput = ref(null);
const scheduleValue = ref(props.scheduledAt || '');
let scheduleFlatpickr = null;

const reviewFailed = ref(false);

async function openReview() {
    if (busy.value) return;
    busy.value = true;
    try {
        if (!(await ensureSaved())) return;
        audience.value = null;
        reviewFailed.value = false;
        showReview.value = true;
        reviewFailed.value = !(await loadAudience());
    } finally {
        busy.value = false;
    }
}

watch([showReview, reviewWhen, audience], () => {
    if (!showReview.value || reviewWhen.value !== 'later') return;
    nextTick(() => {
        const el = scheduleInput.value;
        if (!el || el._flatpickr || typeof window.flatpickr === 'undefined') return;
        const fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
        scheduleFlatpickr = window.flatpickr(el, Object.assign({
            enableTime: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: props.use24Hour ? 'M j, Y H:i' : 'M j, Y h:i K',
            time_24hr: props.use24Hour,
            allowInput: true,
            minDate: 'today',
            defaultDate: scheduleValue.value || null,
            onChange: (dates, str) => { scheduleValue.value = str; },
        }, fpLocale ? { locale: fpLocale } : {}));
    });
});

const abExplain = computed(() => {
    const ab = audience.value && audience.value.ab;
    if (!ab) return '';
    return t.ab_test_review_explain
        .replace(':a', fmt(ab.sample_a))
        .replace(':b', fmt(ab.sample_b))
        .replace(':hours', fmt(ab.hours))
        .replace(':criteria', ab.criteria)
        .replace(':rest', fmt(ab.rest));
});

const reviewButtonLabel = computed(() => {
    if (abPending.value) return t.send_ab_test;
    return reviewWhen.value === 'later' ? t.schedule : t.send_now;
});

const reviewCanGo = computed(() => {
    const a = audience.value;
    if (!a || a.refusal) return false;
    if (a.ab) return true;
    if (reviewWhen.value === 'later') return !!scheduleValue.value;
    return !a.now_refusal;
});

function confirmReview() {
    if (!reviewCanGo.value || busy.value) return;
    busy.value = true;
    if (audience.value.ab) {
        submitFormTo(routes.ab_send);
    } else if (reviewWhen.value === 'later') {
        submitFormTo(routes.schedule, { scheduled_at: scheduleValue.value });
    } else {
        submitFormTo(routes.send);
    }
}

// ── Send a test ───────────────────────────────────────────────────────

const testTo = ref(props.testRecipients.length ? props.testRecipients[0].value : 'me');
const testResult = ref(null);

function openTest() {
    testResult.value = null;
    showTest.value = true;
}

function fromReviewToTest() {
    showReview.value = false;
    openTest();
}

async function sendTest() {
    if (busy.value) return;
    busy.value = true;
    testResult.value = null;
    try {
        if (!(await ensureSaved())) {
            testResult.value = { ok: false, message: barError.value || t.something_went_wrong };
            return;
        }
        const body = new FormData();
        body.append('_token', props.csrfToken);
        body.append('to', testTo.value);
        const response = await fetch(routes.test_send, {
            method: 'POST',
            body,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const data = await response.json().catch(() => ({}));
        testResult.value = { ok: response.ok, message: data.message || t.something_went_wrong };
    } catch {
        testResult.value = { ok: false, message: t.something_went_wrong };
    } finally {
        busy.value = false;
    }
}

// ── Save as template ──────────────────────────────────────────────────

const templateName = ref('');
const templateNameInput = ref(null);

function openSaveAsTemplate() {
    templateName.value = '';
    showSaveAsTemplate.value = true;
    nextTick(() => templateNameInput.value?.focus());
}

async function saveAsTemplate() {
    if (busy.value || !templateName.value.trim()) return;
    busy.value = true;
    // The server keeps what is saved, so the newsletter is saved first.
    if (!(await ensureSaved()) || !routes.save_as_template) {
        busy.value = false;
        return;
    }
    submitFormTo(routes.save_as_template, { template_name: templateName.value.trim() });
}

// ── A/B test ──────────────────────────────────────────────────────────

const abFormOpen = ref(false);
const abForm = reactive({ test_field: 'subject', sample_percentage: 20, winner_wait_hours: 4, winner_criteria: 'open_rate' });

async function createAbTest() {
    if (busy.value) return;
    busy.value = true;
    if (!(await ensureSaved()) || !routes.ab_test) {
        busy.value = false;
        return;
    }
    submitFormTo(routes.ab_test, { ...abForm });
}

// Enter in a field of a dialog must not submit the page's form (which saves); on a button it
// still presses the button. A dialog with one field can name what Enter there does.
function onDialogEnter(e, action = null) {
    if (e.target && e.target.tagName === 'INPUT') {
        e.preventDefault();
        if (action) action();
    }
}

function closeDialogs() {
    if (busy.value && showReview.value) return;
    if (scheduleFlatpickr) {
        scheduleFlatpickr.destroy();
        scheduleFlatpickr = null;
    }
    showReview.value = false;
    showTest.value = false;
    showSaveAsTemplate.value = false;
}

function onEscape(e) {
    if (e.key !== 'Escape') return;
    if (showReview.value || showTest.value || showSaveAsTemplate.value) {
        closeDialogs();
    } else if (phonePreviewOpen.value) {
        closePhonePreview();
    }
}

// ── Tabs ──────────────────────────────────────────────────────────────

const activeSection = ref('content');

const tabs = computed(() => [
    { id: 'content', label: t.content },
    { id: 'style', label: t.style },
    ...(props.isTemplateMode ? [] : [{ id: 'settings', label: t.settings }]),
]);

function showSection(id) {
    activeSection.value = id;
    history.replaceState(null, '', '#' + id);
}

function onHashChange() {
    const hash = window.location.hash.replace('#', '');
    if (['content', 'style', 'settings'].includes(hash)) {
        activeSection.value = hash;
    }
}

// ── Design ────────────────────────────────────────────────────────────

const templateNames = ['modern', 'classic', 'minimal', 'bold', 'compact'];
const LOOK_KEYS = ['backgroundColor', 'accentColor', 'textColor', 'fontFamily', 'buttonRadius'];
const designUndo = ref(null);
let designUndoTimer = null;

function designName(tmpl) {
    return t['design_' + tmpl] || tmpl;
}

// The accent a preset arrives with, for its thumbnail in the picker.
function presetAccent(tmpl) {
    return (props.templateDefaults[tmpl] || {}).accentColor || '#4E81FA';
}

/**
 * A preset changes the look. What the owner wrote, and the layout chosen on the Events block,
 * stay. When the look being replaced was the owner's own (not the last preset's as it came), the
 * change can be taken back.
 */
function chooseDesign(tmpl) {
    const before = { template: template.value, settings: { ...styleSettings } };
    template.value = tmpl;

    if (props.templateDefaults[tmpl]) {
        const preservedLayout = styleSettings.eventLayout;
        const preservedFooterText = styleSettings.footerText;
        const preservedPreviewText = styleSettings.previewText;
        Object.assign(styleSettings, props.templateDefaults[tmpl]);
        styleSettings.eventLayout = preservedLayout;
        styleSettings.footerText = preservedFooterText;
        styleSettings.previewText = preservedPreviewText;
    }

    const previous = props.templateDefaults[before.template] || {};
    const hadOwnLook = LOOK_KEYS.some(k => previous[k] !== undefined && previous[k] !== before.settings[k]);
    const changed = LOOK_KEYS.some(k => before.settings[k] !== styleSettings[k]);

    clearTimeout(designUndoTimer);
    designUndo.value = hadOwnLook && changed ? before : null;
    if (designUndo.value) {
        designUndoTimer = setTimeout(() => { designUndo.value = null; }, 12000);
    }
}

function undoDesign() {
    if (!designUndo.value) return;
    template.value = designUndo.value.template;
    Object.assign(styleSettings, designUndo.value.settings);
    designUndo.value = null;
}

// ── Blocks ────────────────────────────────────────────────────────────

// Outline icons, as everywhere else in the portal.
const BLOCK_ICONS = {
    heading: 'M5 5v14M13 5v14M5 12h8M17 10.5l2.5-1.5V19',
    text: 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12',
    events: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
    button: 'M3 9.75A2.25 2.25 0 015.25 7.5h13.5A2.25 2.25 0 0121 9.75v4.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 14.25v-4.5zM8.25 12h7.5',
    image: 'M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z',
    divider: 'M3.75 12h16.5',
    spacer: 'M12 4.5v15m0-15l-3 3m3-3l3 3m-3 12l-3-3m3 3l3-3',
    social_links: 'M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244',
    profile_image: 'M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z',
    header_banner: 'M3.75 4.5h16.5v8.25H3.75zM3.75 16.5h16.5M3.75 19.5h10.5',
    offer: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z',
    video: 'M21 12a9 9 0 11-18 0 9 9 0 0118 0zM15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z',
    quote: 'M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z',
    sponsors: 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
    poll: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
};

const allBlockTypes = [
    { type: 'heading', label: t.block_heading, defaultData: { text: '', level: 'h1', align: 'center' } },
    { type: 'text', label: t.block_text, defaultData: { content: '' } },
    { type: 'events', label: t.block_events, defaultData: { layout: 'cards', useAllEvents: true, eventIds: [] } },
    { type: 'button', label: t.block_button, defaultData: { text: '', url: '', align: 'center' } },
    { type: 'image', label: t.block_image, defaultData: { images: [{ url: '', alt: '', caption: '', link: '' }], layout: 'column', width: '100%', align: 'center' } },
    { type: 'divider', label: t.block_divider, defaultData: { style: 'solid' } },
    { type: 'spacer', label: t.block_spacer, defaultData: { height: 20 } },
    { type: 'social_links', label: t.block_social_links, defaultData: { links: props.roleSocialLinks.length ? JSON.parse(JSON.stringify(props.roleSocialLinks)) : [{ platform: 'website', url: '' }] } },
    { type: 'profile_image', label: t.block_profile_image, defaultData: {} },
    { type: 'header_banner', label: t.block_header_banner, defaultData: {} },
    { type: 'offer', label: t.block_offer, defaultData: { title: '', description: '', originalPrice: '', salePrice: '', couponCode: '', buttonText: '', buttonUrl: '', align: 'center' } },
    { type: 'video', label: t.block_video, defaultData: { url: '' } },
    { type: 'quote', label: t.block_quote, defaultData: { text: '', author: '', title: '' } },
    { type: 'sponsors', label: t.block_sponsors, defaultData: { source: 'schedule' } },
    { type: 'poll', label: t.block_poll, defaultData: {} },
];

const blockTypes = props.availableBlockTypes
    ? allBlockTypes.filter(bt => props.availableBlockTypes.includes(bt.type))
    : allBlockTypes;

function blockLabel(type) {
    const bt = allBlockTypes.find(b => b.type === type);
    return bt ? bt.label : type;
}

function blockIconPath(type) {
    return BLOCK_ICONS[type] || BLOCK_ICONS.text;
}

// What a closed block holds, in a line.
function blockSnippet(block) {
    const d = block.data || {};
    switch (block.type) {
        case 'heading':
        case 'button':
            return d.text || '';
        case 'text':
            return (d.content || '').replace(/[#*_>\x60~]|\[|\]\([^)]*\)/g, '').replace(/\s+/g, ' ').trim().substring(0, 80);
        case 'quote':
            return (d.text || '').substring(0, 80);
        case 'events':
            return d.useAllEvents ? t.all_upcoming_events : ((d.eventIds || []).length + ' ' + t.events);
        case 'image': {
            const images = d.images || [];
            const filled = images.filter(img => img.url);
            if (filled.length > 1) return filled.length + ' ' + t.images;
            const first = images[0];
            return first ? (first.caption || first.alt || (first.url ? first.url.substring(0, 50) : '')) : '';
        }
        case 'social_links':
            return (d.links || []).length + ' ' + t.links;
        case 'spacer':
            return (d.height || 20) + 'px';
        case 'offer':
            return d.title ? d.title + (d.salePrice ? ' - ' + d.salePrice : '') : '';
        case 'video':
            return d.url ? d.url.substring(0, 50) : '';
        case 'sponsors':
            return d.source === 'first_event' ? t.sponsor_source_first_event : t.sponsor_source_schedule;
        default:
            return '';
    }
}

// A block that says nothing until it is filled in.
function blockWantsContent(block) {
    return ['heading', 'text', 'button', 'image', 'offer', 'video', 'quote'].includes(block.type);
}

const paletteWhere = computed(() => {
    const open = blocks.value.find(b => b.id === selectedBlockId.value);
    return open ? t.newsletter_add_after.replace(':block', blockLabel(open.type)) : t.newsletter_add_at_end;
});

const blockPalette = ref(null);
const blockCanvas = ref(null);

const textBlockRefs = {};
const easyMDEInstances = {};

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

    const et = window.editorTranslations || {};
    const initialDir = resolveEditorDirection(el, block.data.content || '');
    const instance = new EasyMDE({
        element: el,
        toolbar: [
            { name: "bold", action: EasyMDE.toggleBold, className: "editor-button-text", title: et.bold || "Bold", text: "B" },
            { name: "italic", action: EasyMDE.toggleItalic, className: "editor-button-text", title: et.italic || "Italic", text: "I" },
            { name: "heading", action: EasyMDE.toggleHeadingSmaller, className: "editor-button-text", title: et.heading || "Heading", text: "H" },
            "|",
            { name: "link", action: function(editor) { EasyMDE.drawLink(editor); }, className: "editor-button-text", title: et.link || "Link", text: "🔗" },
            { name: "quote", action: EasyMDE.toggleBlockquote, className: "editor-button-text", title: et.quote || "Quote", text: "\"" },
            { name: "unordered-list", action: EasyMDE.toggleUnorderedList, className: "editor-button-text", title: et.unorderedList || "Unordered List", text: "UL" },
            { name: "ordered-list", action: EasyMDE.toggleOrderedList, className: "editor-button-text", title: et.orderedList || "Ordered List", text: "OL" },
            { name: "direction", action: (editor) => toggleEditorDirection(editor, el), className: "editor-button-text no-disable", title: (et.textDirection || "Text Direction") + " (Ctrl+Shift)", text: initialDir.toUpperCase() },
            "|",
            { name: "preview", action: EasyMDE.togglePreview, className: "editor-button-text no-disable", title: et.preview || "Toggle Preview", text: "👁" },
            { name: "guide", action: "https://www.markdownguide.org/basic-syntax/", className: "editor-button-text", title: et.guide || "Markdown Guide", text: "?" },
        ],
        initialValue: block.data.content || '',
        minHeight: "200px",
        spellChecker: true,
        nativeSpellcheck: true,
        status: false,
        direction: initialDir,
    });

    instance.codemirror.on('change', (cm, changeObj) => {
        unlockEditorDirection(el, changeObj && changeObj.origin);
        applyEditorDirection(instance, el);
        updateBlockData(blockId, 'content', instance.value());
    });

    easyMDEInstances[blockId] = instance;

    applyEditorDirection(instance, el);
    attachEditorObserver(instance);
    attachDirectionShortcut(instance, el);
    attachPasteSanitizer(instance);
}

function destroyEasyMDE(blockId) {
    if (easyMDEInstances[blockId]) {
        easyMDEInstances[blockId]._stopEditorObserver?.();
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

function newBlockOf(bt) {
    return normalizeImageBlock({
        id: generateId(),
        type: bt.type,
        data: JSON.parse(JSON.stringify(bt.defaultData)),
    });
}

// After the open block, or at the end: where the owner is working is where it goes.
function addBlockFromPalette(bt) {
    const newBlock = newBlockOf(bt);
    const openIndex = blocks.value.findIndex(b => b.id === selectedBlockId.value);
    blocks.value.splice(openIndex > -1 ? openIndex + 1 : blocks.value.length, 0, newBlock);
    showBlockPalette.value = false;
    openBlock(newBlock.id);
    nextTick(() => document.querySelector(`[data-block-id="${newBlock.id}"]`)?.scrollIntoView({ block: 'nearest', behavior: 'smooth' }));
}

function toggleBlockPalette() {
    showBlockPalette.value = !showBlockPalette.value;
}

// Opens a block, or closes it when it is the open one (the block's own line).
function selectBlock(blockId) {
    if (selectedBlockId.value === blockId) {
        destroyEasyMDE(blockId);
        selectedBlockId.value = null;
        return;
    }
    openBlock(blockId);
}

function openBlock(blockId, { focus = true } = {}) {
    const prevId = selectedBlockId.value;
    if (prevId === blockId) return;
    if (prevId) destroyEasyMDE(prevId);
    selectedBlockId.value = blockId;

    const block = blocks.value.find(b => b.id === blockId);
    if (!block) return;
    nextTick(() => {
        if (block.type === 'text') {
            initEasyMDE(blockId);
            if (focus) easyMDEInstances[blockId]?.codemirror.focus();
        } else if (focus) {
            const blockEl = document.querySelector(`[data-block-id="${blockId}"]`);
            blockEl?.querySelector('.nb-block-body input[type=text], .nb-block-body input[type=url], .nb-block-body input[type=number]')?.focus();
        }
    });
}

function moveBlock(blockId, delta) {
    const from = blocks.value.findIndex(b => b.id === blockId);
    const to = from + delta;
    if (from < 0 || to < 0 || to >= blocks.value.length) return;
    const [moved] = blocks.value.splice(from, 1);
    blocks.value.splice(to, 0, moved);
    nextTick(() => easyMDEInstances[blockId]?.codemirror.refresh());
}

// Track whether the first heading is auto-synced from the subject field
const subjectSyncActive = ref((() => {
    const firstHeading = props.initialBlocks.find(b => b.type === 'heading');
    return !firstHeading || !firstHeading.data.text || firstHeading.data.text === props.initialSubject;
})());

function isFirstHeading(blockId) {
    const firstHeading = blocks.value.find(b => b.type === 'heading');
    return !!firstHeading && firstHeading.id === blockId;
}

function onSubjectInput() {
    const firstHeading = blocks.value.find(b => b.type === 'heading');
    if (firstHeading && subjectSyncActive.value) {
        firstHeading.data.text = subject.value;
    }
}

function onHeadingInput(blockId, value) {
    if (isFirstHeading(blockId)) {
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

function updateImageItem(blockId, imgId, key, value) {
    const block = blocks.value.find(b => b.id === blockId);
    if (!block || !block.data.images) return;
    const img = block.data.images.find(i => i._id === imgId);
    if (img) img[key] = value;
}

function addImageItem(blockId) {
    const block = blocks.value.find(b => b.id === blockId);
    if (block && block.data.images && block.data.images.length < 4) {
        block.data.images.push(newImageItem());
    }
}

function removeImageItem(blockId, imgId) {
    const block = blocks.value.find(b => b.id === blockId);
    if (block && block.data.images && block.data.images.length > 1) {
        const idx = block.data.images.findIndex(i => i._id === imgId);
        if (idx > -1) block.data.images.splice(idx, 1);
    }
}

/**
 * Open the hidden file input for this image.
 *
 * `document` is not a setup binding and is not in Vue's template globals allowlist, so calling it
 * straight from an @click resolved against the render context instead of the global - undefined at
 * runtime, so the Choose file button threw and did nothing. Found by tools/check-vue-bindings.mjs.
 */
function openImagePicker(imageId) {
    document.getElementById('image-upload-' + imageId)?.click();
}

function uploadImageItem(blockId, imgId, file) {
    if (!file) return;
    imageUploading.value[imgId] = true;
    barError.value = '';

    const formData = new FormData();
    formData.append('image', file);

    fetch(routes.upload_image, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': props.csrfToken,
            // Without these a lapsed admin re-auth window 302s to the confirm-password page,
            // fetch follows it, r.ok is true, and r.json() throws a parser error: "Unexpected
            // token '<' ... is not valid JSON".
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => {
        if (!r.ok) return r.json().catch(() => ({})).then(d => { throw new Error(d.error || t.error_uploading_image); });
        return r.json();
    })
    .then(data => {
        if (data.success && data.url) {
            updateImageItem(blockId, imgId, 'url', data.url);
        }
    })
    .catch(e => {
        barError.value = e.message || t.error_uploading_image;
    })
    .finally(() => {
        imageUploading.value[imgId] = false;
    });
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
    if (copy.type === 'image' && copy.data.images) {
        copy.data.images.forEach(img => { img._id = generateId(); });
    }
    blocks.value.splice(idx + 1, 0, copy);
    openBlock(copy.id, { focus: false });
}

// Removed at once, and can be put back for a few seconds from the bar.
const removed = ref(null);
let removedTimer = null;

function removeBlock(blockId) {
    const idx = blocks.value.findIndex(b => b.id === blockId);
    if (idx === -1) return;
    destroyEasyMDE(blockId);
    const [block] = blocks.value.splice(idx, 1);
    if (selectedBlockId.value === blockId) {
        selectedBlockId.value = null;
    }
    clearTimeout(removedTimer);
    removed.value = { block, index: idx };
    removedTimer = setTimeout(() => { removed.value = null; }, 8000);
}

function undoRemove() {
    if (!removed.value) return;
    const { block, index } = removed.value;
    blocks.value.splice(Math.min(index, blocks.value.length), 0, block);
    removed.value = null;
    clearTimeout(removedTimer);
    openBlock(block.id, { focus: false });
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
                    const newBlock = newBlockOf(bt);
                    evt.item.remove();
                    blocks.value.splice(evt.newIndex, 0, newBlock);
                    showBlockPalette.value = false;
                    openBlock(newBlock.id);
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

// ── Preview ───────────────────────────────────────────────────────────

const livePreviewFrame = ref(null);
const previewPane = ref(null);
const previewPaneWidth = ref(0);
const previewLoading = ref(false);
const previewReady = ref(false);
const previewNeedsReauth = ref(false);
const phonePreviewOpen = ref(false);
// 'desktop' or 'mobile': the width the live preview is shown at.
const previewWidth = ref('desktop');
// The widths the mail is LAID OUT at, whatever room the column has. Desktop is past the mail's
// 620px breakpoint with a little ground either side; a wider column is simply used.
const PREVIEW_LAYOUT = { desktop: 680, mobile: 390 };
let previewPaneObserver = null;
let previewDebounceTimer = null;
let previewAbortController = null;
let keepScroll = 0;
let awaitingPreview = false;

const previewUrl = computed(() => routes.preview || '');
const paneHeight = computed(() => phonePreviewOpen.value ? '100dvh - 4rem' : '100vh - 14rem');

const previewFrameStyle = computed(() => {
    const pane = previewPaneWidth.value || PREVIEW_LAYOUT.desktop;
    const layout = previewWidth.value === 'desktop' ? Math.max(PREVIEW_LAYOUT.desktop, pane) : PREVIEW_LAYOUT.mobile;
    const scale = Math.min(1, pane / layout);

    return {
        width: layout + 'px',
        // The frame is scaled down, so it is that much taller before the scale.
        height: `calc((${paneHeight.value}) / ${scale})`,
        minHeight: (400 / scale) + 'px',
        transform: `scale(${scale})`,
        transformOrigin: 'top left',
        marginLeft: Math.max(0, (pane - layout * scale) / 2) + 'px',
    };
});

function debouncedPreview() {
    clearTimeout(previewDebounceTimer);
    previewDebounceTimer = setTimeout(() => fetchPreview(), 800);
}

function fetchPreview() {
    if (previewAbortController) previewAbortController.abort();
    previewAbortController = new AbortController();

    const form = formEl();
    if (!form || !previewUrl.value) return;

    previewLoading.value = true;

    const formData = new FormData(form);
    formData.delete('_method');

    fetch(previewUrl.value, {
        method: 'POST',
        body: formData,
        signal: previewAbortController.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    })
    .then(r => {
        // 423 is EnsureUserIsAdmin saying the re-auth window lapsed. This runs on a keystroke-rate
        // debounce, so without the headers above it would 302 into HTML and r.json() would throw
        // into the silent catch below - the preview silently freezing while the admin keeps typing.
        if (r.status === 423) {
            previewNeedsReauth.value = true;
            throw new Error('Re-authentication required');
        }
        if (!r.ok) throw new Error('Preview failed');
        previewNeedsReauth.value = false;
        return r.json();
    })
    .then(data => {
        const frame = livePreviewFrame.value;
        if (!frame) return;
        // Where the reader was in the mail, so the new one opens at the same place.
        try {
            keepScroll = frame.contentWindow ? frame.contentWindow.scrollY : 0;
        } catch {
            keepScroll = 0;
        }
        awaitingPreview = true;
        frame.srcdoc = data.html;
    })
    .catch(e => { if (e.name !== 'AbortError') previewLoading.value = false; });
}

function onPreviewLoad() {
    if (!awaitingPreview) return;
    awaitingPreview = false;
    previewLoading.value = false;
    previewReady.value = true;

    const frame = livePreviewFrame.value;
    let doc;
    try {
        doc = frame.contentDocument;
    } catch {
        return;
    }
    if (!doc) return;

    frame.contentWindow.scrollTo(0, keepScroll);

    // The block under the pointer, and the open one, outlined; a click opens it in the list.
    const style = doc.createElement('style');
    style.textContent = '[data-nl-block]{cursor:pointer}'
        + '.nb-mark{position:absolute;z-index:2147483647;box-sizing:border-box;pointer-events:none;border-radius:6px;transition:top .15s,left .15s,width .15s,height .15s}'
        + '.nb-mark.is-sel{border:2px solid #4E81FA;box-shadow:0 0 0 4px rgba(78,129,250,.18)}'
        + '.nb-mark.is-hover{border:2px dashed rgba(78,129,250,.55)}';
    doc.head.appendChild(style);

    doc.addEventListener('click', (e) => {
        const el = e.target && e.target.closest ? e.target.closest('[data-nl-block]') : null;
        if (!el) return;
        e.preventDefault();
        openBlockFromPreview(el.getAttribute('data-nl-block'));
    });
    doc.addEventListener('mouseover', (e) => {
        const el = e.target && e.target.closest ? e.target.closest('[data-nl-block]') : null;
        markInPreview('hover', el ? el.getAttribute('data-nl-block') : null);
    });
    doc.addEventListener('mouseleave', () => markInPreview('hover', null));

    markInPreview('sel', selectedBlockId.value);
}

function markInPreview(kind, blockId, scrollTo = false) {
    const frame = livePreviewFrame.value;
    let doc;
    try {
        doc = frame && frame.contentDocument;
    } catch {
        return;
    }
    if (!doc || !doc.body) return;

    let mark = doc.querySelector('.nb-mark.is-' + kind);
    const target = blockId ? Array.from(doc.querySelectorAll('[data-nl-block]')).find(el => el.getAttribute('data-nl-block') === blockId) : null;
    const box = target ? target.getBoundingClientRect() : null;

    if (!box || (kind === 'hover' && blockId === selectedBlockId.value) || box.height === 0) {
        mark?.remove();
        return;
    }
    if (!mark) {
        mark = doc.createElement('div');
        mark.className = 'nb-mark is-' + kind;
        doc.body.appendChild(mark);
    }
    const win = frame.contentWindow;
    mark.style.top = (box.top + win.scrollY) + 'px';
    mark.style.left = (box.left + win.scrollX) + 'px';
    mark.style.width = box.width + 'px';
    mark.style.height = box.height + 'px';

    if (scrollTo) {
        const top = box.top + win.scrollY;
        const visible = box.top >= 0 && box.bottom <= win.innerHeight;
        if (!visible) win.scrollTo({ top: Math.max(0, top - 24), behavior: 'smooth' });
    }
}

function openBlockFromPreview(blockId) {
    if (!blocks.value.some(b => b.id === blockId)) return;
    if (activeSection.value !== 'content') showSection('content');
    if (phonePreviewOpen.value) closePhonePreview();
    openBlock(blockId, { focus: false });
    nextTick(() => document.querySelector(`[data-block-id="${blockId}"]`)?.scrollIntoView({ block: 'nearest', behavior: 'smooth' }));
}

watch(selectedBlockId, (id) => {
    markInPreview('sel', id, true);
    markInPreview('hover', null);
});

function openPhonePreview() {
    phonePreviewOpen.value = true;
    previewWidth.value = 'mobile';
    document.documentElement.classList.add('nb-sheet-open');
    nextTick(() => {
        if (previewPane.value) previewPaneWidth.value = previewPane.value.clientWidth;
        fetchPreview();
    });
}

function closePhonePreview() {
    phonePreviewOpen.value = false;
    document.documentElement.classList.remove('nb-sheet-open');
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
    window.addEventListener('beforeunload', onBeforeUnload);
    window.addEventListener('keydown', onEscape);
    formEl()?.addEventListener('submit', onFormSubmit);

    nextTick(() => {
        // Taken after the first draw, which fills in what older blocks were saved without.
        savedSnapshot.value = snapshot();
        initSortable();
        fetchPreview();
        loadAudience();
    });

    if (previewPane.value) {
        previewPaneWidth.value = previewPane.value.clientWidth;
        if (typeof ResizeObserver !== 'undefined') {
            previewPaneObserver = new ResizeObserver((entries) => {
                previewPaneWidth.value = entries[0].contentRect.width;
            });
            previewPaneObserver.observe(previewPane.value);
        }
    }
});

onBeforeUnmount(() => {
    destroyAllEasyMDE();
    window.removeEventListener('hashchange', onHashChange);
    window.removeEventListener('beforeunload', onBeforeUnload);
    window.removeEventListener('keydown', onEscape);
    formEl()?.removeEventListener('submit', onFormSubmit);
    if (previewPaneObserver) previewPaneObserver.disconnect();
    clearTimeout(previewDebounceTimer);
    clearTimeout(audienceTimer);
    if (previewAbortController) previewAbortController.abort();
    if (scheduleFlatpickr) scheduleFlatpickr.destroy();
});
</script>
