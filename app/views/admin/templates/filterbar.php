<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<?php if (isset($active_menu)):?>
    <section class="content-panel">
        <form class="filter-bar" id="filter-form" method="GET">

            <div class="filter-group--search">
                <input type="search" enterkeyhint="search" name="search" class="filter-bar__search" placeholder="Buscar..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>

            <button type="button" id="mobile-filter-toggle" class="filter-toggle-btn">
                <span class="toggle-text"><i class='bx bx-filter-alt'></i> Filtros</span>
                <i class='bx bx-chevron-down toggle-arrow'></i>
            </button>

            <div class="filter-controls" id="filter-controls-container">
                
                <select name="sort" class="filter-bar__select" onchange="this.form.submit()">
                    <option value="created_at-desc" <?php echo (($_GET['sort'] ?? '') === 'created_at-desc') ? 'selected' : ''; ?>>Recientes</option>
                    <option value="created_at-asc" <?php echo (($_GET['sort'] ?? '') === 'created_at-asc') ? 'selected' : ''; ?>>Antiguos</option>
                    <option value="updated_at-desc" <?php echo (($_GET['sort'] ?? '') === 'updated_at-desc') ? 'selected' : ''; ?>>Edición (Reciente)</option>
                    <option value="updated_at-asc" <?php echo (($_GET['sort'] ?? '') === 'updated_at-asc') ? 'selected' : ''; ?>>Edición (Antigua)</option>
                    
                    <?php if ($active_menu === 'blogs'): ?>
                        <option value="title-asc" <?php echo (($_GET['sort'] ?? '') === 'title-asc') ? 'selected' : ''; ?>>Título (A-Z)</option>
                        <option value="title-desc" <?php echo (($_GET['sort'] ?? '') === 'title-desc') ? 'selected' : ''; ?>>Título (Z-A)</option>
                        <option value="author-asc" <?php echo (($_GET['sort'] ?? '') === 'author-asc') ? 'selected' : ''; ?>>Autor (A-Z)</option>
                        <option value="author-desc" <?php echo (($_GET['sort'] ?? '') === 'author-desc') ? 'selected' : ''; ?>>Autor (Z-A)</option>
                    <?php elseif ($active_menu !== 'receipts'): ?>
                            <option value="name-asc" <?php echo (($_GET['sort'] ?? '') === 'name-asc') ? 'selected' : ''; ?>>A-Z</option>
                            <option value="name-desc" <?php echo (($_GET['sort'] ?? '') === 'name-desc') ? 'selected' : ''; ?>>Z-A</option>
                    <?php endif; ?>

                    <?php if (isset($pageType) && $pageType !== 'basic_list' && $active_menu !== 'blogs'): ?>
                        <option value="price-asc" <?php echo (($_GET['sort'] ?? '') === 'price-asc') ? 'selected' : ''; ?>>$ Menor</option>
                        <option value="price-desc" <?php echo (($_GET['sort'] ?? '') === 'price-desc') ? 'selected' : ''; ?>>$ Mayor</option>
                        <option value="stock-asc" <?php echo (($_GET['sort'] ?? '') === 'stock-asc') ? 'selected' : ''; ?>>Stock Bajo</option>
                        <option value="stock-desc" <?php echo (($_GET['sort'] ?? '') === 'stock-desc') ? 'selected' : ''; ?>>Stock Alto</option>
                    <?php endif; ?>
                </select>

                <?php if ($active_menu === 'blogs'): ?>
                    <select name="status" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Estado</option>
                        <option value="publicado" <?php echo (($_GET['status'] ?? '') === 'publicado') ? 'selected' : ''; ?>>Publicado</option>
                        <option value="borrador" <?php echo (($_GET['status'] ?? '') === 'borrador') ? 'selected' : ''; ?>>Borrador</option>
                    </select>
                <?php endif; ?>

                <?php if ($active_menu === 'receipts'): ?>
                    <select name="status" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Todos los Estados</option>
                        <option value="in_progress" <?php echo (($_GET['status'] ?? '') === 'in_progress') ? 'selected' : ''; ?>>En proceso</option>
                        <option value="finished" <?php echo (($_GET['status'] ?? '') === 'finished') ? 'selected' : ''; ?>>Finalizado</option>
                        <option value="sent_wa" <?php echo (($_GET['status'] ?? '') === 'sent_wa') ? 'selected' : ''; ?>>Enviados (WhatsApp)</option>
                        <option value="sent_email" <?php echo (($_GET['status'] ?? '') === 'sent_email') ? 'selected' : ''; ?>>Enviados (Correo)</option>
                        <option value="not_sent" <?php echo (($_GET['status'] ?? '') === 'not_sent') ? 'selected' : ''; ?>>No Enviados</option>
                        <option value="edited" <?php echo (($_GET['status'] ?? '') === 'edited') ? 'selected' : ''; ?>>Editados</option>
                    </select>
                <?php endif; ?>

                <?php if (isset($active_menu) && $active_menu === 'users'): ?>
                    <select name="role" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Todos los Roles</option>
                        <option value="admin" <?php echo (($_GET['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                        <option value="seller" <?php echo (($_GET['role'] ?? '') === 'seller') ? 'selected' : ''; ?>>Vendedor</option>
                        <option value="tech" <?php echo (($_GET['role'] ?? '') === 'tech') ? 'selected' : ''; ?>>Técnico</option>
                    </select>
                <?php endif; ?>

                <?php if (isset($pageType) && $pageType !== 'basic_list' && $active_menu !== 'blogs'): ?>
                    <select name="category" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Categoría</option>
                        <?php foreach($headerCategories as $category): ?>
                            <option value="<?php echo $category['id_category']; ?>" <?php echo (($_GET['category'] ?? '') == $category['id_category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst(mb_strtolower($category['name']))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="brand" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Marca</option>
                        <?php foreach($headerBrands as $brand): ?>
                            <option value="<?php echo $brand['id_brand']; ?>" <?php echo (($_GET['brand'] ?? '') == $brand['id_brand']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst(mb_strtolower($brand['name']))); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <?php if (isset($pageType) && $pageType === 'full_list' && $active_menu !== 'blogs'): ?>
                    <select name="status" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Estado</option>
                        <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Disponibles</option>
                        <option value="sold_out" <?php echo (($_GET['status'] ?? '') === 'sold_out') ? 'selected' : '';?>>Agotados</option>
                    </select>

                    <select name="type" class="filter-bar__select" onchange="this.form.submit()">
                        <option value="">Tipo</option>
                        <option value="offer" <?php echo (($_GET['type'] ?? '') === 'offer') ? 'selected' : ''; ?>>Oferta</option>
                        <option value="new" <?php echo (($_GET['type'] ?? '') === 'new') ? 'selected' : ''; ?>>Nuevos</option>
                        <option value="none" <?php echo (($_GET['type'] ?? '') === 'none') ? 'selected' : ''; ?>>Normal</option>
                    </select>
                <?php endif; ?>

                <a href="/admin/<?php echo htmlspecialchars($active_menu); ?>" class="btn btn-clear-icon" title="Limpiar Filtros">
                    <i class='bx bx-revision'></i>
                </a>
            </div>
        </form>
    </section>
<?php endif; ?>