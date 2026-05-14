import gsap from 'gsap';
import ScrollTrigger from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);


/* &------------ HOME --------------- */
/* &--------------------------------- */

if (document.querySelector('#home-page')) {
    console.log('Animations for #home-page initialized');

    // Animate each .section-intro
    gsap.utils.toArray('.section-intro').forEach((intro, index) => {
        const title = intro.querySelector('.section-intro__title');
        const subtitle = intro.querySelector('.section-intro__subtitle');
        const underline = intro.querySelector('.section-intro__underline');
        const action = intro.querySelector('.section-intro__action');

        // Title 
        gsap.from(title, {
            opacity: 0,
            y: 50,
            duration: 0.8,
            scrollTrigger: {
                trigger: intro,
                start: 'top 80%',
            },
        });

        // Subtitle 
        gsap.from(subtitle, {
            opacity: 0,
            y: 50,
            duration: 0.8,
            delay: 0.2, 
            scrollTrigger: {
                trigger: intro,
                start: 'top 80%',
            },
        });

        // Underline 
        gsap.from(underline, {
            scaleX: 0,
            transformOrigin: 'right center', 
            duration: 0.8,
            delay: 0.4, 
            scrollTrigger: {
                trigger: intro,
                start: 'top 80%',
            },
        });

        // Button 
        if (action) {
            gsap.from(action, {
                opacity: 0,
                y: 50,
                duration: 0.8,
                delay: 0.6, 
                scrollTrigger: {
                    trigger: intro,
                    start: 'top 80%',
                },
            });
        }

        gsap.to(intro, {
            opacity: 1,
            y: 0,
            duration: 1,
            scrollTrigger: {
                trigger: intro, 
                start: 'top 80%',
            },
        });
    });

    /* &----------- ABOUT US ------------ */
    // Text
    gsap.to('.section-about__text', {
        opacity: 1,
        y: 0,
        duration: 1.2,
        scrollTrigger: {
            trigger: '.section-about',
            start: 'top 30%'
        }
    });

    // Image
    gsap.to('.section-about__image-wrapper', {
        opacity: 1,
        x: 0,
        duration: 1.2,
        delay: 0.5,
        scrollTrigger: {
            trigger: '.section-about',
            start: 'top 30%'
        }
    });

    // Service box
    gsap.to('.section-about__feature', {
        opacity: 1,
        y: 0,
        duration: 1.2,
        stagger: 0.25,
        scrollTrigger: {
            trigger: '.section-about__features',
            start: 'top 80%'
        }
    });

    /* &----------- OUR SERVICES ------------ */
    gsap.to('.section-services__item', {
        opacity: 1,
        duration: 0.8,
        stagger: 0.2,
        scrollTrigger: {
            trigger: '.section-services__list',
            start: 'top 70%'
        }
    });

    /* &-------- OUR PHOTOGRAPHERS ---------- */
    // Apply animation to all slides
    gsap.utils.toArray('.embla__slide').forEach((slide, index) => {
        gsap.to(slide, {
            opacity: 1,
            y: 0,
            duration: 0.8,
            delay: index * 0.3, // Sequential delay for each slide
            scrollTrigger: {
                trigger: '.section-photographs',
                start: 'top 30%'
            }
        });
    });

    // Carousel navigation
    gsap.to('.carousel-nav', {
        opacity: 1,
        duration: 0.8,
        delay: 1,
        scrollTrigger: {
            trigger: '.section-photographs',
            start: 'top 30%'
        }
    });

    /* &-------- OUR LATEST WORKS ---------- */
    // Images transition
    gsap.to('.section-works .gallery__image', {
        filter: 'saturate(1)', 
        duration: 0.8,
        stagger: 0.2, 
        scrollTrigger: {
            trigger: '.section-works',
            start: 'top 70%'
        }
    });

    /* &--------- TESTIMONIALS ------------- */
    // Testimonial cards 
    gsap.to('.testimonial-card', {
        opacity: 1,
        y: 0,
        duration: 0.8,
        stagger: 0.4, 
        scrollTrigger: {
            trigger: '.section-testimonials',
            start: 'top 50%'
        }
    });

    /* &--------- CONTACT US ------------- */
    // Form 
    gsap.to('.contact__form-wrapper', {
        opacity: 1,
        y: 0,
        duration: 1.3,
        scrollTrigger: {
            trigger: '.section-contact',
            start: 'top 50%'
        }
    });

    // Contact info 
    gsap.to('.contact__info', {
        opacity: 1,
        y: 0,
        duration: 1,
        delay: 0.5, 
        scrollTrigger: {
            trigger: '.section-contact',
            start: 'top 70%'
        }
    });

    // Contact details 
    gsap.to('.contact__detail.icon-box__container', {
        opacity: 1,
        y: 0,
        delay: 0.5, 
        duration: 0.8,
        stagger: 0.3, 
        scrollTrigger: {
            trigger: '.contact__info',
            start: 'top 100%'
        }
    });

    /* &--------- ARTICLES & BLOG ------------- */
    // Article cards 
    gsap.to('.article-card', {
        opacity: 1,
        y: 0,
        duration: 1.5,
        stagger: 0.5, 
        scrollTrigger: {
            trigger: '.section-articles',
            start: 'top 50%'
        }
    });
}

/* &------------ BANNER ------------- */
/* &--------------------------------- */

// if (document.querySelector('.page-hero')) {
//     // Title
//     gsap.to('.page-hero__title', {
//         x: 0, 
//         opacity: 1,
//         duration: 1,
//         delay: 0.5, 
//         ease: 'power2.out',
//     });

//     // Subtitle 
//     gsap.to('.page-hero__detail', {
//         y: 0, 
//         opacity: 1,
//         duration: 1,
//         delay: 1, 
//         ease: 'power2.out',
//     });

//     // Breadcrumb
//     gsap.to('.breadcrumb__list', {
//         opacity: 1,
//         duration: 1,
//         delay: 1.5, 
//         ease: 'power2.out',
//     });

// }