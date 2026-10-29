<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>
<section class="content-panel product-view-container view-list"> 
    <?php if (!empty($categories)): ?>
        <div class="table-list-ml">
            <?php foreach($categories as $category): ?>
                <div class="table-item-ml">
                    <a href="/admin/products?search=&sort=created_at-desc&category=<?php echo $category['id_category']; ?>&brand=&status=&type=">
                        <div class="product-item-ml__image">
                            <?php 
                                $originalFile = !empty($category['image']) ? $category['image'] : 'default.jpg'; 
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                }
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/categories/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/categories/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                >
                            </picture>
                        </div>
                        <div class="table-item-ml__info">
                            <?php if ($category['hidden'] == 1): ?>
                                <p class="info__meta">(Oculto)</p>
                            <?php endif; ?>
                            <p class="info__title"><?php echo htmlspecialchars($category['name']); ?></p>
                        </div>
                        <div class="table-item-ml__update">
                            <span class="item__cu">Creado: <?php echo htmlspecialchars(date('d/m/Y H:m:s', strtotime($category['created_at']))); ?></span>    
                            <span class="item__cu">Modificado: <?php echo htmlspecialchars(date('d/m/Y H:m:s', strtotime($category['updated_at']))); ?></span>
                        </div>
                    </a>
                    <div class="table-item-ml__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/categories/edit/<?php echo $category['id_category']; ?>">Editar</a>
                                <a href="#" 
                                    class="btn-visibility-toggle" 
                                    data-id="<?php echo $category['id_category']; ?>" 
                                    data-current-hidden="<?php echo $category['hidden']; ?>">
                                    <?php echo ($category['hidden'] == 1) ? 'Mostrar en web' : 'Ocultar de web'; ?>
                                </a>
                                <a href="/admin/categories/delete/<?php echo $category['id_category']; ?>" class="btn-delete danger">Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="generic-grid-container">
            <?php foreach($categories as $category): ?>
                <div class="grid-item-card">
                    <a href="/admin/products?search=&sort=created_at-desc&category=<?php echo $category['id_category']; ?>&brand=&status=&type=">
                        <div class="grid-item-card__image">
                            <?php 
                                $originalFile = !empty($category['image']) ? $category['image'] : 'default.jpg'; 
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                }
                            ?>
                            <picture>
                                <source srcset="/assets/uploads/categories/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/categories/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($category['name']); ?>"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                >
                            </picture>
                        </div>
                        <div class="grid-item-card__content">
                            <?php if ($category['hidden'] == 1): ?>
                                <p class="info__meta">(Oculto)</p>
                            <?php endif; ?>
                            <p class="grid-item-card__title"><?php echo htmlspecialchars($category['name']); ?></p>
                        </div>
                    </a>
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/categories/edit/<?php echo $category['id_category']; ?>">Editar</a>
                                <a href="#" 
                                    class="btn-visibility-toggle" 
                                    data-id="<?php echo $category['id_category']; ?>" 
                                    data-current-hidden="<?php echo $category['hidden']; ?>">
                                    <?php echo ($category['hidden'] == 1) ? 'Mostrar en web' : 'Ocultar de web'; ?>
                                </a>
                                <a href="/admin/categories/delete/<?php echo $category['id_category']; ?>" class="btn-delete danger">Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-search-alt'></i>
            <h2>No se encontraron categorias</h2>
            <p>Prueba a cambiar los filtros o a restablecer la búsqueda.</p>
        </div>
    <?php endif; ?>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>