<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content">
    <div class="container">
        <form id="filter-form" method="GET">
            <!-- Mantenemos la búsqueda activa -->
            <?php if (!empty($_GET['search'])): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
            <?php endif; ?>
            
            <!-- Mantenemos el tipo (Oferta/Nuevo) activo siempre que exista -->
            <?php if (!empty($_GET['type'])): ?>
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($_GET['type']); ?>">
            <?php endif; ?>
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? 'name-asc'); ?>">
            <div class="products-page-layout">
                <div class="mobile-filters-container">
                <?php 
                    // Lógica auxiliar para saber qué categoría está seleccionada actualmente
                    $current_filter = $_GET['filter_choice'] ?? '';
                    $current_cat_id = null;
                    $current_brand_id = null;

                    // Analizamos el string tipo "cat-1" o "cat-1-brand-5"
                    if (strpos($current_filter, 'cat-') === 0) {
                        $parts = explode('-', $current_filter);
                        // parts[1] es el ID de categoría
                        if (isset($parts[1])) $current_cat_id = $parts[1];
                    }
                ?>

                <div class="mobile-select-group">
                    <label>Categoría:</label>
                    <select onchange="submitMobileFilter(this.value)" class="mobile-select">
                        <option value="" <?php echo $current_filter === '' ? 'selected' : ''; ?>>- Seleccionar Categoría -</option>
                        <?php foreach($categoriesWithData as $category): ?>
                            <?php 
                                $cat_val = "cat-" . $category['id_category']; 
                                $is_selected = ($current_cat_id == $category['id_category']);
                            ?>
                            <option value="<?php echo $cat_val; ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucwords(strtolower($category['name']))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mobile-select-group">
                    <label>Marca:</label>
                    <select onchange="submitMobileFilter(this.value)" class="mobile-select" <?php echo ($current_cat_id === null) ? 'disabled' : ''; ?>>
                        
                        <?php if ($current_cat_id === null): ?>
                            <option value="">Selecciona una categoría primero</option>
                        <?php else: ?>
                            <option value="cat-<?php echo $current_cat_id; ?>">Todas las marcas</option>
                            
                            <?php 
                            $selectedCategoryData = null;
                            foreach($categoriesWithData as $cat) {
                                if ($cat['id_category'] == $current_cat_id) {
                                    $selectedCategoryData = $cat;
                                    break;
                                }
                            }
                            ?>

                            <?php if ($selectedCategoryData): ?>
                                <?php foreach($selectedCategoryData['brands'] as $brand): ?>
                                    <?php 
                                        $brand_val = "cat-" . $current_cat_id . "-brand-" . $brand['id_brand'];
                                        $is_brand_selected = ($current_filter === $brand_val);
                                    ?>
                                    <option value="<?php echo $brand_val; ?>" <?php echo $is_brand_selected ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucwords(strtolower($brand['name']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                    </select>
                </div>
                <div class="mobile-select-group">
                    <label for="sort">Ordenar por:</label>
                    <select name="sort" id="sort" class="mobile-select" >
                        <option value="name-asc" <?php echo ($_GET['sort'] ?? '') === 'name-asc' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                        <option value="name-desc" <?php echo ($_GET['sort'] ?? '') === 'name-desc' ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                        <option value="price-asc" <?php echo ($_GET['sort'] ?? '') === 'price-asc' ? 'selected' : ''; ?>>Precio (Menor a Mayor)</option>
                        <option value="price-desc" <?php echo ($_GET['sort'] ?? '') === 'price-desc' ? 'selected' : ''; ?>>Precio (Mayor a Menor)</option>
                    </select>
                </div>

                <div class="mobile-select-group" style="width: 100%; align-items: center; justify-content: center;">
                    <a href="/productos" class="btn-clear-filters">Limpiar filtros</a>
                </div>
            </div>
                <aside class="filter-sidebar">

                    <?php 
                        // 1. Obtenemos el filtro actual
                        $current_filter = $_GET['filter_choice'] ?? ''; 
                    ?>
                    
                    <h3 style="color: #2a4786ff; padding: 0.5rem 1rem;">Categorías</h3>
                    
                    <?php foreach($categoriesWithData as $category): ?>
                        <?php 
                            $category_value_prefix = "cat-" . $category['id_category'];

                            // Mantenemos el acordeón abierto si es la categoría activa O si contiene una marca seleccionada
                            $is_open = ($current_filter === $category_value_prefix || strpos($current_filter, $category_value_prefix . '-') === 0);
                        ?>

                        <div class="filter-group accordion <?php echo $is_open ? 'is-open' : ''; ?>" id="category-<?php echo $category['id_category']; ?>">
                            
                            <?php 
                                // Valor del radio de CATEGORÍA
                                $category_value = "cat-" . $category['id_category'];
                                
                                // Check si la categoría está seleccionada (directamente o por sus hijos)
                                $is_category_checked = ($current_filter === $category_value || strpos($current_filter, $category_value . '-') === 0);
                            ?>
                            
                            <div class="filter-option-header">
                                <input 
                                    type="radio" 
                                    name="filter_choice" 
                                    value="<?php echo $category_value; ?>" 
                                    id="<?php echo $category_value; ?>"
                                    <?php echo $is_category_checked ? 'checked' : ''; ?>
                                    onchange="submitSidebarFilter()"> 

                                <span class="accordion__header" for="<?php echo $category_value; ?>">
                                    <h4><?php echo htmlspecialchars(ucwords(strtolower($category['name']))); ?></h4>
                                    <i class='bx bx-chevron-down accordion__icon'></i>
                                </span>
                            </div>

                            <div class="accordion__content">
                                <div class="filter-options-list nested-list">

                                    <div class="filter-option">
                                        <input 
                                            type="radio" 
                                            name="filter_choice" 
                                            value="<?php echo $category_value; ?>" 
                                            id="<?php echo $category_value; ?>-all"
                                            <?php echo ($current_filter === $category_value) ? 'checked' : ''; ?>
                                            onchange="submitSidebarFilter()"
                                        > 
                                        <label for="<?php echo $category_value; ?>-all">
                                            - Todas las marcas -
                                        </label>
                                    </div>

                                    <?php foreach($category['brands'] as $brand): ?>
                                        <?php 
                                            $brand_value = "cat-" . $category['id_category'] . "-brand-" . $brand['id_brand'];
                                            $is_brand_checked = ($current_filter === $brand_value);
                                        ?>
                                        <div class="filter-option">
                                            <input 
                                                type="radio" 
                                                name="filter_choice" 
                                                value="<?php echo $brand_value; ?>" 
                                                id="<?php echo $brand_value; ?>"
                                                <?php echo $is_brand_checked ? 'checked' : ''; ?>
                                                onchange="submitSidebarFilter()">
                                            
                                            <label for="<?php echo $brand_value; ?>">
                                                <?php echo htmlspecialchars(ucwords(strtolower($brand['name']))); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </aside>

                <div class="product-grid-container">
                    <div class="product-grid-header">
                        <h1 class="section-header">Productos</h1>
                        <div class="filter-group">
                            <label for="sort">Ordenar por</label>
                            <select name="sort" id="sort" >
                                <option value="name-asc" <?php echo ($_GET['sort'] ?? '') === 'name-asc' ? 'selected' : ''; ?>>Nombre (A-Z)</option>
                                <option value="name-desc" <?php echo ($_GET['sort'] ?? '') === 'name-desc' ? 'selected' : ''; ?>>Nombre (Z-A)</option>
                                <option value="price-asc" <?php echo ($_GET['sort'] ?? '') === 'price-asc' ? 'selected' : ''; ?>>Precio (Menor a Mayor)</option>
                                <option value="price-desc" <?php echo ($_GET['sort'] ?? '') === 'price-desc' ? 'selected' : ''; ?>>Precio (Mayor a Menor)</option>
                            </select>

                            <a href="/productos" class="btn-clear-filters">Limpiar filtros</a>
                        </div>
                    </div>

                    <?php if (!empty($filterSummary)): ?>
                        <div class="filter-summary">
                            <strong>Filtros aplicados:</strong>
                            <?php foreach ($filterSummary as $key => $value): ?>
                                <span><?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($products)): ?>
                        <div class="product-grid columns-5">
                            <?php foreach ($products as $product): ?>
                                <?php if ($product['hidden'] === 0): // Saltar productos ocultos ?>
                                    <a href="/producto/<?php echo $product['id_product']; ?>" class="product-card">
                                        <div class="product-card__inner2">
                                            <div class="product-card__image">
                                                <?php if (isset($product['certification']) && $product['certification'] === 'premium'): ?>
                                                    <img src="/assets/uploads/logo/premium-crown.png" class="premium-crown" alt="Producto Premium">
                                                <?php endif; ?>

                                                <?php 
                                                    $originalFile = !empty($product['file_name']) ? $product['file_name'] : 'default.jpg'; 
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
                                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                        loading="lazy">
                                                </picture>
                                            </div>
                                            <div class="product-card__content">
                                                <p class="info__meta">
                                                    <?php echo htmlspecialchars($product['category_name']); ?> - <?php echo htmlspecialchars($product['brand_name']); ?>
                                                </p>
                                                <p class="product-card__title"><?php echo htmlspecialchars($product['name']); ?>
                                                    <!-- <?php
                                                        $prod_name = trim(mb_strtolower($product['name'], 'UTF-8'));
                                                        if ($prod_name !== '') {
                                                            $prod_name = mb_strtoupper(mb_substr($prod_name, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($prod_name, 1, null, 'UTF-8');
                                                        }
                                                        echo htmlspecialchars($prod_name);
                                                        ?> -->
                                                </p>
                                                
                                                <?php if ($showPrice=='1'): ?>
                                                    <div class="product-card__price">
                                                        <?php if ($product['offer_price'] !== null && $product['offer_price'] < $product['original_price']): ?>
                                                            <span class="original-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['original_price'], 2)); ?></span>
                                                            <div class="offer-line">
                                                                <span class="offer-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['offer_price'], 2)); ?></span>
                                                                <?php $discount = round((($product['original_price'] - $product['offer_price']) / $product['original_price']) * 100); ?>
                                                                <span class="product-card__discount">-<?php echo $discount; ?>%</span>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="price-line">
                                                                <span><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['original_price'], 2)); ?></span>
                                                                
                                                                <?php if ($product['id_new_product'] !== null): ?>
                                                                    <span class="new-tag"> ¡Nuevo!</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>

                                                
                                                <?php if ($showStock=='1'): ?>
                                                    <?php if ($product['stock'] > 0): ?>
                                                        <p class="info__meta" style="color: #006115ff;">DISPONIBLE</p>
                                                    <?php else: ?>
                                                        <p class="info__meta" style="color: #700000ff;">AGOTADO</p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-results-found">
                            <i class='bx bx-search-alt-2'></i>
                            <h3>No se encontraron productos</h3>
                            <p>Intenta con otros términos de búsqueda o ajusta los filtros.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</main>

<script src="/assets/js/products.js" defer></script>
<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>