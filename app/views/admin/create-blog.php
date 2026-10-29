<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<section class="content-panel">
    <div class="admin-header-row blog-header-row">
        <h3>Redactar Nuevo Artículo</h3>
    </div>

    <form class="edit-form" method="POST" action="/admin/blogs/store" enctype="multipart/form-data" id="blog-form">
        
        <div class="form-grid">
            <div class="form-column">
                <label for="title">Título del Artículo:</label>
                <input type="text" id="title" name="title" placeholder="Ej: Las 5 mejores laptops de 2026" required>

                <label for="author">Autor:</label>
                <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($userInfo['name'] ?? ''); ?>" required>

                <label for="status">Estado de publicación:</label>
                <select id="status" name="status" required>
                    <option value="borrador" selected>Borrador (Oculto al público)</option>
                    <option value="publicado">Publicado (Visible)</option>
                </select>
            </div>

            <div class="form-column">
                <label>Imagen de Portada (Opcional):</label>
                
                <div id="image-upload-container" class="blog-image-box">
                    <label for="cover_image" class="blog-image-label">
                        <div id="image-placeholder" class="blog-image-placeholder">
                            <i class='bx bx-image-add blog-image-icon'></i>
                            <span class="blog-image-text">Clic para subir portada</span>
                        </div>
                    </label>
                    
                    <img id="preview-cover" src="" class="blog-image-preview">
                    
                    <input type="file" name="cover_image" id="cover_image" accept="image/*" class="hidden-file-input" onchange="previewBlogImage(this)">
                </div>
            </div>
        </div>

        <div class="form-column full-width blog-form-col-full">
            <label for="content">Contenido del Artículo:</label>
            <div id="editor-container" class="blog-editor-wrapper"></div>
            <input type="hidden" name="content" id="blog_content">
        </div>
        
</section>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>