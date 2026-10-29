document.addEventListener('DOMContentLoaded', function() {

    // --- 1. ELEMENTOS PÁGINA PRINCIPAL ---
    const mainImageContainer = document.querySelector('.gallery-main-image');
    const mainImage = document.getElementById('main-product-image');
    
    // <-- CORRECCIÓN: Separamos TODAS las miniaturas de las que son SOLO FOTOS
    const allThumbnails = document.querySelectorAll('.thumbnail-item'); 
    const imageThumbnails = document.querySelectorAll('.thumbnail-item[data-type="image"]'); 
    
    // --- 2. ELEMENTOS MODAL ---
    const galleryModal = document.getElementById('product-gallery-modal');
    const modalCloseBtn = document.querySelector('.modal-close-btn');
    const modalCurrentImage = document.getElementById('modal-current-image');
    const zoomCanvas = document.getElementById('zoom-canvas');
    const modalPrevBtn = document.querySelector('.modal-prev-btn');
    const modalNextBtn = document.querySelector('.modal-next-btn');
    const modalThumbnailsContainer = document.querySelector('.modal-thumbnails');

    // --- 3. VARIABLES DE ESTADO ---
    let currentImageIndex = 0;
    let currentScale = 1;
    let isDragging = false;
    let startX = 0, startY = 0;
    let translateX = 0, translateY = 0;
    let evCache = []; 
    let prevDiff = -1; 
    
    // <-- CORRECCIÓN: El modal ahora solo extraerá las URLs de las FOTOS, ignorando el video
    const productImages = Array.from(imageThumbnails).map(item => item.querySelector('img').src);
    
    let handleMouseMove, handleMouseEnter, handleMouseLeave;

    if (mainImageContainer && mainImage && allThumbnails.length > 0 && galleryModal && productImages.length > 0) {

        // --- 4. HOVER EN PÁGINA PRINCIPAL (Zoom lupa) ---
        function setupHoverZoom() {
            if (handleMouseMove) {
                mainImageContainer.removeEventListener('mousemove', handleMouseMove);
                mainImageContainer.removeEventListener('mouseenter', handleMouseEnter);
                mainImageContainer.removeEventListener('mouseleave', handleMouseLeave);
            }

            handleMouseEnter = () => { 
                if (mainImage.style.display !== 'none') mainImage.style.transform = 'scale(1.8)'; 
            };
            handleMouseMove = (e) => {
                if (mainImage.style.display !== 'none') {
                    const rect = mainImageContainer.getBoundingClientRect();
                    const x = (e.clientX - rect.left) / rect.width;
                    const y = (e.clientY - rect.top) / rect.height;
                    mainImage.style.transformOrigin = `${x * 100}% ${y * 100}%`;
                }
            };
            handleMouseLeave = () => { mainImage.style.transform = 'scale(1)'; };

            mainImageContainer.addEventListener('mouseenter', handleMouseEnter);
            mainImageContainer.addEventListener('mousemove', handleMouseMove);
            mainImageContainer.addEventListener('mouseleave', handleMouseLeave);
        }

        // --- 5. LÓGICA DE ZOOM ESTILO MERCADO LIBRE ---
        let isModalZoomed = false;
        const ZOOM_SCALE = 2.5;

        let touchStartX = 0;
        let touchStartY = 0;
        let lastTapTime = 0;
        let currentTranslateX = 0;
        let currentTranslateY = 0;

        function resetZoom() {
            isModalZoomed = false;
            currentTranslateX = 0;
            currentTranslateY = 0;
            if(zoomCanvas) zoomCanvas.classList.remove('is-zoomed');
            if(modalCurrentImage) {
                modalCurrentImage.style.transform = 'scale(1) translate(0px, 0px)';
                modalCurrentImage.style.transformOrigin = 'center center';
            }
        }

        if (zoomCanvas) {
            zoomCanvas.addEventListener('click', (e) => {
                if (window.innerWidth > 768) {
                    isModalZoomed = !isModalZoomed;
                    if (isModalZoomed) {
                        zoomCanvas.classList.add('is-zoomed');
                        panImageDesktop(e);
                        modalCurrentImage.style.transform = `scale(${ZOOM_SCALE})`;
                    } else {
                        resetZoom();
                    }
                }
            });

            zoomCanvas.addEventListener('mousemove', (e) => {
                if (isModalZoomed && window.innerWidth > 768) {
                    panImageDesktop(e);
                }
            });
        }

        function panImageDesktop(e) {
            const rect = zoomCanvas.getBoundingClientRect();
            let x = (e.clientX - rect.left) / rect.width;
            let y = (e.clientY - rect.top) / rect.height;
            x = Math.max(0, Math.min(1, x));
            y = Math.max(0, Math.min(1, y));
            modalCurrentImage.style.transformOrigin = `${x * 100}% ${y * 100}%`;
        }

        // --- 6. MÓVIL: DOBLE TOQUE (ZOOM), ARRASTRE Y DESLIZAMIENTO (SWIPE) ---
        if (zoomCanvas) {
            zoomCanvas.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;

                    const currentTime = new Date().getTime();
                    const tapLength = currentTime - lastTapTime;
                    
                    if (tapLength < 300 && tapLength > 0) {
                        e.preventDefault(); 
                        isModalZoomed = !isModalZoomed;
                        if (isModalZoomed) {
                            zoomCanvas.classList.add('is-zoomed');
                            modalCurrentImage.style.transform = `scale(${ZOOM_SCALE})`;
                            modalCurrentImage.style.transformOrigin = 'center center';
                        } else {
                            resetZoom();
                        }
                    }
                    lastTapTime = currentTime;
                }
            }, { passive: false });

            zoomCanvas.addEventListener('touchmove', (e) => {
                if (isModalZoomed && e.touches.length === 1) {
                    e.preventDefault(); 
                    const deltaX = e.touches[0].clientX - touchStartX;
                    const deltaY = e.touches[0].clientY - touchStartY;
                    
                    modalCurrentImage.style.transform = `scale(${ZOOM_SCALE}) translate(${(currentTranslateX + deltaX) / ZOOM_SCALE}px, ${(currentTranslateY + deltaY) / ZOOM_SCALE}px)`;
                }
            }, { passive: false });

            zoomCanvas.addEventListener('touchend', (e) => {
                if (isModalZoomed && e.changedTouches.length === 1) {
                    currentTranslateX += e.changedTouches[0].clientX - touchStartX;
                    currentTranslateY += e.changedTouches[0].clientY - touchStartY;
                    return;
                }

                if (!isModalZoomed && e.changedTouches.length === 1) {
                    const touchEndX = e.changedTouches[0].clientX;
                    const touchEndY = e.changedTouches[0].clientY;
                    const diffX = touchStartX - touchEndX;
                    const diffY = Math.abs(touchStartY - touchEndY);

                    if (Math.abs(diffX) > 50 && diffY < 50) { 
                        if (diffX > 0) {
                            modalNextBtn.click();
                        } else {
                            modalPrevBtn.click();
                        }
                    }
                }
            });
        }

        // --- 8. FUNCIONES BÁSICAS DEL MODAL ---
        function openGalleryModal(startIndex = 0) {
            currentImageIndex = startIndex;
            updateModalImage();
            populateModalThumbnails();
            galleryModal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            resetZoom();
        }

        function closeModal() {
            galleryModal.classList.remove('is-open');
            document.body.style.overflow = '';
            resetZoom();
        }

        function updateModalImage() {
            modalCurrentImage.src = productImages[currentImageIndex];
            resetZoom();

            const modalThumbItems = modalThumbnailsContainer.querySelectorAll('.modal-thumbnail-item');
            modalThumbItems.forEach((item, index) => {
                if (index === currentImageIndex) {
                    item.classList.add('is-active');
                    item.scrollIntoView({ behavior: 'smooth', inline: 'center' });
                } else {
                    item.classList.remove('is-active');
                }
            });
        }

        function populateModalThumbnails() {
            modalThumbnailsContainer.innerHTML = '';
            productImages.forEach((image_url, index) => {
                const thumbItem = document.createElement('div');
                thumbItem.classList.add('modal-thumbnail-item');
                if (index === currentImageIndex) thumbItem.classList.add('is-active');
                thumbItem.innerHTML = `<img src="${image_url}" alt="Miniatura ${index + 1}">`;
                thumbItem.addEventListener('click', () => {
                    currentImageIndex = index;
                    updateModalImage();
                });
                modalThumbnailsContainer.appendChild(thumbItem);
            });
        }

        // --- 9. INICIALIZACIÓN DE MINIATURAS (REESCRITA) ---
        allThumbnails.forEach((thumb) => {
            ['mouseenter', 'click'].forEach(evt => {
                thumb.addEventListener(evt, function(e) {
                    if (evt === 'click') e.preventDefault();

                    allThumbnails.forEach(t => t.classList.remove('is-active'));
                    this.classList.add('is-active');
                    
                    const isVideo = this.getAttribute('data-type') === 'video';
                    const mainVideoContainer = document.getElementById('main-product-video');
                    const videoIframe = document.getElementById('video-iframe');
                    const videoInfoPanel = document.getElementById('video-info-panel');

                    if (isVideo) {
                        mainImage.style.display = 'none';
                        if (mainVideoContainer) {
                            mainVideoContainer.style.display = 'block';
                            const driveId = this.getAttribute('data-video-id');
                            if (!videoIframe.src.includes(driveId)) {
                                videoIframe.src = `https://drive.google.com/file/d/${driveId}/preview`;
                            }
                        }
                        if (videoInfoPanel) videoInfoPanel.style.display = 'block';
                    } else {
                        if (mainVideoContainer) {
                            mainVideoContainer.style.display = 'none';
                            if(videoIframe) videoIframe.src = ''; 
                        }
                        if (videoInfoPanel) videoInfoPanel.style.display = 'none';
                        
                        mainImage.style.display = 'block';
                        mainImage.src = this.querySelector('img').src;
                        
                        // IMPORTANTE: Actualizamos el índice solo dentro de la lista de FOTOS reales
                        currentImageIndex = Array.from(imageThumbnails).indexOf(this);
                    }
                });
            });
        });

        // Prevenir abrir modal al hacer clic en el contenedor cuando el video está reproduciéndose
        mainImageContainer.addEventListener('click', (e) => {
            const mainVideoContainer = document.getElementById('main-product-video');
            if (mainVideoContainer && mainVideoContainer.style.display === 'block') return;
            
            openGalleryModal(currentImageIndex);
        });

        modalCloseBtn.addEventListener('click', closeModal);
        
        modalPrevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
            updateModalImage();
        });
        
        modalNextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            currentImageIndex = (currentImageIndex + 1) % productImages.length;
            updateModalImage();
        });

        document.addEventListener('keydown', (e) => {
            if (galleryModal.classList.contains('is-open')) {
                if (e.key === 'ArrowLeft') modalPrevBtn.click();
                if (e.key === 'ArrowRight') modalNextBtn.click();
                if (e.key === 'Escape') closeModal();
            }
        });

        mainImage.addEventListener('load', () => { setupHoverZoom(); });
        if (mainImage.complete) { setupHoverZoom(); }
    }
});