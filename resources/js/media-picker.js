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

    async createTag(name) {
        return requestJson(config.createTagEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name }),
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

                this.createTag(name.trim());
            },

            async createTag(name) {
                try {
                    await api.createTag(name);
                    this.fetchTags();
                } catch (error) {
                    console.error('Unable to create tag', error);
                    alert(error.message || 'Unable to create tag');
                }
            },

            openTagEditor(asset) {
                this.editingAsset = asset;
                this.selectedTagIds = asset.tags.map((tag) => tag.id);
                this.showTagModal = true;
            },

            closeTagEditor() {
                this.showTagModal = false;
                this.editingAsset = null;
                this.selectedTagIds = [];
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

    alpineInstance.data('mediaPicker', (config) => {
        const api = createMediaLibraryApi(config);

        return {
            assets: [],
            tags: [],
            pagination: createPagination(),
            isOpen: false,
            isLoading: false,
            isUploading: false,
            selectedAssetId: config.initialAssetId ?? null,
            selectedVariantId: config.initialVariantId ?? null,
            previewUrl: config.initialUrl || '',
            activeTag: null,
            currentAsset: null,
            cropper: null,
            search: '',

            openModal() {
                this.isOpen = true;
                if (!this.assets.length) {
                    this.fetchAssets();
                    this.fetchTags();
                }
            },

            closeModal() {
                this.isOpen = false;
                this.destroyCropper();
                this.currentAsset = null;
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
                    console.error('Unable to load media assets', error);
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

            selectAsset(asset) {
                this.currentAsset = asset;
                this.destroyCropper();

                this.$nextTick(() => {
                    const image = this.$refs.cropImage;
                    if (!image) {
                        return;
                    }

                    image.src = asset.url;
                    this.cropper = new Cropper(image, {
                        viewMode: 1,
                        responsive: true,
                        autoCropArea: 1,
                        movable: true,
                        zoomable: true,
                    });
                });
            },

            destroyCropper() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            useOriginal() {
                if (!this.currentAsset) {
                    return;
                }

                this.selectedAssetId = this.currentAsset.id;
                this.selectedVariantId = null;
                this.previewUrl = this.currentAsset.url;
                this.closeModal();
            },

            saveCrop() {
                if (!this.cropper || !this.currentAsset) {
                    return;
                }

                this.isUploading = true;

                this.cropper.getCroppedCanvas().toBlob(async (blob) => {
                    if (!blob) {
                        this.isUploading = false;
                        return;
                    }

                    const formData = new FormData();
                    formData.append('file', blob, `${this.currentAsset.uuid || 'crop'}.png`);

                    if (config.context) {
                        formData.append('context', config.context);
                    }

                    const cropData = this.cropper.getData();
                    if (cropData) {
                        formData.append('crop_meta', JSON.stringify(cropData));
                    }

                    try {
                        const payload = await api.uploadVariant(this.currentAsset.id, formData);
                        const variant = payload.variant;

                        this.selectedAssetId = this.currentAsset.id;
                        this.selectedVariantId = variant.id;
                        this.previewUrl = variant.url;
                        this.closeModal();
                    } catch (error) {
                        console.error('Unable to save crop', error);
                        alert(error.message || 'Unable to save crop');
                    } finally {
                        this.isUploading = false;
                    }
                }, 'image/png');
            },

            clearSelection() {
                this.selectedAssetId = null;
                this.selectedVariantId = null;
                this.previewUrl = '';
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

            async uploadFromPicker(event) {
                const [file] = event.target.files || [];
                event.target.value = '';

                if (!file) {
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                if (config.context) {
                    formData.append('context', config.context);
                }

                try {
                    const payload = await api.uploadAsset(formData);
                    if (payload.asset) {
                        this.selectedAssetId = payload.asset.id;
                        this.selectedVariantId = null;
                        this.previewUrl = payload.asset.url;
                        this.closeModal();
                    }

                    this.fetchAssets(1);
                } catch (error) {
                    console.error('Unable to upload file', error);
                    alert(error.message || 'Unable to upload file');
                }
            },

            variantLabel(asset) {
                return asset.original_filename || 'Asset';
            },
        };
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
}
