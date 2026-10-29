document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('preview-modal');
    const btnOpen = document.getElementById('btn-open-preview');
    const btnClose = document.getElementById('btn-close-preview');

    // --- FUNCIÓN PRINCIPAL: ACTUALIZAR DATOS ---
    function updatePreviewData() {
        
        // 1. TEXTOS (Títulos y Descripciones)
        // Indices en Services: 
        // 0: Clientes/Aliados
        // 1, 2, 3: Tarjetas de Servicio
        // 4: Video
        for (let i = 0; i <= 4; i++) {
            // Títulos
            const inputTitle = document.querySelector(`input[name="service_title_${i}"]`);
            const previewTitle = document.getElementById(`preview-title-${i}`);
            if(inputTitle && previewTitle) previewTitle.innerText = inputTitle.value;

            // Textos
            const inputText = document.querySelector(`textarea[name="service_text_${i}"]`);
            const previewText = document.getElementById(`preview-text-${i}`);
            if(inputText && previewText) {
                previewText.innerHTML = inputText.value.replace(/\n/g, '<br>');
            }
        }

        // 2. IMÁGENES ESTÁTICAS
        // Slot 0: Banner Principal
        updateImagePreview(0, 'preview-banner-bg', true);

        // Slots 1, 2, 3: Imágenes de tarjetas de servicio
        for (let j = 1; j <= 3; j++) {
            updateImagePreview(j, `preview-img-${j}`, false);
        }

        // 3. LOGOS DE CLIENTES (Dinámicos)
        // Buscamos todos los slots dentro del contenedor de clientes
        updateClientsPreview();

        // 4. VIDEO (Youtube)
        updateVideoPreview();
    }

    // --- HELPER: Actualizar Imágenes Simples ---
    function updateImagePreview(slotIndex, targetId, isBackground) {
        const fileInput = document.querySelector(`input[name="image_slot_${slotIndex}"]`);
        const existingImg = document.querySelector(`input[name="slot_image_id_${slotIndex}"]`);
        const targetEl = document.getElementById(targetId);
        
        if (!targetEl) return;

        // Path base depende de si es banner o cuerpo
        const basePath = (slotIndex === 0) ? '/assets/uploads/services/banner/' : '/assets/uploads/services/body/';

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
        } else if (existingImg && existingImg.value) {
            const fullPath = basePath + existingImg.value;
            if(isBackground) {
                targetEl.style.backgroundImage = `url('${fullPath}')`;
            } else {
                targetEl.src = fullPath;
                targetEl.style.display = 'block';
            }
        } else {
            if(!isBackground) targetEl.style.display = 'none';
        }
    }

    // --- HELPER: Actualizar Clientes Dinámicos ---
    function updateClientsPreview() {
        const container = document.getElementById('preview-clients-container');
        container.innerHTML = ''; // Limpiar

        // Buscar todos los slots visuales en el admin dentro de #clients-container
        // Nota: Los inputs reales pueden tener índices altos (image_slot_4, image_slot_105, etc.)
        const clientSlots = document.querySelectorAll('#clients-container .image-slot');

        clientSlots.forEach(slot => {
            // Ignorar el botón de agregar (+)
            if(slot.id === 'btn-add-client') return;

            const fileInput = slot.querySelector('input[type="file"]');
            const hiddenInput = slot.querySelector('input.slot-image-id');
            const deleteInput = slot.querySelector('input.delete-slot-flag');

            // Si está marcado para borrar, no mostrar
            if(deleteInput && deleteInput.value === 'DELETE') return;

            let src = null;

            if (fileInput && fileInput.files && fileInput.files[0]) {
                src = URL.createObjectURL(fileInput.files[0]);
            } else if (hiddenInput && hiddenInput.value) {
                src = '/assets/uploads/services/body/' + hiddenInput.value;
            }

            if (src) {
                const img = document.createElement('img');
                img.src = src;
                img.style.height = '60px';
                img.style.width = 'auto';
                img.style.objectFit = 'contain';
                container.appendChild(img);
            }
        });
    }

    // --- HELPER: Actualizar Video ---
    function updateVideoPreview() {
        const inputLink = document.querySelector('textarea[name="service_link"]');
        const container = document.getElementById('preview-video-container');
        const iframe = document.getElementById('preview-video-iframe');

        if (inputLink && inputLink.value.trim() !== '') {
            const url = inputLink.value;
            let videoId = '';

            // Lógica simple para extraer ID de Youtube (igual que en PHP)
            if (url.includes('youtu.be/')) {
                videoId = url.split('youtu.be/')[1];
            } else if (url.includes('v=')) {
                videoId = url.split('v=')[1].split('&')[0];
            }

            if (videoId) {
                iframe.src = `https://www.youtube.com/embed/${videoId}`;
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        } else {
            container.style.display = 'none';
        }
    }

    // --- EVENTOS ---
    if(btnOpen) {
        btnOpen.addEventListener('click', function() {
            updatePreviewData();
            previewModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; 
        });
    }

    if(btnClose) {
        btnClose.addEventListener('click', function() {
            previewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
    }
});