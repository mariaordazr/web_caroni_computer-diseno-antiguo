<?php include_once ROOT_PATH . 'app/views/admin/templates/sidebar.php'; ?>

<header class="main-header-admin" style="background: transparent; box-shadow: none; padding: 0 1rem; margin-bottom: 1rem;">

    <div class="header-admin__greeting">
        <button id="mobile-menu-toggle" class="mobile-menu-toggle">
            <i class='bx bx-menu'></i>
        </button>
        <h2><?php echo htmlspecialchars($pageTitle ?? ''); ?></h2>

    </div>

    <?php if (isset($pageType) && $pageType != 'crud'):?>
        <div class="header-admin__actions" style="margin-bottom: 20px;">
            <div class="view-toggle">
                <button id="btn-view-list" class="view-toggle__btn is-active" title="Vista de lista"><i class='bx bx-list-ul'></i></button>
                <button id="btn-view-grid" class="view-toggle__btn" title="Vista de cuadrícula"><i class='bx bxs-grid-alt'></i></button>
            </div>
            <a href="/admin/<?php echo htmlspecialchars($active_menu ?? ''); ?>/create" class="btn btn-primary">
                <i class='bx bx-plus'></i> Agregar
            </a>
        </div>
    <?php endif; ?>


    <?php if (isset($active_menu) && (in_array($active_menu, ['pages-about', 'pages-services', 'pages-shipping', 'pages-home']))): ?>
        <div class="header-admin__actions" style="margin-bottom: 20px; display:flex; justify-content:flex-end;">
            <button type="button" id="btn-open-preview" class="btn" style="background-color: #333; color: white; display:flex; align-items:center; gap:5px;">
                <i class='bx bx-show-alt'></i> Previsualizar Cambios
            </button>
        </div>
    <?php endif; ?>
</header>