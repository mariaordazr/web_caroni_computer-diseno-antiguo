document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA DEL ACORDEÓN ---
    
    const accordionItems = document.querySelectorAll('.filter-group.accordion');

    accordionItems.forEach(clickedItem => {
        
        const header = clickedItem.querySelector('.accordion__header');
        
        if (header) { 
            header.addEventListener('click', () => {
                
                // --- 1. COMPORTAMIENTO "PIN" ---
                // Cierra todos los DEMÁS acordeones, excepto los que estén "pineados"
                accordionItems.forEach(otherItem => {
                    if (otherItem !== clickedItem && !otherItem.matches(':has(input[type="radio"]:checked)')) {
                        otherItem.classList.remove('is-open');
                    }
                });
                
                // --- 2. ANULACIÓN MANUAL ---
                // Simplemente abre o cierra el acordeón que SÍ clickeaste,
                // sin importar si estaba "pineado" o no.
                clickedItem.classList.toggle('is-open');
            });
        }
    });

    // --- LÓGICA PARA EL FILTRADO CON RETRASO ---
    const filterForm = document.getElementById('filter-form'); 

    if (filterForm) {
        let filterTimeout;
        
        filterForm.addEventListener('change', (event) => { 
            
            if (event.target.name === 'filter_choice') {
                const hiddenSearchInput = filterForm.querySelector('input[name="search"]');
                if (hiddenSearchInput) {
                    hiddenSearchInput.value = '';
                }
            }

            clearTimeout(filterTimeout);
            
            filterTimeout = setTimeout(() => {
                filterForm.submit();
            }, 500); 
        });
    }
});

// function submitSidebarFilter() {
//     const form = document.getElementById('filter-form');
    
//     // Deshabilitamos los inputs que NO queremos enviar al filtrar por categoría
//     if (form.search) form.search.disabled = true; // Elimina ?search=...
//     if (form.type) form.type.disabled = true;     // Elimina ?type=...
    
//     // El input 'sort' SE MANTIENE porque no lo deshabilitamos
//     // El input 'filter_choice' (el radio que acabas de clicar) SE MANTIENE
    
//     form.submit();
// }

// // El select de ordenamiento solo necesita enviar el formulario tal cual está
// document.getElementById('sort').addEventListener('change', function() {
//     document.getElementById('filter-form').submit();
// });


function submitMobileFilter(selectedValue) {
    if (!selectedValue) return; // Si no hay valor, no hacemos nada

    const form = document.getElementById('filter-form');
    
    // 1. IMPORTANTE: Deshabilitamos los radio buttons del sidebar (Escritorio)
    // para que no entren en conflicto con la selección móvil.
    const desktopRadios = form.querySelectorAll('input[type="radio"][name="filter_choice"]');
    desktopRadios.forEach(radio => radio.disabled = true);
    
    // 2. Verificamos si ya existe un input hidden para el filtro final
    let hiddenInput = document.getElementById('final_mobile_filter');
    
    if (!hiddenInput) {
        // Si no existe, lo creamos dinámicamente
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'filter_choice'; // Este es el nombre que espera PHP
        hiddenInput.id = 'final_mobile_filter';
        form.appendChild(hiddenInput);
    }
    
    // 3. Asignamos el valor seleccionado (ej: "cat-1-brand-5")
    hiddenInput.value = selectedValue;

    // 4. Enviamos el formulario
    form.submit();
}