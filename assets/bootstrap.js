import { Application } from '@hotwired/stimulus'
import CarouselController from './controllers/carousel_controller.js'

const application = Application.start()

// Register controllers
application.register('carousel', CarouselController)

export { application }
