<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/updateCategory" enctype="multipart/form-data">
                    <input type="hidden" name="id_category" value="<?php echo $category['id_category']; ?>">
                    
                    <div class="form-timestamps">
                        <span>Creado: <?php echo htmlspecialchars($category['created_at'] ?? 'No disponible'); ?></span>
                        <span>Modificado: <?php echo htmlspecialchars($category['updated_at'] ?? 'No disponible'); ?></span>
                    </div>

                    <div class="form-grid">
                        <div class="form-column">
                            <label for="name">Nombre de la categoría:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>">
                        </div>
                        <div class="form-column">
                            <label>Imagen:</label>
                            <div class="image-upload-box <?php echo !empty($category['image']) ? 'has-preview' : ''; ?>">
                                <label for="image-input">
                                    <?php if (!empty($category['image'])): ?>
                                        <div class="image-preview-frame">
                                            <img src="/assets/uploads/categories/<?php echo htmlspecialchars($category['image']); ?>" class="image-preview">
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
                                            <div class="upload-btn gallery-btn" data-capture="" title="Subir de galería">
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
<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>