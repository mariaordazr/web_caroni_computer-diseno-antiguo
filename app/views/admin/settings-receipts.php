<?php include ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<section class="content-panel">
    <div class="admin-header-row" style="margin-bottom: 1.5rem;">
        <h3>Configuración de Notas de Entrega</h3>
        <p style="color: var(--secondary-text-color);">Define los datos de tu empresa para que se impriman automáticamente en el PDF.</p>
    </div>
    
    <form class="edit-form" method="POST" action="/admin/receipts/settings">
        <div class="form-grid" style="display: flex; justify-content: center;">
            <div class="form-column" style="width: 100%; max-width: 600px; background: #f8f9fa; padding: 2rem; border-radius: 8px; border: 1px solid var(--border-color);">
                
                <label>Nombre de la Empresa</label>
                <input type="text" name="company_name" value="<?php echo htmlspecialchars($settings['company_name'] ?? 'Grupo Caroni Computer C.A'); ?>" required>
                
                <label>RIF / Documento</label>
                <input type="text" name="company_id" value="<?php echo htmlspecialchars($settings['company_id'] ?? ''); ?>" required>
                
                <label>Teléfono 1</label>
                <input type="text" name="company_phone_1" value="<?php echo htmlspecialchars($settings['company_phone_1'] ?? ''); ?>" required>
                
                <label>Teléfono 2</label>
                <input type="text" name="company_phone_2" value="<?php echo htmlspecialchars($settings['company_phone_2'] ?? ''); ?>" required>
                
                <label>Correo Electrónico</label>
                <input type="email" name="company_email" value="<?php echo htmlspecialchars($settings['company_email'] ?? ''); ?>" required>
                    
                <label>Dirección Física</label>
                <textarea name="company_address" rows="2" required><?php echo htmlspecialchars($settings['company_address'] ?? ''); ?></textarea>
                                
                <label>Nota importante</label>
                <input type="text" name="company_note" value="<?php echo htmlspecialchars($settings['company_note'] ?? ''); ?>" required>
                
                <label>Condición</label>
                <input type="text" name="company_condition" value="<?php echo htmlspecialchars($settings['company_condition'] ?? ''); ?>" required>
                
                <label>Garantía</label>
                <input type="text" name="company_warranty" value="<?php echo htmlspecialchars($settings['company_warranty'] ?? ''); ?>" required>
                
            </div>
        </div>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>