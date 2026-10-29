// public/assets/js/home.js

document.addEventListener('DOMContentLoaded', function() {

    /*=============== INITIALIZE HOME SLIDER (Swiper) ===============*/
    const homeSlider = new Swiper('.home-slider', {
        effect: 'slide',
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true, 
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    new Swiper('.product-page-carousel', {
        slidesPerView: 2, // Móvil
        spaceBetween: 10,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints: {
            640: {
                slidesPerView: 3,
                spaceBetween: 10,
            },
            768: {
                slidesPerView: 4, // Tablet
                spaceBetween: 10,
            },
            1024: {
                slidesPerView: 5, // Laptop pequeña
                spaceBetween: 10,
            },
            1400: {
                slidesPerView: 6,  // <--- AQUÍ ES DONDE PONES EL 7 (Pantallas grandes)
                spaceBetween: 10,
            },
        },
    });

    // En public/assets/js/home.js

    new Swiper('.product-page-carousel', {
        slidesPerView: 'auto',
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        // --- AÑADE ESTE BLOQUE ---
        // on: {
        //     // Se ejecuta cuando Swiper ha terminado de inicializarse
        //     init: function () {
        //         // Hacemos visible el carrusel
        //         this.el.style.opacity = 1;
        //         this.el.style.visibility = 'visible';
        //     },
        // },
    });
});
