document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. LÓGICA PARA CREAR NOTA (create-receipt.php)
    // ==========================================
    const tableBody = document.getElementById('items-body');
    const addRowBtn = document.getElementById('add-row-btn');

    // Solo ejecutamos esto si estamos en la vista de crear nota
    if (tableBody && addRowBtn) {
        
        // Agregar nueva fila
        addRowBtn.addEventListener('click', function() {
            const newRow = tableBody.querySelector('tr').cloneNode(true);
            
            // Limpiar los nuevos campos del formulario
            newRow.querySelector('.und-input').value = 1;
            newRow.querySelector('input[name="model[]"]').value = '';
            newRow.querySelector('input[name="serial[]"]').value = '';
            newRow.querySelector('input[name="accessories[]"]').value = '';
            newRow.querySelector('input[name="details[]"]').value = '';
            newRow.querySelector('input[name="features[]"]').value = '';
            
            // Reactivar el botón de eliminar para la fila clonada
            const removeBtn = newRow.querySelector('.remove-row');
            if (removeBtn) {
                removeBtn.disabled = false;
            }
            
            tableBody.appendChild(newRow);
        });

        // Eliminar fila
        tableBody.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                const row = e.target.closest('tr');
                // Evitamos borrar si solo queda una fila
                if (tableBody.querySelectorAll('tr').length > 1) {
                    row.remove();
                }
            }
        });
    }
});

// ==========================================
// 2. LÓGICA PARA ELIMINAR NOTA (receipts.php)
// ==========================================
window.deleteReceipt = function(id) {
    if(confirm('¿Estás seguro de que deseas eliminar esta nota de entrega? El archivo PDF también se borrará del servidor y esta acción no se puede deshacer.')) {
        
        fetch('/admin/receipts/delete/' + id)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Recarga para actualizar la tabla
            } else {
                alert(data.message || 'Error al eliminar la nota.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error de conexión al intentar eliminar la nota.');
        });
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const phoneInputField = document.querySelector("#phone_input");
    const phoneHiddenField = document.querySelector("#client_phone_hidden");

    if (phoneInputField && phoneHiddenField) {
        // Detectar si estamos en el dominio de Perú
        const isPeru = window.location.hostname.includes('corporaciongruponet.com');

        const iti = window.intlTelInput(phoneInputField, {
            // País por defecto al abrir el formulario: 'pe' para Perú, 've' para Venezuela
            initialCountry: isPeru ? "pe" : "ve",
            // Países favoritos en la parte superior del menú desplegable
            preferredCountries: isPeru ? ["pe", "ve", "co", "cl", "us"] : ["ve", "co", "pe", "cl", "us"],
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
        });

        window.itiInstance = iti;

        const syncPhone = function() {
            const num = iti.getNumber();
            phoneHiddenField.value = num ? num : phoneInputField.value.trim();
        };

        syncPhone();

        phoneInputField.addEventListener('input', syncPhone);
        phoneInputField.addEventListener('change', syncPhone);
        phoneInputField.addEventListener('blur', syncPhone);
        phoneInputField.addEventListener('countrychange', syncPhone);
    }
});


// ==========================================
// 3. LÓGICA PARA FINALIZAR / REABRIR NOTA
// ==========================================
window.toggleReceiptStatus = function(id, newStatus) {
    const actionText = newStatus === 1 ? 'finalizar (marcar como entregada)' : 'reabrir';
    
    // Quitamos la confirmación (confirm) y ejecutamos el fetch directamente
    fetch('/admin/receipts/toggle-status', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload(); // Recarga la página inmediatamente al tener éxito
        } else {
            alert(data.message || `Error al ${actionText} la nota.`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(`Ocurrió un error de conexión al intentar ${actionText} la nota.`);
    });
};


// ==================================================================
// VISOR MODAL DE IMÁGENES CON PESTAÑAS (INICIALES / PROCESO) Y ZOOM
// ==================================================================
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('product-gallery-modal');
    const btnOpenGallery = document.getElementById('btn-open-gallery');
    
    if (modal && btnOpenGallery) {
        const closeBtn = document.getElementById('receipt-modal-close');
        const prevBtn = document.getElementById('receipt-modal-prev');
        const nextBtn = document.getElementById('receipt-modal-next');
        const viewport = document.getElementById('receipt-modal-viewport');
        const mainImg = document.getElementById('modal-current-image');
        
        const tabs = modal.querySelectorAll('.gallery-tab-btn');
        const galleryWrappers = modal.querySelectorAll('.modal-thumbnails-wrapper');
        
        let currentThumbnails = []; 
        let currentIndex = 0;
        let isZoomed = false;

        // --- LÓGICA DE CAMBIO DE IMAGEN ---
        const updateImage = () => {
            if (currentThumbnails.length === 0) return;
            
            currentThumbnails.forEach(t => t.classList.remove('is-active'));
            const activeThumb = currentThumbnails[currentIndex];
            activeThumb.classList.add('is-active');
            mainImg.src = activeThumb.getAttribute('data-fullsrc');
            
            activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            resetZoom(); 
        };

        // --- LÓGICA DE PESTAÑAS (TABS) ---
        const switchTab = (targetId) => {
            tabs.forEach(t => t.classList.remove('active'));
            const activeTab = modal.querySelector(`.gallery-tab-btn[data-target="${targetId}"]`);
            if (activeTab) activeTab.classList.add('active');

            galleryWrappers.forEach(w => w.style.display = 'none');
            const targetWrapper = document.getElementById(targetId);
            
            if (targetWrapper) {
                targetWrapper.style.display = 'flex';
                currentThumbnails = targetWrapper.querySelectorAll('.thumbnail-item');
                currentIndex = 0; 
                updateImage();
            }
        };

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.stopPropagation();
                switchTab(tab.getAttribute('data-target'));
            });
        });

        // Configuración inicial de las miniaturas a leer
        const defaultTab = modal.querySelector('.gallery-tab-btn.active');
        if (defaultTab) {
            currentThumbnails = document.getElementById(defaultTab.getAttribute('data-target')).querySelectorAll('.thumbnail-item');
        } else if (galleryWrappers.length > 0) {
            currentThumbnails = galleryWrappers[0].querySelectorAll('.thumbnail-item'); // Fallback
        }

        // --- CONTROLES DEL MODAL ---
        const openModal = () => {
            if(modal.querySelectorAll('.thumbnail-item').length === 0) return;
            modal.style.display = 'flex'; // Abre instantáneamente
            document.body.style.overflow = 'hidden'; 
            updateImage();
        };

        const closeModal = () => {
            modal.style.display = 'none'; // Cierra instantáneamente
            document.body.style.overflow = '';
            resetZoom();
        };

        btnOpenGallery.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
        if(closeBtn) closeBtn.addEventListener('click', closeModal);
        

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(currentThumbnails.length === 0) return;
                currentIndex = (currentIndex === 0) ? currentThumbnails.length - 1 : currentIndex - 1;
                updateImage();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if(currentThumbnails.length === 0) return;
                currentIndex = (currentIndex === currentThumbnails.length - 1) ? 0 : currentIndex + 1;
                updateImage();
            });
        }

        modal.addEventListener('click', (e) => {
            const thumb = e.target.closest('.thumbnail-item');
            if (thumb) {
                e.stopPropagation();
                const index = Array.from(currentThumbnails).indexOf(thumb);
                if (index !== -1) { currentIndex = index; updateImage(); }
                return; // Detenemos la ejecución aquí si tocó una miniatura
            }

            // NUEVO: Cerrar el visor si se presiona exactamente fuera de la imagen.
            // Excluimos la imagen principal (mainImg), las pestañas, botones de navegación y la 'x'.
            if (
                e.target !== mainImg && 
                !e.target.closest('.modal-tabs-container') && 
                !e.target.closest('.modal-prev-btn') && 
                !e.target.closest('.modal-next-btn') &&
                !e.target.closest('#receipt-modal-close')
            ) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (modal.classList.contains('is-open')) {
                if (e.key === 'Escape') closeModal();
                if (e.key === 'ArrowLeft' && prevBtn) prevBtn.click();
                if (e.key === 'ArrowRight' && nextBtn) nextBtn.click();
            }
        });

        // --- ZOOM 1 CLICK (PC) Y GESTOS (MÓVIL) ---
        let currentScale = 1; let translateX = 0; let translateY = 0;
        let startX = 0, startY = 0; let initialDistance = 0; let initialScale = 1;
        let isTouching = false; let lastTapTime = 0;

        const isTouchDevice = window.matchMedia("(hover: none) and (pointer: coarse)").matches;

        function resetZoom() {
            isZoomed = false; currentScale = 1; translateX = 0; translateY = 0;
            mainImg.classList.remove('is-zoomed');
            mainImg.style.transform = 'scale(1)';
            mainImg.style.transformOrigin = 'center center';
        }

        function calculateZoomOrigin(e) {
            const rect = viewport.getBoundingClientRect();
            const xPercent = ((e.clientX - rect.left) / rect.width) * 100;
            const yPercent = ((e.clientY - rect.top) / rect.height) * 100;
            mainImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
        }

        viewport.addEventListener('click', function(e) {
            // SOLUCIÓN: Si el clic NO es estrictamente sobre la imagen, detenemos la ejecución
            if (e.target !== mainImg) return; 
            if (isTouchDevice) return; 
            
            isZoomed = !isZoomed;
            if (isZoomed) {
                currentScale = 2.5; mainImg.classList.add('is-zoomed');
                mainImg.style.transform = `scale(${currentScale})`;
                calculateZoomOrigin(e);
            } else { resetZoom(); }
        });

        viewport.addEventListener('mousemove', function(e) {
            if (isZoomed && currentScale === 2.5 && !isTouchDevice) calculateZoomOrigin(e);
        });
        
        viewport.addEventListener('mouseleave', function() {
            if(isZoomed && currentScale === 2.5 && !isTouchDevice) resetZoom();
        });

        viewport.addEventListener('touchstart', function(e) {
            if (e.target !== mainImg) return;
            if (e.touches.length === 2) {
                e.preventDefault(); 
                initialDistance = Math.hypot(e.touches[0].pageX - e.touches[1].pageX, e.touches[0].pageY - e.touches[1].pageY);
                initialScale = currentScale;
            } else if (e.touches.length === 1 && currentScale > 1) {
                e.preventDefault(); 
                isTouching = true;
                startX = e.touches[0].pageX - translateX; startY = e.touches[0].pageY - translateY;
            }
        }, { passive: false });

        viewport.addEventListener('touchmove', function(e) {
            if (e.target !== mainImg) return;
            if (e.touches.length === 2) {
                e.preventDefault();
                const currentDist = Math.hypot(e.touches[0].pageX - e.touches[1].pageX, e.touches[0].pageY - e.touches[1].pageY);
                currentScale = Math.max(1, Math.min(initialScale * (currentDist / initialDistance), 4.5));
                mainImg.style.transformOrigin = 'center center'; 
                mainImg.style.transform = `translate(${translateX}px, ${translateY}px) scale(${currentScale})`;
                isZoomed = true;
            } else if (e.touches.length === 1 && isTouching && currentScale > 1) {
                e.preventDefault();
                translateX = e.touches[0].pageX - startX; translateY = e.touches[0].pageY - startY;
                mainImg.style.transform = `translate(${translateX}px, ${translateY}px) scale(${currentScale})`;
            }
        }, { passive: false });

        viewport.addEventListener('touchend', function(e) {
            if (e.touches.length < 2) initialDistance = 0;
            if (e.touches.length === 0) {
                isTouching = false;
                if (currentScale <= 1.05) resetZoom();
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTapTime;
                if (tapLength < 300 && tapLength > 0 && e.target === mainImg) {
                    if (currentScale > 1) { resetZoom(); } else {
                        currentScale = 2.5; mainImg.classList.add('is-zoomed');
                        mainImg.style.transformOrigin = 'center center';
                        mainImg.style.transform = `translate(0px, 0px) scale(${currentScale})`;
                        isZoomed = true;
                    }
                    e.preventDefault();
                }
                lastTapTime = currentTime;
            }
        });
    }
});

// ==================================================================
// 5. LÓGICA DE SLOTS DINÁMICOS Y DRAG&DROP PARA RECIBOS (Múltiples Galerías)
// ==================================================================
document.addEventListener('DOMContentLoaded', function() {
    const galleries = document.querySelectorAll('.receipt-gallery');

    galleries.forEach(container => {
        const inputName = container.getAttribute('data-input-name');
        const deleteName = container.getAttribute('data-delete-name');
        const orderInputId = container.getAttribute('data-order-input');

        // Función para actualizar el JSON del Drag & Drop
        function updateOrder() {
            const orderInput = document.getElementById(orderInputId);
            if (!orderInput) return;
            const order = [];
            container.querySelectorAll('.image-slot.has-preview').forEach(slot => {
                const existingBtn = slot.querySelector('.remove-preview-btn.existing-image');
                if (existingBtn) {
                    order.push('existing_' + existingBtn.getAttribute('data-image-name'));
                } else {
                    const fileInput = slot.querySelector('input[type="file"]');
                    if (fileInput && fileInput.files.length > 0) order.push('new_' + fileInput.files[0].name);
                }
            });
            orderInput.value = JSON.stringify(order);
        }

        function createEmptySlot() {
            const div = document.createElement('div');
            div.classList.add('image-slot', 'dynamic-slot');
            div.innerHTML = `
                <label style="width: 100%; height: 100%; display: block; margin: 0; cursor: pointer;">
                    <div class="image-placeholder desktop-view"><i class='bx bx-plus'></i></div>
                    <div class="image-placeholder mobile-view mobile-split">
                        <div class="upload-btn camera-btn" data-capture="environment"><i class='bx bx-camera'></i><span class="btn-text">Cámara</span></div>
                        <div class="upload-btn gallery-btn" data-capture=""><i class='bx bx-image-add'></i><span class="btn-text">Galería</span></div>
                    </div>
                    <input type="file" name="${inputName}" accept="image/*" multiple style="display:none;">
                </label>
            `;
            return div;
        }

        function setupPreview(slot, file) {
            const input = slot.querySelector('input[type="file"]');
            if (input) {
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    input.files = dt.files;
                } catch (e) {}
            }
            slot.querySelectorAll('.image-placeholder').forEach(p => p.style.display = 'none');
            slot.classList.add('has-preview');
            slot.setAttribute('draggable', 'true');

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
                ev.preventDefault(); ev.stopPropagation();
                slot.remove(); URL.revokeObjectURL(img.src); updateOrder();
            });

            frame.appendChild(img); frame.appendChild(removeBtn);
            slot.querySelector('label').appendChild(frame);
            updateOrder();
        }

        // A. Carga de Archivos
        container.addEventListener('change', function(e) {
            if (e.target.tagName.toLowerCase() === 'input' && e.target.type === 'file') {
                const files = Array.from(e.target.files);
                if (files.length === 0) return;
                let lastSlot = e.target.closest('.image-slot');
                if (!lastSlot) return;

                setupPreview(lastSlot, files[0]);
                for (let i = 1; i < files.length; i++) {
                    const newSlot = createEmptySlot();
                    lastSlot.insertAdjacentElement('afterend', newSlot);
                    setupPreview(newSlot, files[i]);
                    lastSlot = newSlot; 
                }
                if (!lastSlot.nextElementSibling) container.appendChild(createEmptySlot());
            }
        });

        // B. Clics (Borrar y Cámara)
        container.addEventListener('click', function(e) {
            const uploadBtn = e.target.closest('.upload-btn');
            if (uploadBtn) {
                e.preventDefault(); e.stopPropagation();
                const fileInput = uploadBtn.closest('.image-slot').querySelector('input[type="file"]');
                const capture = uploadBtn.getAttribute('data-capture');
                if (capture) fileInput.setAttribute('capture', capture);
                else fileInput.removeAttribute('capture');
                fileInput.click();
            } else if (e.target.closest('.remove-preview-btn.existing-image')) {
                e.preventDefault(); e.stopPropagation();
                const btn = e.target.closest('.remove-preview-btn.existing-image');
                const slot = btn.closest('.image-slot');
                const hiddenDelete = document.createElement('input');
                hiddenDelete.type = 'hidden'; hiddenDelete.name = deleteName;
                hiddenDelete.value = btn.getAttribute('data-image-name');
                container.appendChild(hiddenDelete);
                slot.remove(); updateOrder();
            }
        });

        // C. Drag & Drop (Escritorio)
        let draggedSlot = null;
        container.addEventListener('dragstart', e => {
            const slot = e.target.closest('.image-slot.has-preview');
            if (slot) { draggedSlot = slot; setTimeout(() => slot.classList.add('is-dragging'), 0); }
        });
        container.addEventListener('dragend', () => {
            if (draggedSlot) { draggedSlot.classList.remove('is-dragging'); draggedSlot = null; updateOrder(); }
        });
        container.addEventListener('dragover', e => {
            e.preventDefault();
            if (!draggedSlot) return;
            const targetSlot = e.target.closest('.image-slot.has-preview');
            // Evita arrastrar fotos entre galerías distintas
            if (targetSlot && targetSlot !== draggedSlot && targetSlot.parentNode === draggedSlot.parentNode) {
                const box = targetSlot.getBoundingClientRect();
                const isNext = (e.clientX - box.left) > (box.width / 2);
                targetSlot.parentNode.insertBefore(draggedSlot, isNext ? targetSlot.nextSibling : targetSlot);
            }
        });

        // D. Drag & Drop Táctil (Móvil)
        let touchDraggedSlot = null; let pressTimer = null;
        container.addEventListener('touchstart', e => {
            if (e.target.closest('button') || e.target.closest('.upload-btn') || e.target.tagName.toLowerCase() === 'input') return;
            const slot = e.target.closest('.image-slot.has-preview');
            if (slot) {
                pressTimer = setTimeout(() => {
                    touchDraggedSlot = slot; slot.classList.add('is-dragging');
                    if (navigator.vibrate) navigator.vibrate(50);
                }, 250);
            }
        }, { passive: false });
        
        container.addEventListener('touchmove', e => {
            if (!touchDraggedSlot) { clearTimeout(pressTimer); return; }
            e.preventDefault();
            const touch = e.touches[0];
            const targetElement = document.elementFromPoint(touch.clientX, touch.clientY);
            if (!targetElement) return;
            const targetSlot = targetElement.closest('.image-slot.has-preview');
            if (targetSlot && targetSlot !== touchDraggedSlot && targetSlot.parentNode === touchDraggedSlot.parentNode) {
                const box = targetSlot.getBoundingClientRect();
                const isNext = (touch.clientX - box.left) > (box.width / 2);
                targetSlot.parentNode.insertBefore(touchDraggedSlot, isNext ? targetSlot.nextSibling : targetSlot);
            }
        }, { passive: false });

        const endTouch = () => {
            clearTimeout(pressTimer);
            if (touchDraggedSlot) { touchDraggedSlot.classList.remove('is-dragging'); touchDraggedSlot = null; updateOrder(); }
        };
        container.addEventListener('touchend', endTouch);
        container.addEventListener('touchcancel', endTouch);

        updateOrder(); // Inicializar estado visual
    });
    
});

window.deleteReceipt = function(id) {
    Swal.fire({
        title: '¿Eliminar Nota?',
        text: "Esta acción borrará la nota del sistema y vaciará su contenido en Google Drive.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Eliminando...',
                text: 'Borrando de la base de datos y Google Drive',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch('/admin/receipts/delete/' + id, { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('¡Eliminada!', data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', 'Hubo un problema al eliminar la nota.', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Fallo de conexión.', 'error');
                });
        }
    });
};

// ==========================================
// 6. LÓGICA PARA VER INFORMACIÓN PRIVADA
// ==========================================
window.showPrivateInfo = function(techName, notes) {
    const displayTech = techName ? techName : 'No asignado';
    const displayNotes = notes ? notes : 'No hay notas privadas.';
    
    Swal.fire({
        title: '<h3 style="color:#dc3545; margin-bottom:0; font-size: 1.5rem;"><i class="bx bx-lock-alt"></i> Información Privada</h3>',
        html: `
            <div style="text-align: left; background: #fdf2f2; padding: 15px; border-left: 4px solid #dc3545; border-radius: 4px; margin-top: 10px; font-family: 'Inter', sans-serif;">
                <p style="margin-bottom: 10px; font-size: 0.95rem;"><strong>Técnico Encargado:</strong><br> ${displayTech}</p>
                <p style="margin-bottom: 0; font-size: 0.95rem;"><strong>Notas Privadas de Taller:</strong><br> <span style="white-space: pre-wrap; display: block; margin-top: 5px; color: #333;">${displayNotes}</span></p>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Cerrar',
        confirmButtonColor: '#6c757d'
    });
};

// Escuchador de eventos seguro para resolver el conflicto en móviles
document.addEventListener('click', function(e) {
    const privateBtn = e.target.closest('.btn-private-info');
    
    if (privateBtn) {
        e.preventDefault();  // Evita el salto hacia arriba por el href="#"
        e.stopPropagation(); // Evita que admin.js cierre el menú antes de lanzar la alerta
        
        // Cerramos el menú desplegable visualmente de forma manual
        document.querySelectorAll('.actions-dropdown').forEach(d => d.classList.remove('is-active'));
        
        const tech = privateBtn.getAttribute('data-tech');
        const notes = privateBtn.getAttribute('data-notes');
        
        window.showPrivateInfo(tech, notes);
    }
});