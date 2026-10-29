<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>
            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/storeUser" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-column">
                            <label for="name">Nombre completo:</label>
                            <input type="text" id="name" placeholder="Escribir tu nombre completo" name="name" required>

                            <label for="username">Nombre de usuario:</label>
                            <input type="text" id="username" placeholder="Escribir tu nombre de usuario" name="username" required>

                            <label for="email">Correo:</label>
                            <input type="email" id="email" placeholder="Escribir tu correo" name="email" required>

                            <!-- <label for="is_super_admin">Tipo de usuario:</label>
                            <select name="is_super_admin" id="is_super_admin" required>
                                <option value="0" <?php echo (isset($user['is_super_admin']) && htmlspecialchars($user['is_super_admin']) == 0) ? 'selected' : ''; ?> disabled selected>Estándar</option>
                                <?php if ($userInfo['is_super_admin']==1): ?>
                                    <option value="1" <?php echo (isset($user['is_super_admin']) && htmlspecialchars($user['is_super_admin']) == 1) ? 'selected' : ''; ?>>Administrador</option>
                                <?php endif; ?>
                            </select> -->

                            <label for="role">Rol del usuario:</label>
                            <select name="role" id="role" required>
                                <?php if ($userInfo['is_super_admin']==1): ?>
                                    <option value="admin" <?php echo (isset($user['role']) && htmlspecialchars($user['role']) == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                <?php endif; ?>
                                <option value="seller" <?php echo (isset($user['role']) && htmlspecialchars($user['role']) == 'seller') ? 'selected' : ''; ?> selected>Vendedor</option>
                                <option value="tech" <?php echo (isset($user['role']) && htmlspecialchars($user['role']) == 'tech') ? 'selected' : ''; ?>>Técnico</option>
                            </select>
                        </div>

                        
                        <div class="form-column">
                            <label for="password">Contraseña:</label>
                            <input type="password" id="password" name="password" placeholder="Escribir tu contraseña" required>

                            <label for="password_confirmation">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repetir contraseña nueva" required>

                            <label>Avatar:</label>
                            <div class="image-upload-box">
                                <label for="image-input">
                                    <!-- <div class="image-placeholder" id="image-preview-container">
                                        <i class='bx bx-plus'></i>
                                    </div> -->
                                    <div class="image-placeholder desktop-view" id="image-preview-container">
                                        <i class='bx bx-plus'></i>
                                    </div>

                                    <div class="image-placeholder mobile-view mobile-split" id="image-preview-container">
                                        <div class="upload-btn camera-btn" data-capture="environment" title="Tomar foto">
                                            <i class='bx bx-camera'></i>
                                            <span class="btn-text">Cámara</span>
                                        </div>
                                        <div class="upload-btn gallery-btn" data-capture="" title="Subir de galería" id="image-preview-container">
                                            <i class='bx bx-image-add'></i>
                                            <span class="btn-text">Galería</span>
                                        </div>
                                    </div>
                                </label>
                                <input type="file" name="image" id="image-input" accept="image/*" style="display:none;">
                            </div>
                        </div>
                    </div>
                    <div id="crop-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
                        <div style="background:#fff; padding:20px; border-radius:8px; width:90%; max-width:600px; display:flex; flex-direction:column; gap:15px;">
                            <h3>Recortar Avatar</h3>
                            <div style="max-height: 400px; overflow: hidden;">
                                <img id="image-to-crop" style="max-width: 100%; display: block;">
                            </div>
                            <div style="display:flex; justify-content:end; gap:10px;">
                                <button type="button" id="btn-cancel-crop" class="btn" style="background:#dc3545; color:white;">Cancelar</button>
                                <button type="button" id="btn-crop-confirm" class="btn btn-primary">Recortar y Guardar</button>
                            </div>
                        </div>
                    </div>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>