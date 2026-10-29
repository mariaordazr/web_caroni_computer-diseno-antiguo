// function toggleEditPost(btn) {
//     const row = btn.closest('.blog-item-row');
//     const body = row.querySelector('.blog-item-body');
    
//     if (body.style.display === 'none') {
//         body.style.display = 'block';
//         btn.innerHTML = "<i class='bx bx-chevron-up'></i> Ocultar";
//     } else {
//         body.style.display = 'none';
//         btn.innerHTML = "<i class='bx bx-edit'></i> Editar";
//     }
// }

//     // JS para manejar la visibilidad multimedia (existente)
// function toggleMediaType(radio, index) {
//     document.getElementById('media-image-' + index).style.display = 'none';
//     document.getElementById('media-video-' + index).style.display = 'none';
//     document.getElementById('media-link-'  + index).style.display = 'none';

//     if (radio.value === 'image') document.getElementById('media-image-' + index).style.display = 'block';
//     if (radio.value === 'video') document.getElementById('media-video-' + index).style.display = 'block';
//     if (radio.value === 'link')  document.getElementById('media-link-'  + index).style.display = 'block';
// }