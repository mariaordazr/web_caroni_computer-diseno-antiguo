<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content">
    <?php $originalFile = $bannerImage ?? 'default_banner.jpg'; ?>
    <section class="hero-section" style="background-image: url('/assets/uploads/blog/banner/<?php echo htmlspecialchars($originalFile); ?>');">
        <div class="container">
            <h1 class="hero-section__title">Nuestro Blog</h1>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2  style="padding: 0rem 2rem 2rem 2rem;">Lee nuestros artículos</h2>
            <div class="blog-modern-grid">
                
                <?php if (!empty($blogs)): ?>
                    <?php foreach ($blogs as $blog): ?>
                        <article class="blog-modern-card">
                            <a href="/blog/<?php echo htmlspecialchars($blog['slug']); ?>">
                            
                                <div class="blog-modern-card__media">
                                    <img src="/assets/uploads/blog/posts/<?php echo htmlspecialchars($blog['cover_image'] ?? 'default_blog.webp'); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" loading="lazy">
                                </div>

                                <div class="blog-modern-card__content">
                                    <h2 class="blog-modern-card__title"><?php echo htmlspecialchars($blog['title']); ?></h2>
                                    
                                    <div class="blog-modern-card__excerpt">
                                        <?php 
                                            // strip_tags quita el HTML del editor para mostrar un resumen limpio
                                            $cleanText = strip_tags($blog['content']); 
                                            echo (mb_strlen($cleanText) > 120) ? mb_substr($cleanText, 0, 120) . '...' : $cleanText;
                                        ?>
                                    </div>
                                    
                                    <div class="blog-modern-card__footer">
                                        <span class="read-more-btn">Leer más <i class='bx bx-right-arrow-alt'></i></span>
                                    </div>
                                </div>
                            </a>

                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: #666; padding: 2rem 0;">No hay artículos publicados en este momento.</p>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>