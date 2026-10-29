<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>


<section class="content-panel">
    <form class="edit-form" method="POST" action="/admin/pages/about/update" enctype="multipart/form-data">
        <div class="admin-page-layout">
            <div class="admin-form-column">
                
                <div class="form-grid">
                    <div class="form-column">
                        <h3>Banner Principal</h3>
                        <p>Gestiona la imagen del banner superior.</p>
                        <div class="image-manager">
                            <div class="image-slots-container" style="justify-content: center; align-items: center;">
                                <?php 
                                    $imgSlot = 0; // Iniciamos contador de SLOTS de imagen
                                    $item0 = $bannerImage; 
                                    $imageName0 = $item0['content_value'] ?? null; 
                                    $isExisting0 = !empty($imageName0);
                                ?>
                                <div class="image-slot <?php echo $isExisting0 ? 'has-preview' : ''; ?>" style="width:95%;height:auto;min-height:95% !important; aspect-ratio: 3/1;">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $imgSlot; ?>" value="<?php echo $isExisting0 ? htmlspecialchars($imageName0) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $imgSlot; ?>" value="">
                                    <label for="image-slot-<?php echo $imgSlot; ?>">
                                        <?php if ($isExisting0): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/about-us/banner/<?php echo htmlspecialchars($imageName0); ?>" class="image-preview-slot" >
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $imgSlot; ?>">
                                                    <i class='bx bx-x'></i>
                                                </button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $imgSlot; ?>" id="image-slot-<?php echo $imgSlot; ?>" accept="image/*" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-column">
                        <?php $txtIdx = 0; // Iniciamos contador de TEXTOS ?>

                        <div class="form-column">
                            <h3>Sección 1 (Objetivo)</h3>
                            <label class="form-label">Título</label>
                            <input type="text" name="about_title_<?php echo $txtIdx; ?>" class="form-control" value="<?php echo htmlspecialchars($titleIds[$txtIdx] ?? ''); ?>">
                        
                            <label class="form-label">Texto</label>
                            <textarea name="about_text_<?php echo $txtIdx; ?>" class="form-control"><?php echo htmlspecialchars($textIds[$txtIdx] ?? ''); ?></textarea>

                            <div class="image-manager">
                                <div class="image-slots-container">
                                    <?php 
                                        $imgSlot = 1; // Siguiente slot de imagen
                                        // imageIds viene del controller. imageIds[0] corresponde a la primera imagen del cuerpo
                                        $imageName = $imageIds[$imgSlot - 1] ?? null; 
                                        $isExisting = !empty($imageName);
                                    ?>
                                    <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>">
                                        <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $imgSlot; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                        <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $imgSlot; ?>" value="">
                                    
                                        <label for="image-slot-<?php echo $imgSlot; ?>">
                                            <?php if ($isExisting): ?>
                                                <div class="image-preview-slot-frame">
                                                    <img src="/assets/uploads/about-us/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot" >
                                                    <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $imgSlot; ?>"><i class='bx bx-x'></i></button> 
                                                </div>
                                            <?php else: ?>
                                                <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                            <?php endif; ?>
                                        </label>
                                        <input type="file" name="image_slot_<?php echo $imgSlot; ?>" id="image-slot-<?php echo $imgSlot; ?>" accept="image/*" style="display:none;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php $txtIdx++; // Incrementamos índice de texto para la siguiente sección ?>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-column">
                        <h3>Sección 2 (Misión)</h3>
                        <label class="form-label">Título</label>
                        <input type="text" name="about_title_<?php echo $txtIdx; ?>" class="form-control" value="<?php echo htmlspecialchars($titleIds[$txtIdx] ?? ''); ?>">

                        <label class="form-label">Texto</label>
                        <textarea name="about_text_<?php echo $txtIdx; ?>" class="form-control"><?php echo htmlspecialchars($textIds[$txtIdx] ?? ''); ?></textarea>
                        
                        <div class="image-manager">
                            <div class="image-slots-container">
                                <?php 
                                    $imgSlot = 2;
                                    $imageName = $imageIds[$imgSlot - 1] ?? null; 
                                    $isExisting = !empty($imageName);
                                ?>
                                <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $imgSlot; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $imgSlot; ?>" value="">
                                    <label for="image-slot-<?php echo $imgSlot; ?>">
                                        <?php if ($isExisting): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/about-us/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot" >
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $imgSlot; ?>"><i class='bx bx-x'></i></button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $imgSlot; ?>" id="image-slot-<?php echo $imgSlot; ?>" accept="image/*" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $txtIdx++; ?>

                    <div class="form-column">
                        <h3>Sección 2 (Visión)</h3>
                        <label class="form-label">Título</label>
                        <input type="text" name="about_title_<?php echo $txtIdx; ?>" class="form-control" value="<?php echo htmlspecialchars($titleIds[$txtIdx] ?? ''); ?>">
                    
                        <label class="form-label">Texto</label>
                        <textarea name="about_text_<?php echo $txtIdx; ?>" class="form-control"><?php echo htmlspecialchars($textIds[$txtIdx] ?? ''); ?></textarea>

                        <div class="image-manager">
                            <div class="image-slots-container">
                                <?php 
                                    $imgSlot = 3;
                                    $imageName = $imageIds[$imgSlot - 1] ?? null; 
                                    $isExisting = !empty($imageName);
                                ?>
                                <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $imgSlot; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $imgSlot; ?>" value="">
                                    <label for="image-slot-<?php echo $imgSlot; ?>">
                                        <?php if ($isExisting): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/about-us/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot" >
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $imgSlot; ?>"><i class='bx bx-x'></i></button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $imgSlot; ?>" id="image-slot-<?php echo $imgSlot; ?>" accept="image/*" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $txtIdx++; ?>
                </div>

                <div class="form-grid">

                    <div class="form-column">
                        <h3>Sección 2 (Valores)</h3>
                        <label class="form-label">Título</label>
                        <input type="text" name="about_title_<?php echo $txtIdx; ?>" class="form-control" value="<?php echo htmlspecialchars($titleIds[$txtIdx] ?? ''); ?>">

                        <label class="form-label">Texto</label>
                        <textarea name="about_text_<?php echo $txtIdx; ?>" class="form-control"><?php echo htmlspecialchars($textIds[$txtIdx] ?? ''); ?></textarea>
                        
                        <div class="image-manager">
                            <div class="image-slots-container">
                                <?php 
                                    $imgSlot = 4;
                                    $imageName = $imageIds[$imgSlot - 1] ?? null; 
                                    $isExisting = !empty($imageName);
                                ?>
                                <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $imgSlot; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $imgSlot; ?>" value="">
                                    <label for="image-slot-<?php echo $imgSlot; ?>">
                                        <?php if ($isExisting): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/about-us/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot" >
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $imgSlot; ?>"><i class='bx bx-x'></i></button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $imgSlot; ?>" id="image-slot-<?php echo $imgSlot; ?>" accept="image/*" style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $txtIdx++; ?>

                    <div class="form-column">
                        <h3>Sección 3 (Equipo)</h3>
                        <label class="form-label">Título</label>
                        <input type="text" name="about_title_<?php echo $txtIdx; ?>" class="form-control" value="<?php echo htmlspecialchars($titleIds[$txtIdx] ?? ''); ?>">
                    
                        <label class="form-label">Texto</label>
                        <textarea name="about_text_<?php echo $txtIdx; ?>" class="form-control"><?php echo htmlspecialchars($textIds[$txtIdx] ?? ''); ?></textarea>
                        
                        <div class="image-manager">
                            <div class="image-slots-container">
                                <?php 
                                    $imgSlot = 5;
                                    $imageName = $imageIds[$imgSlot - 1] ?? null; 
                                    $isExisting = !empty($imageName);
                                ?>
                                <div class="image-slot <?php echo $isExisting ? 'has-preview' : ''; ?>">
                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $imgSlot; ?>" value="<?php echo $isExisting ? htmlspecialchars($imageName) : ''; ?>">
                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $imgSlot; ?>" value="">
                                    <label for="image-slot-<?php echo $imgSlot; ?>">
                                        <?php if ($isExisting): ?>
                                            <div class="image-preview-slot-frame">
                                                <img src="/assets/uploads/about-us/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot" >
                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $imgSlot; ?>"><i class='bx bx-x'></i></button> 
                                            </div>
                                        <?php else: ?>
                                            <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                                        <?php endif; ?>
                                    </label>
                                    <input type="file" name="image_slot_<?php echo $imgSlot; ?>" id="image-slot-<?php echo $imgSlot; ?>" accept="image/*" style="display:none;">
                                </div>
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

        <!-------------------- Modal de Vista Previa -------------------->

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
                        <h1 class="hero-section__title">Quiénes somos</h1>
                    </div>
                </section>

                <section class="section section--light">
                    <div class="container content-section">
                        <div class="info-grid">
                            <div class="info-grid__item about-text-content">
                                <h3 id="preview-title-0"></h3>
                                <p id="preview-text-0"></p>
                            </div>
                            <div class="info-grid__item">
                                <div class="about-image-wrapper">
                                    <img id="preview-img-1" src="" style="width:100%; border-radius:8px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="section">
                    <div class="container">
                        <div class="about-values-grid">
                            
                            <div class="about-card">
                                <h3 id="preview-title-1"></h3>
                                <p id="preview-text-1"></p>
                                <div class="about-card__image">
                                    <img id="preview-img-2" class="img-ratio-4-3" src="">
                                </div>
                            </div>

                            <div class="about-card">
                                <h3 id="preview-title-2"></h3>
                                <p id="preview-text-2"></p>
                                <div class="about-card__image">
                                    <img id="preview-img-3" class="img-ratio-4-3" src="">
                                </div>
                            </div>

                            <div class="about-card">
                                <h3 id="preview-title-3"></h3>
                                <p id="preview-text-3"></p>
                                <div class="about-card__image">
                                    <img id="preview-img-4" class="img-ratio-4-3" src="">
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

                <section class="section section--light">
                    <div class="container content-section">
                        <div class="info-grid">
                            <div class="info-grid__item">
                                <div class="about-image-wrapper">
                                    <img id="preview-img-5" src="" style="width:100%; border-radius:8px;">
                                </div>
                            </div>
                            <div class="info-grid__item about-text-content">
                                <h3 id="preview-title-4"></h3>
                                <p id="preview-text-4"></p>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>

        <script src="/assets/admin/js/admin-about-preview.js"></script>
<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>