import tippy from 'tippy.js';

export function initTooltips() {
    const tooltips = Array.from(document.querySelectorAll('.js-tooltip'));
    tooltips.forEach(tooltip => {
        tippy(tooltip, {
            content: tooltip.dataset.tooltipContent,
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
});