<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>

<section class="content-panel product-view-container view-list">    
    <?php if (!empty($blogs)): ?>
        
        <div class="product-list-container">
            <div class="product-list-ml">
                <?php foreach($blogs as $blog): ?>
                    <div class="product-item-ml">
                        <a href="/admin/blogs/edit/<?php echo $blog['id_blog']; ?>">
                            <div class="product-item-ml__image">
                                <?php 
                                    $originalFile = !empty($blog['cover_image']) ? $blog['cover_image'] : 'default.jpg'; 
                                    if ($originalFile === 'default.jpg') {
                                        $smallFile = 'default.jpg';
                                    } else {
                                        $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                        $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                    }
                                ?>

                                <picture>
                                    <source srcset="/assets/uploads/blog/posts/<?php echo rawurlencode($smallFile); ?>">
                                    <img src="/assets/uploads/blog/posts/<?php echo rawurlencode($originalFile); ?>"
                                    alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                    loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                    >
                                </picture>

                                
                            </div>
                            <div class="product-item-ml__info">
                                <p class="info__meta">Autor: <?php echo htmlspecialchars($blog['author'] ?? 'Admin'); ?> | Fecha: <?php echo date('d/m/Y', strtotime($blog['created_at'])); ?></p>
                                <p class="info__title"><?php echo htmlspecialchars($blog['title']); ?></p>
                            </div>
                        </a>

                        <div class="compact-row-details">
                            <div class="product-item-ml__status">
                                <div class="status-toggle-container">
                                    <button class="status-pill <?php echo (isset($blog['status']) && $blog['status'] === 'publicado') ? 'active' : 'inactive'; ?>">
                                        <?php echo ucfirst($blog['status'] ?? 'Borrador'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="product-item-ml__actions">
                            <div class="actions-menu-container">
                                <button type="button" class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                                <div class="actions-dropdown">
                                    <a href="/admin/blogs/edit/<?php echo $blog['id_blog']; ?>">Editar</a>
                                    <a href="/blog/<?php echo htmlspecialchars($blog['slug'] ?? ''); ?>" target="_blank">Ver artículo</a>
                                    <a href="#" class="danger" onclick="deleteBlog(<?php echo $blog['id_blog']; ?>); return false;">Eliminar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-grid-container">
            <?php foreach($blogs as $blog): ?>
                <div class="grid-item-card">
                    <a href="/admin/blogs/edit/<?php echo $blog['id_blog']; ?>">
                        <div class="grid-item-card__image">
                            <?php 
                                $originalFile = !empty($blog['cover_image']) ? $blog['cover_image'] : 'default.jpg'; 
                                if ($originalFile === 'default.jpg') {
                                    $smallFile = 'default.jpg';
                                } else {
                                    $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME); // ej: prod_123
                                    $smallFile = 'small_' . $nameWithoutExt . '.webp'; // ej: small_prod_123.webp
                                }
                            ?>

                            <picture>
                                <source srcset="/assets/uploads/blog/posts/<?php echo rawurlencode($smallFile); ?>">
                                <img src="/assets/uploads/blog/posts/<?php echo rawurlencode($originalFile); ?>"
                                alt="<?php echo htmlspecialchars($blog['title']); ?>"
                                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                                >
                            </picture>
                            
                        </div>
                    </a>
                    <div class="grid-item-card__content">
                        <a href="/admin/blogs/edit/<?php echo $blog['id_blog']; ?>">
                            <p class="info__meta blog-grid-meta">Autor: <?php echo htmlspecialchars($blog['author'] ?? 'Admin'); ?></p>
                            <p class="content__title"><?php echo htmlspecialchars($blog['title']); ?></p>
                        </a>

                        <div class="product-item-ml__status2 blog-grid-status-container">
                            <div class="status-toggle-container">
                                <button class="status-pill blog-status-pill-full <?php echo (isset($blog['status']) && $blog['status'] === 'publicado') ? 'active' : 'inactive'; ?>">
                                    <?php echo ucfirst($blog['status'] ?? 'Borrador'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <button type="button" class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/blogs/edit/<?php echo $blog['id_blog']; ?>">Editar</a>
                                <a href="/blog/<?php echo htmlspecialchars($blog['slug'] ?? ''); ?>" target="_blank">Ver artículo</a>
                                <a href="#" class="danger" onclick="deleteBlog(<?php echo $blog['id_blog']; ?>); return false;">Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-book-content'></i>
            <h2>No hay artículos en el blog todavía</h2>
            <p>Comienza a escribir tu primer post o cambia los filtros de búsqueda.</p>
        </div>
    <?php endif; ?>
</section>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>