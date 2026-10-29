document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. VARIABLES GLOBALES PARA EL CROPPER ---
    let cropper = null;
    let currentFileInput = null; // Guardará cuál input se está editando
    let currentPreviewCallback = null; // Guardará la función para actualizar la vista previa
    
    // Obtener elementos del DOM
    const modal = document.getElementById('crop-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const btnCrop = document.getElementById('btn-crop-confirm');
    const btnCancel = document.getElementById('btn-cancel-crop');
    const modalTitle = document.getElementById('crop-modal-title');

    // Validación de seguridad: Si no existe el modal en el HTML, detener ejecución para evitar errores
    if (!modal || !imageToCrop) {
        console.error("Error: No se encontró el elemento #crop-modal o #image-to-crop en admin-home.php");
        return; 
    }

    // --- 2. FUNCIONES DEL MODAL DE RECORTE ---

    function closeModal() {
        modal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        currentFileInput = null;
        imageToCrop.src = '';
    }

    function cancelCrop() {
        if(currentFileInput) {
            currentFileInput.value = ''; // Borrar el archivo del input si cancela
        }
        closeModal();
    }

    // Eventos del Modal
    if(btnCancel) {
        btnCancel.addEventListener('click', function(e) {
            e.preventDefault();
            cancelCrop();
        });
    }

    // Cerrar al dar clic fuera del modal
    modal.addEventListener('click', function(e) {
        if (e.target === modal) cancelCrop();
    });

    if(btnCrop) {
        btnCrop.addEventListener('click', function() {
            if (!cropper) return;

            // Obtener canvas recortado
            const canvas = cropper.getCroppedCanvas({
                width: 1920, // Calidad alta para sliders
                imageSmoothingQuality: 'high'
            });

            canvas.toBlob(function(blob) {
                // Crear archivo nuevo reemplazando el original
                const croppedFile = new File([blob], "banner_home_recortado.webp", { type: "image/webp" });
                
                // Reemplazar en el input original
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(croppedFile);
                currentFileInput.files = dataTransfer.files;

                // Ejecutar la vista previa en el slot correspondiente
                if (currentPreviewCallback) {
                    currentPreviewCallback(croppedFile);
                }

                closeModal();
            }, 'image/webp', 0.95);
        });
    }


    // --- 3. LÓGICA DE GESTIÓN DE SLOTS (Home) ---

    function resyncSlotNames() {
        const slots = document.querySelectorAll('.image-slot');
        slots.forEach((slot, newIndex) => {
            
            // 1. Actualiza la etiqueta visual
            const label = slot.querySelector('.slot-order-label');
            if (label) {
                label.textContent = `${newIndex + 1}`;
            }

            // 2. Renombra el label for
            const labelElement = slot.querySelector('label');
            if (labelElement) {
                labelElement.setAttribute('for', `image-slot-${newIndex}`);
            }

            // 3. Renombra el input file
            const fileInput = slot.querySelector('input[type="file"]');
            if (fileInput) {
                fileInput.setAttribute('name', `image_slot_${newIndex}`);
                fileInput.setAttribute('id', `image-slot-${newIndex}`);
            }

            // 4. Renombra el input hidden delete (CORREGIDO: delete_slot_)
            // Busca input que empiece por delete_slot_ O delete_image_ para ser compatible
            const deleteInput = slot.querySelector('input.delete-slot-flag');
            if (deleteInput) {
                deleteInput.setAttribute('name', `delete_slot_${newIndex}`);
            }
            
            // 5. Renombra el input hidden ID
            const imageIdInput = slot.querySelector('input.slot-image-id');
            if (imageIdInput) {
                imageIdInput.setAttribute('name', `slot_image_id_${newIndex}`);
            }
        });
    }

    // Lógica de intercambio (Swap)
    document.querySelectorAll('.btn-swap-slot').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentNode;
            const slotLeft = this.previousElementSibling;
            const slotRight = this.nextElementSibling;
            
            if (slotLeft && slotLeft.classList.contains('image-slot') &&
                slotRight && slotRight.classList.contains('image-slot')) {
                
                parent.insertBefore(slotRight, slotLeft);
                parent.insertBefore(this, slotLeft);
                resyncSlotNames();
            }
        });
    });

    /**
     * Inicializa la previsualización y el recorte para un slot.
     */
    function initializeSlot(slot) {
        
        const fileInput = slot.querySelector(`input[type="file"]`);
        const label = slot.querySelector('label');
        
        // CORREGIDO: Usar clase .delete-slot-flag para ser más seguro
        const deleteInput = slot.querySelector(`input.delete-slot-flag`);
        const imageIdInput = slot.querySelector(`input.slot-image-id`);
        
        const initialDeleteBtn = label.querySelector('.remove-preview-btn');
        let currentPreviewFrame = label.querySelector('.image-preview-slot-frame');
        
        // --- Función para mostrar la preview (Callback) ---
        function showPreview(file) {
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
                
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    removeFrame(frame);
                    fileInput.value = null; 
                });

                frame.appendChild(img);
                frame.appendChild(removeBtn);
                label.appendChild(frame);
                currentPreviewFrame = frame;
                
                // Resetear flags de borrado
                if (deleteInput) deleteInput.value = "0"; 
                if (imageIdInput) imageIdInput.value = ""; 
            }
            reader.readAsDataURL(file);
        }

        // --- Función para limpiar ---
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

        // --- ASIGNAR EVENTOS CON RECORTE ---
        fileInput.addEventListener('change', function(event) {
            if (event.target.files && event.target.files.length > 0) {
                const file = event.target.files[0];

                // Validar que sea imagen
                if (!file.type.startsWith('image/')) return;

                // Definir proporción: Home Slider (0-4) y Banner Secundario (5) son 4:1
                const targetAspectRatio = 4 / 1; 
                const titleText = "Recortar Imagen Panorámica (4:1)";

                // Preparar variables globales
                currentFileInput = fileInput;
                currentPreviewCallback = showPreview;
                
                // Abrir Modal
                const reader = new FileReader();
                reader.onload = function(evt) {
                    imageToCrop.src = evt.target.result;
                    modal.style.display = 'flex'; // Mostrar modal
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
            }
        });

        // Evento botón borrar existente
        if (initialDeleteBtn) {
            initialDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Marcar para borrar (Value "1" o "DELETE")
                if (deleteInput) deleteInput.value = "1";
                
                removeFrame(currentPreviewFrame);
                currentPreviewFrame = null;
                fileInput.value = null;
            });
        }
    }
    
    // Inicializar todos los slots
    document.querySelectorAll('.image-slot').forEach(slot => {
        initializeSlot(slot);
    });
    
    
    // --- LÓGICA DE VALIDACIÓN Y LÍMITE DE CHECKBOXES (Categorías/Marcas) ---

    const MAX_ITEMS = 7;
    const $categoryCheckboxes = $('input[name="featured_categories[]"]');
    const $brandCheckboxes = $('input[name="featured_brands[]"]');
    const $mainSubmitButton = $('form.edit-form').find('button[type="submit"].btn-primary');
    
    const $categoryErrorMessage = $('#categories-error-message'); 
    const $brandErrorMessage = $('#brands-error-message'); 

    function validateFeaturedItems() {
        const catCount = $categoryCheckboxes.filter(':checked').length;
        const brandsExist = $brandCheckboxes.length > 0; 
        const brandCount = $brandCheckboxes.filter(':checked').length;

        let categoriesValid = catCount === MAX_ITEMS;
        let brandsValid = brandsExist ? (brandCount === MAX_ITEMS) : true;
        
        // Validar Categorías
        if (catCount >= MAX_ITEMS) {
            $categoryCheckboxes.not(':checked').prop('disabled', true).closest('.form-check').addClass('disabled-item');
        } else {
            $categoryCheckboxes.prop('disabled', false).closest('.form-check').removeClass('disabled-item');
        }
        
        // Validar Marcas
        if (brandsExist) {
            if (brandCount >= MAX_ITEMS) {
                $brandCheckboxes.not(':checked').prop('disabled', true).closest('.form-check').addClass('disabled-item');
            } else {
                $brandCheckboxes.prop('disabled', false).closest('.form-check').removeClass('disabled-item');
            }
            $brandErrorMessage.toggle(!brandsValid);
        }

        $categoryErrorMessage.toggle(!categoriesValid);
        $mainSubmitButton.prop('disabled', !(categoriesValid && brandsValid));
    }

    $categoryCheckboxes.on('change', validateFeaturedItems);
    $brandCheckboxes.on('change', validateFeaturedItems);
    
    validateFeaturedItems();

});