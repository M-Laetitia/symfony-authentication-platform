import { Controller } from '@hotwired/stimulus'
import EmblaCarousel from 'embla-carousel'

export default class extends Controller {
    static targets = ['viewport', 'dots']

    connect() {
        console.log('Carousel controller connected!', this.element)
        
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
        
        console.log('Dots container:', this.dotsTarget)
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
        console.log('Building', count, 'dots')

        this.dotsTarget.innerHTML = Array.from({ length: count })
            .map((_, i) => `<button type="button" class="embla__dot" data-index="${i}" aria-label="Go to slide ${i + 1}"></button>`)
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
        })
    }
}
