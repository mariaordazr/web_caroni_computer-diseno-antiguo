<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/pages/services/update" enctype="multipart/form-data">
                    <div class="admin-page-layout">
                        <div class="admin-form-column">
                            <div class="form-grid">
                                <div class="form-column">
                                    <h3>Banner Principal</h3>
                                    <p>Gestiona la imagen del banner secundario.</p>
                                    <div class="image-manager">
                                        <div class="image-slots-container"  style="justify-content: center; align-items: center;">
                                            <?php $i=0 ?>
                                            <?php $item0 = $bannerImage; $imageName0 = $item0['content_value'] ?? null; $isExisting0 = !empty($imageName0);?>

                                            <div class="image-slot <?php echo $isExisting0 ? 'has-preview' : ''; ?>" style="width:95%;height:auto;min-height:95% !important; aspect-ratio: 3/1;">
                                                <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting0 ? htmlspecialchars($imageName0) : ''; ?>">
                                                <input type="hidden" class="delete-slot-flag" name="delete_slot_<?php echo $i; ?>" value="0">
                                            
                                                <label for="image-slot-<?php echo $i; ?>">
                                                    <?php if ($isExisting0): ?>
                                                        <div class="image-preview-slot-frame">
                                                            <img src="/assets/uploads/services/banner/<?php echo htmlspecialchars($imageName0); ?>" class="image-preview-slot" >
                                                            <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $i; ?>">
                                                                <i class='bx bx-x'></i>
                                                            </button> 
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="image-placeholder-slot">
                                                            <i class='bx bx-plus'></i>
                                                        </div>
                                                    <?php endif; ?>

                                                </label>
                                                <input type="file" name="image_slot_<?php echo $i; ?>" id="image-slot-<?php echo $i; ?>" accept="image/*" style="display:none;">
                                                <?php if ($isExisting0): ?>
                                                    <input type="hidden" name="delete_image_<?php echo $i; ?>" value="">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-column">
                                    <h3>Sección 1 (Tarjeta de Servicio 1)</h3>
                                    <label for="card1_title" class="form-label">Título</label>
                                    <input type="text" name="service_title_1" id="card1_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[1] ?? ''); ?>">

                                    <label for="card1_text" class="form-label">Texto</label>
                                    <textarea name="service_text_1" id="card1_text" class="form-control"><?php echo htmlspecialchars($textIds[1] ?? ''); ?></textarea>
                                
                                    <div class="image-manager">
                                        <div class="image-slots-container">
                                            <?php $i=1;?> <?php $imageName1 = $imageIds[0] ?? null; $isExisting1 = !empty($imageName1);?>

                                            <div class="image-slot <?php echo $isExisting1 ? 'has-preview' : ''; ?>">
                                                <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting1 ? htmlspecialchars($imageName1) : ''; ?>">
                                                <input type="hidden" class="delete-slot-flag" name="delete_slot_<?php echo $i; ?>" value="0">
                                            
                                                <label for="image-slot-<?php echo $i; ?>">
                                                    <?php if ($isExisting1): ?>
                                                        <div class="image-preview-slot-frame">
                                                            <img src="/assets/uploads/services/body/<?php echo htmlspecialchars($imageName1); ?>" class="image-preview-slot" >
                                                            <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $i; ?>">
                                                                <i class='bx bx-x'></i>
                                                            </button> 
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="image-placeholder-slot">
                                                            <i class='bx bx-plus'></i>
                                                        </div>
                                                    <?php endif; ?>

                                                </label>
                                                <input type="file" name="image_slot_<?php echo $i; ?>" id="image-slot-<?php echo $i; ?>" accept="image/*" style="display:none;">
                                                <?php if ($isExisting1): ?>
                                                    <input type="hidden" name="delete_image_<?php echo $i; ?>" value="">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid">
                                
                                <div class="form-column"> 
                                    <h3>Sección 1 (Tarjeta de Servicio 2)</h3>  
                                    <label for="card2_title" class="form-label">Título</label>
                                    <input type="text" name="service_title_2" id="card2_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[2] ?? ''); ?>">

                                    <label for="card2_text" class="form-label">Texto</label>
                                    <textarea name="service_text_2" id="card2_text" class="form-control"><?php echo htmlspecialchars($textIds[2] ?? ''); ?></textarea>
                                
                                    <div class="image-manager">
                                        <div class="image-slots-container">
                                            <?php $i=2;?> <?php $imageName2 = $imageIds[1] ?? null; $isExisting2 = !empty($imageName2);?>

                                            <div class="image-slot <?php echo $isExisting2 ? 'has-preview' : ''; ?>">
                                                <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting2 ? htmlspecialchars($imageName2) : ''; ?>">
                                                <input type="hidden" class="delete-slot-flag" name="delete_slot_<?php echo $i; ?>" value="0">
                                            
                                                <label for="image-slot-<?php echo $i; ?>">
                                                    <?php if ($isExisting2): ?>
                                                        <div class="image-preview-slot-frame">
                                                            <img src="/assets/uploads/services/body/<?php echo htmlspecialchars($imageName2); ?>" class="image-preview-slot" >
                                                            <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $i; ?>">
                                                                <i class='bx bx-x'></i>
                                                            </button> 
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="image-placeholder-slot">
                                                            <i class='bx bx-plus'></i>
                                                        </div>
                                                    <?php endif; ?>

                                                </label>
                                                <input type="file" name="image_slot_<?php echo $i; ?>" id="image-slot-<?php echo $i; ?>" accept="image/*" style="display:none;">
                                                <?php if ($isExisting2): ?>
                                                    <input type="hidden" name="delete_image_<?php echo $i; ?>" value="">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-column">  
                                    <h3>Sección 1 (Tarjeta de Servicio 3)</h3>
                                    <label for="card3_title" class="form-label">Título</label>
                                    <input type="text" name="service_title_3" id="card3_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[3] ?? ''); ?>">

                                    <label for="card3_text" class="form-label">Texto</label>
                                    <textarea name="service_text_3" id="card3_text" class="form-control"><?php echo htmlspecialchars($textIds[3] ?? ''); ?></textarea>
                                
                                    <div class="image-manager">
                                        <div class="image-slots-container">
                                            <?php $i=3;?> <?php $imageName3 = $imageIds[2] ?? null; $isExisting3 = !empty($imageName3);?>

                                            <div class="image-slot <?php echo $isExisting3 ? 'has-preview' : ''; ?>">
                                                <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting3 ? htmlspecialchars($imageName3) : ''; ?>">
                                                <input type="hidden" class="delete-slot-flag" name="delete_slot_<?php echo $i; ?>" value="0">
                                            
                                                <label for="image-slot-<?php echo $i; ?>">
                                                    <?php if ($isExisting3): ?>
                                                        <div class="image-preview-slot-frame">
                                                            <img src="/assets/uploads/services/body/<?php echo htmlspecialchars($imageName3); ?>" class="image-preview-slot" >
                                                            <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $i; ?>">
                                                                <i class='bx bx-x'></i>
                                                            </button> 
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="image-placeholder-slot">
                                                            <i class='bx bx-plus'></i>
                                                        </div>
                                                    <?php endif; ?>

                                                </label>
                                                <input type="file" name="image_slot_<?php echo $i; ?>" id="image-slot-<?php echo $i; ?>" accept="image/*" style="display:none;">
                                                <?php if ($isExisting3): ?>
                                                    <input type="hidden" name="delete_image_<?php echo $i; ?>" value="">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-column">
                                    <h3>Sección 2 (Video)</h3>
                                    <label for="s2_title" class="form-label">Título</label>
                                    <input type="text" name="service_title_4" id="s2_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[4] ?? ''); ?>">
                                    
                                    <label for="s2_text1" class="form-label">Texto</label>
                                    <textarea name="service_text_4" id="s2_text1" class="form-control"><?php echo htmlspecialchars($textIds[4] ?? ''); ?></textarea>
                                
                                    <label for="s2_text2" class="form-label">Video (Youtube)</label>
                                    <textarea name="service_link" id="s2_text2" class="form-control"><?php echo htmlspecialchars($serviceLink['content_value'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-column">
                                <!-- <div class="admin-form-group" style="padding-top:1.5rem;"> -->
                                    <h3>Clientes / Aliados (Logos)</h3>
                                    
                                    <label for="s1_title" class="form-label">Título</label>
                                    <input type="text" name="service_title_0" id="s1_title" class="form-control" value="<?php echo htmlspecialchars($titleIds[0] ?? ''); ?>">
                                    
                                    <label for="s1_text" class="form-label">Texto</label>
                                    <textarea name="service_text_0" id="s1_text" class="form-control"><?php echo htmlspecialchars($textIds[0] ?? ''); ?></textarea>

                                    <div class="image-manager">
                                        <div class="image-slots-container" id="clients-container" style="flex-wrap: wrap;">
                                            
                                            <?php 
                                            // Iniciamos en 4 porque 0=Banner, 1,2,3=Cards Fijas
                                            $currentIndex = 4; 
                                            
                                            // $clientsContent viene del controlador
                                            if (!empty($clientsContent)): 
                                                foreach ($clientsContent as $client):
                                                    $imageName = $client['content_value'];
                                            ?>
                                                    <div class="image-slot has-preview" data-index="<?php echo $currentIndex; ?>">
                                                        <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $currentIndex; ?>" value="<?php echo htmlspecialchars($imageName); ?>">
                                                        <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $currentIndex; ?>" value="">
                                                    
                                                        <label for="image-slot-<?php echo $currentIndex; ?>">
                                                            <div class="image-preview-slot-frame">
                                                                <img src="/assets/uploads/services/body/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot">
                                                                <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $currentIndex; ?>"><i class='bx bx-x'></i></button> 
                                                            </div>
                                                        </label>
                                                        <input type="file" name="image_slot_<?php echo $currentIndex; ?>" id="image-slot-<?php echo $currentIndex; ?>" accept="image/*" style="display:none;">
                                                    </div>
                                                    <?php $currentIndex++; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                            <div class="image-slot" id="btn-add-client">
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
                                    <h1 class="hero-section__title">Nuestros Servicios</h1>
                                </div>
                            </section>

                            <section class="section">
                                <div class="container content-section">
                                    <h2 id="preview-title-0"></h2>
                                    <p id="preview-text-0"></p>

                                    <div style="display: flex; gap: 2rem; overflow-x: auto; padding: 20px 0; justify-content: center;" id="preview-clients-container">
                                        </div>
                                </div>
                            </section>

                            <section class="section">
                                <div class="container">
                                    <div class="services-grid">
                                        
                                        <div class="service-card">
                                            <div class="service-card__icon"><i class='bx bx-chip'></i></div>
                                            <h3 id="preview-title-1"></h3>
                                            <p id="preview-text-1"></p>
                                            <div class="service-card__image">
                                                <img id="preview-img-1" class="img-ratio-4-3" src="" style="display:none;">
                                            </div>
                                            <a href="#" class="btn btn-primary">Saber más</a>
                                        </div>

                                        <div class="service-card">
                                            <div class="service-card__icon"><i class='bx bx-wrench'></i></div>
                                            <h3 id="preview-title-2"></h3>
                                            <p id="preview-text-2"></p>
                                            <div class="service-card__image">
                                                <img id="preview-img-2" class="img-ratio-4-3" src="" style="display:none;">
                                            </div>
                                            <a href="#" class="btn btn-primary">Saber más</a>
                                        </div>

                                        <div class="service-card">
                                            <div class="service-card__icon"><i class='bx bx-desktop'></i></div>
                                            <h3 id="preview-title-3"></h3>
                                            <p id="preview-text-3"></p>
                                            <div class="service-card__image">
                                                <img id="preview-img-3" class="img-ratio-4-3" src="" style="display:none;">
                                            </div>
                                            <a href="#" class="btn btn-primary">Saber más</a>
                                        </div>

                                    </div>
                                </div>
                            </section>

                            <section class="section">
                                <div class="container content-section">
                                    <h2 id="preview-title-4"></h2>
                                    <p id="preview-text-4"></p>
                                    
                                    <div class="video-wrapper" id="preview-video-container" style="display:none;">
                                        <iframe id="preview-video-iframe" width="100%" height="450" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </section>

                        </div>
                    </div>

                    <script src="/assets/admin/js/admin-services-preview.js"></script>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>