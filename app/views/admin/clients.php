<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>
<section class="content-panel product-view-container view-list"> 
    <?php if (!empty($clients)): ?>
        <div class="table-list-ml">
            <?php foreach($clients as $client): ?>
                <div class="table-item-ml">
                    <a href="/admin/clients/edit/<?php echo $client['id_client']; ?>">
                        <div class="table-item-ml__info" style="min-width: 200px;">
                            <p class="info__meta">DNI / RIF / Pasaporte: <?php echo htmlspecialchars($client['client_id_number']); ?></p>
                            <p class="info__title" style="font-weight: bold; font-size: 1em;"><?php echo htmlspecialchars($client['client_name']); ?></p>
                        </div>
                    </a>
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/clients/edit/<?php echo $client['id_client']; ?>"><i class='bx bx-edit'></i> Editar</a>
                                <a href="/admin/clients/delete/<?php echo $client['id_client']; ?>" class="danger" onclick="return confirm('¿Seguro que deseas eliminar este cliente?');"><i class='bx bx-trash'></i> Eliminar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-group'></i>
            <h2>No hay clientes registrados</h2>
            <p>Comienza a agregar clientes desde el botón superior.</p>
        </div>
    <?php endif; ?>
</section>
<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>