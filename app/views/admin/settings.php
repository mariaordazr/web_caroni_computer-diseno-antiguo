<?php include ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

    <section class="content-panel">
        <div class="admin-page-layout">
            <div class="admin-form-column">
            
                <form action="/admin/settings/update" method="POST">
                    
                    <div class="admin-form-group" style="padding: 1rem;">
                        <label for="show_price"><h3>Precio</h3></label>
                        <p class="form-help">
                            Seleccione si desea mostrar u ocultar el precio en la tienda.
                        </p>
                        <select name="show_price" id="show_price" class="form-control">
                            <option value="1" <?php echo ($showPrice == 1) ? 'selected' : ''; ?>>Mostrar</option>
                            <option value="0" <?php echo ($showPrice == 0) ? 'selected' : ''; ?>>Ocultar</option>
                        </select>
                    </div>

                    <div class="admin-form-group" style="padding: 1rem;">
                        <label for="show_stock"><h3>Stock</h3></label>
                        <p class="form-help">
                            Seleccione si desea mostrar u ocultar el stock en la tienda.
                        </p>
                        <select name="show_stock" id="show_stock" class="form-control">
                            <option value="1" <?php echo ($showStock == 1) ? 'selected' : ''; ?>>Mostrar</option>
                            <option value="0" <?php echo ($showStock == 0) ? 'selected' : ''; ?>>Ocultar</option>
                        </select>
                    </div>

                    <?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>