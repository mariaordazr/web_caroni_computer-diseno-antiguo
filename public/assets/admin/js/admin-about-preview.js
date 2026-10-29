document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('preview-modal');
    const btnOpen = document.getElementById('btn-open-preview');
    const btnClose = document.getElementById('btn-close-preview');

    // --- FUNCIÓN PRINCIPAL: ACTUALIZAR DATOS ---
    function updatePreviewData() {
        
        // 1. Textos (Títulos y Descripciones)
        // Recorremos del 0 al 4 (o más si tienes más secciones)
        for (let i = 0; i < 10; i++) {
            // Títulos
            const inputTitle = document.querySelector(`input[name="about_title_${i}"]`);
            const previewTitle = document.getElementById(`preview-title-${i}`);
            if(inputTitle && previewTitle) previewTitle.innerText = inputTitle.value;

            // Textos (Textarea)
            const inputText = document.querySelector(`textarea[name="about_text_${i}"]`);
            const previewText = document.getElementById(`preview-text-${i}`);
            if(inputText && previewText) {
                // Convertir saltos de línea en <br> visuales
                previewText.innerHTML = inputText.value.replace(/\n/g, '<br>');
            }
        }

        // 2. Imágenes (Lo más complejo)
        // Slot 0 es el Banner
        updateImagePreview(0, 'preview-banner-bg', true); // True porque es background-image

        // Slots 1 al 5 son imágenes normales (img src)
        for (let j = 1; j <= 5; j++) {
            updateImagePreview(j, `preview-img-${j}`, false);
        }
    }

    // Función auxiliar para leer imágenes (nuevas o existentes)
    function updateImagePreview(slotIndex, targetId, isBackground) {
        const fileInput = document.querySelector(`input[name="image_slot_${slotIndex}"]`);
        const existingImg = document.querySelector(`input[name="slot_image_id_${slotIndex}"]`);
        const targetEl = document.getElementById(targetId);
        
        if (!targetEl) return;

        // A. Si el usuario subió un archivo nuevo (File Input)
        if (fileInput && fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if(isBackground) {
                    targetEl.style.backgroundImage = `url('${e.target.result}')`;
                } else {
                    targetEl.src = e.target.result;
                    targetEl.style.display = 'block';
                }
            }
            reader.readAsDataURL(fileInput.files[0]);
        } 
        // B. Si no hay archivo nuevo, usamos la imagen guardada en BD
        else if (existingImg && existingImg.value) {
            const path = (slotIndex === 0) ? '/assets/uploads/about-us/banner/' : '/assets/uploads/about-us/body/';
            const fullPath = path + existingImg.value;
            
            if(isBackground) {
                targetEl.style.backgroundImage = `url('${fullPath}')`;
            } else {
                targetEl.src = fullPath;
                targetEl.style.display = 'block';
            }
        } 
        // C. Si no hay nada, ocultar o poner placeholder
        else {
            if(!isBackground) targetEl.style.display = 'none';
        }
    }

    // --- EVENTOS ---
    btnOpen.addEventListener('click', function() {
        updatePreviewData(); // Cargar datos al abrir
        previewModal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Evitar scroll en el admin detrás
    });

    btnClose.addEventListener('click', function() {
        previewModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
});