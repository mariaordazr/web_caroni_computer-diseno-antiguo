<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content">
    <?php 
    $originalFile = $bannerImage; // ej: prod_123.jpg
    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
    ?>
    <source media="(max-width: 768px)" srcset="/assets/uploads/shipping/banner/<?php echo $smallFile; ?>">
    <section class="hero-section" style="background-image: url('/assets/uploads/shipping/banner/<?php echo htmlspecialchars($originalFile); ?>');">
        <div class="container">
            <h1 class="hero-section__title">Envíos a Nivel Nacional</h1>
        </div>
    </section>

    <?php $i = 0;?>
    
    <?php if (isset($shippingTitles[$i]) || isset($shippingTexts[$i]) || isset($shippingImages[$i])): ?>
        <section class="section">
            <div class="container content-section">
                <?php if (isset($shippingTitles[$i])): ?>
                    <h2><?php echo htmlspecialchars($shippingTitles[$i]); ?></h2>
                <?php endif; ?> 
                <?php if (isset($shippingTexts[$i])): ?>
                    <p>
                        <?php echo htmlspecialchars($shippingTexts[$i]); ?>
                    </p>
                <?php endif; ?>
                <?php if (isset($shippingBrands[$i])): ?>
                    <div class="swiper shipping-logo-carousel">
                        <div class="swiper-wrapper">
                            <?php 
                                // Truco: Duplicamos el array 3 veces para asegurar que el loop funcione
                                // aunque tengas pocas marcas registradas.
                                $infiniteBrands = array_merge($shippingBrands, $shippingBrands, $shippingBrands);
                                
                                foreach ($infiniteBrands as $brand) { ?>
                                    <div class="swiper-slide">
                                        <?php 
                                            $originalFile = $brand; // ej: prod_123.jpg
                                            $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                            $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                        ?>
                                        <picture>
                                            <source srcset="/assets/uploads/shipping/body/<?php echo rawurlencode($smallFile); ?>">
                                            <img src="/assets/uploads/shipping/body/<?php echo rawurlencode($originalFile); ?>"
                                            alt="Agencias de envío"
                                            loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                            <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                            >
                                        </picture>
                                    </div>
                            <?php } ?>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php $i++;?>

    <?php if (isset($shippingTitles[$i]) || isset($shippingTexts[$i]) || isset($shippingImages[$i])): ?>
        <section class="section section--light">
            <div class="container content-section">
                <div class="shipping-location-container">
                    <?php if (isset($shippingTitles[$i])): ?>
                        <h2><?php echo htmlspecialchars($shippingTitles[$i]); ?></h2>
                    <?php endif; ?>
                    <?php if (isset($shippingTexts[$i])): ?>
                        <p class="about__description">
                            <?php echo htmlspecialchars($shippingTexts[$i]); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (isset($shippingImage)): ?>
                        <div class="store-image-container">
                            <?php 
                                $originalFile = $shippingImage; // ej: prod_123.jpg
                                $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                            ?>
                            <picture>
                                <source media="(max-width: 768px)" srcset="/assets/uploads/shipping/body/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/shipping/body/<?php echo rawurlencode($originalFile); ?>"
                                alt="Fachada del local"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                >
                            </picture>
                        </div>
                    <?php endif; ?>
                    
                    <?php $i++;?>
                    <?php if (isset($shippingLink)): ?>
                        <iframe src="<?php echo htmlspecialchars($shippingLink); ?>" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<script src="/assets/js/shipping.js" defer></script>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>