<?php include ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<section class="content-panel">
    <div class="admin-header-row blog-header-row">
        <h3>Editar Artículo</h3>
    </div>

    <form class="edit-form" method="POST" action="/admin/blogs/update" enctype="multipart/form-data" id="blog-form">
        <input type="hidden" name="id_blog" value="<?php echo $blog['id_blog']; ?>">
        
        <div class="form-grid">
            <div class="form-column">
                <label>Título del Artículo</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                
                <label>Autor</label>
                <input type="text" name="author" value="<?php echo htmlspecialchars($blog['author']); ?>" required>
                
                <label>Estado de publicación</label>
                <select name="status" required>
                    <option value="borrador" <?php echo $blog['status'] === 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                    <option value="publicado" <?php echo $blog['status'] === 'publicado' ? 'selected' : ''; ?>>Publicado</option>
                </select>
            </div>
            
            <div class="form-column">
                <label>Imagen de Portada (Sube una nueva para reemplazar)</label>
                <div id="image-upload-container" class="blog-image-box has-preview">
                    <label for="cover_image" class="blog-image-label"></label>
                    <img id="preview-cover" src="/assets/uploads/blog/posts/<?php echo htmlspecialchars($blog['cover_image']); ?>" class="blog-image-preview">
                    <input type="file" name="cover_image" id="cover_image" accept="image/*" class="hidden-file-input" onchange="previewBlogImage(this)">
                </div>
            </div>
            
            <div class="form-column full-width blog-form-col-full">
                <label>Contenido del Artículo</label>
                <div id="editor-container" class="blog-editor-wrapper"><?php echo $blog['content']; ?></div>
                <input type="hidden" name="content" id="blog_content">
            </div>
        </div>

</section>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>