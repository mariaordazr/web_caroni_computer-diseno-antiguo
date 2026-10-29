<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>
            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/clients/store">
                    <div class="admin-header-row" style="margin-bottom: 1.5rem;">
                        <h3>Registrar Nuevo Cliente</h3>
                    </div>
                    <div class="form-grid" style="margin-bottom: 2rem;">
                        <div class="form-column">
                            <label>Identificación (DNI / RIF):</label>
                            <input type="text" name="client_id_number" required style="width: 100%;" placeholder="Ej: V12345678">
                        </div>
                        <div class="form-column">
                            <label>Nombre y Apellido / Razón Social:</label>
                            <input type="text" name="client_name" required style="width: 100%;" placeholder="Nombre completo">
                        </div>
                    </div>
<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>