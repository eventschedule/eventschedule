import { createApp } from 'vue';
import ImageLibraryManager from './components/ImageLibraryManager.vue';
import ImagePickerModal from './components/ImagePickerModal.vue';

const mountComponent = (selector, component, propResolver = () => ({})) => {
  document.querySelectorAll(selector).forEach((element) => {
    if (element.__vue_app__) {
      return;
    }

    const props = propResolver(element);
    const app = createApp(component, props);
    app.mount(element);
    element.__vue_app__ = app;
  });
};

const mountLibraryManagers = () => {
  mountComponent('[data-image-library-manager]', ImageLibraryManager, (element) => ({
    apiBase: element.dataset.apiBase || '/admin/images',
    initialSearch: element.dataset.initialSearch || '',
    initialType: element.dataset.initialType || '',
    initialView: element.dataset.initialView || 'grid',
    selectable: element.dataset.selectable === 'true',
  }));
};

const mountPickers = () => {
  mountComponent('[data-image-picker-modal]', ImagePickerModal, (element) => ({
    apiBase: element.dataset.apiBase || '/admin/images',
    initialView: element.dataset.initialView || 'grid',
  }));
};

document.addEventListener('DOMContentLoaded', () => {
  mountLibraryManagers();
  mountPickers();
});

if (typeof window !== 'undefined') {
  window.ImageLibrary = window.ImageLibrary || {};
  Object.assign(window.ImageLibrary, {
    mountLibraryManagers,
    mountPickers,
    ImageLibraryManager,
    ImagePickerModal,
  });
}

export { ImageLibraryManager, ImagePickerModal, mountLibraryManagers, mountPickers };
