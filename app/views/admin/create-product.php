<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/storeProduct" enctype="multipart/form-data">
                    
                    <div class="form-grid">
                        <div class="form-column">

                            <label for="sku">Código de identificación (SKU):</label>
                            <input type="text" id="sku" name="sku" required>

                            <label for="name">Nombre:</label>
                            <input type="text" id="name" name="name" required>

                            <label for="description">Características principales:</label>
                            <textarea id="description" name="description" placeholder="Característica 1&#10;Característica 2&#10;Característica 3..."></textarea>

                            <label for="ext_description">Descripción Extendida:</label>
                            <textarea id="ext_description" name="ext_description" rows="10"></textarea>

                        </div>
                        <div class="form-column">

                            <label for="category">Categoría:</label>
                            <select id="category" name="category" required>
                                <option value="" disabled selected>Selecciona una categoría</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id_category']; ?>"><?php echo htmlspecialchars(ucfirst(mb_strtolower($cat['name']))); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label for="brand">Marca:</label>
                            <select id="brand" name="brand" required>
                                <option value="" disabled selected>Selecciona una marca</option>
                                <?php foreach($brands as $brand): ?>
                                    <option value="<?php echo $brand['id_brand']; ?>"><?php echo htmlspecialchars(ucfirst(mb_strtolower($brand['name']))); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <label for="stock">Stock:</label>
                            <input type="number" id="stock" name="stock" placeholder="0">
                            
                            <label for="price">Precio ( <?php echo htmlspecialchars($moneySymbol); ?> ):</label>
                            <input type="number" id="price" name="price" step="0.01" placeholder="0.00" required>

                            <label for="certification">Nivel de certificación:</label>
                            <select id="certification" name="certification" required>
                                <option value="standard" <?= (isset($product) && $product['certification'] == 'standard') ? 'selected' : '' ?>>Estándar</option>
                                <option value="premium" <?= (isset($product) && $product['certification'] == 'premium') ? 'selected' : '' ?>>Premium</option>
                            </select>
                            
                            <label for="condition">Condición del producto:</label>
                            <select id="condition" name="condition" required>
                                <option value="" disabled selected>Selecciona una condición</option>
                                <option value="new"><?= (isset($product) && $product['condition'] == 'new') ? 'selected' : '' ?>Nuevo</option>
                                <option value="used"><?= (isset($product) && $product['condition'] == 'used') ? 'selected' : '' ?>Usado</option>
                                <option value="refurbished"><?= (isset($product) && $product['condition'] == 'refurbished') ? 'selected' : '' ?>Reacondicionado</option>
                            </select>

                            <label for="has_warranty">¿Tiene garantía?</label>
                            <select id="has_warranty" name="has_warranty" required>
                                <option value="" disabled selected>Selecciona una opción</option>
                                <option value="no">No</option>
                                <option value="yes">Sí</option>
                            </select>

                            <div id="warranty-details" style="display:none; margin-top:0.5rem;">
                                <label for="warranty_length">Duración de la garantía:</label>
                                <div class="warranty-row" style="display:flex; gap:0.5rem; align-items:center;">
                                    <input type="number" id="warranty_length" name="warranty_length" min="0" placeholder="0" style="width:6rem;">
                                    <select id="warranty_unit" name="warranty_unit">
                                        <option value="days">Días</option>
                                        <option value="months">Meses</option>
                                        <option value="years">Años</option>
                                    </select>
                                </div>
                            </div>
                            

                        </div>
                    </div>
                    <div class="form-column full-width">
                        <label>Imágenes:</label>
                        <input type="hidden" name="cover_reference" id="cover_reference" value="">
                        <input type="hidden" name="image_order" id="image_order" value="">
                        
                        <div class="image-manager">
                            <div class="image-slots-container dynamic-slots-container" id="product-dynamic-slots">
                                
                                <div class="image-slot dynamic-slot product-slot">
                                    <label style="width: 100%; height: 100%; display: block; margin: 0; cursor: pointer;">
                                        <div class="image-placeholder desktop-view">
                                            <i class='bx bx-plus'></i>
                                        </div>
                                        <div class="image-placeholder mobile-view mobile-split">
                                            <div class="upload-btn camera-btn" data-capture="environment" title="Tomar foto">
                                                <i class='bx bx-camera'></i>
                                                <span class="btn-text">Cámara</span>
                                            </div>
                                            <div class="upload-btn gallery-btn" data-capture="" title="Subir de galería">
                                                <i class='bx bx-image-add'></i>
                                                <span class="btn-text">Galería</span>
                                            </div>
                                        </div>
                                        <input type="file" name="product_images[]" accept="image/*" multiple style="display:none;">
                                    </label>
                                </div>

                            </div>
                        </div>
                        <small style="color: #666; display: block; margin-top: 5px;">Sube todas las imágenes que desees. Se creará un nuevo espacio automáticamente.</small>
                    </div>


                    <div class="form-column full-width" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px dashed #ccc;">
                        <h4 style="margin-bottom: 1rem;">Video del Producto (Opcional)</h4>
                        <div class="form-grid">
                            <div class="form-column">
                                <label for="video_title">Título del Video:</label>
                                <input type="text" id="video_title" name="video_title" value="<?php echo htmlspecialchars($product['video_title'] ?? ''); ?>" placeholder="Ej: Demostración del equipo">
                                
                                <div class="receipt-video-container" style="margin-top: 10px; padding: 1rem; border: 1px dashed #0d6efd; border-radius: 8px; background: #f8fbff;">
                                    <h5 style="margin-bottom: 10px; color: #0d6efd;"><i class='bx bx-video'></i> Archivo de Video</h5>
                                    
                                    <?php if (isset($product) && !empty($product['video_url'])): ?>
                                        <input type="hidden" name="old_video_url" id="old_video_url" value="<?php echo htmlspecialchars($product['video_url']); ?>">
                                    <?php endif; ?>

                                    <div class="add-video-card" style="<?php echo (isset($product) && !empty($product['video_url'])) ? 'display:none;' : 'display:flex;'; ?>">
                                        <label class="btn btn-outline" style="cursor: pointer; margin:0; display: inline-flex; align-items: center; gap: 5px;">
                                            <i class='bx bx-cloud-upload'></i> Seleccionar Video
                                            <input type="file" id="video_file" name="video_file" class="video-file-input" accept="video/*" style="display:none;">
                                        </label>
                                        <small style="margin-left: 10px; color: #6c757d;">Se subirá directo a Drive al guardar.</small>
                                    </div>

                                    <div class="video-preview-container" style="<?php echo (isset($product) && !empty($product['video_url'])) ? 'display:block;' : 'display:none;'; ?> margin-top: 10px; position: relative; width: fit-content; text-align: center;">
                                        <div style="position: relative; display: inline-block;">
                                            <?php if(isset($product) && !empty($product['video_url'])): ?>
                                                <iframe src="<?php echo str_replace('/view', '/preview', htmlspecialchars($product['video_url'])); ?>" width="320" height="220" frameborder="0" allow="autoplay" allowfullscreen style="border-radius: 8px; background:#000; pointer-events: none;"></iframe>
                                                <!-- Escudo antibotones externos -->
                                                <div style="position: absolute; top:0; left:0; width:100%; height:100%; z-index: 10;"></div>
                                            <?php else: ?>
                                                <video src="" controls style="max-width: 100%; height: 200px; border-radius: 8px; background: #000;"></video>
                                            <?php endif; ?>
                                            
                                            <!-- Nuevo Botón Eliminar Formato Recibo -->
                                            <button type="button" class="btn btn-sm danger remove-video-btn" onclick="<?php echo (isset($product) && !empty($product['video_url'])) ? "deleteProductVideo({$product['id_product']}, '{$product['video_url']}')" : ''; ?>" style="position: absolute; top: -10px; right: -10px; border-radius: 50%; width: 30px; height: 30px; padding: 0; display: flex; align-items: center; justify-content: center; z-index: 20;"><i class='bx bx-x'></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-column">
                                <label for="video_description">Descripción del Video:</label>
                                <textarea id="video_description" name="video_description" rows="4" placeholder="Breve descripción del video..."><?php echo htmlspecialchars($product['video_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form.edit-form');
        
        if (form) {
            form.addEventListener('submit', async function(e) {
                const videoInput = document.getElementById('video_file');
                const hasVideo = videoInput && videoInput.files && videoInput.files.length > 0;
                
                if (hasVideo) {
                    e.preventDefault(); 
                    
                    const formData = new FormData(form);
                    // Quitamos el video para no ahogar la petición principal de PHP
                    formData.delete('video_file'); 
                    
                    // Deshabilitar botón para evitar doble clic
                    const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button.btn-primary');
                    if(submitBtn) submitBtn.disabled = true;

                    Swal.fire({
                        title: 'Guardando Datos...',
                        text: 'Creando registro principal...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    
                    try {
                        let response = await fetch(form.action, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json' },
                            body: formData
                        });
                        
                        let result = await response.json();
                        
                        if (result.success) {
                            Swal.close();
                            const productId = result.product_id;
                            const productName = document.getElementById('name').value;
                            
                            // Lanzar carga del video y PASAR FUNCIÓN DE REDIRECCIÓN PARA EL FINAL
                            uploadVideoBackground(productId, productName, videoInput.files[0], function() {
                                window.location.href = '/admin/products';
                            });
                        } else {
                            Swal.fire('Error', result.message || 'Error al guardar los datos.', 'error');
                            if(submitBtn) submitBtn.disabled = false;
                        }
                    } catch (error) {
                        Swal.fire('Error', 'Fallo al guardar el producto en la base de datos.', 'error');
                        if(submitBtn) submitBtn.disabled = false;
                    }
                }
            });
        }
    });

    async function uploadVideoBackground(productId, productName, file, onCompleteCallback) {
        const APPS_SCRIPT_URL = <?php echo json_encode($webhookUrl); ?>; // <-- NUEVO: Usar el enlace de la API de videos
        
        // 1. Crear el widget visual con barra de progreso real
        const widget = document.createElement('div');
        widget.id = 'bg-upload-widget';
        widget.style.cssText = `
            position: fixed; bottom: 25px; right: 25px; width: 330px; background: #ffffff; 
            border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.22); border: 1px solid #e9ecef; 
            border-left: 6px solid #0d6efd; padding: 16px; z-index: 999999; font-family: 'Inter', sans-serif; transition: all 0.3s ease;
        `;
        widget.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-weight: 700; font-size: 0.85rem; color: #0d6efd; display: flex; align-items: center; gap: 6px;">
                    <i class='bx bx-cloud-upload bx-flashing' style='font-size: 1.25rem;'></i> Subiendo Video Directo...
                </span>
                <span id="video-progress-text" style="font-size: 0.8rem; font-weight: bold; color: #6c757d;">0%</span>
            </div>
            <div style="font-size: 0.9rem; font-weight: 700; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 8px;">
                ${productName}
            </div>
            <progress id="video-progress" value="0" max="100" style="width: 100%; height: 8px; border-radius: 4px;"></progress>
            <div id="video-status-text" style="font-size: 0.72rem; color: #6c757d; margin-top: 8px; font-style: italic;">
                Solicitando conexión segura a Google...
            </div>
        `;
        document.body.appendChild(widget);

        try {
            let finalFileName = 'vid_' + Date.now() + '_' + file.name.replace(/[^a-zA-Z0-9.\-_]/g, '');

            // FASE 1: Inicializar Subida (Pedir URL temporal segura)
            let initPayload = {
                action: 'initResumable',
                productFolder: productId + ' - ' + productName,
                fileName: finalFileName,
                mimeType: file.type,
                deleteFileIds: []
            };

            const oldProductName = document.getElementById('old_product_name');
            if (oldProductName) initPayload.oldProductFolder = productId + ' - ' + oldProductName.value;
            
            const oldVideoUrl = document.getElementById('old_video_url');
            if (oldVideoUrl && oldVideoUrl.value) {
                let match = oldVideoUrl.value.match(/id=([^&]+)/) || oldVideoUrl.value.match(/d\/([a-zA-Z0-9_-]+)/);
                if (match) initPayload.deleteFileIds.push(match[1]);
            }

            // Fetch estándar para leer el JSON de respuesta directamente
            let initRes = await fetch(APPS_SCRIPT_URL, {
                method: 'POST',
                body: JSON.stringify(initPayload)
            });
            let initData = await initRes.json();

            if (!initData.resumeUrl) {
                throw new Error("No se pudo obtener el permiso de subida de Drive.");
            }

            document.getElementById('video-status-text').innerText = 'Transfiriendo archivo puro (Sin Base64)...';

            // FASE 2: Subida Binaria Directa
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open("PUT", initData.resumeUrl, true);
                
                // Evento para visualizar el progreso real en porcentaje
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        let percent = Math.round((e.loaded / e.total) * 100);
                        document.getElementById('video-progress').value = percent;
                        document.getElementById('video-progress-text').innerText = percent + '%';
                    }
                };
                
                xhr.onload = async function() {
                    try {
                        if (xhr.status === 200 || xhr.status === 201 || xhr.status === 308) {
                            document.getElementById('video-status-text').innerText = 'Aplicando permisos públicos...';
                            
                            let fileResponse = JSON.parse(xhr.responseText);
                            
                            // FASE 3: Aplicar permisos y obtener URL final de Google Drive
                            let finalRes = await fetch(APPS_SCRIPT_URL, {
                                method: 'POST',
                                body: JSON.stringify({ action: 'finalizeVideo', fileId: fileResponse.id })
                            });
                            let finalData = await finalRes.json();

                            if (finalData.status !== 'success') throw new Error("Fallo al aplicar permisos en Drive.");

                            document.getElementById('video-status-text').innerText = 'Guardando enlace en base de datos...';

                            // FASE 4: Guardar silenciosamente el enlace en PHP
                            const saveForm = new FormData();
                            saveForm.append('product_id', productId);
                            saveForm.append('video_url', finalData.fileLink);
                            
                            let saveDbRes = await fetch('/admin/saveProductVideoLink', { method: 'POST', body: saveForm });
                            let dbResult = await saveDbRes.json();

                            if(dbResult.success){
                                widget.style.borderLeftColor = '#198754';
                                widget.innerHTML = `<span style="color: #198754; font-weight: bold; font-size: 0.9rem;"><i class='bx bxs-check-circle'></i> ¡Video de alta calidad subido!</span>`;
                                resolve();
                            } else {
                                throw new Error("Fallo al guardar en DB.");
                            }
                        } else {
                            throw new Error("La transferencia se interrumpió.");
                        }
                    } catch (e) {
                        reject(e);
                    }
                };
                
                xhr.onerror = function() { reject(new Error("Error de red durante la transferencia.")); };
                
                // Mandamos el archivo CRUDO (binario), eludiendo todas las limitantes de conversión web
                xhr.send(file);
                
            }).then(() => {
                setTimeout(() => { if(onCompleteCallback) onCompleteCallback(); }, 2000);
            }).catch(err => {
                throw err;
            });

        } catch (err) {
            widget.style.borderLeftColor = '#dc3545';
            widget.innerHTML = `<div style="color:#dc3545; font-weight:bold; font-size:0.85rem;"><i class='bx bxs-error-circle'></i> Error:</div><div style="font-size:0.75rem; margin-top:4px;">${err.message}</div>`;
            setTimeout(() => { if(onCompleteCallback) onCompleteCallback(); }, 4000);
        }
    }   
</script>
