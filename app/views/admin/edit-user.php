<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/updateUser" enctype="multipart/form-data">
                    <input type="hidden" name="id_user" value="<?php echo $user['id_user']; ?>">

                    <div class="form-timestamps">
                        <span>Creado: <?php echo htmlspecialchars($user['created_at'] ?? 'No disponible'); ?></span>
                        <span>Modificado: <?php echo htmlspecialchars($user['updated_at'] ?? 'No disponible'); ?></span>
                    </div>

                    <div class="form-grid">
                        <div class="form-column">
                            <label for="name">Nombre completo:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                            <label for="username">Nombre de usuario:</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

                            <label for="email">Correo:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                                                    
                            <label for="role">Rol del usuario:</label>
                            <?php $isEditingSelf = ($user['id_user'] == $_SESSION['idUser']); ?>
                            
                            <select name="role" id="role" required <?php echo $isEditingSelf ? 'disabled' : ''; ?>>
                                <?php if ($userInfo['is_super_admin']==1): ?>
                                    <option value="admin" <?php echo (isset($user['role']) && htmlspecialchars($user['role']) == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                <?php endif; ?>
                                <option value="seller" <?php echo (isset($user['role']) && htmlspecialchars($user['role']) == 'seller') ? 'selected' : ''; ?>>Vendedor</option>
                                <option value="tech" <?php echo (isset($user['role']) && htmlspecialchars($user['role']) == 'tech') ? 'selected' : ''; ?>>Técnico</option>
                            </select>
                            
                            <?php if ($isEditingSelf): ?>
                                <!-- Se envía el rol actual de forma oculta al backend -->
                                <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                                <small style="color: #e74c3c; display: block; margin-top: 5px;">Por seguridad, no puedes cambiar tu propio rol.</small>
                            <?php endif; ?> 
                            
                        </div>
                        
                        <div class="form-column">
                            <label for="password">Nueva Contraseña (Dejar en blanco para no cambiar):</label>
                            <input type="password" id="password" name="password" placeholder="Escribir nueva contraseña">

                            <label for="password_confirmation">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repetir contraseña nueva">

                            <label>Avatar:</label>
                            <div class="image-upload-box <?php echo !empty($user['avatar']) ? 'has-preview' : ''; ?>">
                                <label for="image-input">
                                    <?php if (!empty($user['avatar'])): ?>
                                        <div class="image-preview-frame">
                                            <img src="/assets/uploads/users/<?php echo htmlspecialchars($user['avatar']); ?>" class="image-preview">
                                            <button type="button" class="remove-preview-btn"><i class='bx bx-x'></i></button>
                                        </div>
                                    <?php else: ?>
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
                                    <?php endif; ?>
                                </label>
                                <input type="file" name="image" id="image-input" accept="image/*" style="display:none;">
                                <input type="hidden" name="delete_image" value="">
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