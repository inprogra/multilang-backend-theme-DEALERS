export class ScrollToSection {

    static scroll(position) {
        const headerHeight = document.querySelector('.js-header__bar').offsetHeight;
        position -= headerHeight;
        var smoothScrollFeature = 'scrollBehavior' in document.documentElement.style;
        var i = parseInt(window.pageYOffset);
        if (i != position) {
            if (!smoothScrollFeature) {
                position = parseInt(position);
                if (i < position) {
                    var int = setInterval(function () {
                        if (i > (position - 20)) i += 1;
                        else if (i > (position - 40)) i += 3;
                        else if (i > (position - 80)) i += 8;
                        else if (i > (position - 160)) i += 18;
                        else if (i > (position - 200)) i += 24;
                        else if (i > (position - 300)) i += 40;
                        else i += 60;
                        window.scroll(0, i);
                        if (i >= position) clearInterval(int);
                    }, 15);
                } else {
                    var int = setInterval(function () {
                        if (i < (position + 20)) i -= 1;
                        else if (i < (position + 40)) i -= 3;
                        else if (i < (position + 80)) i -= 8;
                        else if (i < (position + 160)) i -= 18;
                        else if (i < (position + 200)) i -= 24;
                        else if (i < (position + 300)) i -= 40;
                        else i -= 60;
                        window.scroll(0, i);
                        if (i <= position) clearInterval(int);
                    }, 15);
                }
            } else {
                window.scroll({
                    top: position,
                    left: 0,
                    behavior: 'smooth'
                });
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const scrollToElements = Array.from(document.querySelectorAll('.js-scroll-to'));
    scrollToElements.forEach(element => {
        element.addEventListener('click', () => {
            const elementToScroll = document.querySelector(element.dataset.scrollToElement);
            if(elementToScroll) {
                ScrollToSection.scroll(elementToScroll.offsetTop);
            }
        });
    });
});