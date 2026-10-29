<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>
            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/storeBrand" enctype="multipart/form-data">

                    <div class="form-grid">
                        <div class="form-column">
                            <label for="name">Nombre de la Marca:</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-column">
                            <label>Imagen de la Marca:</label>
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
                                        <div class="upload-btn gallery-btn" data-capture="" title="Subir de galería">
                                            <i class='bx bx-image-add'></i>
                                            <span class="btn-text">Galería</span>
                                        </div>
                                    </div>
                                </label>
                                <input type="file" name="image" id="image-input" accept="image/*" style="display:none;">
                            </div>
                        </div>
                    </div>
<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>