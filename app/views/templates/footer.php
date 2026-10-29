</main> 
    <section class="features-section">
        <div class="header-container">
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-box__icon">
                        <i class='bx bx-shield-quarter'></i>
                    </div>
                    <h3 class="feature-box__title">Compra con Seguridad</h3>
                    <p class="feature-box__text">Contamos productos de calidad garantizada.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-box__icon">
                        <i class='bx bx-rocket'></i>
                    </div>
                    <h3 class="feature-box__title">Envíos a Nivel Nacional</h3>
                    <p class="feature-box__text">Recibe tus productos en la puerta de tu casa.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-box__icon">
                        <i class='bx bx-support'></i>
                    </div>
                    <h3 class="feature-box__title">Soporte Especializado</h3>
                    <p class="feature-box__text">Tenemos un equipo capacitado para servirte.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="header-container">
            <div class="footer-main-grid">
                <div class="footer-column">
                    <h3 class="footer-column__title">Información</h3>
                    <ul class="footer-column__list">
                        <li><?php echo htmlspecialchars($locationAddress);?></li>
                        <li>Celular: +<?php echo htmlspecialchars($contactPhone);?></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-column__title">Servicios</h3>
                    <ul class="footer-column__list">
                        <li><a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola equipo de Caroni Computer, me interesa el servicio electrónico" target="_blank">Servicio Electrónico</a></li>
                        <li><a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola equipo de Caroni Computer, me interesa el servicio técnico" target="_blank">Servicio Técnico</a></li>
                        <li><a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola equipo de Caroni Computer, me interesa la instalación y software" target="_blank">Instalación y Software</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-column__title">Sobre Nosotros</h3>
                    <ul class="footer-column__list">
                        <li><a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola equipo de Caroni Computer, tengo una consulta" target="_blank">Centro de Ayuda</a></li>
                        <li><a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola equipo de Caroni Computer, tengo una consulta" target="_blank">Atención al Cliente</a></li>
                        <li><a href="/about-us">Nosotros</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-column__title">Métodos de Pago</h3>
                    <div class="footer-logos-container">

                        <?php foreach ($paymentMethods as $pay) { 
                            $originalFile = $pay; // ej: prod_123.jpg
                            $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                            $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                        ?>
                            <picture>
                                <source srcset="/assets/uploads/payments/<?php echo $smallFile; ?>">
                                <img src="/assets/uploads/payments/<?php echo $originalFile; ?>"
                                alt="Método de pago"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                >
                            </picture>
                        <?php } ?>
                    </div>
                </div>
                <div class="footer-column">
                    <h3 class="footer-column__title">¡Contáctanos!</h3>
                    <ul class="footer-column__list">
                        <li>
                            <div class="footer-social-icons">
                                <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola equipo de Caroni Computer, tengo una consulta" target="_blank" title="WhatsApp"><i class='bx bxl-whatsapp'></i></a>
                                <a href="<?php echo htmlspecialchars($facebook); ?>" target="_blank" title="Facebook"><i class='bx bxl-facebook-square'></i></a>
                                <a href="<?php echo htmlspecialchars($instagram); ?>" target="_blank" title="Instagram"><i class='bx bxl-instagram-alt'></i></a>
                                <a href="<?php echo htmlspecialchars($youtube); ?>" target="_blank" title="YouTube"><i class='bx bxl-youtube'></i></a>
                                <a href="<?php echo htmlspecialchars($tiktok); ?>" target="_blank" title="TikTok"><i class='bx bxl-tiktok'></i></a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            <div class="header-container">
                <span>
                    ©<?php echo date('Y'); ?> 
                    <?php echo htmlspecialchars($copyrightText);?><C class="A"></C>.
                    es un negocio independiente. Las marcas comerciales mencionadas pertenecen a sus respectivos fabricantes. | Sitio web creado por: 
                    <a href="https://mariaordaz.dev" target="_blank">mariaordaz.dev</a>
                </span>
            </div>
        </div>
    </footer>
    <div class="floating-buttons-container">
        <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=Hola,%20quisiera%20más%20información." class="float-btn btn-whatsapp" target="_blank" title="Contáctanos por WhatsApp">
            <i class='bx bxl-whatsapp'></i>
        </a>
        
        <button id="scrollTopBtn" class="float-btn btn-scroll-top" title="Ir arriba">
            <i class='bx bx-up-arrow-alt'></i>
        </button>
    </div>

    <script src="/assets/js/scrollreveal.min.js" defer></script>
    <script src="/assets/js/swiper-bundle.min.js" ></script>
    
    <script src="/assets/js/main.js" defer></script>
    
    <script type="module" src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.0.0/dist/ionicons/ionicons.js"></script>
</body>
</html>