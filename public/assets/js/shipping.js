document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.shipping-logo-carousel')) {
        const container = document.querySelector('.shipping-logo-carousel');

        const shippingSwiper = new Swiper('.shipping-logo-carousel', {
            slidesPerView: 3,
            spaceBetween: 10,
            loop: true,
            autoplay: {
                delay: 0, // desplazamiento continuo
                disableOnInteraction: false,
                waitForTransition: false
            },
            speed: 2000, // ajustar para la velocidad de desplazamiento continuo
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                640: { slidesPerView: 4 },
                768: { slidesPerView: 5 },
                1024: { slidesPerView: 6 },
            }
        });
    }
});