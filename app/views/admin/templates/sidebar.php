<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Llamada al Manifest para la PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#343a40">
    <link rel="apple-touch-icon" href="/assets/icon-192x192.png">

    <!-- Registro del Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => console.log('SW registrado en login'))
            .catch(error => console.log('Error SW:', error));
        });
    }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    
    <link rel="icon" type="image/x-icon" href="/assets/uploads/logo/gruponet.webp">
    
    <title><?php echo htmlspecialchars($pageName);?><?php echo isset($pageTitle) ? ' | ' . $pageTitle : ''; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Raleway:wght@100..900&display=swap" rel="stylesheet">
    <link rel='stylesheet' href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
    <link rel="stylesheet" href="/assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css"/>

    <link rel="stylesheet" href="/assets/css/styles.css?v=preview"> 
    <link rel="stylesheet" href="/assets/admin/css/admin.css?v=<2.8">

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // 1. Evitar que el navegador restaure el scroll automáticamente (para evitar conflictos)
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        // 2. Restaurar la posición INMEDIATAMENTE, antes de pintar el body
        const savedScroll = sessionStorage.getItem('scrollPosition');
        if (savedScroll) {
            window.scrollTo(0, parseInt(savedScroll));
            // No borramos el item aquí, lo borramos en el admin.js después de asegurar la carga
        }
    </script>
    <style>
    /* Fuerza al plugin a usar el 100% del ancho de tu columna */
    .iti { width: 100%; margin-bottom: 1rem; } 
</style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar__header">
                <div>
                    <a href="/" class="sidebar__logo">
                        <img src="/assets/uploads/logo/gruponet.webp" alt="Logo Gruponet">
                    </a>
                </div>

                <div class="nav-item__profile">
                    <a href="/admin/users/edit/<?php echo $_SESSION['idUser']; ?>" class="header-admin__profile"> 
                        <img src="/assets/uploads/users/<?php echo htmlspecialchars($userInfo['avatar'] ?? 'default.jpg'); ?>" alt="Avatar Admin">
                        <div style="margin:0; display:flex; flex-direction:column; line-height:1.2;">
                            <span style="font-size: 0.8rem;"><?php echo htmlspecialchars($userInfo['name'])?></span> 
                            <span style="font-size: 0.7rem; color: var(--secondary-text-color);font-weight: 400;"><?php echo htmlspecialchars($userInfo['email'])?> </span>
                        </div>
                    </a>
                </div>
                
                <button class="sidebar__close-mobile" id="sidebar-close-mobile">
                    <i class='bx bx-x'></i>
                </button>
            </div>
            
            <nav class="sidebar__nav" style="padding-top:0;">
                <ul>
                    <?php if ($userInfo['role'] == 'admin' || $userInfo['role'] == 'vendedor'): ?>
                        <li class="nav-header">ADMINISTRACIÓN</li>
                        <li class="nav-item <?php echo (isset($active_menu) && $active_menu == 'products') ? 'active' : ''; ?>">
                            <a href="/admin/products">
                                <i class='bx bx-shopping-bag'></i>
                                <span>Productos</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (isset($active_menu) && $active_menu == 'offers') ? 'active' : ''; ?>">
                            <a href="/admin/offers">
                                <i class='bx bx-gift'></i>
                                <span>Productos en Oferta</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (isset($active_menu) && $active_menu == 'new-products') ? 'active' : ''; ?>">
                            <a href="/admin/new-products">
                                <i class='bx bx-star'></i>
                                <span>Recién Llegados</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (isset($active_menu) && $active_menu == 'categories') ? 'active' : ''; ?>">
                            <a href="/admin/categories">
                                <i class='bx bx-category'></i>
                                <span>Categorías</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (isset($active_menu) && $active_menu == 'brands') ? 'active' : ''; ?>">
                            <a href="/admin/brands">
                                <i class='bx bx-buildings'></i>
                                <span>Marcas</span>
                            </a>
                        </li>
                        <li class="nav-item <?php echo (isset($active_menu) && $active_menu == 'users') ? 'active' : ''; ?>">
                            <a href="/admin/users">
                                <i class='bx bx-user'></i>
                                <span>Usuarios</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-header">FACTURACIÓN</li>
                    <li class="nav-item <?php echo ($active_menu === 'receipts') ? 'active' : ''; ?>">
                        <a href="/admin/receipts">
                            <i class='bx bx-receipt'></i>
                            <span>Notas de Recibo</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($active_menu === 'receipts-settings') ? 'active' : ''; ?>">
                        <a href="/admin/receipts/settings">
                            <i class='bx bx-building-house'></i>
                            <span>Conf. de Notas</span>
                        </a>
                    </li>
                    <li class="nav-item <?php echo ($active_menu === 'clients') ? 'active' : ''; ?>">
                            <a href="/admin/clients">
                                <i class='bx bx-group'></i>
                                <span>Clientes</span>
                            </a>
                        </li>
                    
                    <?php if ($userInfo['role'] == 'admin'): ?>
                        <li class="nav-header">GESTIÓN DE PÁGINAS</li>

                        <li class="nav-item <?php echo ($active_menu === 'pages-general') ? 'active' : ''; ?>">
                            <a href="/admin/pages/general">
                                <i class='bx bx-slider-alt'></i> <span>General</span>
                            </a>
                        </li>

                        <li class="nav-item <?php echo ($active_menu === 'pages-home') ? 'active' : ''; ?>">
                            <a href="/admin/pages/home">
                                <i class='bx bx-home-alt'></i>
                                <span>Página de Inicio</span>
                            </a>
                        </li>

                        <li class="nav-item <?php echo ($active_menu === 'pages-blog') ? 'active' : ''; ?>">
                            <a href="/admin/pages/blog">
                                <i class='bx bx-news'></i>
                                <span>Página de Blog</span>
                            </a>
                        </li>

                        <li class="nav-item <?php echo ($active_menu === 'pages-about') ? 'active' : ''; ?>">
                            <a href="/admin/pages/about">
                                <i class='bx bx-buildings'></i>
                                <span>Página de Nosotros</span>
                            </a>
                        </li>

                        <li class="nav-item <?php echo ($active_menu === 'pages-services') ? 'active' : ''; ?>">
                            <a href="/admin/pages/services">
                                <i class='bx bx-wrench'></i>
                                <span>Página de Servicios</span>
                            </a>
                        </li>

                        <li class="nav-item <?php echo ($active_menu === 'pages-shipping') ? 'active' : ''; ?>">
                            <a href="/admin/pages/shipping">
                                <i class='bx bxs-truck'></i>
                                <span>Página de Envíos</span>
                            </a>
                        </li>

                        <li class="nav-header">BLOGS</li>
                        <li class="nav-item <?php echo ($active_menu === 'blogs') ? 'active' : ''; ?>">
                            <a href="/admin/blogs">
                                <i class='bx bx-news'></i>
                                <span>Blog Post</span>
                            </a>
                        </li>
                        <li class="nav-header">CONFIGURACIÓN</li>

                        <li class="nav-item <?php echo ($active_menu === 'settings') ? 'active' : ''; ?>">
                            <a href="/admin/settings">
                                <i class='bx bx-slider-alt'></i> <span>Conf. Avanzada</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="sidebar__footer">
                <a href="/auth/logout" class="logout-btn">
                    <i class='bx bx-log-out'></i>
                    <span>Cerrar sesión</span>
                </a>
            </div>
        </aside>

        <main class="main-content">
            
