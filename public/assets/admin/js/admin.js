document.addEventListener('DOMContentLoaded', function() {

    // ==================================================================
    // 0. LÓGICA DE RESTAURACIÓN DE SCROLL Y TOASTS
    // ==================================================================
    
    // 1. Restaurar Scroll
    const scrollPosition = sessionStorage.getItem('scrollPosition');
    if (scrollPosition) {
        window.scrollTo(0, parseInt(scrollPosition));
        sessionStorage.removeItem('scrollPosition');
    }

    // 2. Mostrar Toast pendiente (si venimos de una recarga AJAX)
    const pendingToast = sessionStorage.getItem('pendingToast');
    if (pendingToast) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({
            icon: 'success',
            title: pendingToast
        });
        sessionStorage.removeItem('pendingToast');
    }
    

    // ==================================================================
    // 1. LÓGICA DEL SIDEBAR (ESCRITORIO Y MÓVIL)
    // ==================================================================
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle'); 
    const mobileToggle = document.getElementById('mobile-menu-toggle'); 
    const mobileClose = document.getElementById('sidebar-close-mobile'); 

    // --- A. Lógica Escritorio (Colapsar) ---
    if (sidebar && sidebarToggle) {
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            sidebar.classList.add('sidebar--collapsed');
        }
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar--collapsed');
            localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('sidebar--collapsed'));
        });
    }

    // --- B. Lógica Móvil (Off-Canvas) ---
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    function openMobileMenu() {
        if (sidebar) sidebar.classList.add('mobile-active');
        if (overlay) overlay.classList.add('active');
        document.body.style.overflow = 'hidden'; 
    }

    function closeMobileMenu() {
        if (sidebar) sidebar.classList.remove('mobile-active');
        if (overlay) overlay.classList.remove('active');
        document.body.style.overflow = ''; 
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', (e) => {
            e.preventDefault();
            openMobileMenu();
        });
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', (e) => {
            e.preventDefault();
            closeMobileMenu();
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileMenu);
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });


    // ==================================================================
    // 2. LÓGICA DE FILTRADO (BUSCADOR)
    // ==================================================================
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        const searchInput = filterForm.querySelector('input[name="search"]');
        
        if (searchInput) {
            searchInput.addEventListener('keydown', (e) => {
                // e.keyCode === 13 asegura la captura en teclados móviles donde e.key falla
                if (e.key === 'Enter' || e.keyCode === 13) {
                    
                    // Condicionar para que aplique su lógica específica en móviles
                    if (window.innerWidth <= 768) {
                        e.preventDefault(); // Previene que el navegador salte al siguiente campo
                        filterForm.submit(); // Fuerza la búsqueda de inmediato
                    } else {
                        // Mantiene el comportamiento habitual en pantallas de escritorio
                        e.preventDefault();
                        filterForm.submit();
                    }
                }
            });
        }
                
        // Envía el formulario al cambiar select o checkboxes
        filterForm.querySelectorAll('select, input[type="checkbox"]').forEach(input => {
            input.addEventListener('change', () => {
                filterForm.submit();
            });
        });
    }

    

    // ==================================================================
    // 3. LÓGICA DE VISTA (LISTA VS CUADRÍCULA)
    // ==================================================================
    const btnViewList = document.getElementById('btn-view-list');
    const btnViewGrid = document.getElementById('btn-view-grid');
    const productViewContainer = document.querySelector('.product-view-container');

    if (btnViewList && btnViewGrid && productViewContainer) {
        const preferredView = localStorage.getItem('product_view') || 'list';
        
        if (preferredView === 'grid') {
            productViewContainer.classList.remove('view-list');
            productViewContainer.classList.add('view-grid');
            btnViewList.classList.remove('is-active');
            btnViewGrid.classList.add('is-active');
        }

        btnViewList.addEventListener('click', () => {
            productViewContainer.classList.remove('view-grid');
            productViewContainer.classList.add('view-list');
            btnViewGrid.classList.remove('is-active');
            btnViewList.classList.add('is-active');
            localStorage.setItem('product_view', 'list');
        });

        btnViewGrid.addEventListener('click', () => {
            productViewContainer.classList.remove('view-list');
            productViewContainer.classList.add('view-grid');
            btnViewList.classList.remove('is-active');
            btnViewGrid.classList.add('is-active');
            localStorage.setItem('product_view', 'grid');
        });
    }
    
    // Toggle filtros móvil
    const toggleBtn = document.getElementById('mobile-filter-toggle');
    const filterControls = document.getElementById('filter-controls-container');
    
    if(toggleBtn && filterControls) {
        toggleBtn.addEventListener('click', function() {
            filterControls.classList.toggle('is-visible');
            toggleBtn.classList.toggle('is-active');
        });
    }
});

// ==================================================================
// 4. LÓGICA DE MENÚS DESPLEGABLES (DROPDOWNS)
// ==================================================================
document.addEventListener('click', function(event) {
    
    if (!event.target.closest('.actions-menu-toggle, .stock-toggle-btn, .type-toggle-btn')) {
        closeAllDropdowns();
    }

    // --- A. Menú de ACCIONES ---
    const actionsToggle = event.target.closest('.actions-menu-toggle');
    if (actionsToggle) {
        event.stopPropagation();
        const dropdown = actionsToggle.nextElementSibling;
        closeAllDropdowns('.actions-dropdown, .status-dropdown, .type-dropdown', dropdown);
        dropdown.classList.toggle('is-active');
        return;
    }

    // --- B. Menú de ESTADO ---
    const stockToggle = event.target.closest('.stock-toggle-btn');
    if (stockToggle) {
        event.stopPropagation();
        const dropdown = stockToggle.nextElementSibling;
        closeAllDropdowns('.actions-dropdown, .status-dropdown, .type-dropdown', dropdown);
        dropdown.classList.toggle('is-active');
        return;
    }
    const stockOption = event.target.closest('.stock-option');
    if (stockOption) {
        event.preventDefault();
        event.stopPropagation();
        handleStockOptionClick(stockOption);
        return;
    }

    // --- C. Menú de TIPO ---
    const typeToggle = event.target.closest('.type-toggle-btn');
    if (typeToggle) {
        event.stopPropagation();
        const dropdown = typeToggle.nextElementSibling;
        closeAllDropdowns('.actions-dropdown, .status-dropdown, .type-dropdown', dropdown);
        dropdown.classList.toggle('is-active');
        return;
    }
    const typeOption = event.target.closest('.type-option');
    if (typeOption) {
        event.preventDefault();
        event.stopPropagation();
        handleTypeOptionClick(typeOption);
        return;
    }

    // ==================================================================
    // 5. LÓGICA DE ELIMINACIÓN
    // ==================================================================
    const deleteButton = event.target.closest('.btn-delete');
    if (deleteButton) {
        event.preventDefault();
        event.stopPropagation();
        const deleteUrl = deleteButton.getAttribute('href'); 
        
        if (deleteUrl.includes('/admin/products/')) {
            showSimpleDeleteConfirmation(deleteUrl);
        } else if (deleteUrl.includes('/admin/categories/')) {
            showAdvancedDeleteConfirmation(deleteUrl, 'categoría');
        } else if (deleteUrl.includes('/admin/brands/')) {
            showAdvancedDeleteConfirmation(deleteUrl, 'marca');
        } else {
            showSimpleDeleteConfirmation(deleteUrl);
        }
    }
});

// ==================================================================
// 6. FUNCIONES AUXILIARES
// ==================================================================

// --- NUEVA FUNCIÓN: Recargar guardando el scroll ---
function reloadWithScroll() {
    sessionStorage.setItem('scrollPosition', window.scrollY);
    location.reload();
}

function closeAllDropdowns(selector = '.actions-dropdown, .status-dropdown, .type-dropdown', exclude = null) {
    document.querySelectorAll(selector).forEach(dropdown => {
        if (dropdown !== exclude) {
            dropdown.classList.remove('is-active');
        }
    });
}

function handleStockOptionClick(optionElement) {
    const dropdown = optionElement.closest('.status-dropdown');
    const button = dropdown.previousElementSibling;
    const productId = button.getAttribute('data-product-id');
    const newStockValue = optionElement.getAttribute('data-stock-value'); 

    if (newStockValue === '0') {
        updateStatus(productId, 0);
    } else {
        Swal.fire({
            title: 'Actualizar Stock',
            text: 'Ingresa la nueva cantidad de unidades:',
            input: 'number',
            inputAttributes: { min: 1, step: 1 },
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            preConfirm: (value) => {
                if (!value || value < 1 || !Number.isInteger(Number(value))) {
                    Swal.showValidationMessage('Por favor, ingresa un número entero positivo.');
                    return false;
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateStatus(productId, result.value);
            }
        });
    }
}

function handleTypeOptionClick(optionElement) {
    const type = optionElement.getAttribute('data-type');
    const container = optionElement.closest('.type-toggle-container');
    const button = container.querySelector('.type-toggle-btn');
    const productId = button.getAttribute('data-product-id');
    
    if (type === 'offer') {
        Swal.fire({
            title: 'Establecer Precio de Oferta',
            input: 'number',
            inputAttributes: { step: '0.01', placeholder: '0.00' },
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            preConfirm: (value) => {
                if (!value || isNaN(value) || value <= 0) {
                    Swal.showValidationMessage('Por favor, ingresa un precio válido y positivo.');
                    return false;
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateProductType(productId, 'offer', result.value);
            }
        });
    } else {
        updateProductType(productId, type);
    }
}

function updateStatus(productId, stockValue) {
    fetch('/admin/updateProductStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&stock_value=${stockValue}`
    }).then(res => res.json()).then(data => {
        if (data.success) {
            // Pasamos el mensaje aquí
            reloadWithScroll('¡Estado actualizado correctamente!'); 
        } else {
            Swal.fire('Error', 'No se pudo actualizar el estado.', 'error');
        }
    });
}

function updateProductType(productId, type, price = 0) {
    const formData = new URLSearchParams();
    formData.append('product_id', productId);
    formData.append('type', type);
    formData.append('price', price);
    fetch('/admin/updateProductType', {
        method: 'POST',
        body: formData
    }).then(res => res.json()).then(data => {
        if (data.success) {
            reloadWithScroll('¡Tipo de producto actualizado!');
        } else {
            Swal.fire('Error', 'No se pudo actualizar el producto.', 'error');
        }
    });
}

function showSimpleDeleteConfirmation(url) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, ¡eliminar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    // En el bloque .then(data => { ...
                    if (data.success) {
                        // Ya no usamos Swal.fire aquí, sino reloadWithScroll con mensaje
                        reloadWithScroll('El registro ha sido eliminado.');
                    } else {
                        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                    }
                });
        }
    });
}

function showAdvancedDeleteConfirmation(url, typeName) {
    const countUrl = url.replace('/delete/', '/productCount/'); 

    fetch(countUrl)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('Error', 'No se pudo obtener el conteo de productos.', 'error');
                return;
            }

            const count = data.count;
            let title = '¿Estás seguro?';
            let text = `Se eliminará esta ${typeName}. ¡No podrás revertir esta acción!`;

            if (count > 0) {
                title = `¡Atención! Esta ${typeName} tiene ${count} producto(s) asociado(s).`;
                text = `Si eliminas esta ${typeName}, todos sus productos asociados también serán borrados permanentemente. ¿Estás seguro de continuar?`;
            }

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, ¡eliminar todo!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, { method: 'POST' })
                        .then(res => res.json())
                        .then(deleteData => {
                            // En el bloque .then(deleteData => { ...
                            if (deleteData.success) {
                                // Ya no usamos Swal.fire aquí, sino reloadWithScroll con mensaje
                                reloadWithScroll(`La ${typeName} y sus productos han sido eliminados.`);
                            } else {
                                Swal.fire('Error', 'No se pudo completar la eliminación.', 'error');
                            }
                        });
                }
            });
        })
        .catch(() => {
            Swal.fire('Error', 'Error de conexión al verificar productos.', 'error');
        });
}


// ==========================================
// MENÚ INTELIGENTE: DETECCIÓN DE BORDES
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    function adjustDropdownPosition(btn) {
        const container = btn.closest('.actions-menu-container');
        if (!container) return;
        
        const dropdown = container.querySelector('.actions-dropdown');
        if (!dropdown) return;

        // 1. Si el menú NO está activo (invisible), lo reseteamos para su próxima apertura
        if (!dropdown.classList.contains('is-active')) {
            dropdown.classList.remove('drop-up');
            return;
        }

        // 2. Si ya está abierto y YA calculamos que va hacia arriba, salimos para EVITAR EL PARPADEO
        if (dropdown.classList.contains('drop-up')) {
            return;
        }

        // 3. Calculamos la posición solo una vez cuando se abre hacia abajo
        const rect = dropdown.getBoundingClientRect();
        
        // Si la parte inferior del menú supera la ventana, lo invertimos
        if ((rect.bottom + 20) > window.innerHeight) {
            dropdown.classList.add('drop-up');
        }
    }

    // Escuchar cuando el usuario hace CLIC en el botón
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.actions-menu-toggle');
        if (btn) {
            // Esperamos 10ms a que tu otro evento de clic (arriba en el código) le agregue el 'is-active'
            setTimeout(() => {
                adjustDropdownPosition(btn);
            }, 10);
        }
    });

    // NOTA: Se eliminó intencionalmente el document.addEventListener('mouseenter')
    // para evitar que el navegador recalcule la posición y genere parpadeos.
});