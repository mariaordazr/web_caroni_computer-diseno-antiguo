document.addEventListener('DOMContentLoaded', function() {
    
    // --- VARIABLES GLOBALES PARA EL CROPPER ---
    let cropper = null;
    let currentFileInput = null; // Guardará cuál input se está editando
    let currentPreviewCallback = null; // Guardará la función para actualizar la vista previa
    let currentSlot = null; // Referencia al div .image-slot actual
    
    const modal = document.getElementById('crop-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const btnCrop = document.getElementById('btn-crop-confirm');
    const btnCancel = document.getElementById('btn-cancel-crop');
    const modalTitle = document.getElementById('crop-modal-title');

    // --- FUNCIÓN PARA CANCELAR Y LIMPIAR ---
    function cancelCrop() {
        if(currentFileInput) {
            currentFileInput.value = ''; // 1. Borrar el archivo del input
            
            // 2. Si era un slot dinámico nuevo (Marcas/Clientes) y estaba oculto, elimínalo por completo
            if (currentSlot && currentSlot.style.display === 'none') {
                currentSlot.remove();
            }
        }
        closeModal();
    }

    // --- FUNCIÓN PARA CERRAR MODAL ---
    function closeModal() {
        if(modal) modal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        currentFileInput = null;
        currentSlot = null;
        imageToCrop.src = '';
    }

    // --- EVENTOS DEL MODAL ---
    if(btnCancel) {
        btnCancel.addEventListener('click', function(e) {
            e.preventDefault();
            cancelCrop(); // Usar la función de limpieza
        });
    }

    // Cerrar si se hace clic fuera del contenido del modal (Opcional, pero recomendado)
    if(modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cancelCrop();
            }
        });
    }

    if(btnCrop) {
        btnCrop.addEventListener('click', function() {
            if (!cropper) return;

            // Obtener canvas recortado
            const canvas = cropper.getCroppedCanvas({
                width: 1200, 
                imageSmoothingQuality: 'high'
            });

            canvas.toBlob(function(blob) {
                // Crear archivo nuevo reemplazando el original
                const croppedFile = new File([blob], "imagen_recortada.webp", { type: "image/webp" });
                
                // Reemplazar en el input original
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                currentFileInput.files = dataTransfer.files;

                // Ejecutar la vista previa en el slot correspondiente
                if (currentPreviewCallback) {
                    currentPreviewCallback(croppedFile);
                }

                closeModal();
            }, 'image/webp', 0.9);
        });
    }

    // --- LÓGICA DE INICIALIZACIÓN DE SLOTS ---
    function initializeSlot(slot) {
        const fileInput = slot.querySelector(`input[type="file"]`);
        const label = slot.querySelector('label');
        const deleteInput = slot.querySelector(`input.delete-slot-flag`); 
        const imageIdInput = slot.querySelector(`input.slot-image-id`);   
        const initialDeleteBtn = label.querySelector('.remove-preview-btn'); 
        let currentPreviewFrame = label.querySelector('.image-preview-slot-frame');

        // Función interna para mostrar la preview (se llamará SOLO después del recorte)
        function showPreview(file) {
            // 3. Hacer visible el slot (importante para los dinámicos que nacen ocultos)
            slot.style.display = '';

            if (currentPreviewFrame) currentPreviewFrame.remove();
            
            const placeholder = label.querySelector('.image-placeholder-slot');
            const reader = new FileReader();

            reader.onload = function(e) {
                if (placeholder) placeholder.style.display = 'none';
                slot.classList.add('has-preview');

                const frame = document.createElement('div');
                frame.classList.add('image-preview-slot-frame');
                const img = document.createElement('img');
                img.classList.add('image-preview-slot');
                img.src = e.target.result;
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.classList.add('remove-preview-btn');
                removeBtn.innerHTML = '<i class="bx bx-x"></i>';
                
                // Evento borrar imagen nueva
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (slot.closest('#brands-container') || slot.closest('#clients-container') || slot.closest('#payments-container')) {
                        slot.remove();
                    } else {
                        removeFrame(frame);
                        fileInput.value = null; 
                    }
                });

                frame.appendChild(img);
                frame.appendChild(removeBtn);
                label.appendChild(frame);
                currentPreviewFrame = frame;
                
                if (deleteInput) deleteInput.value = ""; 
                if (imageIdInput) imageIdInput.value = ""; 
            }
            reader.readAsDataURL(file);
        }

        // Función para restaurar placeholder
        function removeFrame(frame) {
            if (frame) frame.remove();
            let existingPlaceholder = label.querySelector('.image-placeholder-slot');
            
            if (!existingPlaceholder) {
                const newPlaceholder = document.createElement('div');
                newPlaceholder.classList.add('image-placeholder-slot');
                newPlaceholder.innerHTML = "<i class='bx bx-plus'></i>";
                label.appendChild(newPlaceholder);
            } else {
                existingPlaceholder.style.display = 'flex';
            }
            slot.classList.remove('has-preview');
        }

        // --- INTERCEPTAR EL CAMBIO DE ARCHIVO PARA RECORTAR ---
        if(fileInput) {
            fileInput.addEventListener('change', function(event) {
                if (event.target.files && event.target.files.length > 0) {
                    const file = event.target.files[0];

                    // Si NO es imagen, ignorar
                    if (!file.type.startsWith('image/')) return;

                    // Verificar si debemos recortar (Solo para slots principales de About)
                    const isBanner = (fileInput.name === 'image_slot_0'); // Slot 0 es el banner
                    
                    // Definir Ratio
                    let targetAspectRatio = 4 / 3; // Por defecto para el resto
                    let titleText = "Recortar Imagen (4:3)";

                    if (isBanner) {
                        targetAspectRatio = 4.5 / 1;
                        titleText = "Recortar Banner Principal (4.5:1)";
                    } else if (slot.closest('#brands-container') || slot.closest('#clients-container') || slot.closest('#payments-container')) {
                         targetAspectRatio = 1/1; // Libre (opcional) o forzar 1/1
                         titleText = "Recortar Imagen (1:1)";
                    }

                    // Preparar variables globales
                    currentFileInput = fileInput;
                    currentSlot = slot; // Guardamos referencia al slot para poder borrarlo si cancela
                    currentPreviewCallback = showPreview; 
                    
                    // Abrir Modal
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        imageToCrop.src = evt.target.result;
                        modal.style.display = 'flex';
                        if(modalTitle) modalTitle.innerText = titleText;

                        // Iniciar Cropper
                        if (cropper) cropper.destroy();
                        cropper = new Cropper(imageToCrop, {
                            aspectRatio: targetAspectRatio, 
                            viewMode: 1,
                            autoCropArea: 1,
                        });
                    };
                    reader.readAsDataURL(file);
                    
                } else {
                    // Si el usuario cancela el diálogo del navegador (antes de llegar al cropper)
                    // y era un slot dinámico nuevo, borrarlo.
                    if (slot.style.display === 'none') {
                        slot.remove();
                    }
                }
            });
        }

        // --- LÓGICA DE BORRADO DE IMAGEN EXISTENTE ---
        if (initialDeleteBtn) {
            initialDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (slot.closest('#brands-container') || slot.closest('#clients-container') || slot.closest('#payments-container')) {
                    slot.remove();
                } else {
                    if (deleteInput) deleteInput.value = "DELETE";
                    removeFrame(currentPreviewFrame);
                    currentPreviewFrame = null;
                    if (fileInput) fileInput.value = null;
                }
            });
        }
    }
    
    // Inicializar slots existentes
    document.querySelectorAll('.image-slot').forEach(slot => {
        initializeSlot(slot);
    });

    // --- LÓGICA DINÁMICA (MARCAS, CLIENTES, ETC) ---
    function setupDynamicAddButton(btnId, containerId, varIndexName) {
        const btn = document.getElementById(btnId);
        const container = document.getElementById(containerId);
        
        if (btn && container) {
            let nextIndex = window[varIndexName] || 100;

            btn.addEventListener('click', function() {
                // Borrar fantasmas previos
                const ghosts = container.querySelectorAll('.image-slot[style*="display: none"]');
                ghosts.forEach(g => g.remove());

                const newSlot = document.createElement('div');
                newSlot.classList.add('image-slot');
                newSlot.style.display = 'none'; // Nace oculto
                newSlot.dataset.index = nextIndex;

                newSlot.innerHTML = `
                    <input type="hidden" class="slot-image-id" name="slot_image_id_${nextIndex}" value="">
                    <input type="hidden" class="delete-slot-flag" name="delete_image_${nextIndex}" value="">
                    <label for="image-slot-${nextIndex}">
                        <div class="image-placeholder-slot"><i class='bx bx-plus'></i></div>
                    </label>
                    <input type="file" name="image_slot_${nextIndex}" id="image-slot-${nextIndex}" accept="image/*" style="display:none;">
                `;

                container.insertBefore(newSlot, btn);
                initializeSlot(newSlot); // Aplicar lógica de recorte

                // Simular clic
                const fileInput = newSlot.querySelector('input[type="file"]');
                fileInput.click();
                
                nextIndex++;
                if(window[varIndexName]) window[varIndexName] = nextIndex;
            });
        }
    }

    setupDynamicAddButton('btn-add-brand', 'brands-container', 'lastBrandIndex');
    setupDynamicAddButton('btn-add-client', 'clients-container', 'lastClientIndex');
    setupDynamicAddButton('btn-add-payment', 'payments-container', 'lastPaymentIndex');

});