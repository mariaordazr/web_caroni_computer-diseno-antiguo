<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>
<body>
<section class="content-panel product-view-container view-list">    
    <?php if (!empty($products)): ?>
        <div class="product-list-container">
            <div class="product-list-ml">
                <?php foreach($products as $product): ?>
                    <div class="product-item-ml">
                        <a href="/admin/products/edit/<?php echo $product['id_product']; ?>">
                            <div class="product-item-ml__image">
                                <?php 
                                    $originalFile = !empty($product['file_name']) ? $product['file_name'] : 'default.jpg'; 
                                    $showWebp = false;
                                    $smallFile = '';

                                    if ($originalFile !== 'default.jpg') {
                                        $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME);
                                        $smallFile = 'small_' . $nameWithoutExt . '.webp';
                                        
                                        // Verificamos si realmente existe la miniatura optimizada en el servidor
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
                                         alt="<?php echo htmlspecialchars($product['name'] ?? 'Producto'); ?>" 
                                         loading="lazy" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </picture>
                            </div>
                            <div class="product-item-ml__info">
                                <p class="info__meta">
                                    <?php echo htmlspecialchars($product['category_name']); ?> - <?php echo htmlspecialchars($product['brand_name']);?> | sku: <?php echo htmlspecialchars($product['sku']); ?> <?php echo htmlspecialchars($product['hidden'] == 0) ? '' : ' | (Oculto)'; ?>
                                </p>
                                <p class="info__title"><?php echo htmlspecialchars(ucfirst(mb_strtolower($product['name']))); ?></p>
                            </div>
                        </a>
                        <div class="compact-row-details">
                            <div class="product-item-ml__status">
                                <div class="status-toggle-container">
                                    <button class="status-pill <?php echo ($product['stock'] > 0) ? 'active' : 'inactive'; ?> stock-toggle-btn" data-product-id="<?php echo $product['id_product']; ?>">
                                        <?php echo ($product['stock'] > 0) ? 'Activo' : 'Agotado'; ?>
                                    </button>
                                    <div class="status-dropdown">
                                        <a href="#" class="stock-option" data-stock-value="1">Disponible</a>
                                        <a href="#" class="stock-option" data-stock-value="0">Agotado</a>
                                    </div>
                                </div>

                                <div class="type-toggle-container">
                                    <button class="status-pill 
                                        <?php if ($product['offer_price'] !== null) echo 'offer';
                                            elseif ($product['id_new_product'] !== null) echo 'new';
                                            else echo 'none'; ?>
                                        type-toggle-btn" 
                                        data-product-id="<?php echo $product['id_product']; ?>">

                                        <?php if ($product['offer_price'] !== null) echo 'Oferta';
                                            elseif ($product['id_new_product'] !== null) echo 'Nuevo';
                                            else echo 'Ninguno'; ?>
                                    </button>
                                    <div class="type-dropdown">
                                        <a href="#" class="type-option" data-type="offer">Oferta</a>
                                        <a href="#" class="type-option" data-type="new">Nuevo</a>
                                        <a href="#" class="type-option" data-type="none">Ninguno</a>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="/admin/products/edit/<?php echo $product['id_product']; ?>">
                                <div class="product-item-ml__pricing">
                                    <div class="pricing__offer-stock">
                                        <?php if ($product['offer_price'] !== null): ?>
                                            <span class="pricing__offer"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['offer_price'], 2)); ?></span>
                                        <?php else: ?>
                                            <span class="pricing__no-offer"></span>
                                        <?php endif; ?>
                                        <span class="pricing__original <?php echo ($product['offer_price'] !== null) ? 'has-offer' : ''; ?>">
                                            <?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['original_price'], 2)); ?>
                                        </span>
                                    </div>
                                    <span class="pricing__stock"><?php echo htmlspecialchars($product['stock']); ?> unidades</span>
                                </div>
                            </a>
                        </div>
                        <div class="product-item-ml__actions">
                            <div class="actions-menu-container">
                                <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="actions-dropdown">
                                    <a href="/admin/products/edit/<?php echo $product['id_product']; ?>">Editar</a>
                                    <a href="/producto/<?php echo $product['id_product']; ?>" target="_blank">Ver publicación</a>
                                    <a href="#" 
                                        class="btn-visibility-toggle" 
                                        data-id="<?php echo $product['id_product']; ?>" 
                                        data-current-hidden="<?php echo $product['hidden']; ?>">
                                        <?php echo ($product['hidden'] == 1) ? 'Mostrar en web' : 'Ocultar de web'; ?>
                                    </a>

                                    <?php if ($active_menu === 'products'): ?>
                                        <a href="/admin/<?php echo $active_menu; ?>/delete/<?php echo $product['id_product']; ?>" class="btn-delete danger">Eliminar</a>
                                    <?php elseif (in_array($active_menu, ['offers', 'new-products'])): ?>
                                        <a href="/admin/<?php echo $active_menu; ?>/delete/<?php echo $product['id_product']; ?>" class="btn-delete danger">Eliminar <?php echo htmlspecialchars($buttonAgg); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-grid-container">
            <?php foreach($products as $product): ?>
                <div class="grid-item-card">
                    <a href="/admin/products/edit/<?php echo $product['id_product']; ?>">
                        <div class="grid-item-card__image">
                            <?php 
                                $originalFile = !empty($product['file_name']) ? $product['file_name'] : 'default.jpg'; 
                                $showWebp = false;
                                $smallFile = '';

                                if ($originalFile !== 'default.jpg') {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME);
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp';
                                    
                                    // Verificamos si realmente existe la miniatura optimizada en el servidor
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
                                        alt="<?php echo htmlspecialchars($product['name'] ?? 'Producto'); ?>" 
                                        loading="lazy" 
                                        style="width: 100%; height: 100%; object-fit: cover;">
                            </picture>
                        </div>
                    </a>
                    <div class="grid-item-card__content">
                        <a href="/admin/products/edit/<?php echo $product['id_product']; ?>">
                            <p class="info__meta">
                                <?php echo htmlspecialchars($product['category_name']); ?> - <?php echo htmlspecialchars($product['brand_name']);?> | sku: <?php echo htmlspecialchars($product['sku']); ?> <?php echo htmlspecialchars($product['hidden'] == 0) ? '' : ' | (Oculto)'; ?>
                            </p>
                            <p class="content__title"><?php echo htmlspecialchars(ucfirst(mb_strtolower($product['name']))); ?></p>
                            
                            <div class="pricing__offer-stock">
                                <?php if ($product['offer_price'] !== null): ?>
                                    <span class="pricing__offer"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['offer_price'], 2)); ?></span>
                                <?php else: ?>
                                    <span class="pricing__no-offer"></span>
                                <?php endif; ?>
                                <span class="pricing__original <?php echo ($product['offer_price'] !== null) ? 'has-offer' : ''; ?>">
                                    <?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['original_price'], 2)); ?>
                                </span>
                            </div>
                            <span class="pricing__stock"><?php echo htmlspecialchars($product['stock']); ?> unidades</span>
                        </a>

                        <div class="product-item-ml__status2">
                            <div class="status-toggle-container">
                                <button class="status-pill <?php echo ($product['stock'] > 0) ? 'active' : 'inactive'; ?> stock-toggle-btn" data-product-id="<?php echo $product['id_product']; ?>">
                                    <?php echo ($product['stock'] > 0) ? 'Activo' : 'Agotado'; ?>
                                </button>
                                <div class="status-dropdown">
                                    <a href="#" class="stock-option" data-stock-value="1">Disponible</a>
                                    <a href="#" class="stock-option" data-stock-value="0">Agotado</a>
                                </div>
                            </div>

                            <div class="type-toggle-container">
                                <button class="status-pill 
                                    <?php if ($product['offer_price'] !== null) echo 'offer';
                                        elseif ($product['id_new_product'] !== null) echo 'new';
                                        else echo 'none'; ?>
                                    type-toggle-btn" 
                                    data-product-id="<?php echo $product['id_product']; ?>">

                                    <?php if ($product['offer_price'] !== null) echo 'Oferta';
                                        elseif ($product['id_new_product'] !== null) echo 'Nuevo';
                                        else echo 'Ninguno'; ?>
                                </button>
                                <div class="type-dropdown">
                                    <a href="#" class="type-option" data-type="offer">Oferta</a>
                                    <a href="#" class="type-option" data-type="new">Nuevo</a>
                                    <a href="#" class="type-option" data-type="none">Ninguno</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/products/edit/<?php echo $product['id_product']; ?>">Editar</a>
                                <a href="/producto/<?php echo $product['id_product']; ?>">Ver publicación</a>
                                <a href="#" 
                                    class="btn-visibility-toggle" 
                                    data-id="<?php echo $product['id_product']; ?>" 
                                    data-current-hidden="<?php echo $product['hidden']; ?>">
                                    <?php echo ($product['hidden'] == 1) ? 'Mostrar' : 'Ocultar'; ?>
                                </a>

                                <?php if ($active_menu === 'products'): ?>
                                    <a href="/admin/<?php echo $active_menu; ?>/delete/<?php echo $product['id_product']; ?>" class="btn-delete danger">Eliminar</a>
                                <?php elseif (in_array($active_menu, ['offers', 'new-products'])): ?>
                                    <a href="/admin/<?php echo $active_menu; ?>/delete/<?php echo $product['id_product']; ?>" class="btn-delete danger">Eliminar <?php echo htmlspecialchars($buttonAgg); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-search-alt'></i>
            <h2>No se encontraron productos</h2>
            <p>Prueba a cambiar los filtros o a restablecer la búsqueda.</p>
        </div>
    <?php endif; ?>
    
<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>