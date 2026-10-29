document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('preview-modal');
    const btnOpen = document.getElementById('btn-open-preview');
    const btnClose = document.getElementById('btn-close-preview');

    // function updatePreviewData() {
    //     const sliderWrapper = document.getElementById('preview-slider-wrapper');
    //     sliderWrapper.innerHTML = '';

    //     // Buscamos todos los slots de imagen en la página de Admin Home
    //     // Asumiendo que usas la clase .image-slot para los slides del banner
    //     const slots = document.querySelectorAll('.image-slot');

    //     slots.forEach(slot => {
    //         // Ignorar botones de agregar si los hay
    //         if(slot.id && slot.id.includes('btn-add')) return;

    //         const fileInput = slot.querySelector('input[type="file"]');
    //         const hiddenInput = slot.querySelector('input.slot-image-id');
    //         const deleteInput = slot.querySelector('input.delete-slot-flag');

    //         // Si está marcado para borrar, saltar
    //         if(deleteInput && (deleteInput.value === 'DELETE' || deleteInput.value === '1')) return;

    //         let src = null;

    //         if (fileInput && fileInput.files && fileInput.files[0]) {
    //             src = URL.createObjectURL(fileInput.files[0]);
    //         } else if (hiddenInput && hiddenInput.value) {
    //             // Ajusta la ruta si tus banners se guardan en otra carpeta
    //             src = '/assets/uploads/home/banner/' + hiddenInput.value;
    //         }

    //         if (src) {
    //             // Crear estructura del slide
    //             const slide = document.createElement('div');
    //             slide.className = 'swiper-slide';
    //             slide.style.width = '100%';
    //             slide.style.flexShrink = '0';
                
    //             const img = document.createElement('img');
    //             img.src = src;
    //             img.style.width = '100%';
    //             img.style.height = '100%';
    //             img.style.objectFit = 'cover';

    //             slide.appendChild(img);
    //             sliderWrapper.appendChild(slide);
    //         }
    //     });

    // Dentro de la función updatePreviewData en admin-home-preview.js

    function updatePreviewData() {
        const sliderWrapper = document.getElementById('preview-slider-wrapper');
        const secondaryBannerImg = document.getElementById('preview-secondary-banner');
        const secondaryBannerPlaceholder = document.getElementById('preview-banner-placeholder');
        
        sliderWrapper.innerHTML = ''; // Limpiar slider principal

        const slots = document.querySelectorAll('.image-slot');

        slots.forEach((slot, index) => {
            const fileInput = slot.querySelector('input[type="file"]');
            const hiddenInput = slot.querySelector('input.slot-image-id');
            const deleteInput = slot.querySelector('input.delete-slot-flag');

            if(deleteInput && (deleteInput.value === 'DELETE' || deleteInput.value === '1')) return;

            let src = null;
            if (fileInput && fileInput.files && fileInput.files[0]) {
                src = URL.createObjectURL(fileInput.files[0]);
            } else if (hiddenInput && hiddenInput.value) {
                // Verificamos si es del slider (0-4) o del banner (5)
                const folder = (index < 5) ? 'slider' : 'banner';
                src = '/assets/uploads/home/' + folder + '/' + hiddenInput.value;
            }

            if (src) {
                // LÓGICA PARA EL SLIDER (Slots 0 al 4)
                if (index < 5) {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide';
                    slide.style.cssText = 'width: 100%; flex-shrink: 0;';
                    
                    const img = document.createElement('img');
                    img.src = src;
                    img.style.cssText = 'width: 100%; height: 100%; object-fit: cover;';

                    slide.appendChild(img);
                    sliderWrapper.appendChild(slide);
                } 
                // LÓGICA PARA EL BANNER SECUNDARIO (Slot 5)
                else if (index === 5 && secondaryBannerImg) {
                    secondaryBannerImg.src = src;
                    secondaryBannerImg.style.display = 'block';
                    if(secondaryBannerPlaceholder) secondaryBannerPlaceholder.style.display = 'none';
                }
            }
        });
        
        // Si no hay slides, mostrar mensaje
        if(sliderWrapper.children.length === 0) {
            sliderWrapper.innerHTML = '<div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#ccc; color:#666;">Sin imágenes</div>';
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