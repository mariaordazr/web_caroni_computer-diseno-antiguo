<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>
            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/clients/update">
                    <div class="admin-header-row" style="margin-bottom: 1.5rem;">
                        <h3>Editar Cliente</h3>
                    </div>
                    <input type="hidden" name="id_client" value="<?php echo $client['id_client']; ?>">
                    <div class="form-grid" style="margin-bottom: 2rem;">
                        <div class="form-column">
                            <label>Identificación (DNI / RIF):</label>
                            <input type="text" name="client_id_number" value="<?php echo htmlspecialchars($client['client_id_number']); ?>" required style="width: 100%;">
                        </div>
                        <div class="form-column">
                            <label>Nombre y Apellido / Razón Social:</label>
                            <input type="text" name="client_name" value="<?php echo htmlspecialchars($client['client_name']); ?>" required style="width: 100%;">
                        </div>
                    </div>
<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>