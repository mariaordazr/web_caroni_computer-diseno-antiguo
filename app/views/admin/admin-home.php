<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/pages/home/update" enctype="multipart/form-data">
                    <div class="admin-page-layout">
                        <div class="admin-form-column">
                            <div class="admin-form-group">
                                <h3>Slider Principal</h3>
                                <p>Gestiona las imágenes del carrusel grande de la cabecera.</p>
                                <div class="image-manager">
                                    <div class="image-slots-container">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <?php $item1 = $sliderImages[$i]; $imageName1 = $item1['content_value'] ?? null; $isExisting1 = !empty($imageName1);?>

                                            <div class="image-slot <?php echo $isExisting1 ? 'has-preview' : ''; ?>">
                                                <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting1 ? htmlspecialchars($imageName1) : ''; ?>">
                                                <input type="hidden" class="delete-slot-flag" name="delete_slot_<?php echo $i; ?>" value="0">
                                            
                                                <span class="slot-order-label" style="top:-20px;"><?php echo $i + 1; ?></span>
                                                <label for="image-slot-<?php echo $i; ?>">
                                                    <?php if ($isExisting1): ?>
                                                        <div class="image-preview-slot-frame">
                                                            <img src="/assets/uploads/home/slider/<?php echo htmlspecialchars($imageName1); ?>" class="image-preview-slot">
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
                                            <?php if ($i < 4): ?>
                                                <button type="button" class="btn-swap-slot" aria-label="Intercambiar slots">
                                                    <i class='bx bx-transfer'></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-form-group">
                                <h3>Categorías Destacadas</h3>
                                <p>Selecciona las 7 categorías que deseas destacar en la página de inicio.</p>
                                
                                <div class="form-grid-admin">
                                    <?php 
                                    // Las variables $categories y $featuredCategoryIds fueron pasadas por el AdminController::showHome()
                                    if (!empty($categories)): 
                                        foreach ($categories as $category): 
                                            // Asumo que el ID de la categoría está bajo la clave 'id' o 'category_id'
                                            $categoryId = (string)($category['id_category']);
                                            
                                            // Comprobar si la ID de esta categoría está en el array de destacadas
                                            // La función in_array() realiza la verificación
                                            $isChecked = in_array($categoryId, $featuredCategoryIds);
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                type="checkbox" 
                                                name="featured_categories[]" 
                                                value="<?php echo htmlspecialchars($categoryId); ?>" 
                                                id="category-<?php echo htmlspecialchars($categoryId); ?>"
                                                <?php echo $isChecked ? 'checked' : ''; // LÓGICA CLAVE: Agrega 'checked' si está destacada ?>
                                            >
                                            <label class="form-check-label" for="category-<?php echo htmlspecialchars($categoryId); ?>">
                                                <?php echo htmlspecialchars(ucfirst(mb_strtolower($category['name'], 'UTF-8'))); ?>
                                            </label>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    endif; 
                                    ?>
                                </div>
                                <div id="categories-error-message" class="alert alert-danger" style="display: none;">
                                    <strong>¡Atención!</strong> Debe seleccionar exactamente 7 categorías destacadas para poder guardar.
                                </div>
                            </div>
                             
                            <div class="admin-form-group">
                                <h3>Banner secundario</h3>
                                <p>Gestiona la imagen del banner secundario.</p>
                                <div class="image-manager">
                                    <div class="image-slots-container" style="justify-content: center;">
                                        <?php $i=5 ?>
                                        <?php $item2 = $bannerImage; $imageName2 = $item2['content_value'] ?? null; $isExisting2 = !empty($imageName2);?>

                                        <div class="image-slot <?php echo $isExisting2 ? 'has-preview' : ''; ?>" style="width:95%;height:auto;min-height:95% !important; aspect-ratio: 3/1;">
                                            <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $i; ?>" value="<?php echo $isExisting2 ? htmlspecialchars($imageName2) : ''; ?>">
                                            <input type="hidden" class="delete-slot-flag" name="delete_slot_<?php echo $i; ?>" value="0">
                                        
                                            <label for="image-slot-<?php echo $i; ?>" style="aspect-ratio:16/9">
                                                <?php if ($isExisting2): ?>
                                                    <div class="image-preview-slot-frame">
                                                        <img src="/assets/uploads/home/banner/<?php echo htmlspecialchars($imageName2); ?>" class="image-preview-slot" >
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
                            <div class="admin-form-group">
                                <h3>Marcas Destacadas</h3>
                                <p>Selecciona las 7 marcas que deseas destacar en la página de inicio.</p>
                                <div class="form-grid-admin">
                                    <?php 
                                    // Las variables $categories y $featuredCategoryIds fueron pasadas por el AdminController::showHome()
                                    if (!empty($brands)): 
                                        foreach ($brands as $brand): 
                                            // Asumo que el ID de la marca está bajo la clave 'id' o 'brand_id'
                                            $brandId = (string)($brand['id_brand']);
                                            
                                            // Comprobar si la ID de esta marca está en el array de destacadas
                                            // La función in_array() realiza la verificación
                                            $isChecked = in_array($brandId, $featuredBrandIds);
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                type="checkbox" 
                                                name="featured_brands[]" 
                                                value="<?php echo htmlspecialchars($brandId); ?>" 
                                                id="brand-<?php echo htmlspecialchars($brandId); ?>"
                                                <?php echo $isChecked ? 'checked' : ''; // LÓGICA CLAVE: Agrega 'checked' si está destacada ?>
                                            >
                                            <label class="form-check-label" for="brand-<?php echo htmlspecialchars($brandId); ?>">
                                                <?php echo htmlspecialchars(ucfirst(mb_strtolower($brand['name'], 'UTF-8'))); ?>
                                            </label>
                                        </div>
                                    <?php 
                                        endforeach; 
                                    endif; 
                                    ?>
                                </div>
                                <div id="brands-error-message" class="alert alert-danger" style="display: none;">
                                    <strong>¡Atención!</strong> Debe seleccionar exactamente 7 marcas destacadas para poder guardar.
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
                            
                            <section class="home-section">
                                <div class="home-slider swiper" style="aspect-ratio: 3.5/1; position: relative; overflow: hidden;">
                                    <div class="swiper-wrapper" id="preview-slider-wrapper" style="display: flex;">
                                        </div>
                                    <div class="swiper-button-next" style="position:absolute; right:10px; top:50%; z-index:10; color:white; font-size: 2rem; font-weight: bold; cursor: pointer;">&gt;</div>
                                    <div class="swiper-button-prev" style="position:absolute; left:10px; top:50%; z-index:10; color:white; font-size: 2rem; font-weight: bold; cursor: pointer;">&lt;</div>
                                </div>
                            </section>
                            
                            <div class="container" style="text-align: center; margin: 2rem 0;">
                                <p style="color: #666; font-style: italic;">(Aquí se mostrarían las Categorías Destacadas y Productos Recientes tal cual están en la web)</p>
                            </div>

                            <section class="home-banner-section" style="margin: 3rem auto; max-width: 1200px; padding: 0 1rem;">
                                <h4 style="text-align: center; color: #999; margin-bottom: 1rem;">Vista Previa: Banner Secundario</h4>
                                <div style="width: 100%; aspect-ratio: 4/1; border-radius: 8px; overflow: hidden; background: #ddd;">
                                    <img id="preview-secondary-banner" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                    <div id="preview-banner-placeholder" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#666;">Sin imagen</div>
                                </div>
                            </section>

                        </div>
                    </div>

    <script src="/assets/admin/js/admin-home-preview.js"></script>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>