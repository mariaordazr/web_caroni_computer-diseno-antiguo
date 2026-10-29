<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content">
    <?php
    $originalFile = $bannerImage; // ej: prod_123.jpg
    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
    ?>
    <source media="(max-width: 768px)" srcset="/assets/uploads/services/banner/<?php echo $smallFile; ?>">
    <section class="hero-section" style="background-image: url('/assets/uploads/services/banner/<?php echo htmlspecialchars($originalFile); ?>');">
        <div class="container">
            <h1 class="hero-section__title">Nuestros Servicios</h1>
        </div>
    </section>

    <?php $i = 0;?>

    <?php if (isset($serviceTitles[$i]) || isset($serviceTexts[$i]) || isset($serviceClients)): ?>
        <section class="section">
            <div class="container content-section">
                <?php if (isset($serviceTitles[$i])): ?>
                    <h2><?php echo htmlspecialchars($serviceTitles[$i]); ?></h2>
                <?php endif; ?>
                <?php if (isset($serviceTexts[$i])): ?>
                    <p>
                        <?php echo htmlspecialchars($serviceTexts[$i]); ?>
                    </p>
                <?php endif; ?>

                <?php if (isset($serviceClients)): ?>
                    <div class="swiper clients-logo-carousel">
                        <div class="swiper-wrapper">
                            <?php 
                                // Truco: Duplicamos el array 3 veces para asegurar que el loop funcione
                                // aunque tengas pocas marcas registradas.
                                $infiniteClients = array_merge($serviceClients, $serviceClients, $serviceClients);
                                
                                foreach ($infiniteClients as $client) { ?>
                                    <div class="swiper-slide">
                                        <?php 
                                            $originalFile = $client; // ej: prod_123.jpg
                                            $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                            $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                        ?>
                                        <picture>
                                            <source srcset="/assets/uploads/services/body/<?php echo rawurlencode($smallFile); ?>">
                                            <img src="/assets/uploads/services/body/<?php echo rawurlencode($originalFile); ?>" 
                                            alt="Nuestros clientes"
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
    <?php if (isset($serviceTitles[$i]) || isset($serviceTexts[$i]) || isset($serviceImages[$i])): ?>
        <section class="section">
            <div class="container">
                <div class="services-grid">
                    <?php if (isset($serviceTitles[$i]) || isset($serviceTexts[$i]) || isset($serviceImages[$i-1])): ?>
                        <div class="service-card">
                            <div class="service-card__icon"><i class='bx bx-chip'></i></div>
                            <?php if (isset($serviceTitles[$i])): ?>
                                <h3><?php echo htmlspecialchars($serviceTitles[$i]); ?></h3>
                            <?php endif; ?>
                            <?php if (isset($serviceTexts[$i])): ?>
                                <p><?php echo htmlspecialchars($serviceTexts[$i]); ?></p>
                            <?php endif; ?>
                            <?php if (isset($serviceImages[$i-1])): ?>
                                <div class="service-card__image">
                                    <?php 
                                        $originalFile = $serviceImages[$i-1]; // ej: prod_123.jpg
                                        $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                        $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                    ?>
                                    <picture>
                                        <source media="(max-width: 768px)" srcset="/assets/uploads/services/body/<?php echo rawurlencode($smallFile); ?>">
                                        <img src="/assets/uploads/services/body/<?php echo rawurlencode($originalFile); ?>" class="img-ratio-4-3"
                                        alt="<?php echo htmlspecialchars($serviceTitles[$i]); ?>"
                                        loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                        <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                        >
                                    </picture>
                                </div>
                            <?php endif; ?>
                            <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=<?php echo urlencode('Hola, quiero saber más de ' . $serviceTitles[$i]); ?>" target="_blank" class="btn btn-primary">Saber más</a>
                        </div>
                    <?php endif; ?>
                    <?php $i++; ?>
                    <?php if (isset($serviceTitles[$i]) || isset($serviceTexts[$i]) || isset($serviceImages[$i-1])): ?>
                        <div class="service-card">
                            <div class="service-card__icon"><i class='bx bx-wrench'></i></div>
                            <?php if (isset($serviceTitles[$i])): ?>
                                <h3><?php echo htmlspecialchars($serviceTitles[$i]); ?></h3>
                            <?php endif; ?>
                            <?php if (isset($serviceTexts[$i])): ?>
                                <p><?php echo htmlspecialchars($serviceTexts[$i]); ?></p>
                            <?php endif; ?>
                            <?php if (isset($serviceImages[$i-1])): ?>
                                <div class="service-card__image">
                                    <?php 
                                        $originalFile = $serviceImages[$i-1]; // ej: prod_123.jpg
                                        $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                        $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                    ?>
                                    <picture>
                                        <source media="(max-width: 768px)" srcset="/assets/uploads/services/body/<?php echo rawurlencode($smallFile); ?>">
                                        <img src="/assets/uploads/services/body/<?php echo rawurlencode($originalFile); ?>" class="img-ratio-4-3"
                                        alt="<?php echo htmlspecialchars($serviceTitles[$i]); ?>"
                                        loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                        <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                        >
                                    </picture>
                                </div>
                            <?php endif; ?>
                            <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=<?php echo urlencode('Hola, quiero saber más de ' . $serviceTitles[$i]); ?>" target="_blank" class="btn btn-primary">Saber más</a>
                        </div>
                    <?php endif; ?>
                    <?php $i++; ?>
                    <?php if (isset($serviceTitles[$i]) || isset($serviceTexts[$i]) || isset($serviceImages[$i-1])): ?>
                        <div class="service-card">
                            <div class="service-card__icon"><i class='bx bx-desktop'></i></div>
                            <?php if (isset($serviceTitles[$i])): ?>
                                <h3><?php echo htmlspecialchars($serviceTitles[$i]); ?></h3>
                            <?php endif; ?>
                            <?php if (isset($serviceTexts[$i])): ?>
                                <p><?php echo htmlspecialchars($serviceTexts[$i]); ?></p>
                            <?php endif; ?>
                            <?php if (isset($serviceImages[$i-1])): ?>
                                <div class="service-card__image">
                                    <?php 
                                        $originalFile = $serviceImages[$i-1]; // ej: prod_123.jpg
                                        $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                        $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                    ?>
                                    <picture>
                                        <source media="(max-width: 768px)" srcset="/assets/uploads/services/body/<?php echo rawurlencode($smallFile); ?>">
                                        <img src="/assets/uploads/services/body/<?php echo rawurlencode($originalFile); ?>" class="img-ratio-4-3"
                                        alt="<?php echo htmlspecialchars($serviceTitles[$i]); ?>"
                                        loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                        <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                        >
                                    </picture>
                                </div>
                            <?php endif; ?>
                            <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=<?php echo urlencode('Hola, quiero saber más de ' . $serviceTitles[$i]); ?>" target="_blank" class="btn btn-primary">Saber más</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php $i++; ?>
    <?php if (isset($serviceTitles[$i]) || isset($serviceTexts[$i]) || isset($serviceImages[$i])): ?>
        <section class="section">
            <div class="container content-section">
                <?php if (isset($serviceTitles[$i])): ?>
                    <h2><?php echo htmlspecialchars($serviceTitles[$i]); ?></h2>
                <?php endif; ?>
                <?php if (isset($serviceTexts[$i])): ?>
                    <p><?php echo htmlspecialchars($serviceTexts[$i]); ?></p>
                <?php endif; ?>
                <?php
                    $embedUrl = '';
                    if (!empty($serviceLink)) {
                        $parsed = parse_url($serviceLink);
                        $host = $parsed['host'] ?? '';
                        $id = '';
                        if (strpos($host, 'youtu.be') !== false) {
                            $id = ltrim($parsed['path'] ?? '', '/');
                        } elseif (strpos($host, 'youtube.com') !== false) {
                            parse_str($parsed['query'] ?? '', $qs);
                            $id = $qs['v'] ?? '';
                        }
                        if (!empty($id)) {
                            $embedUrl = 'https://www.youtube.com/embed/' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
                        }
                    }
                if (!empty($embedUrl)) : ?>
                    <div class="video-wrapper">
                        <iframe width="100%" height="450" src="<?php echo $embedUrl; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<script src="/assets/js/services.js" defer></script>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>