import Alpine from 'alpinejs';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const csrfToken = () => {
    const tokenElement = document.head.querySelector('meta[name="csrf-token"]');
    return tokenElement ? tokenElement.getAttribute('content') : '';
};

const buildHeaders = (extra = {}) => ({
    'X-CSRF-TOKEN': csrfToken(),
    'X-Requested-With': 'XMLHttpRequest',
    Accept: 'application/json',
    ...extra,
});

const registerMediaLibraryComponents = (alpineInstance) => {
    if (!alpineInstance || registerMediaLibraryComponents.registered) {
        return;
    }

    registerMediaLibraryComponents.registered = true;

    alpineInstance.data('mediaLibraryPage', (config) => ({
        assets: [],
        tags: [],
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 24,
            total: 0,
        },
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
                const params = new URLSearchParams({ page: String(page) });

                if (this.activeTag) {
                    params.append('tag', this.activeTag);
                }

                if (this.search) {
                    params.append('search', this.search);
                }

                const response = await fetch(`${config.assetsEndpoint}?${params.toString()}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Failed to load assets');
                }

                const data = await response.json();
                this.assets = data.data || [];
                this.pagination = data.pagination || this.pagination;
            } catch (error) {
                console.error(error);
            } finally {
                this.isLoading = false;
            }
        },

        async fetchTags() {
            try {
                const response = await fetch(config.tagsEndpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Failed to load tags');
                }

                const data = await response.json();
                this.tags = data.data || [];
            } catch (error) {
                console.error(error);
            }
        },

        async handleUpload(event) {
            const [file] = event.target.files || [];

            if (!file) {
                return;
            }

            const formData = new FormData();
            formData.append('file', file);

            try {
                const response = await fetch(config.uploadEndpoint, {
                    method: 'POST',
                    headers: buildHeaders(),
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Upload failed');
                }

                await response.json();
                this.fetchAssets(this.pagination.current_page);
            } catch (error) {
                console.error(error);
                alert(error.message || 'Unable to upload file');
            } finally {
                event.target.value = '';
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
                const response = await fetch(config.createTagEndpoint, {
                    method: 'POST',
                    headers: buildHeaders({ 'Content-Type': 'application/json' }),
                    body: JSON.stringify({ name }),
                });

                if (!response.ok) {
                    throw new Error('Unable to create tag');
                }

                await response.json();
                this.fetchTags();
            } catch (error) {
                console.error(error);
                alert(error.message || 'Unable to create tag');
            }
        },

        openTagEditor(asset) {
            this.editingAsset = asset;
            this.selectedTagIds = asset.tags.map((tag) => tag.id);
            this.showTagModal = true;
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
                const endpoint = config.syncTagsTemplate.replace('__ID__', this.editingAsset.id);
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: buildHeaders({ 'Content-Type': 'application/json' }),
                    body: JSON.stringify({ tags: this.selectedTagIds }),
                });

                if (!response.ok) {
                    throw new Error('Unable to save tags');
                }

                await response.json();
                this.editingAsset.tags = this.tags.filter((tag) => this.selectedTagIds.includes(tag.id));
                this.showTagModal = false;
            } catch (error) {
                console.error(error);
                alert(error.message || 'Unable to update tags');
            }
        },

        closeTagEditor() {
            this.showTagModal = false;
            this.editingAsset = null;
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
    }));

    alpineInstance.data('mediaPicker', (config) => ({
        assets: [],
        tags: [],
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 24,
            total: 0,
        },
        isOpen: false,
        isLoading: false,
        isUploading: false,
        selectedAssetId: config.initialAssetId ?? null,
        selectedVariantId: config.initialVariantId ?? null,
        previewUrl: config.initialUrl || '',
        activeTag: null,
        currentAsset: null,
        cropper: null,
        showCropper: false,
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
            this.showCropper = false;
        },

        async fetchAssets(page = 1) {
            this.isLoading = true;
            try {
                const params = new URLSearchParams({ page: String(page) });
                if (this.activeTag) {
                    params.append('tag', this.activeTag);
                }
                if (this.search) {
                    params.append('search', this.search);
                }

                const response = await fetch(`${config.assetsEndpoint}?${params.toString()}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Unable to load media assets');
                }

                const data = await response.json();
                this.assets = data.data || [];
                this.pagination = data.pagination || this.pagination;
            } catch (error) {
                console.error(error);
            } finally {
                this.isLoading = false;
            }
        },

        async fetchTags() {
            try {
                const response = await fetch(config.tagsEndpoint, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Failed to load tags');
                }

                const data = await response.json();
                this.tags = data.data || [];
            } catch (error) {
                console.error(error);
            }
        },

        selectAsset(asset) {
            this.currentAsset = asset;
            this.showCropper = true;
            this.destroyCropper();

            this.$nextTick(() => {
                const image = this.$refs.cropImage;
                if (image) {
                    image.src = asset.url;
                    this.cropper = new Cropper(image, {
                        viewMode: 1,
                        responsive: true,
                        autoCropArea: 1,
                        movable: true,
                        zoomable: true,
                    });
                }
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
                    const endpoint = config.variantEndpointTemplate.replace('__ASSET__', this.currentAsset.id);
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: buildHeaders(),
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('Unable to save crop');
                    }

                    const data = await response.json();
                    const variant = data.variant;
                    this.selectedAssetId = this.currentAsset.id;
                    this.selectedVariantId = variant.id;
                    this.previewUrl = variant.url;
                    this.closeModal();
                } catch (error) {
                    console.error(error);
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
            if (!file) {
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            if (config.context) {
                formData.append('context', config.context);
            }

            try {
                const response = await fetch(config.uploadEndpoint, {
                    method: 'POST',
                    headers: buildHeaders(),
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Upload failed');
                }

                const data = await response.json();
                if (data.asset) {
                    this.selectedAssetId = data.asset.id;
                    this.selectedVariantId = null;
                    this.previewUrl = data.asset.url;
                    this.closeModal();
                }

                this.fetchAssets(1);
            } catch (error) {
                console.error(error);
                alert(error.message || 'Unable to upload file');
            } finally {
                event.target.value = '';
            }
        },

        variantLabel(asset) {
            return asset.original_filename || 'Asset';
        },
    }));
};

if (window.Alpine) {
    registerMediaLibraryComponents(window.Alpine);
} else {
    document.addEventListener('alpine:init', () => {
        registerMediaLibraryComponents(window.Alpine || Alpine);
    });
}
