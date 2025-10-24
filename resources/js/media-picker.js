import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const csrfToken = () => {
    const token = document.head?.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
};

const buildHeaders = (acceptJson = true) => {
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
    };

    const token = csrfToken();
    if (token) {
        headers['X-CSRF-TOKEN'] = token;
    }

    if (acceptJson) {
        headers.Accept = 'application/json';
    }

    return headers;
};

const parseErrorMessage = async (response) => {
    try {
        const data = await response.clone().json();
        if (data?.message) {
            return data.message;
        }
    } catch (_) {
        // Ignore JSON parse errors.
    }

    try {
        const text = await response.clone().text();
        if (text) {
            return text;
        }
    } catch (_) {
        // Ignore text parse errors.
    }

    return `Request failed with status ${response.status}`;
};

const requestJson = async (url, options = {}) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            ...buildHeaders(options.acceptJson ?? true),
            ...(options.headers || {}),
        },
    });

    if (!response.ok) {
        throw new Error(await parseErrorMessage(response));
    }

    return response.json();
};

const createPagination = () => ({
    current_page: 1,
    last_page: 1,
    per_page: 24,
    total: 0,
});

const createMediaLibraryApi = (config) => ({
    async listAssets({ page = 1, tag = null, search = '' } = {}) {
        const params = new URLSearchParams({ page: String(page) });
        if (tag) {
            params.append('tag', tag);
        }
        if (search) {
            params.append('search', search);
        }

        return requestJson(`${config.assetsEndpoint}?${params.toString()}`);
    },

    async listTags() {
        return requestJson(config.tagsEndpoint);
    },

    async uploadAsset(formData) {
        return requestJson(config.uploadEndpoint, {
            method: 'POST',
            body: formData,
        });
    },

    async uploadVariant(assetId, formData) {
        const endpoint = config.variantEndpointTemplate.replace('__ASSET__', assetId);
        return requestJson(endpoint, {
            method: 'POST',
            body: formData,
        });
    },

    async deleteAsset(assetId) {
        const endpoint = config.deleteAssetTemplate.replace('__ID__', assetId);
        return requestJson(endpoint, {
            method: 'DELETE',
        });
    },

    async createTag(name) {
        return requestJson(config.createTagEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name }),
        });
    },

    async deleteTag(tagId) {
        const endpoint = config.deleteTagTemplate.replace('__ID__', tagId);
        return requestJson(endpoint, {
            method: 'DELETE',
        });
    },

    async syncTags(assetId, tags) {
        const endpoint = config.syncTagsTemplate.replace('__ID__', assetId);
        return requestJson(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tags }),
        });
    },
});

const refreshAssetsFromPayload = (state, payload) => {
    state.assets = payload.data || [];
    state.pagination = payload.pagination || state.pagination;
};

const refreshTagsFromPayload = (state, payload) => {
    state.tags = payload.data || [];
};

export const registerMediaLibraryComponents = (alpineInstance) => {
    if (!alpineInstance || registerMediaLibraryComponents.registered) {
        return;
    }

    registerMediaLibraryComponents.registered = true;

    alpineInstance.data('mediaLibraryPage', (config) => {
        const api = createMediaLibraryApi(config);

        return {
            assets: [],
            tags: [],
            pagination: createPagination(),
            activeTag: null,
            search: '',
            isLoading: false,
            showTagModal: false,
            editingAsset: null,
            selectedTagIds: [],
            newTagName: '',
            isCreatingTag: false,
            showDetails: false,
            detailAsset: null,

            init() {
                this.fetchTags();
                this.fetchAssets();
            },

            async fetchAssets(page = 1) {
                this.isLoading = true;

                try {
                    const payload = await api.listAssets({
                        page,
                        tag: this.activeTag,
                        search: this.search,
                    });

                    refreshAssetsFromPayload(this, payload);
                } catch (error) {
                    console.error('Failed to load assets', error);
                } finally {
                    this.isLoading = false;
                }
            },

            async fetchTags() {
                try {
                    const payload = await api.listTags();
                    refreshTagsFromPayload(this, payload);
                } catch (error) {
                    console.error('Failed to load tags', error);
                }
            },

            async handleUpload(event) {
                const [file] = event.target.files || [];
                event.target.value = '';

                if (!file) {
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                try {
                    await api.uploadAsset(formData);
                    this.fetchAssets(this.pagination.current_page);
                } catch (error) {
                    console.error('Upload failed', error);
                    alert(error.message || 'Unable to upload file');
                }
            },

            promptCreateTag() {
                const name = window.prompt('Tag name');
                if (!name) {
                    return;
                }

                this.createTag(name);
            },

            async createTag(name, { select = false } = {}) {
                const value = (name || '').trim();

                if (!value) {
                    return null;
                }

                try {
                    const response = await api.createTag(value);
                    const tag = response?.tag;

                    if (tag) {
                        const existing = this.tags.filter((item) => item.id !== tag.id);
                        this.tags = [...existing, tag].sort((a, b) => a.name.localeCompare(b.name));

                        if (select && !this.selectedTagIds.includes(tag.id)) {
                            this.selectedTagIds.push(tag.id);
                        }

                        return tag;
                    }

                    await this.fetchTags();
                } catch (error) {
                    console.error('Unable to create tag', error);
                    alert(error.message || 'Unable to create tag');
                }

                return null;
            },

            async createTagFromModal() {
                if (this.isCreatingTag) {
                    return;
                }

                this.isCreatingTag = true;

                try {
                    const tag = await this.createTag(this.newTagName, { select: true });
                    if (tag) {
                        this.newTagName = '';
                    }
                } finally {
                    this.isCreatingTag = false;
                }
            },

            async removeTag(tag) {
                if (!tag) {
                    return;
                }

                const confirmed = window.confirm(`Remove tag "${tag.name}"?`);
                if (!confirmed) {
                    return;
                }

                try {
                    await api.deleteTag(tag.id);
                    const wasActive = this.activeTag === tag.slug;

                    this.tags = this.tags.filter((item) => item.id !== tag.id);
                    this.assets = this.assets.map((asset) => ({
                        ...asset,
                        tags: (asset.tags || []).filter((assetTag) => assetTag.id !== tag.id),
                    }));

                    if (this.detailAsset) {
                        this.detailAsset.tags = (this.detailAsset.tags || []).filter((assetTag) => assetTag.id !== tag.id);
                    }

                    if (this.editingAsset) {
                        this.editingAsset.tags = (this.editingAsset.tags || []).filter((assetTag) => assetTag.id !== tag.id);
                        this.selectedTagIds = this.selectedTagIds.filter((id) => id !== tag.id);
                    }

                    if (wasActive) {
                        this.activeTag = null;
                        this.pagination.current_page = 1;
                        await this.fetchAssets(1);
                    }
                } catch (error) {
                    console.error('Unable to delete tag', error);
                    alert(error.message || 'Unable to delete tag');
                }
            },

            openTagEditor(asset) {
                this.editingAsset = asset;
                const tags = Array.isArray(asset.tags) ? asset.tags : [];
                this.selectedTagIds = tags.map((tag) => tag.id);
                this.newTagName = '';
                this.isCreatingTag = false;
                this.showTagModal = true;
            },

            closeTagEditor() {
                this.showTagModal = false;
                this.editingAsset = null;
                this.selectedTagIds = [];
                this.newTagName = '';
                this.isCreatingTag = false;
            },

            toggleTag(tagId) {
                if (this.selectedTagIds.includes(tagId)) {
                    this.selectedTagIds = this.selectedTagIds.filter((id) => id !== tagId);
                } else {
                    this.selectedTagIds.push(tagId);
                }
            },

            async saveTags() {
                if (!this.editingAsset) {
                    return;
                }

                try {
                    await api.syncTags(this.editingAsset.id, this.selectedTagIds);
                    this.editingAsset.tags = this.tags.filter((tag) => this.selectedTagIds.includes(tag.id));
                    this.closeTagEditor();
                } catch (error) {
                    console.error('Unable to update tags', error);
                    alert(error.message || 'Unable to update tags');
                }
            },

            openDetails(asset) {
                this.detailAsset = asset;
                this.showDetails = true;
            },

            closeDetails() {
                this.detailAsset = null;
                this.showDetails = false;
            },

            async deleteAsset(asset) {
                if (!asset) {
                    return;
                }

                const confirmed = window.confirm(`Delete "${asset.original_filename}"? This action cannot be undone.`);
                if (!confirmed) {
                    return;
                }

                const currentPage = Number(this.pagination.current_page) || 1;
                const perPage = Number(this.pagination.per_page) || 24;
                const updatedTotal = Math.max(0, (Number(this.pagination.total) || 0) - 1);
                const lastPageAfterDeletion = Math.max(1, Math.ceil(updatedTotal / perPage));
                const targetPage = Math.min(currentPage, lastPageAfterDeletion);

                try {
                    await api.deleteAsset(asset.id);
                    this.assets = this.assets.filter((item) => item.id !== asset.id);
                    this.pagination.total = updatedTotal;

                    if (this.detailAsset && this.detailAsset.id === asset.id) {
                        this.closeDetails();
                    }

                    await this.fetchAssets(targetPage);
                } catch (error) {
                    console.error('Unable to delete asset', error);
                    alert(error.message || 'Unable to delete asset');
                }
            },

            changePage(page) {
                this.pagination.current_page = page;
                this.fetchAssets(page);
            },

            formatDimensions(asset) {
                if (!asset.width || !asset.height) {
                    return '';
                }

                return `${asset.width}×${asset.height}`;
            },

            formatUsage(count) {
                const value = Number(count || 0);

                if (value === 0) {
                    return 'Not used yet';
                }

                return value === 1 ? 'Used in 1 record' : `Used in ${value} records`;
            },
        };
    });

};

class MediaPickerController {
    constructor(root) {
        this.root = root;

        const { dataset } = root;
        const parseNumber = (value) => {
            if (!value) {
                return null;
            }

            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : null;
        };

        this.config = {
            assetsEndpoint: dataset.assetsEndpoint,
            uploadEndpoint: dataset.uploadEndpoint,
            tagsEndpoint: dataset.tagsEndpoint,
            deleteTagTemplate: dataset.deleteTagTemplate,
            variantEndpointTemplate: dataset.variantEndpointTemplate,
            context: dataset.context ? dataset.context : null,
            initialAssetId: parseNumber(dataset.initialAssetId),
            initialVariantId: parseNumber(dataset.initialVariantId),
            initialUrl: dataset.initialUrl || '',
            labels: {
                allAssets: dataset.labelAll || 'All assets',
            },
        };

        this.api = createMediaLibraryApi(this.config);

        this.elements = {
            previewImage: root.querySelector('[data-role="preview-image"]'),
            placeholder: root.querySelector('[data-role="placeholder"]'),
            assetInput: root.querySelector('[data-role="asset-input"]'),
            variantInput: root.querySelector('[data-role="variant-input"]'),
            clearButton: root.querySelector('[data-action="clear"]'),
            openButton: root.querySelector('[data-action="open"]'),
            fileInput: root.querySelector('[data-role="file-input"]'),
            modal: root.querySelector('[data-role="modal"]'),
        };

        const modal = this.elements.modal;

        this.elements.modalCloseButtons = modal
            ? modal.querySelectorAll('[data-action="close"]')
            : [];
        this.elements.tagFilter = modal?.querySelector('[data-role="tag-filter"]') || null;
        this.elements.assetGrid = modal?.querySelector('[data-role="asset-grid"]') || null;
        this.elements.loading = modal?.querySelector('[data-role="loading"]') || null;
        this.elements.empty = modal?.querySelector('[data-role="empty"]') || null;
        this.elements.pagination = modal?.querySelector('[data-role="pagination"]') || null;
        this.elements.pageCurrent = modal?.querySelector('[data-role="page-current"]') || null;
        this.elements.pageTotal = modal?.querySelector('[data-role="page-total"]') || null;
        this.elements.prevButton = modal?.querySelector('[data-action="prev"]') || null;
        this.elements.nextButton = modal?.querySelector('[data-action="next"]') || null;
        this.elements.selectionPlaceholder = modal?.querySelector('[data-role="selection-placeholder"]') || null;
        this.elements.editorContent = modal?.querySelector('[data-role="editor-content"]') || null;
        this.elements.cropImage = modal?.querySelector('[data-role="crop-image"]') || null;
        this.elements.saveButton = modal?.querySelector('[data-action="save-crop"]') || null;
        this.elements.saveLabel = modal?.querySelector('[data-role="save-label"]') || null;
        this.elements.savingLabel = modal?.querySelector('[data-role="saving-label"]') || null;
        this.elements.useOriginalButton = modal?.querySelector('[data-action="use-original"]') || null;
        this.elements.cancelButton = modal?.querySelector('[data-action="cancel"]') || null;

        this.assets = [];
        this.tags = [];
        this.pagination = createPagination();
        this.activeTag = null;
        this.selectedAssetId = this.config.initialAssetId;
        this.selectedVariantId = this.config.initialVariantId;
        this.previewUrl = this.config.initialUrl || '';
        this.currentAsset = null;
        this.cropper = null;
        this.isLoading = false;
        this.isUploading = false;
        this.modalOpen = false;
        this.keydownHandler = null;
    }

    init() {
        this.updatePreview();
        this.registerEventListeners();
    }

    registerEventListeners() {
        this.elements.openButton?.addEventListener('click', () => {
            if (!this.isUploading) {
                this.openModal();
            }
        });

        this.elements.clearButton?.addEventListener('click', () => {
            if (!this.isUploading) {
                this.clearSelection();
            }
        });

        this.elements.fileInput?.addEventListener('change', (event) => {
            if (!this.isUploading) {
                this.uploadFromPicker(event);
            }
        });

        this.elements.modalCloseButtons?.forEach((button) => {
            button.addEventListener('click', () => this.closeModal());
        });

        this.elements.prevButton?.addEventListener('click', () => {
            if (!this.isLoading && !this.isUploading) {
                this.changePage((this.pagination.current_page || 1) - 1);
            }
        });

        this.elements.nextButton?.addEventListener('click', () => {
            if (!this.isLoading && !this.isUploading) {
                this.changePage((this.pagination.current_page || 1) + 1);
            }
        });

        this.elements.saveButton?.addEventListener('click', () => {
            if (!this.isUploading) {
                this.saveCrop();
            }
        });

        this.elements.useOriginalButton?.addEventListener('click', () => {
            if (!this.isUploading) {
                this.useOriginal();
            }
        });

        this.elements.cancelButton?.addEventListener('click', () => {
            if (!this.isUploading) {
                this.closeModal();
            }
        });
    }

    updatePreview() {
        const hasPreview = Boolean(this.previewUrl);

        if (this.elements.previewImage) {
            this.elements.previewImage.src = this.previewUrl || '';
            this.elements.previewImage.classList.toggle('hidden', !hasPreview);
        }

        this.elements.placeholder?.classList.toggle('hidden', hasPreview);
        this.elements.clearButton?.classList.toggle('hidden', !hasPreview);
        if (this.elements.assetInput) {
            this.elements.assetInput.value = this.selectedAssetId ? String(this.selectedAssetId) : '';
        }
        if (this.elements.variantInput) {
            this.elements.variantInput.value = this.selectedVariantId ? String(this.selectedVariantId) : '';
        }
    }

    openModal() {
        if (this.modalOpen || !this.elements.modal) {
            return;
        }

        this.modalOpen = true;
        this.elements.modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        this.showEditorPlaceholder();

        this.fetchTags();
        this.fetchAssets(1);

        this.keydownHandler = (event) => {
            if (event.key === 'Escape') {
                this.closeModal();
            }
        };

        document.addEventListener('keydown', this.keydownHandler);
    }

    closeModal() {
        if (!this.modalOpen || !this.elements.modal) {
            return;
        }

        this.modalOpen = false;
        this.elements.modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        if (this.keydownHandler) {
            document.removeEventListener('keydown', this.keydownHandler);
            this.keydownHandler = null;
        }

        this.destroyCropper();
        this.currentAsset = null;
        this.showEditorPlaceholder();
    }

    async fetchAssets(page = 1) {
        if (!this.elements.assetGrid) {
            return;
        }

        this.isLoading = true;
        this.showLoading(true);
        this.showEmpty(false);

        try {
            const payload = await this.api.listAssets({
                page,
                tag: this.activeTag,
            });

            this.assets = payload.data || [];
            this.pagination = payload.pagination || this.pagination;
            this.renderAssets();
            this.updatePagination();
        } catch (error) {
            console.error('Unable to load media assets', error);
            alert(error.message || 'Unable to load media assets');
        } finally {
            this.isLoading = false;
            this.showLoading(false);
            this.showEmpty(this.assets.length === 0);
        }
    }

    async fetchTags() {
        if (!this.elements.tagFilter) {
            return;
        }

        try {
            const previousTag = this.activeTag;
            const payload = await this.api.listTags();
            this.tags = payload.data || [];
            const hasContextTag = this.config.context
                && this.tags.some((tag) => tag.slug === this.config.context);

            if (hasContextTag && this.activeTag !== this.config.context) {
                this.activeTag = this.config.context;
            } else if (this.activeTag && !this.tags.some((tag) => tag.slug === this.activeTag)) {
                this.activeTag = null;
            }

            this.renderTags();

            if (this.modalOpen && previousTag !== this.activeTag) {
                this.fetchAssets(1);
            }
        } catch (error) {
            console.error('Failed to load tags', error);
        }
    }

    renderTags() {
        const container = this.elements.tagFilter;
        if (!container) {
            return;
        }

        container.innerHTML = '';

        if (!this.tags.length) {
            container.hidden = true;
            return;
        }

        container.hidden = false;

        const createButton = (label, slug = null) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.classList.add('px-3', 'py-1', 'rounded-full', 'text-xs');
            button.dataset.tagSlug = slug ?? '';
            button.textContent = label;
            button.addEventListener('click', () => {
                if (!this.isLoading && !this.isUploading) {
                    this.activeTag = slug;
                    this.fetchAssets(1);
                    this.updateTagButtons();
                }
            });
            container.appendChild(button);
        };

        createButton(this.config.labels.allAssets, null);
        this.tags.forEach((tag) => {
            createButton(tag.name, tag.slug);
        });

        this.updateTagButtons();
    }

    updateTagButtons() {
        const container = this.elements.tagFilter;
        if (!container) {
            return;
        }

        container.querySelectorAll('button').forEach((button) => {
            const slug = button.dataset.tagSlug || null;
            const isActive = slug ? this.activeTag === slug : this.activeTag === null;

            button.classList.toggle('bg-indigo-600', isActive);
            button.classList.toggle('text-white', isActive);
            button.classList.toggle('bg-gray-100', !isActive);
            button.classList.toggle('dark:bg-gray-800', !isActive);
            button.classList.toggle('text-gray-700', !isActive);
            button.classList.toggle('dark:text-gray-200', !isActive);
        });
    }

    renderAssets() {
        const grid = this.elements.assetGrid;
        if (!grid) {
            return;
        }

        grid.innerHTML = '';

        this.assets.forEach((asset) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.assetId = String(asset.id);
            button.className = 'border border-gray-200 dark:border-gray-700 rounded-md overflow-hidden hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500';

            const image = document.createElement('img');
            image.src = asset.url;
            image.alt = asset.original_filename || '';
            image.className = 'w-full h-24 object-cover';
            button.appendChild(image);

            const caption = document.createElement('div');
            caption.className = 'px-2 py-1 text-xs text-gray-600 dark:text-gray-300 truncate';
            caption.textContent = asset.original_filename || 'Asset';
            button.appendChild(caption);

            button.addEventListener('click', () => {
                if (!this.isUploading) {
                    this.selectAsset(asset);
                }
            });

            grid.appendChild(button);
        });

        this.highlightSelectedAsset();
    }

    highlightSelectedAsset() {
        const grid = this.elements.assetGrid;
        if (!grid) {
            return;
        }

        grid.querySelectorAll('button[data-asset-id]').forEach((button) => {
            const assetId = Number(button.dataset.assetId);
            const isSelected = this.selectedAssetId != null && assetId === this.selectedAssetId;
            button.classList.toggle('ring-2', isSelected);
            button.classList.toggle('ring-indigo-500', isSelected);
            button.classList.toggle('border-indigo-500', isSelected);
        });
    }

    changePage(page) {
        const totalPages = this.pagination.last_page || 1;
        const nextPage = Math.min(Math.max(page, 1), totalPages);

        if (nextPage === (this.pagination.current_page || 1)) {
            return;
        }

        this.fetchAssets(nextPage);
    }

    updatePagination() {
        const container = this.elements.pagination;
        if (!container) {
            return;
        }

        const total = Number(this.pagination.total || 0);
        const perPage = Number(this.pagination.per_page || 24);
        const current = Number(this.pagination.current_page || 1);
        const last = Number(this.pagination.last_page || 1);

        const shouldShow = total > perPage;
        container.hidden = !shouldShow;

        if (!shouldShow) {
            return;
        }

        if (this.elements.pageCurrent) {
            this.elements.pageCurrent.textContent = String(current);
        }

        if (this.elements.pageTotal) {
            this.elements.pageTotal.textContent = String(Math.max(last, 1));
        }

        if (this.elements.prevButton) {
            this.elements.prevButton.disabled = current <= 1 || this.isLoading || this.isUploading;
        }

        if (this.elements.nextButton) {
            this.elements.nextButton.disabled = current >= last || this.isLoading || this.isUploading;
        }
    }

    selectAsset(asset) {
        if (!this.elements.cropImage) {
            return;
        }

        this.currentAsset = asset;
        this.showEditorContent();
        this.destroyCropper();
        this.setUploadingState(false);

        const image = this.elements.cropImage;
        const initialiseCropper = () => {
            this.destroyCropper();
            this.cropper = new Cropper(image, {
                viewMode: 1,
                responsive: true,
                autoCropArea: 1,
                movable: true,
                zoomable: true,
            });
        };

        const handleLoad = () => {
            image.removeEventListener('load', handleLoad);
            initialiseCropper();
            this.setUploadingState(false);
        };

        image.addEventListener('load', handleLoad);
        image.src = asset.url;

        if (image.complete && image.naturalWidth) {
            handleLoad();
        }
        this.highlightSelectedAsset();
    }

    destroyCropper() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    }

    showEditorPlaceholder() {
        this.elements.editorContent?.classList.add('hidden');
        this.elements.selectionPlaceholder?.classList.remove('hidden');
    }

    showEditorContent() {
        this.elements.selectionPlaceholder?.classList.add('hidden');
        this.elements.editorContent?.classList.remove('hidden');
    }

    setUploadingState(isUploading) {
        this.isUploading = isUploading;
        if (this.elements.saveButton) {
            this.elements.saveButton.disabled = isUploading || !this.cropper;
        }

        this.elements.useOriginalButton?.classList.toggle('opacity-70', isUploading);
        if (this.elements.useOriginalButton) {
            this.elements.useOriginalButton.disabled = isUploading;
        }

        if (this.elements.savingLabel && this.elements.saveLabel) {
            this.elements.savingLabel.classList.toggle('hidden', !isUploading);
            this.elements.saveLabel.classList.toggle('hidden', isUploading);
        }

        this.updatePagination();
    }

    useOriginal() {
        if (!this.currentAsset) {
            return;
        }

        this.selectedAssetId = this.currentAsset.id;
        this.selectedVariantId = null;
        this.previewUrl = this.currentAsset.url;
        this.updatePreview();
        this.closeModal();
    }

    saveCrop() {
        if (!this.cropper || !this.currentAsset) {
            return;
        }

        this.setUploadingState(true);

        this.cropper.getCroppedCanvas().toBlob(async (blob) => {
            if (!blob) {
                this.setUploadingState(false);
                return;
            }

            const formData = new FormData();
            formData.append('file', blob, `${this.currentAsset.uuid || 'crop'}.png`);

            if (this.config.context) {
                formData.append('context', this.config.context);
            }

            const cropData = this.cropper.getData();
            if (cropData) {
                formData.append('crop_meta', JSON.stringify(cropData));
            }

            try {
                const payload = await this.api.uploadVariant(this.currentAsset.id, formData);
                const variant = payload.variant;

                if (variant) {
                    this.selectedAssetId = this.currentAsset.id;
                    this.selectedVariantId = variant.id;
                    this.previewUrl = variant.url;
                    this.updatePreview();
                    this.closeModal();
                }
            } catch (error) {
                console.error('Unable to save crop', error);
                alert(error.message || 'Unable to save crop');
            } finally {
                this.setUploadingState(false);
            }
        }, 'image/png');
    }

    clearSelection() {
        this.selectedAssetId = null;
        this.selectedVariantId = null;
        this.previewUrl = '';
        this.updatePreview();
    }

    async uploadFromPicker(event) {
        const input = event.target;
        const file = input.files?.[0];
        input.value = '';

        if (!file) {
            return;
        }

        this.showLoading(true);
        this.isUploading = true;

        const formData = new FormData();
        formData.append('file', file);

        if (this.config.context) {
            formData.append('context', this.config.context);
        }

        try {
            const payload = await this.api.uploadAsset(formData);
            if (payload.asset) {
                this.selectedAssetId = payload.asset.id;
                this.selectedVariantId = null;
                this.previewUrl = payload.asset.url;
                this.updatePreview();
                this.closeModal();
            }

            await this.fetchAssets(1);
        } catch (error) {
            console.error('Unable to upload file', error);
            alert(error.message || 'Unable to upload file');
        } finally {
            this.isUploading = false;
            this.showLoading(false);
        }
    }

    showLoading(visible) {
        this.elements.loading?.classList.toggle('hidden', !visible);
    }

    showEmpty(visible) {
        this.elements.empty?.classList.toggle('hidden', !visible);
    }
}

export const initMediaPickers = () => {
    document.querySelectorAll('[data-media-picker]').forEach((root) => {
        if (root.__mediaPickerInstance) {
            return;
        }

        const picker = new MediaPickerController(root);
        picker.init();
        root.__mediaPickerInstance = picker;
    });
};

if (typeof window !== 'undefined') {
    if (window.Alpine) {
        registerMediaLibraryComponents(window.Alpine);
    } else {
        document.addEventListener('alpine:init', (event) => {
            registerMediaLibraryComponents(event.detail || window.Alpine);
        });
    }

    const bootstrapPickers = () => initMediaPickers();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrapPickers, { once: true });
    } else {
        bootstrapPickers();
    }
}
