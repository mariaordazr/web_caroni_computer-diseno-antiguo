<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content">
    <?php 
    $originalFile = $bannerImage; // ej: prod_123.jpg
    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
    ?>
    <source media="(max-width: 768px)" srcset="/assets/uploads/about-us/banner/<?php echo $smallFile; ?>">
    <section class="hero-section" style="background-image: url('/assets/uploads/about-us/banner/<?php echo htmlspecialchars($originalFile); ?>');">
        <div class="container">
            <h1 class="hero-section__title">Quiénes somos</h1>
        </div>
    </section>

    <?php $i=0;?>
    

     <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i]) || isset($aboutImages[$i])): ?>
        <section class="section section--light">
            <div class="container content-section">
                <div class="info-grid">
                    <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i])): ?>
                        <div class="info-grid__item about-text-content">
                            <?php if (isset($aboutTitles[$i])): ?>
                                <h3><?php echo htmlspecialchars($aboutTitles[$i]); ?></h3>
                            <?php endif; ?>
                            <?php if (isset($aboutTitles[$i])): ?>
                                <p><?php echo nl2br(htmlspecialchars($aboutTexts[$i])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($aboutImages[$i])): ?>
                        <div class="info-grid__item">
                            <div class="about-image-wrapper">
                                <?php 
                                    $originalFile = $aboutImages[$i]; // ej: prod_123.jpg
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                ?>
                                <picture>
                                    <source media="(max-width: 768px)" srcset="/assets/uploads/about-us/body/<?php echo rawurlencode($smallFile); ?>">
                                    <img src="/assets/uploads/about-us/body/<?php echo rawurlencode($originalFile); ?>"
                                    alt="<?php echo htmlspecialchars($aboutTitles[$i]) ? htmlspecialchars($aboutTitles[$i]) : ''; ?>"
                                    loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                    <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                    >
                                </picture>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php $i++;?>

    <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i]) || isset($aboutImages[$i])): ?>
    <section class="section">
        <div class="container">
            <div class="about-values-grid">
                <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i])): ?>
                    <div class="about-card">
                        <?php if (isset($aboutTitles[$i])): ?>
                            <h3><?php echo htmlspecialchars($aboutTitles[$i]); ?></h3>
                        <?php endif; ?>
                        <?php if (isset($aboutTexts[$i])): ?>
                            <p><?php echo htmlspecialchars($aboutTexts[$i]); ?></p>
                        <?php endif; ?>
                        <?php if (isset($aboutImages[$i])): ?>
                            <div class="about-card__image">
                                <?php 
                                    $originalFile = $aboutImages[$i]; // ej: prod_123.jpg
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                ?>
                                <picture>
                                    <source media="(max-width: 768px)" srcset="/assets/uploads/about-us/body/<?php echo rawurlencode($smallFile); ?>">
                                    <img src="/assets/uploads/about-us/body/<?php echo rawurlencode($originalFile); ?>" class="img-ratio-4-3"
                                    alt="<?php echo htmlspecialchars($aboutTitles[$i]); ?>"
                                    loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                    <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                    >
                                </picture>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php $i++; ?>
                <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i])): ?>
                    <div class="about-card">
                        <?php if (isset($aboutTitles[$i])): ?>
                            <h3><?php echo htmlspecialchars($aboutTitles[$i]); ?></h3>
                        <?php endif; ?>
                        <?php if (isset($aboutTexts[$i])): ?>
                            <p><?php echo htmlspecialchars($aboutTexts[$i]); ?></p>
                        <?php endif; ?>
                        <?php if (isset($aboutImages[$i])): ?>
                            <div class="about-card__image">
                                <?php 
                                    $originalFile = $aboutImages[$i]; // ej: prod_123.jpg
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                ?>
                                <picture>
                                    <source media="(max-width: 768px)" srcset="/assets/uploads/about-us/body/<?php echo rawurlencode($smallFile); ?>">
                                    <img src="/assets/uploads/about-us/body/<?php echo rawurlencode($originalFile); ?>" class="img-ratio-4-3"
                                    alt="<?php echo htmlspecialchars($aboutTitles[$i]); ?>"
                                    loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                    <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                    >
                                </picture>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php $i++; ?>
                <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i])): ?>
                    <div class="about-card">
                        <?php if (isset($aboutTitles[$i])): ?>
                            <h3><?php echo htmlspecialchars($aboutTitles[$i]); ?></h3>
                        <?php endif; ?>
                        <?php if (isset($aboutTexts[$i])): ?>
                            <p><?php echo htmlspecialchars($aboutTexts[$i]); ?></p>
                        <?php endif; ?>
                        <?php if (isset($aboutImages[$i])): ?>
                            <div class="about-card__image">
                                <?php 
                                    $originalFile = $aboutImages[$i]; // ej: prod_123.jpg
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                ?>
                                <picture>
                                    <source media="(max-width: 768px)" srcset="/assets/uploads/about-us/body/<?php echo rawurlencode($smallFile); ?>">
                                    <img src="/assets/uploads/about-us/body/<?php echo rawurlencode($originalFile); ?>" class="img-ratio-4-3"
                                    alt="<?php echo htmlspecialchars($aboutTitles[$i]); ?>"
                                    loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                    <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                    >
                                </picture>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>


    <?php $i++;?>
    
    <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i]) || isset($aboutImages[$i])): ?>
        <section class="section section--light">
            <div class="container content-section">
                <div class="info-grid">
                    <?php if (isset($aboutImages[$i])): ?>
                        <div class="info-grid__item">
                            <div class="about-image-wrapper">
                                <?php 
                                    $originalFile = $aboutImages[$i]; // ej: prod_123.jpg
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                ?>
                                <picture>
                                    <source media="(max-width: 768px)" srcset="/assets/uploads/about-us/body/<?php echo rawurlencode($smallFile); ?>">
                                    <img src="/assets/uploads/about-us/body/<?php echo rawurlencode($originalFile); ?>"
                                    alt="<?php echo htmlspecialchars($aboutTitles[$i]); ?>"
                                    loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                    <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                                    >
                                </picture>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($aboutTitles[$i]) || isset($aboutTexts[$i])): ?>
                        <div class="info-grid__item about-text-content">
                            <?php if (isset($aboutTitles[$i])): ?>
                                <h3><?php echo htmlspecialchars($aboutTitles[$i]); ?></h3>
                            <?php endif; ?>
                            <?php if (isset($aboutTexts[$i])): ?>
                                <p><?php echo nl2br(htmlspecialchars($aboutTexts[$i])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>