<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<section class="content-panel">
    <form class="edit-form" method="POST" action="/admin/pages/blog/update" enctype="multipart/form-data">
        <div class="admin-page-layout">
            <div class="admin-form-column">
                
                <div class="admin-form-group">
                    <h3>Banner Principal del Blog</h3>
                    <p>Gestiona la imagen del encabezado de la página.</p>
                    <div class="image-manager">
                        <div class="image-slots-container" style="justify-content: center;">
                            <?php 
                                // Banner logic
                                $item0 = $bannerImage; 
                                $imageName0 = $item0['content_value'] ?? null; 
                                $isExisting0 = !empty($imageName0);
                            ?>
                            <div class="image-slot <?php echo $isExisting0 ? 'has-preview' : ''; ?>" style="width:95%;height:auto;min-height:95% !important; aspect-ratio: 3.5/1;">
                                <input type="hidden" class="slot-image-id" name="slot_image_id_banner" value="<?php echo $isExisting0 ? htmlspecialchars($imageName0) : ''; ?>">
                                <input type="hidden" class="delete-slot-flag" name="delete_image_banner" value="">
                                <label for="banner-slot">
                                    <?php if ($isExisting0): ?>
                                        <div class="image-preview-slot-frame">
                                            <img src="/assets/uploads/blog/banner/<?php echo htmlspecialchars($imageName0); ?>" class="image-preview-slot" >
                                            <button type="button" class="remove-preview-btn"><i class='bx bx-x'></i></button> 
                                        </div>
                                    <?php else: ?>
                                        <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                    <?php endif; ?>
                                </label>
                                <input type="file" name="blog_banner_image" id="banner-slot" accept="image/*" style="display:none;">
                            </div>
                        </div>
                    </div>
                </div>

                
                <div id="crop-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
                    <div style="background:#fff; padding:20px; border-radius:8px; width:90%; max-width:800px; display:flex; flex-direction:column; gap:15px; max-height:90vh;">
                        <h3 id="crop-modal-title">Recortar Imagen</h3>
                        <div style="max-height: 60vh; overflow: hidden; background:#f0f0f0; display:flex; justify-content:center;">
                            <img id="image-to-crop" style="max-width: 100%; display: block;">
                        </div>
                        <div style="display:flex; justify-content:end; gap:10px;">
                            <button type="button" id="btn-cancel-crop" class="btn" style="background:#dc3545; color:white;">Cancelar</button>
                            <button type="button" id="btn-crop-confirm" class="btn btn-primary">Recortar y Usar</button>
                        </div>
                    </div>
                </div>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>