<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<section class="content-panel">
    <form class="edit-form" method="POST" action="/admin/receipts/store" id="receipt-form" enctype="multipart/form-data">

        <?php 
            $prefix = 'NOTA-'; 
            $receiptCode = $prefix . date('Ymd-His');
        ?>
        <input type="hidden" name="receipt_code" id="receipt_code" value="<?php echo $receiptCode; ?>">
        
        <div class="form-grid" style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #eee;">
            <div class="form-column">

                <?php $isPeru = strpos($_SERVER['HTTP_HOST'], 'corporaciongruponet.com') !== false; ?>
                <label>Identificación del Cliente (DNI / RIF / Pasaporte):</label>
                
                <?php if ($isPeru): ?>
                    <!-- VISTA PERÚ: Sin selector V/E/J/P -->
                    <input type="text" name="client_id_digits" id="input-dni-digits" maxlength="20" placeholder="Ej: 71234568 o 20123456789" required style="width: 100%;">
                    <input type="hidden" name="client_id_prefix" id="input-dni-prefix" value="">
                <?php else: ?>
                    <!-- VISTA VENEZUELA: Con selector V/E/J/P -->
                    <div style="display: flex; gap: 5px;">
                        <select name="client_id_prefix" id="input-dni-prefix" style="width: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; background-color: #f9f9f9; outline: none;">
                            <option value="V">V</option>
                            <option value="E">E</option>
                            <option value="J">J</option>
                            <option value="P">P</option>
                            <option value="G">G</option>
                        </select>
                        <input type="text" name="client_id_digits" id="input-dni-digits" maxlength="15" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="Ej: 12345678" required style="flex: 1;">
                    </div>
                <?php endif; ?>
                

                <label>Nombre del Cliente o Razón Social:</label>
                <input type="text" name="client_name" id="input-name" list="clients-datalist" autocomplete="off" placeholder="Escriba o seleccione un cliente..." required>
                <datalist id="clients-datalist">
                    <?php if (!empty($clientsList)): foreach ($clientsList as $clientOption): ?>
                        <option value="<?php echo htmlspecialchars($clientOption['client_name'] . ' - ' . $clientOption['client_id_number']); ?>"></option>
                    <?php endforeach; endif; ?>
                </datalist>

                <label>Dirección del Cliente:</label>
                <input type="text" name="client_address" required>
            </div>

            <div class="form-column">
                <label>Teléfono WhatsApp:</label>
                <input type="tel" id="phone_input" name="phone_input_raw" required style="width: 100%;">
                <input type="hidden" name="client_phone" id="client_phone_hidden">

                <label>Correo Electrónico:</label>
                <input type="email" name="client_email" required>
            </div>
        </div>

        <div class="form-column full-width">
            <h4 style="margin-bottom: 1rem;">Artículos a recibir</h4>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;" id="items-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6;">Und.</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6;">Modelo</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6;">S/N</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6;">Complementos</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6;">Detalles físicos</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6;">Características y fallas</th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="number" name="und[]" class="und-input" value="1" min="1" required style="width: 100%;"></td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="text" name="model[]" required style="width: 100%;"></td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="text" name="serial[]" style="width: 100%;"></td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="text" name="accessories[]" style="width: 100%;"></td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="text" name="details[]" style="width: 100%;"></td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="text" name="features[]" style="width: 100%;"></td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: center;"><button type="button" class="btn btn-sm danger remove-row" disabled><i class='bx bx-x'></i></button></td>
                    </tr>
                </tbody>
            </table>

            <div>
                <button type="button" id="add-row-btn" class="btn btn-outline"><i class='bx bx-plus'></i> Añadir Fila</button>
            </div>
        </div>

        <div class="form-column full-width">
            <h4 style="margin-top: 2rem; padding: 1rem; margin-bottom: 0.5rem; background: #fff3cd; border-radius: 4px; border-left: 4px solid #ffc107;">Presupuesto Tentativo (Inicial)</h4>
            <span style="font-size: 0.85rem; color: #666; display: block; margin-bottom: 10px;"><em>* Este presupuesto inicial está sujeto a cambios</em></span>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;" id="tentative-budget-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 55%;">Descripción</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 20%;">Tipo</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 20%;">Monto (<?php echo htmlspecialchars($moneySymbol ?? '$'); ?>)</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="tentative-budget-body">
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <input type="text" name="tentative_description[]" style="width: 100%;">
                        </td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <select class="budget-type" style="width: 100%; padding: 4px; outline: none; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="single">Valor Único</option>
                                <option value="range">Rango</option>
                            </select>
                        </td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <div class="single-container">
                                <input type="text" class="amount-input amount-single" placeholder="Ej: 10.00" style="width: 100%;">
                            </div>
                            <div class="range-container" style="display: none; align-items: center; gap: 5px;">
                                <input type="text" class="amount-input amount-min" placeholder="10.00" style="width: 45%;">
                                <span>-</span>
                                <input type="text" class="amount-input amount-max" placeholder="20.00" style="width: 45%;">
                            </div>
                            <input type="hidden" name="tentative_amount[]" class="hidden-amount" value="">
                        </td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: center;">
                            <button type="button" class="btn btn-sm danger remove-budget-row" disabled><i class='bx bx-x'></i></button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa;">
                        <td colspan="2" style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: right; font-weight: bold; font-size: 1.1rem;">Monto Final (Tentativo):</td>
                        <td colspan="2" style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <div style="display: flex; align-items: center; font-weight: bold;">
                                <span style="font-size: 1.1rem; color: #000; margin-right: 5px;"><?php echo htmlspecialchars($moneySymbol ?? '$'); ?></span>
                                <input type="text" name="tentative_final_amount" id="tentative_final_amount" value="0.00" readonly style="width: 100%; background: transparent; border: none; font-weight: bold; font-size: 1.1rem; color: #000; outline: none;">
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div>
                <button type="button" id="add-tentative-row-btn" class="btn btn-outline"><i class='bx bx-plus'></i> Añadir Fila</button>
            </div>
        </div>

        <div class="form-column full-width">
            <h4 style="margin-top: 2rem; padding: 1rem; margin-bottom: 1rem; background: #e7f1ff; border-radius: 4px; border-left: 4px solid #0d6efd; color: #084298;">Presupuesto Definitivo</h4>
            
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1rem;" id="budget-table">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 55%;">Descripción</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 20%;">Tipo</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 20%;">Monto (<?php echo htmlspecialchars($moneySymbol ?? '$'); ?>)</th>
                        <th style="padding: 0.5rem; border: 1px solid #dee2e6; width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="budget-body">
                    <tr>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <input type="text" name="description[]" style="width: 100%;">
                        </td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <select class="budget-type" style="width: 100%; padding: 4px; outline: none; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="single">Valor Único</option>
                                <option value="range">Rango</option>
                            </select>
                        </td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <div class="single-container">
                                <input type="text" class="amount-input amount-single" placeholder="0.00" style="width: 100%;">
                            </div>
                            <div class="range-container" style="display: none; align-items: center; gap: 5px;">
                                <input type="text" class="amount-input amount-min" placeholder="0.00" style="width: 45%;">
                                <span>-</span>
                                <input type="text" class="amount-input amount-max" placeholder="0.00" style="width: 45%;">
                            </div>
                            <!-- Input oculto para que PHP reciba el valor final concatenado -->
                            <input type="hidden" name="amount[]" class="hidden-amount" value="0.00">
                        </td>
                        <td style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: center;">
                            <button type="button" class="btn btn-sm danger remove-budget-row" disabled><i class='bx bx-x'></i></button>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #f8f9fa;">
                        <td colspan="2" style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: right; font-weight: bold; font-size: 1.1rem;">Monto Final:</td>
                        <td colspan="2" style="padding: 0.5rem; border: 1px solid #dee2e6;">
                            <div style="display: flex; align-items: center; font-weight: bold;">
                                <span style="font-size: 1.1rem; color: #000; margin-right: 5px;"><?php echo htmlspecialchars($moneySymbol ?? '$'); ?></span>
                                <input type="text" name="final_amount" id="final_amount" value="0.00" readonly style="width: 100%; background: transparent; border: none; font-weight: bold; font-size: 1.1rem; color: #000; outline: none;">
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div>
                <button type="button" id="add-budget-row-btn" class="btn btn-outline"><i class='bx bx-plus'></i> Añadir Fila</button>
            </div>
        </div>

        <div class="form-column full-width">
            <h4 style="margin-bottom: 1rem;">Imágenes del Equipo (Opcional)</h4>
            
            <input type="hidden" name="initial_image_order" id="initial_image_order" value="">
            
            <div class="image-manager">
                <div class="image-slots-container dynamic-slots-container receipt-gallery" 
                    id="receipt-dynamic-slots"
                    data-input-name="receipt_images[]" 
                    data-delete-name="delete_receipt_images[]" 
                    data-order-input="initial_image_order">
                    
                    <div class="image-slot dynamic-slot">
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
                            <input type="file" name="receipt_images[]" accept="image/*" multiple style="display:none;">
                        </label>
                    </div>
                </div>
            </div>
            <small style="color: #666; display: block; margin-top: 5px;">Añade imágenes una a una. Se creará un nuevo espacio automáticamente.</small>
        </div>

        <div class="form-column full-width" style="margin-top: 1.5rem;">
            <label>Observaciones Adicionales (Opcional):</label>
            <textarea name="notes" rows="2" placeholder="Ej: Equipo entregado con cargador original, Garantía de 3 meses, etc."></textarea>
        </div>

        <div class="form-column full-width" style="margin-top: 1.5rem; background: #fdf2f2; padding: 15px; border-left: 4px solid #dc3545; border-radius: 4px;">
            <h4 style="margin-bottom: 1rem; color: #dc3545;">Zona Privada (Uso Interno)</h4>

            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <label>Técnico Encargado (Seleccione o escriba nombre):</label>
                    <input type="text" name="technician_name" list="tech-datalist" autocomplete="off" placeholder="Ej: Juan Pérez" style="width: 100%;">

                    <!-- Datalist para el dropdown con técnicos guardados -->
                    <datalist id="tech-datalist">
                        <?php if (!empty($systemUsers)): foreach ($systemUsers as $u): ?>
                            <option value="<?php echo htmlspecialchars($u['name']); ?>"></option>
                        <?php endforeach; endif; ?>
                    </datalist>
                </div>
            </div>

            <div style="margin-top: 1rem;">
                <label>Notas Privadas de Taller (Solo visibles aquí):</label>
                <textarea name="private_notes" rows="3" placeholder="Anotaciones internas del equipo, contraseñas, pines, diagnósticos internos, etc."></textarea>
            </div>
        </div>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dniPrefix = document.getElementById('input-dni-prefix');
        const dniDigits = document.getElementById('input-dni-digits');
        const nameInput = document.getElementById('input-name');

        if (dniPrefix && dniDigits && nameInput) {
            const checkClient = function() {
                const dni = dniPrefix.value + dniDigits.value;
                if (dniDigits.value.length > 0) {
                    nameInput.placeholder = "Buscando cliente...";
                    fetch('/admin/receipts/check-client?dni=' + dni)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                nameInput.value = data.name;
                                nameInput.readOnly = true;
                                nameInput.style.backgroundColor = '#e9ecef';
                            } else {
                                if(nameInput.readOnly) { nameInput.value = ''; }
                                nameInput.readOnly = false;
                                nameInput.style.backgroundColor = '';
                                nameInput.placeholder = "Escriba o seleccione un cliente...";
                            }
                        })
                        .catch(err => console.error("Error buscando cliente:", err));
                } else {
                    nameInput.readOnly = false;
                    nameInput.style.backgroundColor = '';
                    nameInput.placeholder = "Escriba o seleccione un cliente...";
                }
            };
            dniDigits.addEventListener('blur', checkClient);
            dniPrefix.addEventListener('change', function() {
                if(dniDigits.value.length > 0) checkClient();
            });

            // Desbloquear si el usuario borra el DNI manualmente
            dniDigits.addEventListener('input', function() {
                if(this.value.length === 0) {
                    nameInput.readOnly = false;
                    nameInput.style.backgroundColor = '';
                    nameInput.value = '';
                }
            });

            // NUEVO: Autocompletar y separar datos al seleccionar un cliente de la lista
            nameInput.addEventListener('input', function(e) {
                const val = this.value;
                const datalist = document.getElementById('clients-datalist');
                if (!datalist) return;
                
                let isFromList = false;
                for (let i = 0; i < datalist.options.length; i++) {
                    if (datalist.options[i].value === val) {
                        isFromList = true;
                        break;
                    }
                }

                if (isFromList) {
                    const parts = val.split(' - ');
                    // Extraemos el DNI que siempre es la última parte, y el resto lo reconstruimos como Nombre
                    const dniPart = parts.pop().trim(); 
                    const namePart = parts.join(' - ').trim(); 
                    
                    this.value = namePart; // Dejamos solo el nombre limpio en la casilla
                    
                    // Extraer prefijo si existe (Ej: V26777004)
                    const match = dniPart.match(/^([A-Za-z])(\d+)$/);
                    if (match && dniPrefix.tagName.toLowerCase() === 'select') {
                        // Lógica para VENEZUELA
                        dniPrefix.value = match[1].toUpperCase();
                        dniDigits.value = match[2];
                    } else {
                        // Lógica para PERÚ (o DNI que solo tienen números)
                        dniDigits.value = dniPart.replace(/[^0-9A-Za-z]/g, '');
                    }
                    
                    // Bloquear la casilla visualmente para que el usuario sepa que está vinculado
                    this.readOnly = true;
                    this.style.backgroundColor = '#e9ecef';
                }
            });
            
            // TIP UX: Doble clic para limpiar todo rápido si se equivocaron de cliente
            nameInput.addEventListener('dblclick', function() {
                if (this.readOnly) {
                    this.readOnly = false;
                    this.style.backgroundColor = '';
                    this.value = '';
                    dniDigits.value = '';
                    this.focus();
                }
            });
        }
    });

    // ============================================================================
    // MOTOR DE SEGUNDO PLANO Y NAVEGACIÓN ASÍNCRONA (PJAX)
    // ============================================================================
    let isUploadingBackground = false;

    // 1. BLINDAJE ANTI-SALIDA (Cerrar pestaña o recargar)
    window.addEventListener('beforeunload', function (e) {
        if (isUploadingBackground) {
            e.preventDefault();
            e.returnValue = 'Hay archivos subiéndose en segundo plano. ¿Estás seguro de que deseas salir y cancelar la carga?';
            return e.returnValue;
        }
    });

    // 2. INTERCEPTOR DE ENLACES (Navegación fluida vs. Salida pública)
    document.addEventListener('click', async function(e) {
        const link = e.target.closest('a');
        if (!link || !isUploadingBackground) return;

        const href = link.getAttribute('href');
        if (!href || href === '#' || link.getAttribute('target') === '_blank' || href.includes('?action=')) return;

        // Si intenta salir de administración (Logo público "/" o Cerrar Sesión "/auth/logout")
        if (!href.startsWith('/admin/')) {
            if (!confirm('⚠️ Hay imágenes subiéndose en segundo plano a Google Drive.\n\nSi sales de la administración ahora, la carga se interrumpirá.\n¿Deseas cancelar la subida y salir?')) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                isUploadingBackground = false;
            }
            return;
        }

        // NAVEGACIÓN ASÍNCRONA INTERNA (Permite navegar por /admin/* sin cortar la subida)
        e.preventDefault();
        navigateToAdminPage(href);
    }, true);

    // Función para cambiar de sección en /admin/* sin recargar el navegador
    async function navigateToAdminPage(url) {
        try {
            // Indicador visual discreto de cambio de página
            document.body.style.cursor = 'wait';
            const response = await fetch(url);
            const html = await response.text();
            
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            
            // Reemplazar contenido principal y sidebar activa
            const currentMain = document.querySelector('.main-content');
            const newMain = newDoc.querySelector('.main-content');
            if (currentMain && newMain) {
                currentMain.innerHTML = newMain.innerHTML;
            }

            const currentNav = document.querySelector('.sidebar__nav');
            const newNav = newDoc.querySelector('.sidebar__nav');
            if (currentNav && newNav) {
                currentNav.innerHTML = newNav.innerHTML;
            }

            // Actualizar título y URL del navegador
            document.title = newDoc.title;
            history.pushState(null, '', url);
            window.scrollTo(0, 0);

            // Reinicializar scripts de la nueva vista (búsqueda, filtros, modales)
            window.dispatchEvent(new Event('DOMContentLoaded'));
        } catch (err) {
            console.error("Error en navegación asíncrona:", err);
            window.location.href = url; // Fallback
        } finally {
            document.body.style.cursor = 'default';
        }
    }

    

    // ============================================================================
    // PROCESO DE SUBIDA EN SEGUNDO PLANO Y CREACIÓN DE NOTA
    // ============================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('receipt-form');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const initialFileInputs = form.querySelectorAll('input[name="receipt_images[]"]');
                const processFileInputs = form.querySelectorAll('input[name="process_images[]"]');
                let filesToUpload = [];

                initialFileInputs.forEach(input => {
                    if(input.files && input.files.length > 0) {
                        for(let i = 0; i < input.files.length; i++) filesToUpload.push({file: input.files[i], type: 'iniciales'});
                    }
                });
                processFileInputs.forEach(input => {
                    if(input.files && input.files.length > 0) {
                        for(let i = 0; i < input.files.length; i++) filesToUpload.push({file: input.files[i], type: 'proceso'});
                    }
                });

                // 🛡️ BLINDAJE JS 1: Reactivar campos apagados para garantizar que se lean
                form.querySelectorAll(':disabled').forEach(el => el.disabled = false);

                if (filesToUpload.length === 0) { 
                    Swal.fire({
                        title: 'Generando Nota...',
                        text: 'Creando PDF y guardando datos de forma segura. Por favor espera.',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    form.submit(); 
                    return; 
                }

                const finalFormData = new FormData(form);

                // 🛡️ BLINDAJE JS 2: Eliminar las fotos físicas de la petición final a PHP
                // Esto reduce la carga de 20MB a 2KB, evitando que PHP se ahogue y borre variables.
                finalFormData.delete('receipt_images[]');
                finalFormData.delete('process_images[]');

                // 1. Capturar todos los datos del formulario en memoria ANTES de cambiar de página
                const storeUrl = form.getAttribute('action');
                const clientName = document.getElementById('input-name').value || 'Cliente';
                const receiptCode = document.getElementById('receipt_code').value || 'Nota de Recibo';
                
                const oldClientName = document.getElementById('old-client-name') ? document.getElementById('old-client-name').value : clientName;
                const dniPrefix = document.getElementById('input-dni-prefix') ? document.getElementById('input-dni-prefix').value : 'V';
                const dniDigits = document.getElementById('input-dni-digits') ? document.getElementById('input-dni-digits').value : '';
                const fullDni = dniPrefix + dniDigits;
                const oldDni = document.getElementById('old-client-id') ? document.getElementById('old-client-id').value : fullDni;

                // 2. Activar bandera de subida
                isUploadingBackground = true;

                // 3. CREAR VENTANA FLOTANTE COMPACTA EN SEGUNDO PLANO
                const widget = document.createElement('div');
                widget.id = 'bg-upload-widget';
                widget.style.cssText = `
                    position: fixed;
                    bottom: 25px;
                    right: 25px;
                    width: 330px;
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.22);
                    border: 1px solid #e9ecef;
                    border-left: 6px solid #0d6efd;
                    padding: 16px;
                    z-index: 999999;
                    font-family: 'Inter', sans-serif;
                    transition: all 0.3s ease;
                `;

                widget.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 700; font-size: 0.85rem; color: #0d6efd; display: flex; align-items: center; gap: 6px;">
                            <i class='bx bx-cloud-upload bx-flashing' style='font-size: 1.25rem;'></i> Subiendo a Drive...
                        </span>
                        <span id="widget-counter" style="font-size: 0.75rem; font-weight: 700; background: #e9ecef; padding: 3px 9px; border-radius: 20px; color: #495057;">
                            0 / ${filesToUpload.length}
                        </span>
                    </div>
                    <div style="font-size: 0.9rem; font-weight: 700; color: #212529; white-space: nowrap; overflow: hidden; text-ellipsis: ellipsis; margin-bottom: 2px;">
                        ${receiptCode}
                    </div>
                    <div style="font-size: 0.8rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-ellipsis: ellipsis; margin-bottom: 12px;">
                        <i class='bx bx-user'></i> ${clientName}
                    </div>
                    <div style="width: 100%; height: 7px; background: #e9ecef; border-radius: 10px; overflow: hidden;">
                        <div id="widget-progress-bar" style="width: 0%; height: 100%; background: #0d6efd; transition: width 0.3s ease;"></div>
                    </div>
                    <div id="widget-status-text" style="font-size: 0.72rem; color: #6c757d; margin-top: 8px; text-align: right; font-style: italic;">
                        Puedes seguir navegando en el panel...
                    </div>
                `;
                document.body.appendChild(widget);

                // 4. REDIRIGIR INMEDIATAMENTE AL LISTADO DE RECIBOS (/admin/receipts)
                navigateToAdminPage('/admin/receipts');

                // 5. BUCLE ASÍNCRONO DE SUBIDA DE ARCHIVOS EN SEGUNDO PLANO
                let uploadedCount = 0;
                for (let item of filesToUpload) {
                    let uploadData = new FormData();
                    uploadData.append('file', item.file);
                    uploadData.append('folderType', item.type);
                    uploadData.append('receiptCode', receiptCode);
                    uploadData.append('clientName', clientName);
                    uploadData.append('client_id_number', fullDni);
                    uploadData.append('oldClientName', oldClientName);
                    uploadData.append('old_client_id_number', oldDni);

                    try {
                        let response = await fetch('/admin/receipts/upload-async', {
                            method: 'POST',
                            body: uploadData
                        });
                        let result = await response.json();
                        
                        if (result.success) {
                            // Adjuntar el enlace resultante al FormData en memoria
                            const fieldName = item.type === 'iniciales' ? 'preuploaded_initial[]' : 'preuploaded_process[]';
                            finalFormData.append(fieldName, result.link);
                        }
                    } catch (e) {
                        console.error("Error subiendo archivo en segundo plano:", e);
                    }

                    uploadedCount++;
                    const percentage = Math.round((uploadedCount / filesToUpload.length) * 100);
                    const counterEl = document.getElementById('widget-counter');
                    const barEl = document.getElementById('widget-progress-bar');
                    if (counterEl) counterEl.innerText = `${uploadedCount} / ${filesToUpload.length}`;
                    if (barEl) barEl.style.width = `${percentage}%`;
                }

                // 6. FINALIZAR: GENERAR PDF Y GUARDAR REGISTRO EN MYSQL
                const statusEl = document.getElementById('widget-status-text');
                const barEl = document.getElementById('widget-progress-bar');
                if (statusEl) {
                    statusEl.style.color = '#fd7e14';
                    statusEl.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> Generando PDF y guardando nota...";
                }
                if (barEl) barEl.style.background = '#fd7e14';

                try {
                    // Enviar la petición de guardado final a PHP
                    await fetch(storeUrl, {
                        method: 'POST',
                        body: finalFormData
                    });

                    isUploadingBackground = false;

                    // Actualizar widget a estado de ÉXITO (verde)
                    widget.style.borderLeftColor = '#198754';
                    widget.innerHTML = `
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-weight: 700; font-size: 0.9rem; color: #198754; display: flex; align-items: center; gap: 6px;">
                                <i class='bx bxs-check-circle' style='font-size: 1.3rem;'></i> ¡Nota guardada con éxito!
                            </span>
                            <button onclick="this.closest('#bg-upload-widget').remove()" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:#6c757d;">&times;</button>
                        </div>
                        <div style="font-size: 0.85rem; color: #212529; margin-top: 6px;">
                            <strong>${receiptCode}</strong> subida a Drive correctamente.
                        </div>
                    `;

                    // Si el usuario está actualmente en la lista de recibos, refrescar la tabla silenciosamente para mostrar la nueva nota
                    if (window.location.pathname === '/admin/receipts') {
                        navigateToAdminPage('/admin/receipts');
                    }

                    // Desaparecer el widget automáticamente después de 6 segundos
                    setTimeout(() => { if(widget && widget.parentNode) widget.remove(); }, 6000);

                } catch (err) {
                    isUploadingBackground = false;
                    if (widget) {
                        widget.style.borderLeftColor = '#dc3545';
                        widget.innerHTML = `
                            <div style="color: #dc3545; font-weight: 700; font-size: 0.85rem;">
                                <i class='bx bxs-error-circle'></i> Error al guardar la nota en BD.
                            </div>
                        `;
                    }
                }
            });
        }
    });

    // ==========================================================
    // LÓGICA DINÁMICA DE PRESUPUESTO (Sumatorias y Rangos)
    // ==========================================================
    function initBudgetLogic() {
        const budgetBody = document.getElementById('budget-body');
        const addBudgetBtn = document.getElementById('add-budget-row-btn');
        if (!budgetBody) return;
        
        function calculateTotal() {
            let totalMin = 0;
            let totalMax = 0;
            let hasRange = false;

            budgetBody.querySelectorAll('tr').forEach(row => {
                const type = row.querySelector('.budget-type').value;
                const hiddenAmount = row.querySelector('.hidden-amount');
                
                if (type === 'single') {
                    const val = parseFloat(row.querySelector('.amount-single').value) || 0;
                    totalMin += val;
                    totalMax += val;
                    // Actualiza el input oculto que recibe PHP
                    hiddenAmount.value = val.toFixed(2);
                } else {
                    hasRange = true;
                    const min = parseFloat(row.querySelector('.amount-min').value) || 0;
                    const max = parseFloat(row.querySelector('.amount-max').value) || 0;
                    totalMin += min;
                    totalMax += max;
                    // Actualiza el input oculto que recibe PHP
                    hiddenAmount.value = min.toFixed(2) + ' - ' + max.toFixed(2);
                }
            });

            const finalInput = document.getElementById('final_amount');
            if (hasRange) {
                finalInput.value = totalMin.toFixed(2) + ' - ' + totalMax.toFixed(2);
            } else {
                finalInput.value = totalMin.toFixed(2);
            }
        }

        // Restringir a números reales (con punto decimal)
        function validateRealNumber(e) {
            if (['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
            // Solo permitir números y punto
            if (!/^[0-9.]$/.test(e.key)) e.preventDefault();
            // Evitar más de un punto decimal
            if (e.key === '.' && e.target.value.includes('.')) e.preventDefault();
        }

        // Si se deja en blanco, rellenar con 0.00
        function formatEmptyZero(e) {
            let val = e.target.value.trim();
            if (val === '' || val === '.') {
                e.target.value = '0.00';
            } else {
                let num = parseFloat(val);
                if (!isNaN(num)) {
                    e.target.value = num.toFixed(2);
                } else {
                    e.target.value = '0.00';
                }
            }
            calculateTotal();
        }

        // Cambio entre "Valor Único" y "Rango"
        function handleTypeChange(e) {
            const row = e.target.closest('tr');
            const singleContainer = row.querySelector('.single-container');
            const rangeContainer = row.querySelector('.range-container');
            
            if (e.target.value === 'single') {
                singleContainer.style.display = 'block';
                rangeContainer.style.display = 'none';
            } else {
                singleContainer.style.display = 'none';
                rangeContainer.style.display = 'flex';
            }
            calculateTotal();
        }

        // Adjuntar eventos a una fila
        function attachEvents(row) {
            row.querySelectorAll('.amount-input').forEach(input => {
                input.addEventListener('keydown', validateRealNumber);
                input.addEventListener('blur', formatEmptyZero);
                input.addEventListener('input', calculateTotal);
            });
            row.querySelector('.budget-type').addEventListener('change', handleTypeChange);
            
            const removeBtn = row.querySelector('.remove-budget-row');
            if (removeBtn) {
                removeBtn.addEventListener('click', function() {
                    if (budgetBody.querySelectorAll('tr').length > 1) {
                        row.remove();
                        calculateTotal();
                    }
                });
            }
        }

        // Botón añadir fila
        if (addBudgetBtn) {
            addBudgetBtn.addEventListener('click', () => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                        <input type="text" name="description[]" style="width: 100%;">
                    </td>
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                        <select class="budget-type" style="width: 100%; padding: 4px; outline: none; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="single">Valor Único</option>
                            <option value="range">Rango</option>
                        </select>
                    </td>
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                        <div class="single-container">
                            <input type="text" class="amount-input amount-single" value="0.00" style="width: 100%;">
                        </div>
                        <div class="range-container" style="display: none; align-items: center; gap: 5px;">
                            <input type="text" class="amount-input amount-min" value="0.00" style="width: 45%;">
                            <span>-</span>
                            <input type="text" class="amount-input amount-max" value="0.00" style="width: 45%;">
                        </div>
                        <input type="hidden" name="amount[]" class="hidden-amount" value="0.00">
                    </td>
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: center;">
                        <button type="button" class="btn btn-sm danger remove-budget-row"><i class='bx bx-x'></i></button>
                    </td>
                `;
                budgetBody.appendChild(tr);
                attachEvents(tr);
                
                // Activar botón de eliminar en la primera fila si hay más de 1
                const firstRemoveBtn = budgetBody.querySelector('tr:first-child .remove-budget-row');
                if (firstRemoveBtn) firstRemoveBtn.disabled = false;
            });
        }

        // Inicializar filas existentes
        budgetBody.querySelectorAll('tr').forEach(attachEvents);
        calculateTotal();
    }
    
    initBudgetLogic();
    initTentativeBudgetLogic(); // <-- ESTA ES LA LÍNEA QUE TE FALTA

    function initTentativeBudgetLogic() {
        const tentativeBody = document.getElementById('tentative-budget-body');
        const addTentativeBtn = document.getElementById('add-tentative-row-btn');
        if (!tentativeBody) return;

        function calculateTotalTentative() {
            let totalMin = 0;
            let totalMax = 0;
            let hasRange = false;

            tentativeBody.querySelectorAll('tr').forEach(row => {
                const type = row.querySelector('.budget-type').value;
                const hiddenAmount = row.querySelector('.hidden-amount');
                
                if (type === 'single') {
                    const val = parseFloat(row.querySelector('.amount-single').value) || 0;
                    totalMin += val;
                    totalMax += val;
                    hiddenAmount.value = val.toFixed(2);
                } else {
                    hasRange = true;
                    const min = parseFloat(row.querySelector('.amount-min').value) || 0;
                    const max = parseFloat(row.querySelector('.amount-max').value) || 0;
                    totalMin += min;
                    totalMax += max;
                    hiddenAmount.value = min.toFixed(2) + ' - ' + max.toFixed(2);
                }
            });

            const finalInput = document.getElementById('tentative_final_amount');
            if (finalInput) {
                if (hasRange) {
                    finalInput.value = totalMin.toFixed(2) + ' - ' + totalMax.toFixed(2);
                } else {
                    finalInput.value = totalMin.toFixed(2);
                }
            }
        }

        function validateRealNumber(e) {
            if (['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
            if (!/^[0-9.]$/.test(e.key)) e.preventDefault();
            if (e.key === '.' && e.target.value.includes('.')) e.preventDefault();
        }

        function formatEmptyZero(e) {
            let val = e.target.value.trim();
            if (val === '' || val === '.') {
                e.target.value = '0.00';
            } else {
                let num = parseFloat(val);
                if (!isNaN(num)) {
                    e.target.value = num.toFixed(2);
                } else {
                    e.target.value = '0.00';
                }
            }
            calculateTotalTentative();
        }

        function handleTypeChange(e) {
            const row = e.target.closest('tr');
            const singleContainer = row.querySelector('.single-container');
            const rangeContainer = row.querySelector('.range-container');
            if (e.target.value === 'single') {
                singleContainer.style.display = 'block'; rangeContainer.style.display = 'none';
            } else {
                singleContainer.style.display = 'none'; rangeContainer.style.display = 'flex';
            }
            calculateTotalTentative();
        }

        function attachEvents(row) {
            row.querySelectorAll('.amount-input').forEach(input => {
                input.addEventListener('keydown', validateRealNumber);
                input.addEventListener('blur', formatEmptyZero);
                input.addEventListener('input', calculateTotalTentative);
            });
            row.querySelector('.budget-type').addEventListener('change', handleTypeChange);
            const removeBtn = row.querySelector('.remove-budget-row');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => { 
                    if (tentativeBody.querySelectorAll('tr').length > 1) {
                        row.remove();
                        calculateTotalTentative();
                    }
                });
            }
        }

        if (addTentativeBtn) {
            addTentativeBtn.addEventListener('click', () => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><input type="text" name="tentative_description[]" style="width: 100%;"></td>
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6;"><select class="budget-type" style="width: 100%; padding: 4px; border: 1px solid #ccc; border-radius: 4px;"><option value="single">Valor Único</option><option value="range">Rango</option></select></td>
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6;">
                        <div class="single-container"><input type="text" class="amount-input amount-single" placeholder="0.00" style="width: 100%;"></div>
                        <div class="range-container" style="display: none; align-items: center; gap: 5px;"><input type="text" class="amount-input amount-min" placeholder="0.00" style="width: 45%;"><span>-</span><input type="text" class="amount-input amount-max" placeholder="0.00" style="width: 45%;"></div>
                        <input type="hidden" name="tentative_amount[]" class="hidden-amount" value="0.00">
                    </td>
                    <td style="padding: 0.5rem; border: 1px solid #dee2e6; text-align: center;"><button type="button" class="btn btn-sm danger remove-budget-row"><i class='bx bx-x'></i></button></td>
                `;
                tentativeBody.appendChild(tr);
                attachEvents(tr);
                const firstRemoveBtn = tentativeBody.querySelector('tr:first-child .remove-budget-row');
                if (firstRemoveBtn) firstRemoveBtn.disabled = false;
            });
        }
        tentativeBody.querySelectorAll('tr').forEach(attachEvents);
        calculateTotalTentative();
    }
</script>