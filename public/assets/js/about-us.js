// public/assets/js/about-us.js
document.addEventListener('DOMContentLoaded', function() {
    const sr = ScrollReveal({ origin: 'bottom', distance: '60px', duration: 2500, delay: 400 });
    sr.reveal(`.about-us__data`);
    sr.reveal(`.mission-vision__item`, { interval: 100 });
    sr.reveal(`.clients__container`, { interval: 100 });
    sr.reveal(`.brands__container`, { interval: 100 });
    sr.reveal(`.payment-methods__container`, { interval: 100 });
});