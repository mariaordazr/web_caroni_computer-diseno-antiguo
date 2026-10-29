<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content blog-detail-main">
    <div class="container blog-detail-container">
        
        <a href="/blog" class="btn btn-outline blog-detail-back-btn">
            <i class='bx bx-arrow-back'></i> Volver a todos los artículos
        </a>

        <article class="blog-detail-article">
            
            <header class="blog-detail-header">
                <h1 class="blog-detail-title">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>
                <p class="blog-detail-meta">
                    Redactado por <strong class="blog-detail-author"><?php echo htmlspecialchars($post['author']); ?></strong> | 
                    Publicado el <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                </p>
            </header>

            <?php if (!empty($post['cover_image']) && $post['cover_image'] !== 'default_blog.webp' && $post['cover_image'] !== 'default.jpg'): ?>
                <div class="blog-detail-image-wrapper">
                    <img src="/assets/uploads/blog/posts/<?php echo htmlspecialchars($post['cover_image']); ?>" alt="Portada del artículo" class="blog-detail-image">
                </div>
            <?php endif; ?>

            <div class="blog-post-content">
                <?php echo $post['content']; ?>
            </div>
            
        </article>
    </div>
</main>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>  