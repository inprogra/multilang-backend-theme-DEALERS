export function toggleSlide(toggledElement, toggleContainer, toggleContainerMaxHeightProvider) {
    toggledElement.classList.toggle('is-active');
    const isItemActive = toggledElement.classList.contains('is-active');

    if (isItemActive) {
        toggleContainer.style.maxHeight = toggleContainerMaxHeightProvider() + 'px';
    } else {
        toggleContainer.style.removeProperty('max-height');
        toggleContainer.style.removeProperty('overflow');
    }

    toggleContainer.ontransitionend = () => {
        if (isItemActive) {
            toggleContainer.style.overflow = 'unset';
        }
    };
}