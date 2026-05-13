import { Controller } from '@hotwired/stimulus'
import EmblaCarousel from 'embla-carousel'

export default class extends Controller {
    static targets = ['viewport', 'dots']

    connect() {
        
        this.embla = EmblaCarousel(this.viewportTarget, {
            loop: true,
            align: 'start',
            dragFree: false,
            skipSnaps: false,
            containScroll: 'trimSnaps',
            slidesToScroll: 1
        })

        this.buildDots()
        this.bindEvents()
        
    }

    disconnect() {
        if (this.embla) {
            this.embla.destroy()
        }
    }

    prev() {
        this.embla.scrollPrev()
    }

    next() {
        this.embla.scrollNext()
    }

    buildDots() {
        const count = this.embla.scrollSnapList().length

        this.dotsTarget.innerHTML = Array.from({ length: count })
            .map((_, i) => `<button type="button" class="embla__dot" data-index="${i}" role="tab" aria-selected="false" aria-label="Go to slide ${i + 1}"></button>`)
            .join('')

        this.dotsTarget.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', () => {
                this.embla.scrollTo(Number(btn.dataset.index))
            })
        })
    }

    bindEvents() {
        this.embla.on('select', () => this.updateDots())
        this.embla.on('reInit', () => this.updateDots())

        this.updateDots()
    }

    updateDots() {
        const index = this.embla.selectedScrollSnap()

        this.dotsTarget.querySelectorAll('button').forEach((btn, i) => {
            btn.classList.toggle('is-active', i === index)
            btn.setAttribute('aria-selected', i === index ? 'true' : 'false')
        })
    }
}
