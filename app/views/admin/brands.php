<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>
<section class="content-panel product-view-container view-list"> 
    <?php if (!empty($brands)): ?>
        <div class="table-list-ml">
            <?php foreach($brands as $brand): ?>
                <div class="table-item-ml">
                    <a href="/admin/products?search=&sort=created_at-desc&category=&brand=<?php echo $brand['id_brand']; ?>&status=&type=">
                        <div class="product-item-ml__image">
                            <?php 
                                $originalFile = !empty($brand['image']) ? $brand['image'] : 'default.jpg'; 
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                }
                            ?>
                            
                            <picture>
                                <source srcset="/assets/uploads/brands/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/brands/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($brand['name']); ?>"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                >
                            </picture>
                        </div>
                        <div class="table-item-ml__info">
                            <?php if ($brand['hidden'] == 1): ?>
                                <p class="info__meta">(Oculto)</p>
                            <?php endif; ?>
                            <p class="info__title"><?php echo htmlspecialchars($brand['name']); ?></p>
                        </div>
                        <div class="table-item-ml__update">
                            <span class="item__cu">Creado: <?php echo htmlspecialchars(date('d/m/Y H:m', strtotime($brand['created_at']))); ?></span>    
                            <span class="item__cu">Modificado: <?php echo htmlspecialchars(date('d/m/Y H:m', strtotime($brand['updated_at']))); ?></span>
                        </div>
                    </a>
                        <div class="table-item-ml__actions">
                            <div class="actions-menu-container">
                                <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="actions-dropdown">
                                    <a href="/admin/brands/edit/<?php echo $brand['id_brand']; ?>">Editar</a>
                                    <a href="#" 
                                        class="btn-visibility-toggle" 
                                        data-id="<?php echo $brand['id_brand']; ?>" 
                                        data-current-hidden="<?php echo $brand['hidden']; ?>">
                                        <?php echo ($brand['hidden'] == 1) ? 'Mostrar en web' : 'Ocultar de web'; ?>
                                    </a>
                                    <a href="/admin/brands/delete/<?php echo $brand['id_brand']; ?>" class="btn-delete danger">Eliminar</a>
                                </div>
                            </div>
                        </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="generic-grid-container">
            <?php foreach($brands as $brand): ?>
                <div class="grid-item-card">
                    <a href="/admin/products?search=&sort=created_at-desc&category=&brand=<?php echo $brand['id_brand']; ?>&status=&type=">
                        <div class="grid-item-card__image">
                            <?php 
                                $originalFile = !empty($brand['image']) ? $brand['image'] : 'default.jpg'; 
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                }
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/brands/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/brands/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($brand['name']); ?>"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                >
                            </picture>
                        </div>
                        <div class="grid-item-card__content">
                            <?php if ($brand['hidden'] == 1): ?>
                                <p class="info__meta">(Oculto)</p>
                            <?php endif; ?>
                            <p class="grid-item-card__title"><?php echo htmlspecialchars($brand['name']); ?></p>
                        </div>
                    </a>
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/brands/edit/<?php echo $brand['id_brand']; ?>">Editar</a>
                                <a href="#" 
                                    class="btn-visibility-toggle" 
                                    data-id="<?php echo $brand['id_brand']; ?>" 
                                    data-current-hidden="<?php echo $brand['hidden']; ?>">
                                    <?php echo ($brand['hidden'] == 1) ? 'Mostrar en web' : 'Ocultar de web'; ?>
                                </a>
                                <a href="/admin/brands/delete/<?php echo $brand['id_brand']; ?>" class="btn-delete danger">Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-search-alt'></i>
            <h2>No se encontraron marcas</h2>
            <p>Prueba a cambiar los filtros o a restablecer la búsqueda.</p>
        </div>
    <?php endif; ?>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>