document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================================
    // 1. LÓGICA PARA IMAGEN ÚNICA (Categorías y Marcas)
    // =========================================================
    const singleImageInput = document.getElementById('image-input');

    if (singleImageInput) {
        // Buscamos el contenedor padre para encontrar los elementos relacionados
        // Usamos closest para asegurarnos de estar en el contexto correcto
        const uploadBox = singleImageInput.closest('.image-upload-box') || singleImageInput.parentNode;
        const deleteInput = uploadBox.querySelector('input[name="delete_image"]');
        const label = uploadBox.querySelector('label');
        // Buscamos el placeholder por ID o clase
        let placeholder = document.getElementById('image-preview-container');
        if (!placeholder) placeholder = uploadBox.querySelector('.image-placeholder');

        // A. Lógica para el botón "X" de una imagen YA EXISTENTE (cargada de BD)
        const existingRemoveBtn = uploadBox.querySelector('.remove-preview-btn');
        if (existingRemoveBtn) {
            existingRemoveBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // 1. Eliminar visualmente la imagen
                const frame = this.closest('.image-preview-frame');
                if (frame) frame.remove();

                // 2. Mostrar el placeholder (+ azul)
                if (placeholder) {
                    placeholder.style.display = 'flex'; 
                }
                
                // 3. Quitar la clase que oculta el placeholder en CSS
                if (uploadBox.classList.contains('has-preview')) {
                    uploadBox.classList.remove('has-preview');
                }

                // 4. IMPORTANTE: Marcar el input oculto para que el Controller sepa que debe borrar
                if (deleteInput) {
                    deleteInput.value = 'true'; 
                }

                // 5. Limpiar el input file (por si el usuario había seleccionado algo antes)
                singleImageInput.value = '';
            });
        }

        // B. Lógica para previsualizar una NUEVA imagen seleccionada
        singleImageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Si el usuario sube una foto, REINICIAMOS el flag de borrar
                // (porque la intención es reemplazar, no dejar vacío)
                if (deleteInput) deleteInput.value = ''; 

                const reader = new FileReader();
                reader.onload = function(e) {
                    // Limpiar previews anteriores
                    const existingFrame = label.querySelector('.image-preview-frame');
                    if (existingFrame) existingFrame.remove();

                    // Ocultar placeholder
                    if (placeholder) placeholder.style.display = 'none';
                    
                    // Crear nuevo frame
                    const frame = document.createElement('div');
                    frame.classList.add('image-preview-frame');

                    const img = document.createElement('img');
                    img.classList.add('image-preview');
                    img.src = e.target.result;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('remove-preview-btn');
                    removeBtn.innerHTML = '<i class="bx bx-x"></i>';

                    // Lógica de borrado para la PREVISUALIZACIÓN (imagen nueva)
                    removeBtn.addEventListener('click', function(ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                        frame.remove();
                        if (placeholder) placeholder.style.display = 'flex';
                        singleImageInput.value = ''; // Limpiar el input file
                    });

                    frame.appendChild(img);
                    frame.appendChild(removeBtn);
                    label.appendChild(frame);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // =========================================================
    // 2. LÓGICA DINÁMICA PARA MÚLTIPLES IMÁGENES (Productos)
    // =========================================================
    const productsContainer = document.getElementById('product-dynamic-slots');

    if (productsContainer) {
        
        // --- Lógica de Portada ---
        function updateCoverReference(slot) {
            const coverInput = document.getElementById('cover_reference');
            if (!coverInput) return;

            // 1. Quitar el diseño de portada de todos los demás
            productsContainer.querySelectorAll('.image-slot').forEach(s => s.classList.remove('is-cover'));
            
            // 2. Añadir el diseño de portada al seleccionado
            slot.classList.add('is-cover');

            // 3. Informar al backend si es una imagen vieja o un archivo nuevo
            const existingBtn = slot.querySelector('.remove-preview-btn.existing-image');
            if (existingBtn) {
                // Si es vieja, enviamos su ID
                const imageId = existingBtn.getAttribute('data-image-id');
                coverInput.value = 'existing_' + imageId;
            } else {
                // Si es nueva, enviamos el nombre del archivo
                const fileInput = slot.querySelector('input[type="file"]');
                if (fileInput && fileInput.files.length > 0) {
                    coverInput.value = 'new_' + fileInput.files[0].name;
                }
            }
        }

        function autoSetCover() {
            // Si el usuario borra la portada actual, elegimos la primera disponible
            const currentCover = productsContainer.querySelector('.image-slot.is-cover');
            if (!currentCover) {
                const firstFilledSlot = productsContainer.querySelector('.image-slot.has-preview');
                if (firstFilledSlot) {
                    updateCoverReference(firstFilledSlot);
                } else {
                    const coverInput = document.getElementById('cover_reference');
                    if (coverInput) coverInput.value = '';
                }
            }
        }

        // --- A. Escuchar la carga de archivos ---
        productsContainer.addEventListener('change', function(e) {
            if (e.target.tagName.toLowerCase() === 'input' && e.target.type === 'file') {
                const files = Array.from(e.target.files);
                if (files.length === 0) return;

                const currentSlot = e.target.closest('.image-slot');
                if (!currentSlot) return;

                let lastFilledSlot = currentSlot;

                // 1. Procesar el primer archivo en el slot actual
                setupProductSlotPreview(currentSlot, files[0]);

                // 2. Crear nuevos slots para el resto de archivos seleccionados
                for (let i = 1; i < files.length; i++) {
                    const newSlot = createEmptyProductSlot();
                    lastFilledSlot.insertAdjacentElement('afterend', newSlot);
                    setupProductSlotPreview(newSlot, files[i]);
                    lastFilledSlot = newSlot; 
                }

                // 3. Crear el slot vacío final siempre
                const nextSib = lastFilledSlot.nextElementSibling;
                if (!nextSib || !nextSib.classList.contains('image-slot')) {
                    const finalEmptySlot = createEmptyProductSlot();
                    productsContainer.appendChild(finalEmptySlot);
                }

                autoSetCover();
            }
        });

        // --- B. Delegación de Clicks (Portada, Borrar, Cámara) ---
        productsContainer.addEventListener('click', function(e) {
            
            // Click en "Seleccionar como portada"
            if (e.target.closest('.btn-set-cover')) {
                e.preventDefault();
                e.stopPropagation();
                const slot = e.target.closest('.image-slot');
                updateCoverReference(slot);
            }

            // Click en botones de móvil
            else if (e.target.closest('.upload-btn')) {
                e.preventDefault(); 
                e.stopPropagation();
                const uploadBtn = e.target.closest('.upload-btn');
                const slot = uploadBtn.closest('.image-slot');
                const fileInput = slot.querySelector('input[type="file"]');
                if (!fileInput) return;

                const captureType = uploadBtn.getAttribute('data-capture');
                if (captureType) {
                    fileInput.setAttribute('capture', captureType);
                } else {
                    fileInput.removeAttribute('capture');
                }
                fileInput.click();
            } 
            
            // Borrar imágenes que ya existen en la base de datos
            else if (e.target.closest('.remove-preview-btn.existing-image')) {
                e.preventDefault();
                e.stopPropagation();
                const btn = e.target.closest('.remove-preview-btn.existing-image');
                const slot = btn.closest('.image-slot');
                const imageId = btn.getAttribute('data-image-id');

                const hiddenDelete = document.createElement('input');
                hiddenDelete.type = 'hidden';
                hiddenDelete.name = 'delete_product_images[]';
                hiddenDelete.value = imageId;
                productsContainer.appendChild(hiddenDelete);

                slot.remove();
                autoSetCover();
            }
        });

        // =========================================================
        // --- LÓGICA DE DRAG & DROP TÁCTIL (MÓVILES) ---
        // =========================================================
        let touchDraggedSlot = null;
        let pressTimer = null;

        // 1. Al tocar la pantalla (Inicio)
        productsContainer.addEventListener('touchstart', function(e) {
            // Ignorar si se tocó un botón (borrar, elegir portada) o la cámara/galería
            if (e.target.closest('button') || e.target.closest('.upload-btn') || e.target.tagName.toLowerCase() === 'input') {
                return;
            }

            const slot = e.target.closest('.image-slot.has-preview');
            if (slot) {
                // Iniciamos un temporizador de 250ms (Toque prolongado)
                pressTimer = setTimeout(() => {
                    touchDraggedSlot = slot;
                    slot.classList.add('is-dragging'); // Reutilizamos tu clase CSS
                    // Opcional: Pequeña vibración si el móvil lo soporta para dar feedback
                    if (navigator.vibrate) navigator.vibrate(50);
                }, 250);
            }
        }, { passive: false });

        // 2. Al mover el dedo por la pantalla
        productsContainer.addEventListener('touchmove', function(e) {
            if (!touchDraggedSlot) {
                // Si el usuario desliza ANTES de los 250ms, cancelamos el timer (es un scroll normal)
                clearTimeout(pressTimer);
                return;
            }
            
            // Si ya estamos arrastrando, evitamos el scroll de la página para no perder el elemento
            e.preventDefault(); 

            const touch = e.touches[0];
            
            // Calculamos qué elemento está exactamente debajo del dedo en este momento
            const targetElement = document.elementFromPoint(touch.clientX, touch.clientY);
            if (!targetElement) return;

            const targetSlot = targetElement.closest('.image-slot.has-preview');
            
            // Si el dedo está sobre OTRO slot válido, intercambiamos sus posiciones en el HTML
            if (targetSlot && targetSlot !== touchDraggedSlot) {
                const box = targetSlot.getBoundingClientRect();
                // ¿El dedo está en la mitad izquierda o derecha del objetivo?
                const isNext = (touch.clientX - box.left) > (box.width / 2);

                if (isNext) {
                    targetSlot.parentNode.insertBefore(touchDraggedSlot, targetSlot.nextSibling);
                } else {
                    targetSlot.parentNode.insertBefore(touchDraggedSlot, targetSlot);
                }
            }
        }, { passive: false });

        // 3. Al soltar el dedo de la pantalla
        const endTouch = function() {
            clearTimeout(pressTimer); // Cancelamos por si fue un toque rápido
            if (touchDraggedSlot) {
                touchDraggedSlot.classList.remove('is-dragging');
                touchDraggedSlot = null;
                // Reutilizamos la misma función de escritorio que actualiza el JSON para PHP
                updateImageOrderField(); 
            }
        };

        productsContainer.addEventListener('touchend', endTouch);
        productsContainer.addEventListener('touchcancel', endTouch);

        // --- Funciones Auxiliares UI ---
        function createEmptyProductSlot() {
            const slotDiv = document.createElement('div');
            slotDiv.classList.add('image-slot', 'dynamic-slot', 'product-slot');
            slotDiv.innerHTML = `
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
            `;
            return slotDiv;
        }

        // --- FUNCIÓN PARA GUARDAR EL ORDEN EXACTO ---
        function updateImageOrderField() {
            const orderInput = document.getElementById('image_order');
            if (!orderInput) return;

            const currentOrder = [];
            productsContainer.querySelectorAll('.image-slot.has-preview').forEach(slot => {
                const existingBtn = slot.querySelector('.remove-preview-btn.existing-image');
                if (existingBtn) {
                    currentOrder.push('existing_' + existingBtn.getAttribute('data-image-id'));
                } else {
                    const fileInput = slot.querySelector('input[type="file"]');
                    if (fileInput && fileInput.files.length > 0) {
                        currentOrder.push('new_' + fileInput.files[0].name);
                    }
                }
            });
            orderInput.value = JSON.stringify(currentOrder);
        }

        // --- LÓGICA DE DRAG & DROP (Arrastrar y Soltar) ---
        let draggedSlot = null;

        productsContainer.addEventListener('dragstart', function(e) {
            const slot = e.target.closest('.image-slot.has-preview');
            if (slot) {
                draggedSlot = slot;
                // Pequeño timeout para que el navegador genere la miniatura de arrastre antes de ocultar el original
                setTimeout(() => slot.classList.add('is-dragging'), 0);
            }
        });

        productsContainer.addEventListener('dragend', function(e) {
            if (draggedSlot) {
                draggedSlot.classList.remove('is-dragging');
                draggedSlot = null;
                updateImageOrderField(); // Actualizamos el JSON con el nuevo orden visual
            }
        });

        productsContainer.addEventListener('dragover', function(e) {
            e.preventDefault(); // Necesario para permitir que el elemento caiga aquí
            if (!draggedSlot) return;

            const targetSlot = e.target.closest('.image-slot.has-preview');
            if (targetSlot && targetSlot !== draggedSlot) {
                // ¿El mouse está en la mitad izquierda o derecha del objetivo?
                const box = targetSlot.getBoundingClientRect();
                const isNext = (e.clientX - box.left) > (box.width / 2);

                // Reordenamos el DOM dinámicamente
                if (isNext) {
                    targetSlot.parentNode.insertBefore(draggedSlot, targetSlot.nextSibling);
                } else {
                    targetSlot.parentNode.insertBefore(draggedSlot, targetSlot);
                }
            }
        });

        // Llama a esta función al cargar la página para que registre el orden inicial
        updateImageOrderField();


        // --- Funciones Auxiliares UI ---
        // (Asegúrate de que tu setupProductSlotPreview quede así)
        function setupProductSlotPreview(slot, file) {
            const input = slot.querySelector('input[type="file"]');
            
            if (input) {
                try {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                } catch (err) {}
            }

            const label = slot.querySelector('label');
            const placeholders = slot.querySelectorAll('.image-placeholder');
            placeholders.forEach(p => p.style.display = 'none');

            slot.classList.add('has-preview');
            slot.setAttribute('draggable', 'true'); // NUEVO: Hacemos que la nueva imagen sea arrastrable

            const frame = document.createElement('div');
            frame.classList.add('image-preview-frame');
            
            const img = document.createElement('img');
            img.classList.add('image-preview');
            img.src = URL.createObjectURL(file);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.classList.add('remove-preview-btn');
            removeBtn.innerHTML = '<i class="bx bx-x"></i>';

            removeBtn.addEventListener('click', function(ev) {
                ev.preventDefault();
                ev.stopPropagation();
                slot.remove(); 
                URL.revokeObjectURL(img.src);
                autoSetCover();
                updateImageOrderField(); // NUEVO: Actualizar orden al borrar
            });

            const coverBtn = document.createElement('button');
            coverBtn.type = 'button';
            coverBtn.classList.add('btn-set-cover');
            coverBtn.innerHTML = "<i class='bx bx-star'></i> Elegir Portada";

            const coverIndicator = document.createElement('div');
            coverIndicator.classList.add('cover-indicator');
            coverIndicator.innerHTML = "<i class='bx bxs-star'></i> Portada";

            frame.appendChild(img);
            frame.appendChild(removeBtn);
            frame.appendChild(coverBtn);
            frame.appendChild(coverIndicator);
            label.appendChild(frame);

            updateImageOrderField(); // NUEVO: Actualizar orden al agregar
        }
    }

    // =========================================================
    // 3. UI GENERAL (Filtros Móviles)
    // =========================================================
    const toggleBtn = document.getElementById('mobile-filter-toggle');
    const filterControls = document.getElementById('filter-controls-container');
    
    if(toggleBtn && filterControls) {
        toggleBtn.addEventListener('click', function() {
            filterControls.classList.toggle('is-visible');
            toggleBtn.classList.toggle('is-active');
        });
    }

});
document.addEventListener('DOMContentLoaded', function () {
    var hasWarranty = document.getElementById('has_warranty');
    var details = document.getElementById('warranty-details');
    var lengthInput = document.getElementById('warranty_length');
    var unitSelect = document.getElementById('warranty_unit');

    function updateWarrantyVisibility() {
        if (hasWarranty.value === 'yes') {
            details.style.display = 'block';
            lengthInput.required = true;
            unitSelect.required = true;
        } else {
            details.style.display = 'none';
            lengthInput.required = false;
            unitSelect.required = false;
            lengthInput.value = '';
            unitSelect.selectedIndex = 0;
        }
    }
    if (hasWarranty) {
        hasWarranty.addEventListener('change', updateWarrantyVisibility);

        // Inicializar estado (útil si el select viene preseleccionado por servidor)
        updateWarrantyVisibility();
    }
});