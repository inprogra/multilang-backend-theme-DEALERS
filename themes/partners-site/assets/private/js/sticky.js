class Sticky {
    constructor(el) {
        this.animate = this.animate.bind(this)

        this.initialized = false
        this.parent = el
        this.el = el.querySelector('.js-sticky__element')
        this.mediaQuery = this.parent.dataset.stickyMediaQuery
        this.offest = {
            // top: parseInt(this.parent.dataset.stickyOffsetTop) || 0,
            bottom: parseInt(this.parent.dataset.stickyOffsetBottom) || 0
        }
        this.mode = this.parent.dataset.stickyMode || 'top'
        this.position = null
        this.positions = {
            0: 'is-sticky-over',
            1: 'is-sticky',
            2: 'is-sticky-under'
        }

        if (this.mediaQuery) {
            this.init()
            window.addEventListener('resize', () => {
                this.init()
            })
        }
    }

    init() {
        if (window.matchMedia(this.mediaQuery).matches) {
            this.create()
        } else {
            this.destroy()
        }
    }

    create() {
        this.parent.classList.add('is-sticky-initialized')

        this.elHeight = this.el.offsetHeight
        this.headerHeight = document.querySelector('.js-header__bar').offsetHeight
        if (document.querySelector('body').classList.contains('admin-bar')) {
            this.headerHeight += 32;
        }

        this.animate()
        document.addEventListener('scroll', this.animate, {passive: true})

        if (this.initialized && this.position === 1) {
            const parentPosition = this.parent.getBoundingClientRect()
            this.el.style.left = parentPosition.left + 'px'
            this.el.style.right = document.body.clientWidth - parentPosition.right + 'px'
        }

        this.initialized = true
    }

    destroy() {
        this.position = null
        this.parent.classList.remove('is-sticky-initialized')
        if (this.initialized) {
            document.removeEventListener('scroll', this.animate, {passive: true})
            this.el.style.removeProperty('left')
            this.el.style.removeProperty('right')
            Object.values(this.positions).forEach((position) => {
                this.parent.classList.remove(position)
            })
        }
        this.initialized = false
    }

    animate() {
        const parentPosition = this.parent.getBoundingClientRect()
        let isAbove, isBelow

        if (this.mode === 'top') {
            isAbove = parentPosition.top - this.headerHeight > 0;
            isBelow = parentPosition.bottom - this.headerHeight - this.el.offsetHeight < 0
        } else if (this.mode === 'bottom') {
            isAbove = parentPosition.top - window.innerHeight + this.el.offsetHeight + this.offest.bottom > 0;
            isBelow = parentPosition.bottom - window.innerHeight + this.offest.bottom < 0
        }

        if (isAbove) {
            this.transitPosition(0)
        } else if (!isAbove && !isBelow) {
            this.transitPosition(1)
        } else if (isBelow) {
            this.transitPosition(2)
        }
    }


    transitPosition(newPosition) {
        if (this.position !== newPosition) {
            if (this.position === 1 && newPosition !== 1) {
                this.el.style.removeProperty('left')
                this.el.style.removeProperty('right')
            } else if (this.position !== 1 && newPosition === 1) {
                const parentPosition = this.parent.getBoundingClientRect()
                this.el.style.left = parentPosition.left + 'px'
                this.el.style.right = document.body.clientWidth - parentPosition.right + 'px'
            }

            Object.values(this.positions).forEach((position) => {
                this.parent.classList.remove(position)
            })

            this.parent.classList.add(this.positions[newPosition])
            this.position = newPosition
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let element = document.querySelectorAll('.js-sticky')
    element.forEach((el) => {
        new Sticky(el);
    })
});
