function switchDesktopTab() {

}

document.addEventListener('DOMContentLoaded', () => {
   const desktopTabsButtons = Array.from(document.querySelectorAll('.js-desktop-tabs__button'));
   desktopTabsButtons.forEach(button => {
       button.addEventListener('click', () => {
           const currentActiveButton = document.querySelector('.js-desktop-tabs__button.is-active');

           if(currentActiveButton) {
               currentActiveButton.classList.remove('is-active');
           }

           const currentActiveTab = document.querySelector('.js-desktop-tabs__category.is-active');

           if(currentActiveTab) {
               currentActiveTab.classList.remove('is-active');
           }

           button.classList.add('is-active');
           const tabNumber = button.dataset.category;
           const desktopTab = document.querySelector(`.js-desktop-tabs__category[data-category="${tabNumber}"]`);
           desktopTab.classList.add('is-active');
       });
   });
});