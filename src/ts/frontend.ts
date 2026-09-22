interface TooltipManagerType {
    /**
     * Show the tooltip for a given swatch.
     * @param swatch - The swatch element to display the tooltip for.
     * @returns void
     */
    show(swatch: HTMLElement): void;

    /**
     * Hide the tooltip.
     * @returns void
     */
    hide(): void;
}


document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector<HTMLFormElement>('.variations_form');
    if (!form) return;

    const selectEls = form.querySelectorAll<HTMLSelectElement>('select');
    const labels = form.querySelectorAll<HTMLLabelElement>('th label');
    const swatchContainers = form.querySelectorAll<HTMLElement>('.ssasfw-swatch-container');
    const selectedOptions = new Map<string, string>();

    // Check if jQuery is available in the global scope
    const jQueryFn = (window as Window & typeof globalThis & { jQuery?: any }).jQuery;


    // Initialize labels with colons and span elements
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


    /**
     * Handles the display and positioning of the tooltip for swatches.
     */
    const TooltipManager: TooltipManagerType | null = (() => {
        const tooltip = document.getElementById('ssasfw-tooltip');
        if (!tooltip) return null;

        const thumb = tooltip.querySelector<HTMLElement>('.ssasfw-tooltip-thumbnail');
        const label = tooltip.querySelector<HTMLElement>('.ssasfw-tooltip-label');
        const arrow = tooltip.querySelector<HTMLElement>('.ssasfw-tooltip-arrow');

        return {
            show(swatch: HTMLElement): void {
                if (!swatch || swatch.classList.contains('disabled')) {
                    this.hide();
                    return;
                }

                const image = swatch.querySelector<HTMLImageElement>('img.image-swatch');
                const color = swatch.querySelector<HTMLDivElement>('div.color-swatch');

                if (!image && !color) return; // Only proceed if dealing with images or colors

                const rect = swatch.getBoundingClientRect();

                if (label) label.textContent = swatch.dataset['label'] || '';
                if (thumb) thumb.innerHTML = image?.outerHTML || color?.outerHTML || '';

                const margin = 10;
                const padding = 10;

                const viewportWidth = document.documentElement.clientWidth;

                // If tooltip is wider than the viewport, resize it to fit
                tooltip.style.width = '';
                if ((tooltip.offsetWidth + (2 * padding)) > viewportWidth) {
                    tooltip.style.width = `${viewportWidth - (2 * padding)}px`;
                }

                // Calculate vertical position (above swatch, with margin)
                let tooltipTop = rect.top - tooltip.offsetHeight - margin;

                // Calculate horizontal position (centered, clamped to viewport)
                const tooltipLeft = Math.max(
                    padding,
                    Math.min(
                        rect.left + rect.width / 2 - tooltip.offsetWidth / 2,
                        viewportWidth - tooltip.offsetWidth - padding
                    )
                );

                // Center the arrow on top of the swatch regardless of tooltip position
                const arrowLeft = rect.left + rect.width / 2 - tooltipLeft;
                if (arrow) arrow.style.left = `${arrowLeft}px`;

                // Apply styles and show tooltip
                tooltip.style.top = `${tooltipTop}px`;
                tooltip.style.left = `${tooltipLeft}px`;
                tooltip.classList.add('ssasfw-tooltip-visible');
                tooltip.removeAttribute('aria-hidden');
            },

            hide(): void {
                tooltip.classList.remove('ssasfw-tooltip-visible');
                tooltip.setAttribute('aria-hidden', 'true');
            }
        };
    })();

    // Add tooltip event handlers
    if (TooltipManager) {
        form.addEventListener('mouseenter', (e: Event) => {
            if (window.matchMedia('(hover: hover)').matches) {
                const swatch = (e.target as Element)?.closest<HTMLElement>('.ssasfw-swatch-wrapper');
                if (swatch && !swatch.classList.contains('disabled')) {
                    TooltipManager.show(swatch);
                }
            }
        }, true);

        form.addEventListener('mouseleave', (e: Event) => {
            if ((e.target as Element)?.closest('.ssasfw-swatch-wrapper')) {
                TooltipManager.hide();
            }
        }, true);

        window.addEventListener('scroll', () => TooltipManager.hide());
    }


    /**
     * Update the label for a given attribute with the selected option text.
     * @param attribute - The attribute name (e.g., "pa_color").
     * @param text - The text to display in the label.
     * @returns void
     */
    const updateAttributeLabel = (attribute: string, text: string): void => {
        const label = Array.from(labels).find(l => l.getAttribute('for') === attribute);
        const span = label?.parentElement?.querySelector('span.selected-option-label');
        if (span) span.textContent = text;
    };

    /**
     * Get the swatch element for a given attribute and slug.
     * @param attribute - The attribute name (e.g., "pa_color").
     * @param slug - The slug of the swatch to find.
     * @returns The swatch element or null if not found.
     */
    const getSwatch = (attribute: string, slug: string): HTMLElement | null => {
        const container = Array.from(swatchContainers).find(c => c.classList.contains(attribute));
        if (!container) return null;

        return container.querySelector(`.ssasfw-swatch-wrapper[data-slug="${slug}"]`);
    };


    /**
     * Handle select changes.
     * @param select - The select element that changed.
     * @returns void
     */
    const handleSelectChange = (select: HTMLSelectElement): void => {
        if (select.value) {
            const selected = select.options[select.selectedIndex];
            updateAttributeLabel(select.id, selected ? selected.text : '');
            selectedOptions.set(select.id, select.value);
        } else {
            updateAttributeLabel(select.id, '');
            selectedOptions.delete(select.id);
        }
    };
    // Initialize select elements with their current values
    selectEls.forEach((select) => {
        if (select.value) handleSelectChange(select);
        select.addEventListener('change', () => handleSelectChange(select));
    });


    /**
     * Handle swatch clicks.
     * @param e - The mouse event.
     * @returns void
     */
    const handleClick = (e: MouseEvent): void => {
        const target = e.target as HTMLElement | null;
        const swatch = target?.closest<HTMLElement>('.ssasfw-swatch-wrapper');

        if (!swatch || swatch.classList.contains('disabled')) return;

        const selectElement = swatch.closest('td')?.querySelector<HTMLSelectElement>('select');
        if (!selectElement) return;

        const attribute = selectElement.id;
        const slug = swatch.dataset['slug'];
        if (!slug) return;

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

        if (TooltipManager) TooltipManager.show(swatch);
    };
    form.addEventListener('click', handleClick);


    /**
     * Synchronize swatch availability with WooCommerce select options.
     * Only handles disabled/enabled states.
     * @returns void
     */
    const syncSwatchesState = (): void => {
        selectEls.forEach((select) => {
            const attribute = select.id;
            const container = Array.from(swatchContainers).find(c => c.classList.contains(attribute));
            if (!container) return;

            // Gather available option values from select element
            const validOptions = new Set<string>();
            Array.from(select.options).forEach((opt) => {
                if (opt.value && !opt.disabled) {
                    validOptions.add(opt.value);
                }
            });

            const swatches = container.querySelectorAll<HTMLElement>('.ssasfw-swatch-wrapper');

            swatches.forEach((swatch) => {
                const slug = swatch.dataset['slug'];
                const isAvailable = slug ? validOptions.has(slug) : false;

                if (!isAvailable) {
                    swatch.classList.add('disabled');
                    swatch.setAttribute('aria-disabled', 'true');
                } else {
                    swatch.classList.remove('disabled');
                    swatch.removeAttribute('aria-disabled');
                }
            });
        });
    };
    syncSwatchesState(); // Initial state sync on page load
    if (jQueryFn) {
        jQueryFn(form).on('woocommerce_variation_has_changed woocommerce_update_variation_values', syncSwatchesState);
    }


    /**
     * Handle form reset.
     * @returns void
     */
    const handleReset = (): void => {
        selectEls.forEach((select) => {
            select.value = '';
            updateAttributeLabel(select.id, '');
        });

        selectedOptions.clear();

        swatchContainers.forEach((container) => {
            const swatches = container.querySelectorAll<HTMLElement>('.ssasfw-swatch-wrapper');
            swatches.forEach((swatch) => {
                swatch.classList.remove('selected', 'disabled');
                swatch.removeAttribute('aria-selected');
                swatch.removeAttribute('aria-disabled');
            });
        });
    };
    form.querySelector('a.reset_variations')?.addEventListener('click', handleReset);
});
