document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('preview-modal');
    const btnOpen = document.getElementById('btn-open-preview');
    const btnClose = document.getElementById('btn-close-preview');

    function updatePreviewData() {
        // 1. TEXTOS
        // Shipping tiene índices 0 (Envíos) y 1 (Local)
        for (let i = 0; i <= 1; i++) {
            const inputTitle = document.querySelector(`input[name="shipping_title_${i}"]`);
            const previewTitle = document.getElementById(`preview-title-${i}`);
            if(inputTitle && previewTitle) previewTitle.innerText = inputTitle.value;

            const inputText = document.querySelector(`textarea[name="shipping_text_${i}"]`);
            const previewText = document.getElementById(`preview-text-${i}`);
            if(inputText && previewText) {
                previewText.innerHTML = inputText.value.replace(/\n/g, '<br>');
            }
        }

        // 2. IMÁGENES
        // Banner (slot 0)
        updateImagePreview(0, 'preview-banner-bg', true);
        // Local (slot 1)
        updateImagePreview(1, 'preview-img-1', false);

        // 3. MARCAS (Dynamic Slots)
        updateBrandsPreview();

        // 4. MAPA
        updateMapPreview();
    }

    function updateImagePreview(slotIndex, targetId, isBackground) {
        const fileInput = document.querySelector(`input[name="image_slot_${slotIndex}"]`);
        const existingImg = document.querySelector(`input[name="slot_image_id_${slotIndex}"]`);
        const targetEl = document.getElementById(targetId);
        
        if (!targetEl) return;
        const basePath = (slotIndex === 0) ? '/assets/uploads/shipping/banner/' : '/assets/uploads/shipping/body/';

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

    function updateBrandsPreview() {
        const container = document.getElementById('preview-brands-container');
        container.innerHTML = ''; 

        // Buscar slots en brands-container
        const brandSlots = document.querySelectorAll('#brands-container .image-slot');

        brandSlots.forEach(slot => {
            if(slot.id === 'btn-add-brand') return;

            const fileInput = slot.querySelector('input[type="file"]');
            const hiddenInput = slot.querySelector('input.slot-image-id');
            const deleteInput = slot.querySelector('input.delete-slot-flag');

            if(deleteInput && deleteInput.value === 'DELETE') return;

            let src = null;
            if (fileInput && fileInput.files && fileInput.files[0]) {
                src = URL.createObjectURL(fileInput.files[0]);
            } else if (hiddenInput && hiddenInput.value) {
                src = '/assets/uploads/shipping/body/' + hiddenInput.value;
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

    function updateMapPreview() {
        const inputMap = document.querySelector('input[name="shipping_link"]');
        const iframe = document.getElementById('preview-map-iframe');
        const container = document.getElementById('preview-map-container');

        if (inputMap && inputMap.value.trim() !== '') {
            iframe.src = inputMap.value;
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

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