// public/assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {

    /*=============== CHANGE BACKGROUND HEADER ===============*/
    function scrollHeader() {
        const header = document.getElementById('header');
        if (this.scrollY >= 50) {
            header.classList.add('scroll-header');
        } else {
            header.classList.remove('scroll-header');
        }
    }
    window.addEventListener('scroll', scrollHeader);

    /*=============== SCROLL REVEAL INITIALIZATION ===============*/
    const sr = ScrollReveal({
        origin: 'top',
        distance: '60px',
        duration: 2500,
        delay: 400,
    });

    /*=============== MOBILE MENU TOGGLE ===============*/
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const navMenu = document.getElementById('nav-menu');

    if (mobileMenuButton && navMenu) {
        mobileMenuButton.addEventListener('click', () => {
            navMenu.classList.toggle('is-active');
        });
    }

    /*=============== DROPDOWN MENUS (CATEGORÍAS) ===============*/
    // 1. Seleccionamos todos los botones que activan dropdowns
    const dropdownToggles = document.querySelectorAll('[data-dropdown-toggle]');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            event.preventDefault(); // Evita saltos de página
            event.stopPropagation(); // IMPORTANTE: Evita que el clic suba al window inmediatamente

            const dropdownId = this.getAttribute('data-dropdown-toggle');
            const dropdownMenu = document.getElementById(dropdownId);
            
            // Si el menú ya está abierto, lo cerramos
            if (dropdownMenu.classList.contains('is-active')) {
                dropdownMenu.classList.remove('is-active');
                this.classList.remove('is-active'); // Opcional: para rotar flechas CSS
            } else {
                // Si está cerrado:
                // 1. Cerramos cualquier otro dropdown abierto primero (comportamiento acordeón)
                document.querySelectorAll('.dropdown-menu.is-active').forEach(menu => {
                    menu.classList.remove('is-active');
                });
                document.querySelectorAll('[data-dropdown-toggle].is-active').forEach(btn => {
                    btn.classList.remove('is-active');
                });

                // 2. Abrimos el actual
                dropdownMenu.classList.add('is-active');
                this.classList.add('is-active');
            }
        });
    });

    /*=============== CIERRE AL HACER CLIC FUERA ===============*/
    window.addEventListener('click', function(event) {
        // Verifica si el clic NO ocurrió dentro de un dropdown ni en su botón activador
        if (!event.target.closest('.dropdown-menu') && !event.target.closest('[data-dropdown-toggle]')) {
            
            document.querySelectorAll('.dropdown-menu.is-active').forEach(menu => {
                menu.classList.remove('is-active');
            });
            
            document.querySelectorAll('[data-dropdown-toggle].is-active').forEach(btn => {
                btn.classList.remove('is-active');
            });
        }
    });

    // General reveals
    sr.reveal(`.footer__container`);
});

document.addEventListener('DOMContentLoaded', function() {
    const scrollBtn = document.getElementById('scrollTopBtn');

    window.addEventListener('scroll', function() {
        // Mostrar botón si bajamos más de 300px
        if (window.scrollY > 300) {
            scrollBtn.classList.add('show');
        } else {
            scrollBtn.classList.remove('show');
        }
    });

    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});