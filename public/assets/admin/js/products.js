document.addEventListener('DOMContentLoaded', function() {
    const visibilityButtons = document.querySelectorAll('.btn-visibility-toggle');

    visibilityButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-id');
            const currentHidden = parseInt(this.getAttribute('data-current-hidden'));
            const newStatus = currentHidden === 1 ? 0 : 1; 

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('hidden_status', newStatus);

            fetch('/admin/products/toggle-visibility', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Usamos la función para recargar la página y mostrar el mensaje
                    if (typeof reloadWithScroll === 'function') {
                        reloadWithScroll(data.message);
                    } else {
                        sessionStorage.setItem('pendingToast', data.message);
                        location.reload();
                    }
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Hubo un problema de conexión.', 'error');
            });
        });
    });
});

// --- NUEVA FUNCIÓN: Recargar guardando el scroll y el mensaje ---
function reloadWithScroll(message = '') {
    sessionStorage.setItem('scrollPosition', window.scrollY);
    if (message) {
        sessionStorage.setItem('pendingToast', message);
    }
    location.reload();
}