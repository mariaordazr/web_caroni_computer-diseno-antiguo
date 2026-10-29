<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<section class="content-panel">
    <form class="edit-form" method="POST" action="/admin/pages/shipping/update" enctype="multipart/form-data">
        <div class="admin-page-layout">
            
            <div class="admin-form-column">
                
                <div class="form-grid">
                    
                    <div class="form-column">
                        <h3>Banner Principal</h3>
                        <div class="image-manager">
                            <div class="image-slots-container" style="justify-content: center;">
                                <?php 
                                    $i=0; 
                                    $imageName = $bannerImage['content_value'] ?? null; 
                                    $isExisting = !empty($imageName);
                                ?>
                                <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>" style="width:95%;height:auto;min-height:95% !important; aspect-ratio: 3/1;">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $i; ?>" value=""> <label for="image-slot-<?php echo $i; ?>">
                                        <?php if ($isExisting): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/shipping/banner/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot">
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $i; ?>"><i class='bx bx-x'></i></button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $i; ?>" id="image-slot-<?php echo $i; ?>" accept="image/*" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-column">

                        <h3>Sección Local</h3>
                        
                        <label for="s2_title" class="form-label" style="margin-top:1rem;">Título Local</label>
                        <input type="text" name="shipping_title_1" id="s2_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[1] ?? ''); ?>">
                        
                        <label for="s2_address" class="form-label">Texto Dirección</label>
                        <textarea name="shipping_text_1" id="s2_address" class="form-control"><?php echo htmlspecialchars($textIds[1] ?? ''); ?></textarea>
                    
                        <div class="image-manager">
                            <div class="image-slots-container">
                                <?php 
                                    $i=1; 
                                    $imageName = $shippingImage['content_value'] ?? null; 
                                    $isExisting = !empty($imageName);
                                ?>
                                <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $i; ?>" value="">
                                
                                    <label for="image-slot-<?php echo $i; ?>">
                                        <?php if ($isExisting): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/shipping/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot">
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $i; ?>"><i class='bx bx-x'></i></button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $i; ?>" id="image-slot-<?php echo $i; ?>" accept="image/*" style="display:none;">
                                </div>
                            </div>
                        </div>

                        <label for="s2_map_url" class="form-label">URL Google Maps</label>
                        <input type="text" name="shipping_link" id="s2_map_url" class="form-control" value="<?php echo htmlspecialchars($shippingLink['content_value'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-column">
                        <h3>Sección Envíos / Marcas</h3>
                        <label for="s1_title" class="form-label">Título Envíos</label>
                        <input type="text" name="shipping_title_0" id="s1_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[0] ?? ''); ?>">
                    
                        <label for="s1_text" class="form-label">Texto Envíos</label>
                        <textarea name="shipping_text_0" id="s1_text" class="form-control"><?php echo htmlspecialchars($textIds[0] ?? ''); ?></textarea>

                        <div class="image-manager">
                            <div class="image-slots-container" id="brands-container" style="flex-wrap: wrap;">
                                
                                <?php $currentIndex = 2; 
                                // $brandContent viene del controlador con todas las marcas ordenadas
                                if (!empty($brandContent)): 
                                    foreach ($brandContent as $brand):
                                        $imageName = $brand['content_value'];?>
                                        <div class="image-slot has-preview" data-index="<?php echo $currentIndex; ?>">
                                            <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $currentIndex; ?>" value="<?php echo htmlspecialchars($imageName); ?>">
                                            <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $currentIndex; ?>" value="">
                                        
                                            <label for="image-slot-<?php echo $currentIndex; ?>">
                                                <div class="image-preview-slot-frame">
                                                    <img src="/assets/uploads/shipping/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot">
                                                    <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $currentIndex; ?>"><i class='bx bx-x'></i></button> 
                                                </div>
                                            </label>
                                            <input type="file" name="image_slot_<?php echo $currentIndex; ?>" id="image-slot-<?php echo $currentIndex; ?>" accept="image/*" style="display:none;">
                                        </div>
                                        <?php $currentIndex++;
                                    endforeach; 
                                endif; ?>
                                <div class="image-slot" id="btn-add-brand">
                                    <label>
                                        <div class="image-placeholder-slot" >
                                            <i class='bx bx-plus'></i>
                                        </div>
                                    </label>
                                </div>
                            </div>
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

            <div id="preview-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,1); z-index:99999; overflow-y:auto;">
                
                <div style="position:sticky; top:0; left:0; width:100%; background:#000; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; z-index:1000; box-shadow:0 2px 10px rgba(0,0,0,0.2);">
                    <h3 style="color:#fff; margin:0;">Vista Previa (No guardado)</h3>
                    <div style="display:flex; gap:10px;">
                        <button type="button" id="btn-close-preview" class="btn" style="background:#fff; color:#000;">Seguir Editando</button>
                        <button type="button" onclick="document.querySelector('.edit-form').submit();" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </div>

                <div class="preview-content-wrapper" style="background-color: #ededed; padding-bottom: 50px;">
                    
                    <section class="hero-section" id="preview-banner-bg" style="background-size: cover; background-position: center;">
                        <div class="container">
                            <h1 class="hero-section__title">Envíos a Nivel Nacional</h1>
                        </div>
                    </section>

                    <section class="section">
                        <div class="container content-section">
                            <h2 id="preview-title-0"></h2>
                            <p id="preview-text-0"></p>

                            <div style="display: flex; gap: 3rem; overflow-x: auto; padding: 20px 0; justify-content: center; align-items: center;" id="preview-brands-container">
                                </div>
                        </div>
                    </section>

                    <section class="section section--light">
                        <div class="container content-section">
                            <div class="shipping-location-container">
                                <h2 id="preview-title-1"></h2>
                                <p class="about__description" id="preview-text-1"></p>
                                
                                <div class="store-image-container">
                                    <img id="preview-img-1" src="" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                
                                <div id="preview-map-container" style="margin-top: 2rem; width: 100%;">
                                    <iframe id="preview-map-iframe" width="100%" height="450" style="border:0; border-radius:12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);" allowfullscreen="" loading="lazy"></iframe>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>

    <script src="/assets/admin/js/admin-shipping-preview.js"></script>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>