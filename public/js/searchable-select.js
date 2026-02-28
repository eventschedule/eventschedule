/**
 * Searchable Select - Enhances <select data-searchable> into combobox with search
 */
(function() {
    'use strict';

    var instanceId = 0;
    var noResultsText = (window._ssI18n && window._ssI18n.noResults) || 'No results found';

    function normalizeText(str) {
        return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        var normalizedText = normalizeText(text);
        var normalizedQuery = normalizeText(query);
        var idx = normalizedText.indexOf(normalizedQuery);
        if (idx === -1) return escapeHtml(text);
        var before = text.substring(0, idx);
        var match = text.substring(idx, idx + query.length);
        var after = text.substring(idx + query.length);
        return escapeHtml(before) + '<mark style="background:rgba(78,129,250,0.15);font-weight:600;padding:0;color:inherit">' + escapeHtml(match) + '</mark>' + escapeHtml(after);
    }

    function isSeparatorOption(option) {
        var text = option.textContent.trim();
        return option.disabled && /^[\u2500-\u257F\-─]+$/.test(text);
    }

    function createSearchableSelect(select) {
        if (select._searchableInit) return;
        select._searchableInit = true;

        var id = 'ss-' + (++instanceId);
        var isRequired = select.required || select.hasAttribute('required');
        var isRtl = document.documentElement.dir === 'rtl';
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Read options from select
        var options = [];
        function readOptions() {
            options = [];
            var opts = select.querySelectorAll('option');
            for (var i = 0; i < opts.length; i++) {
                var opt = opts[i];
                options.push({
                    value: opt.value,
                    text: opt.textContent.trim(),
                    disabled: opt.disabled,
                    separator: isSeparatorOption(opt),
                    element: opt
                });
            }
        }
        readOptions();

        // Get current selection
        function getSelectedText() {
            var opt = select.options[select.selectedIndex];
            return opt ? opt.textContent.trim() : '';
        }

        var currentText = getSelectedText();

        // Build wrapper
        var wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = select.style.display === 'none' ? 'none' : '';

        // Copy width-related classes from select to wrapper
        var selectClasses = select.className.split(/\s+/);
        var widthClasses = [];
        for (var i = 0; i < selectClasses.length; i++) {
            if (/^(w-|max-w-|min-w-)/.test(selectClasses[i]) || /^(flex-1|flex-auto|flex-none|flex-initial|grow|shrink)$/.test(selectClasses[i])) {
                widthClasses.push(selectClasses[i]);
            }
        }
        if (widthClasses.length > 0) {
            wrapper.className = widthClasses.join(' ');
        }

        // Build input
        var input = document.createElement('input');
        input.type = 'text';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-controls', id + '-listbox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-activedescendant', '');
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('autocapitalize', 'off');
        input.setAttribute('autocorrect', 'off');
        input.setAttribute('spellcheck', 'false');
        input.value = currentText;

        // Copy classes from select, excluding width classes (those go on wrapper)
        var inputClasses = [];
        for (var i = 0; i < selectClasses.length; i++) {
            if (!/^(w-|max-w-|min-w-)/.test(selectClasses[i]) && !/^(flex-1|flex-auto|flex-none|flex-initial|grow|shrink)$/.test(selectClasses[i])) {
                inputClasses.push(selectClasses[i]);
            }
        }
        input.className = inputClasses.join(' ');
        // Ensure full width within wrapper
        input.style.width = '100%';
        input.style.boxSizing = 'border-box';

        // Add chevron and padding
        var chevronSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E";
        if (isRtl) {
            input.style.backgroundImage = 'url("' + chevronSvg + '")';
            input.style.backgroundPosition = 'left 0.5rem center';
            input.style.backgroundSize = '1.5em 1.5em';
            input.style.backgroundRepeat = 'no-repeat';
            input.style.paddingLeft = '2.5rem';
        } else {
            input.style.backgroundImage = 'url("' + chevronSvg + '")';
            input.style.backgroundPosition = 'right 0.5rem center';
            input.style.backgroundSize = '1.5em 1.5em';
            input.style.backgroundRepeat = 'no-repeat';
            input.style.paddingRight = '2.5rem';
        }

        // Build clear button
        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.setAttribute('aria-label', 'Clear selection');
        clearBtn.setAttribute('tabindex', '-1');
        clearBtn.innerHTML = '&times;';
        clearBtn.style.cssText = 'position:absolute;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;line-height:1;padding:4px 6px;color:#9ca3af;z-index:1;display:none;';
        if (isRtl) {
            clearBtn.style.left = '2.2rem';
        } else {
            clearBtn.style.right = '2.2rem';
        }
        clearBtn.addEventListener('mouseenter', function() { clearBtn.style.color = '#6b7280'; });
        clearBtn.addEventListener('mouseleave', function() { clearBtn.style.color = '#9ca3af'; });

        function updateClearBtn() {
            if (!isRequired && select.value) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }
        }

        // Build dropdown
        var dropdown = document.createElement('div');
        dropdown.id = id + '-listbox';
        dropdown.setAttribute('role', 'listbox');
        dropdown.style.cssText = 'position:absolute;left:0;right:0;z-index:1050;overflow-y:auto;overflow-x:hidden;overscroll-behavior:contain;max-height:250px;display:none;border-radius:0.375rem;border:1px solid;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1),0 4px 6px -4px rgba(0,0,0,0.1);';
        dropdown.style.minWidth = '200px';

        // Live region for screen readers
        var liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('role', 'status');
        liveRegion.style.cssText = 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;';

        // Apply dark/light styles
        function applyThemeStyles() {
            var isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                dropdown.style.backgroundColor = '#1e1e1e';
                dropdown.style.borderColor = '#2d2d30';
            } else {
                dropdown.style.backgroundColor = '#ffffff';
                dropdown.style.borderColor = '#e5e7eb';
            }
        }
        applyThemeStyles();

        // Watch for theme changes
        var themeObserver = new MutationObserver(function() {
            applyThemeStyles();
        });
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        // Assemble DOM
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(input);
        wrapper.appendChild(clearBtn);
        wrapper.appendChild(dropdown);
        wrapper.appendChild(liveRegion);
        wrapper.appendChild(select);
        select.style.display = 'none';
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');

        // State
        var isOpen = false;
        var highlightedIndex = -1;
        var filteredOptions = [];
        var previousValue = select.value;
        var previousText = currentText;

        function filterOptions(query) {
            if (!query) {
                filteredOptions = [];
                for (var i = 0; i < options.length; i++) {
                    filteredOptions.push(options[i]);
                }
                return;
            }
            var normalizedQuery = normalizeText(query);
            var prefix = [];
            var substring = [];
            for (var i = 0; i < options.length; i++) {
                var opt = options[i];
                if (opt.separator) continue;
                var normalizedText = normalizeText(opt.text);
                if (normalizedText.indexOf(normalizedQuery) === 0) {
                    prefix.push(opt);
                } else if (normalizedText.indexOf(normalizedQuery) > 0) {
                    substring.push(opt);
                }
            }
            filteredOptions = prefix.concat(substring);
        }

        function renderDropdown(query) {
            dropdown.innerHTML = '';
            var isDark = document.documentElement.classList.contains('dark');
            var count = 0;

            for (var i = 0; i < filteredOptions.length; i++) {
                var opt = filteredOptions[i];
                var item = document.createElement('div');
                item.setAttribute('role', 'option');
                item.setAttribute('id', id + '-opt-' + i);
                item.setAttribute('aria-selected', opt.value === select.value ? 'true' : 'false');
                item.dataset.index = i;

                if (opt.separator) {
                    item.style.cssText = 'border-bottom:1px solid ' + (isDark ? '#2d2d30' : '#e5e7eb') + ';height:1px;margin:4px 0;pointer-events:none;';
                    item.setAttribute('aria-disabled', 'true');
                } else if (opt.disabled) {
                    item.style.cssText = 'padding:0.5rem 0.75rem;min-height:44px;display:flex;align-items:center;font-size:0.875rem;color:' + (isDark ? '#9ca3af' : '#9ca3af') + ';cursor:default;';
                    item.setAttribute('aria-disabled', 'true');
                    item.innerHTML = escapeHtml(opt.text);
                } else {
                    item.style.cssText = 'padding:0.5rem 0.75rem;min-height:44px;display:flex;align-items:center;justify-content:space-between;font-size:0.875rem;color:' + (isDark ? '#d1d5db' : '#111827') + ';cursor:pointer;';
                    var labelHtml = '<span style="flex:1;min-width:0">' + highlightMatch(opt.text, query) + '</span>';
                    if (opt.value === select.value) {
                        labelHtml += '<svg style="flex-shrink:0;width:16px;height:16px;margin-' + (isRtl ? 'right' : 'left') + ':8px;color:#4E81FA" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    }
                    item.innerHTML = labelHtml;
                    count++;
                }
                dropdown.appendChild(item);
            }

            if (count === 0) {
                var noResults = document.createElement('div');
                noResults.style.cssText = 'padding:0.75rem;text-align:center;font-size:0.875rem;color:' + (isDark ? '#9ca3af' : '#9ca3af') + ';';
                noResults.textContent = noResultsText;
                dropdown.appendChild(noResults);
                liveRegion.textContent = noResultsText;
            } else {
                liveRegion.textContent = count + ' result' + (count !== 1 ? 's' : '') + ' available';
            }
        }

        function getSelectableIndex(startIndex, direction) {
            var idx = startIndex;
            while (idx >= 0 && idx < filteredOptions.length) {
                var opt = filteredOptions[idx];
                if (!opt.disabled && !opt.separator) return idx;
                idx += direction;
            }
            return -1;
        }

        function setHighlight(idx) {
            var items = dropdown.querySelectorAll('[role="option"]');
            for (var i = 0; i < items.length; i++) {
                var isDark = document.documentElement.classList.contains('dark');
                var opt = filteredOptions[i];
                if (parseInt(items[i].dataset.index) === idx) {
                    items[i].style.backgroundColor = isDark ? '#2d2d30' : '#f3f4f6';
                    items[i].scrollIntoView({ block: 'nearest' });
                    input.setAttribute('aria-activedescendant', items[i].id);
                } else if (!opt.separator && !opt.disabled) {
                    items[i].style.backgroundColor = 'transparent';
                }
            }
            highlightedIndex = idx;
        }

        function positionDropdown() {
            var rect = input.getBoundingClientRect();
            var spaceBelow = window.innerHeight - rect.bottom - 10;
            var spaceAbove = rect.top - 10;
            var maxH;

            if (spaceBelow >= 200 || spaceBelow >= spaceAbove) {
                dropdown.style.top = '100%';
                dropdown.style.bottom = 'auto';
                dropdown.style.marginTop = '4px';
                dropdown.style.marginBottom = '0';
                maxH = Math.max(150, Math.min(250, spaceBelow));
            } else {
                dropdown.style.top = 'auto';
                dropdown.style.bottom = '100%';
                dropdown.style.marginTop = '0';
                dropdown.style.marginBottom = '4px';
                maxH = Math.max(150, Math.min(250, spaceAbove));
            }
            dropdown.style.maxHeight = maxH + 'px';
        }

        function openDropdown(query) {
            filterOptions(query || '');
            renderDropdown(query || '');
            positionDropdown();
            dropdown.style.display = 'block';

            if (!reducedMotion) {
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(-4px)';
                dropdown.style.transition = 'opacity 100ms ease, transform 100ms ease';
                requestAnimationFrame(function() {
                    dropdown.style.opacity = '1';
                    dropdown.style.transform = 'translateY(0)';
                });
            }

            isOpen = true;
            input.setAttribute('aria-expanded', 'true');
            highlightedIndex = -1;

            // Highlight current value
            for (var i = 0; i < filteredOptions.length; i++) {
                if (filteredOptions[i].value === select.value && !filteredOptions[i].disabled) {
                    setHighlight(i);
                    break;
                }
            }
        }

        function closeDropdown() {
            dropdown.style.display = 'none';
            dropdown.style.transition = '';
            isOpen = false;
            input.setAttribute('aria-expanded', 'false');
            input.setAttribute('aria-activedescendant', '');
            highlightedIndex = -1;
        }

        function acceptSelection(idx) {
            if (idx >= 0 && idx < filteredOptions.length) {
                var opt = filteredOptions[idx];
                if (!opt.disabled && !opt.separator) {
                    selectValue(opt.value, opt.text);
                }
            }
        }

        function selectValue(value, text) {
            select.value = value;
            input.value = text;
            previousValue = value;
            previousText = text;
            closeDropdown();
            updateClearBtn();

            // Dispatch events for Vue v-model sync
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function revertInput() {
            input.value = previousText;
            closeDropdown();
        }

        // Event handlers
        input.addEventListener('mousedown', function(e) {
            if (isOpen) {
                closeDropdown();
            } else {
                openDropdown();
                input.select();
            }
        });

        input.addEventListener('input', function() {
            var query = input.value;
            if (!isOpen) {
                openDropdown(query);
            } else {
                filterOptions(query);
                renderDropdown(query);
                positionDropdown();
            }
            // Auto-highlight first selectable result
            var first = getSelectableIndex(0, 1);
            if (first >= 0) {
                setHighlight(first);
            }
        });

        input.addEventListener('keydown', function(e) {
            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    if (!isOpen) {
                        openDropdown();
                        var first = getSelectableIndex(0, 1);
                        if (first >= 0) setHighlight(first);
                    } else {
                        var next = getSelectableIndex(highlightedIndex + 1, 1);
                        if (next >= 0) setHighlight(next);
                    }
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    if (!isOpen) {
                        openDropdown();
                        var last = getSelectableIndex(filteredOptions.length - 1, -1);
                        if (last >= 0) setHighlight(last);
                    } else {
                        var prev = getSelectableIndex(highlightedIndex - 1, -1);
                        if (prev >= 0) setHighlight(prev);
                    }
                    break;

                case 'Enter':
                    if (isOpen) {
                        e.preventDefault();
                        if (highlightedIndex >= 0) {
                            acceptSelection(highlightedIndex);
                        }
                    }
                    break;

                case 'Escape':
                    if (isOpen) {
                        e.preventDefault();
                        e.stopPropagation();
                        revertInput();
                    }
                    break;

                case 'Tab':
                    if (isOpen) {
                        if (highlightedIndex >= 0) {
                            acceptSelection(highlightedIndex);
                        } else {
                            revertInput();
                        }
                    }
                    break;
            }
        });

        input.addEventListener('focus', function() {
            // Focus via Tab: don't auto-open, let user press Down or type
        });

        input.addEventListener('blur', function(e) {
            // Delay to allow click on dropdown option
            setTimeout(function() {
                if (isOpen && !wrapper.contains(document.activeElement)) {
                    revertInput();
                }
            }, 200);
        });

        // Dropdown click handler
        dropdown.addEventListener('mousedown', function(e) {
            // Prevent blur on input
            e.preventDefault();
        });

        dropdown.addEventListener('click', function(e) {
            var target = e.target.closest('[role="option"]');
            if (!target) return;
            var idx = parseInt(target.dataset.index);
            if (isNaN(idx)) return;
            var opt = filteredOptions[idx];
            if (opt && !opt.disabled && !opt.separator) {
                acceptSelection(idx);
                input.focus();
            }
        });

        // Hover highlighting
        dropdown.addEventListener('mousemove', function(e) {
            var target = e.target.closest('[role="option"]');
            if (!target) return;
            var idx = parseInt(target.dataset.index);
            if (isNaN(idx)) return;
            var opt = filteredOptions[idx];
            if (opt && !opt.disabled && !opt.separator) {
                setHighlight(idx);
            }
        });

        // Clear button
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            // Find the empty/default option or set to empty
            select.value = '';
            input.value = '';
            previousValue = '';
            previousText = '';
            updateClearBtn();
            select.dispatchEvent(new Event('change', { bubbles: true }));
            select.dispatchEvent(new Event('input', { bubbles: true }));
            openDropdown();
            input.focus();
        });

        // Close on outside click
        document.addEventListener('mousedown', function(e) {
            if (isOpen && !wrapper.contains(e.target)) {
                revertInput();
            }
        });

        // Close on scroll (except within dropdown)
        window.addEventListener('scroll', function(e) {
            if (isOpen && !dropdown.contains(e.target)) revertInput();
        }, true);

        // Watch for Vue v-for option changes via MutationObserver
        var optionObserver = new MutationObserver(function() {
            readOptions();
            var newText = getSelectedText();
            if (newText !== previousText) {
                previousText = newText;
                previousValue = select.value;
                input.value = newText;
                updateClearBtn();
            }
        });
        optionObserver.observe(select, { childList: true, subtree: true });

        // Watch for programmatic value changes on select
        var valueDescriptor = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
        if (valueDescriptor && valueDescriptor.set) {
            Object.defineProperty(select, 'value', {
                get: function() {
                    return valueDescriptor.get.call(this);
                },
                set: function(val) {
                    valueDescriptor.set.call(this, val);
                    var newText = getSelectedText();
                    input.value = newText;
                    previousValue = val;
                    previousText = newText;
                    updateClearBtn();
                },
                configurable: true
            });
        }

        // Watch for programmatic selectedIndex changes on select
        var idxDescriptor = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'selectedIndex');
        if (idxDescriptor && idxDescriptor.set) {
            Object.defineProperty(select, 'selectedIndex', {
                get: function() {
                    return idxDescriptor.get.call(this);
                },
                set: function(val) {
                    idxDescriptor.set.call(this, val);
                    var newText = getSelectedText();
                    input.value = newText;
                    previousValue = select.value;
                    previousText = newText;
                    updateClearBtn();
                },
                configurable: true
            });
        }

        // Watch for v-show on select's original display context
        // If select was inside a v-show/v-if container, observe wrapper visibility
        var parentObserver = new MutationObserver(function() {
            // Check if wrapper's parent is hidden
            if (wrapper.offsetParent === null && wrapper.style.display !== 'none') {
                closeDropdown();
            }
        });
        if (wrapper.parentElement) {
            parentObserver.observe(wrapper.parentElement, { attributes: true, attributeFilter: ['style', 'class'] });
        }

        // Copy over any id from select to make labels work
        if (select.id) {
            var labelFor = document.querySelector('label[for="' + select.id + '"]');
            if (labelFor) {
                var inputId = select.id + '-search';
                input.id = inputId;
                labelFor.setAttribute('for', inputId);
            }
        }

        updateClearBtn();

        // If select is disabled, disable input too
        if (select.disabled) {
            input.disabled = true;
            clearBtn.style.display = 'none';
        }

        // Watch for disabled state changes
        var disabledObserver = new MutationObserver(function() {
            input.disabled = select.disabled;
            isRequired = select.required || select.hasAttribute('required');
            if (select.disabled) {
                clearBtn.style.display = 'none';
                closeDropdown();
            } else {
                updateClearBtn();
            }
        });
        disabledObserver.observe(select, { attributes: true, attributeFilter: ['disabled', 'required'] });
    }

    function initAll() {
        var selects = document.querySelectorAll('select[data-searchable]');
        for (var i = 0; i < selects.length; i++) {
            createSearchableSelect(selects[i]);
        }
    }

    // Init on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    // Re-init for dynamically added selects (Vue, etc.)
    var bodyObserver = new MutationObserver(function(mutations) {
        for (var i = 0; i < mutations.length; i++) {
            var added = mutations[i].addedNodes;
            for (var j = 0; j < added.length; j++) {
                if (added[j].nodeType === 1) {
                    if (added[j].matches && added[j].matches('select[data-searchable]')) {
                        createSearchableSelect(added[j]);
                    }
                    var nested = added[j].querySelectorAll && added[j].querySelectorAll('select[data-searchable]');
                    if (nested) {
                        for (var k = 0; k < nested.length; k++) {
                            createSearchableSelect(nested[k]);
                        }
                    }
                }
            }
        }
    });

    if (document.body) {
        bodyObserver.observe(document.body, { childList: true, subtree: true });
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            bodyObserver.observe(document.body, { childList: true, subtree: true });
        });
    }
})();
