"use strict";
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.variations_form');
    if (!form)
        return;
    const selectEls = form.querySelectorAll('select');
    const labels = form.querySelectorAll('th label');
    const swatchContainers = form.querySelectorAll('.ssasfw-swatch-container');
    const selectedOptions = new Map();
    const jQueryFn = window.jQuery;
    labels.forEach((label) => {
        if (label.textContent && !label.textContent.includes(':')) {
            label.textContent += ': ';
        }
        let span = label.parentElement?.querySelector('span.selected-option-label');
        if (!span) {
            span = document.createElement('span');
            span.className = 'selected-option-label';
            label.after(span);
        }
    });
    const TooltipManager = (() => {
        const tooltip = document.getElementById('ssasfw-tooltip');
        if (!tooltip)
            return null;
        const thumb = tooltip.querySelector('.ssasfw-tooltip-thumbnail');
        const label = tooltip.querySelector('.ssasfw-tooltip-label');
        const arrow = tooltip.querySelector('.ssasfw-tooltip-arrow');
        return {
            show(swatch) {
                if (!swatch || swatch.classList.contains('disabled')) {
                    this.hide();
                    return;
                }
                const image = swatch.querySelector('img.image-swatch');
                const color = swatch.querySelector('div.color-swatch');
                if (!image && !color)
                    return;
                const rect = swatch.getBoundingClientRect();
                if (label)
                    label.textContent = swatch.dataset['label'] || '';
                if (thumb)
                    thumb.innerHTML = image?.outerHTML || color?.outerHTML || '';
                const margin = 10;
                const padding = 10;
                const viewportWidth = document.documentElement.clientWidth;
                tooltip.style.width = '';
                if ((tooltip.offsetWidth + (2 * padding)) > viewportWidth) {
                    tooltip.style.width = `${viewportWidth - (2 * padding)}px`;
                }
                let tooltipTop = rect.top - tooltip.offsetHeight - margin;
                const tooltipLeft = Math.max(padding, Math.min(rect.left + rect.width / 2 - tooltip.offsetWidth / 2, viewportWidth - tooltip.offsetWidth - padding));
                const arrowLeft = rect.left + rect.width / 2 - tooltipLeft;
                if (arrow)
                    arrow.style.left = `${arrowLeft}px`;
                tooltip.style.top = `${tooltipTop}px`;
                tooltip.style.left = `${tooltipLeft}px`;
                tooltip.classList.add('ssasfw-tooltip-visible');
                tooltip.removeAttribute('aria-hidden');
            },
            hide() {
                tooltip.classList.remove('ssasfw-tooltip-visible');
                tooltip.setAttribute('aria-hidden', 'true');
            }
        };
    })();
    if (TooltipManager) {
        form.addEventListener('mouseenter', (e) => {
            if (window.matchMedia('(hover: hover)').matches) {
                const swatch = e.target?.closest('.ssasfw-swatch-wrapper');
                if (swatch && !swatch.classList.contains('disabled')) {
                    TooltipManager.show(swatch);
                }
            }
        }, true);
        form.addEventListener('mouseleave', (e) => {
            if (e.target?.closest('.ssasfw-swatch-wrapper')) {
                TooltipManager.hide();
            }
        }, true);
        window.addEventListener('scroll', () => TooltipManager.hide());
    }
    const updateAttributeLabel = (attribute, text) => {
        const label = Array.from(labels).find(l => l.getAttribute('for') === attribute);
        const span = label?.parentElement?.querySelector('span.selected-option-label');
        if (span)
            span.textContent = text;
    };
    const getSwatch = (attribute, slug) => {
        const container = Array.from(swatchContainers).find(c => c.classList.contains(attribute));
        if (!container)
            return null;
        return container.querySelector(`.ssasfw-swatch-wrapper[data-slug="${slug}"]`);
    };
    const handleSelectChange = (select) => {
        if (select.value) {
            const selected = select.options[select.selectedIndex];
            updateAttributeLabel(select.id, selected ? selected.text : '');
            selectedOptions.set(select.id, select.value);
        }
        else {
            updateAttributeLabel(select.id, '');
            selectedOptions.delete(select.id);
        }
    };
    selectEls.forEach((select) => {
        if (select.value)
            handleSelectChange(select);
        select.addEventListener('change', () => handleSelectChange(select));
    });
    const handleClick = (e) => {
        const target = e.target;
        const swatch = target?.closest('.ssasfw-swatch-wrapper');
        if (!swatch || swatch.classList.contains('disabled'))
            return;
        const selectElement = swatch.closest('td')?.querySelector('select');
        if (!selectElement)
            return;
        const attribute = selectElement.id;
        const slug = swatch.dataset['slug'];
        if (!slug)
            return;
        const currentSelected = selectedOptions.get(attribute);
        if (currentSelected !== slug) {
            if (currentSelected) {
                const currentSwatch = getSwatch(attribute, currentSelected);
                currentSwatch?.classList.remove('selected');
                currentSwatch?.removeAttribute('aria-selected');
            }
            swatch.classList.add('selected');
            swatch.setAttribute('aria-selected', 'true');
            selectElement.value = slug;
            selectElement.dispatchEvent(new Event('change', { bubbles: true }));
        }
        if (TooltipManager)
            TooltipManager.show(swatch);
    };
    form.addEventListener('click', handleClick);
    const syncSwatchesState = () => {
        selectEls.forEach((select) => {
            const attribute = select.id;
            const container = Array.from(swatchContainers).find(c => c.classList.contains(attribute));
            if (!container)
                return;
            const validOptions = new Set();
            Array.from(select.options).forEach((opt) => {
                if (opt.value && !opt.disabled) {
                    validOptions.add(opt.value);
                }
            });
            const swatches = container.querySelectorAll('.ssasfw-swatch-wrapper');
            swatches.forEach((swatch) => {
                const slug = swatch.dataset['slug'];
                const isAvailable = slug ? validOptions.has(slug) : false;
                if (!isAvailable) {
                    swatch.classList.add('disabled');
                    swatch.setAttribute('aria-disabled', 'true');
                }
                else {
                    swatch.classList.remove('disabled');
                    swatch.removeAttribute('aria-disabled');
                }
            });
        });
    };
    syncSwatchesState();
    if (jQueryFn) {
        jQueryFn(form).on('woocommerce_variation_has_changed woocommerce_update_variation_values', syncSwatchesState);
    }
    const handleReset = () => {
        selectEls.forEach((select) => {
            select.value = '';
            updateAttributeLabel(select.id, '');
        });
        selectedOptions.clear();
        swatchContainers.forEach((container) => {
            const swatches = container.querySelectorAll('.ssasfw-swatch-wrapper');
            swatches.forEach((swatch) => {
                swatch.classList.remove('selected', 'disabled');
                swatch.removeAttribute('aria-selected');
                swatch.removeAttribute('aria-disabled');
            });
        });
    };
    form.querySelector('a.reset_variations')?.addEventListener('click', handleReset);
});
//# sourceMappingURL=frontend.js.map