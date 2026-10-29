<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<section class="home-section" id="home">
    
    <div class="full-width-container">
        <div class="swiper home-slider">
            <div class="swiper-wrapper">
                <?php $first = true;
                foreach ($sliderImages as $imageName): ?>
                    <div class="swiper-slide">
                        <?php 
                            $originalFile = $imageName; // ej: prod_123.jpg
                            $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                            $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                        ?>
                        <picture>
                            <source media="(max-width: 768px)" srcset="/assets/uploads/home/slider/<?php echo rawurlencode($smallFile); ?>">
                            <img src="/assets/uploads/home/slider/<?php echo rawurlencode($originalFile); ?>"
                            alt="Slider de Grupo Caroní Computer"
                            loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                            <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                            width="1200" height="400"
                            >
                        </picture>
                    </div>
                <?php $first = false;
                endforeach; ?>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>

    <div class="container overlap-container">

        <div class="category-grid">
            <?php foreach ($featuredCategories as $category): ?>
                <a href="/productos?search=&filter_choice=cat-<?php echo $category['id_category']; ?>&sort=name-asc" class="category-card">
                    <h2><?php echo htmlspecialchars(ucwords(strtolower($category['name']))); ?></h2>
                    <div class="category-card__image">
                        <?php if (!empty($category['image'])): ?>
                            <?php 
                                $originalFile = $category['image']; // ej: prod_123.jpg
                                $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/categories/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/categories/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                loading="lazy"
                                width="300" height="300" 
                                >
                            </picture>
                        <?php else: ?>
                            <img src="/assets/categories/default.jpg"
                            alt="<?php echo htmlspecialchars($category['name']); ?>"
                            loading="lazy" 
                            width="500" height="500" 
                            >
                        <?php endif; ?>
                    </div>
                    <span>Ver más</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<?php if (!empty($newProducts)): ?>
    <section class="section">
        <div class="container products-container">
            <div class="swiper product-page-carousel">
                <h2 class="section__subtitle product-carousel-title_new">Recién llegados</h2>
                <div class="swiper-wrapper">
                    <?php foreach ($newProducts as $newProduct): ?>
                        <div class="swiper-slide">
                            <span class="product-card__tag discount--new">NUEVO</span>
                            <a href="/producto/<?php echo $newProduct['id_product']; ?>" class="product-card">
                                <div class="product-card__inner">
                                    <div class="product-card__image swiper-no-dragging">
                                        <?php 
                                            $originalFile = !empty($newProduct['file_name']) ? $newProduct['file_name'] : 'default.jpg'; 
                                            $showWebp = false;
                                            $smallFile = '';

                                            if ($originalFile !== 'default.jpg') {
                                                $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME);
                                                $smallFile = 'small_' . $nameWithoutExt . '.webp';
                                                
                                                // Verificamos si realmente existe la miniatura webp en el servidor
                                                $webpPath = ROOT_PATH . 'public/assets/uploads/products/' . $smallFile;
                                                if (file_exists($webpPath)) {
                                                    $showWebp = true;
                                                }
                                            }
                                        ?>
                                        <picture>
                                            <?php if ($showWebp): ?>
                                                <source srcset="/assets/uploads/products/<?php echo rawurlencode($smallFile); ?>" type="image/webp">
                                            <?php endif; ?>
                                            
                                            <img src="/assets/uploads/products/<?php echo rawurlencode($originalFile); ?>"
                                                alt="<?php echo htmlspecialchars($newProduct['name']); ?>"
                                                loading="lazy">
                                        </picture>
                                    </div>
                                    <div class="product-card__content">
                                        <p class="info__meta"><?php echo htmlspecialchars($newProduct['category_name']); ?> - <?php echo htmlspecialchars($newProduct['brand_name']); ?></p>
                                        <p class="product-card__title"><?php echo htmlspecialchars($newProduct['name']); ?></p>
                                        <div class="product-card__price">
                                            
                                            <?php if ($showPrice=='1'): ?>
                                                <div class="price-line">
                                                    <span><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($newProduct['original_price'], 2)); ?></span>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if (!empty($bannerImage)): ?>
    <section class="home-banner-section">
        <div class="container">
            <?php 
                $originalFile = $bannerImage; // ej: prod_123.jpg
                $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
            ?>
            <picture>
                <source media="(max-width: 768px)" srcset="/assets/uploads/home/banner/<?php echo rawurlencode($smallFile); ?>">
                <img src="/assets/uploads/home/banner/<?php echo rawurlencode($originalFile); ?>"
                alt="Banner promocional" class="home-banner-image"
                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                <?php echo $first ? 'fetchpriority="high"' : ''; ?>
                
                >
            </picture>
        </div>
    </section>
<?php endif; ?>

<section class="section">
    <div class="container overlap-container1">

        <div class="brand-grid">
            <?php foreach ($featuredBrands as $brand): ?>
                <a href="/productos?search=&filter_choice=brand-<?php echo $brand['id_brand']; ?>" class="brand-card">
                    <h2><?php echo htmlspecialchars(ucwords(strtolower($brand['name']))); ?></h2>
                    <div class="brand-card__image">
                        <?php if (!empty($brand['image'])): ?>
                            <?php 
                                $originalFile = $brand['image']; // ej: prod_123.jpg
                                $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/brands/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/brands/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($brand['name']); ?>"
                                loading="lazy" 
                                width="300" height="300"
                                >
                            </picture>
                        <?php else: ?>
                            <img src="/assets/brands/default.jpg"
                            alt="<?php echo htmlspecialchars($brand['name']); ?>"
                            loading="lazy" 
                            width="300" height="300" 
                            >
                        <?php endif; ?>
                    </div>
                    <span>Ver más</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($offers)): ?>
    <section class="section">
        <div class="container products-container">
            <div class="swiper product-page-carousel">
                <h2 class="section__subtitle product-carousel-title_offer">Ofertas destacadas</h2>
                <div class="swiper-wrapper">
                    <?php foreach ($offers as $offer): ?>
                        <div class="swiper-slide">
                            <span class="product-card__tag discount--offer">OFERTA</span>
                            <a href="/producto/<?php echo $offer['id_product']; ?>" class="product-card">
                                <div class="product-card__inner">     
                                    <div class="product-card__image swiper-no-dragging">
                                        <?php 
                                            $originalFile = !empty($offer['file_name']) ? $offer['file_name'] : 'default.jpg'; 
                                            $showWebp = false;
                                            $smallFile = '';

                                            if ($originalFile !== 'default.jpg') {
                                                $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME);
                                                $smallFile = 'small_' . $nameWithoutExt . '.webp';
                                                
                                                // Verificamos si realmente existe la miniatura webp en el servidor
                                                $webpPath = ROOT_PATH . 'public/assets/uploads/products/' . $smallFile;
                                                if (file_exists($webpPath)) {
                                                    $showWebp = true;
                                                }
                                            }
                                        ?>
                                        <picture>
                                            <?php if ($showWebp): ?>
                                                <source srcset="/assets/uploads/products/<?php echo rawurlencode($smallFile); ?>" type="image/webp">
                                            <?php endif; ?>
                                            
                                            <img src="/assets/uploads/products/<?php echo rawurlencode($originalFile); ?>"
                                                alt="<?php echo htmlspecialchars($offer['name']); ?>"
                                                loading="lazy">
                                        </picture>
                                    </div>
                                    <div class="product-card__content">
                                        <p class="info__meta"><?php echo htmlspecialchars($offer['category_name']); ?> - <?php echo htmlspecialchars($offer['brand_name']); ?></p>
                                        <p class="product-card__title"><?php echo htmlspecialchars($offer['name']); ?></p>
                                        
                                        <?php if ($showPrice=='1'): ?>
                                            <div class="product-card__price">
                                                <?php if ($offer['offer_price'] !== null && $offer['offer_price'] < $offer['original_price']): ?>
                                                    <span class="original-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($offer['original_price'], 2)); ?></span>
                                                    <div class="offer-line">
                                                        <span class="offer-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($offer['offer_price'], 2)); ?></span>
                                                        <?php $discount = round((($offer['original_price'] - $offer['offer_price']) / $offer['original_price']) * 100); ?>
                                                        <span class="product-card__discount">-<?php echo $discount; ?>%</span>
                                                    </div>
                                                <?php else: ?>
                                                    <span><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($offer['original_price'], 2)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            </div>
        </div>
    </section>
    
<?php endif; ?>

<script src="/assets/js/home.js" defer></script>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>