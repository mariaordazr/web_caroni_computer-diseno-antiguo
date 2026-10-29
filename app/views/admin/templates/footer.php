                <?php if ($pageType == 'crud'): ?>
                        <div class="form-actions">
                            <?php $route = preg_replace('/-/', '/', $active_menu, 1); ?>
                            <a href="/admin/<?php echo htmlspecialchars($route); ?>" class="btn">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <?php
        // Revisa si existe un mensaje flash en la sesión
        if (isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])) {
            
            // CONFIGURACIÓN DE NOTIFICACIÓN TIPO "TOAST"
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-start', // A la izquierda
                        showConfirmButton: false,
                        timer: 3000, // Dura 3 segundos
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.onmouseenter = Swal.stopTimer;
                            toast.onmouseleave = Swal.resumeTimer;
                        }
                    });

                    Toast.fire({
                        icon: 'success',
                        title: '" . addslashes($_SESSION['flash_message']) . "'
                    });
                });
            </script>";
            
            unset($_SESSION['flash_message']);
        }
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="/assets/admin/js/admin.js" defer></script>

    
    <?php if (isset($active_menu) && $active_menu === 'pages-home'): ?>
        <script src="/assets/admin/js/admin-home.js" defer></script>
    <?php elseif (isset($active_menu) && in_array($active_menu, ['pages-about', 'pages-services', 'pages-shipping', 'pages-general', 'pages-blog'])): ?>
        <?php if ($active_menu == 'pages-shipping'):?>
            <script>
                window.lastBrandIndex = <?php echo isset($currentIndex) ? $currentIndex : 2; ?>;
            </script>
        <?php elseif ($active_menu == 'pages-services'):?>
            <script>
                window.lastClientIndex = <?php echo isset($currentIndex) ? $currentIndex : 4; ?>;
            </script>
        <?php elseif ($active_menu == 'pages-general'):?>
            <script>
                window.lastPaymentIndex = <?php echo isset($currentIndex) ? $currentIndex : 0; ?>;
            </script>
        <?php endif;?>
        <script src="/assets/admin/js/admin-about-services-shipping.js" defer></script>
    <?php elseif ($pageType == 'crud'): ?>
        <script src="/assets/admin/js/crud.js" defer></script>
    <?php endif; ?>


    <?php if (isset($active_menu) && $active_menu === 'products'): ?>
        <script src="/assets/admin/js/products.js" defer></script>
    <?php elseif (isset($active_menu) && $active_menu === 'categories'): ?>
        <script src="/assets/admin/js/categories.js" defer></script>
    <?php elseif (isset($active_menu) && $active_menu === 'brands'): ?>
        <script src="/assets/admin/js/brands.js" defer></script>
    <?php elseif (isset($active_menu) && $active_menu == 'blogs'):?>
        <script src="/assets/admin/js/blog-posts.js" defer></script>
    <?php elseif (isset($active_menu) && in_array($active_menu, ['receipts', 'receipts-settings'])):?>
        <script src="/assets/admin/js/receipts.js?v=2.0" defer></script>
    <?php elseif (isset($active_menu) && $active_menu === 'users' && $pageType == 'crud'): ?>
        <script src="/assets/admin/js/avatar-cropper.js" defer></script>
    <?php endif; ?>
</body>
</html>
