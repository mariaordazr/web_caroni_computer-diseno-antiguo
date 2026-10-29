document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('image-input');
    const modal = document.getElementById('crop-modal');
    const imageToCrop = document.getElementById('image-to-crop');
    const btnCrop = document.getElementById('btn-crop-confirm');
    const btnCancel = document.getElementById('btn-cancel-crop');
    
    // Elementos de tu vista actual para mostrar el resultado final
    const previewContainer = document.getElementById('image-preview-container'); // El placeholder (+)
    const uploadBoxLabel = document.querySelector('.image-upload-box label');

    let cropper = null;

    // 1. Interceptar la selección de archivo
    fileInput.addEventListener('click', function() {
        // Resetear valor para permitir seleccionar la misma imagen si se canceló antes
        this.value = null;
    });

    fileInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files.length > 0) {
            const file = e.target.files[0];
            
            // Validar que sea imagen
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(evt) {
                // Cargar imagen en el modal
                imageToCrop.src = evt.target.result;
                modal.style.display = 'flex';

                // Iniciar Cropper
                if (cropper) cropper.destroy();
                cropper = new Cropper(imageToCrop, {
                    aspectRatio: 1 / 1, // FORZAR CUADRADO EXACTO
                    viewMode: 1,        // Restringir el cuadro dentro de la imagen
                    autoCropArea: 1,
                });
            };
            reader.readAsDataURL(file);
        }
    });

    // 2. Acción de Recortar
    btnCrop.addEventListener('click', function() {
        if (!cropper) return;

        // Obtener el canvas recortado
        const canvas = cropper.getCroppedCanvas({
            width: 500,  // Redimensionar a un tamaño razonable para avatar
            height: 500,
            imageSmoothingQuality: 'high'
        });

        // Convertir canvas a Blob (archivo)
        canvas.toBlob(function(blob) {
            // Crear un nuevo archivo File desde el Blob
            const croppedFile = new File([blob], "avatar_recortado.webp", { type: "image/webp" });

            // TRUCO: Reemplazar el archivo en el input original
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(croppedFile);
            fileInput.files = dataTransfer.files;

            // --- ACTUALIZAR LA VISTA PREVIA (Reutilizando lógica visual) ---
            // Ocultar placeholder
            if(previewContainer) previewContainer.style.display = 'none';
            
            // Limpiar previews anteriores si existen
            const existingFrame = uploadBoxLabel.querySelector('.image-preview-frame');
            if (existingFrame) existingFrame.remove();

            // Crear el marco de preview (tomado de tu CSS actual)
            const frame = document.createElement('div');
            frame.classList.add('image-preview-frame');
            
            const img = document.createElement('img');
            img.classList.add('image-preview');
            img.src = canvas.toDataURL('image/webp'); // Mostrar el resultado recortado

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-preview-btn';
            removeBtn.innerHTML = "<i class='bx bx-x'></i>";
            
            // Lógica para borrar el preview
            removeBtn.onclick = (e) => {
                e.preventDefault(); 
                frame.remove();
                if(previewContainer) previewContainer.style.display = 'flex';
                fileInput.value = ''; // Limpiar input
            };

            frame.appendChild(img);
            frame.appendChild(removeBtn);
            uploadBoxLabel.appendChild(frame);
            
            // Agregar clase al padre para quitar bordes
            uploadBoxLabel.closest('.image-upload-box').classList.add('has-preview');

            // Cerrar modal
            closeModal();

        }, 'image/webp', 0.9);
    });

    // 3. Cancelar
    btnCancel.addEventListener('click', function() {
        fileInput.value = ''; // Limpiar selección
        closeModal();
    });

    function closeModal() {
        modal.style.display = 'none';
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }
});