<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Venta de repuestos, accesorios y servicio técnico y electrónico especializado para equipos digitales: monitores, tv, tablets, celulares, computadoras, portátiles y mucho más">
    <meta name="keywords" content="computadoras, laptops, Perú, Venezuela, servicio técnico, hardware">
    <meta name="author" content="<?php echo htmlspecialchars($pageName);?>">
    
    <link rel="icon" type="image/x-icon" href="/assets/uploads/logo/gruponet.webp">
    
    <title><?php echo htmlspecialchars($pageName);?><?php echo isset($pageTitle) ? ' | ' . $pageTitle : ''; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Raleway:wght@100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"></noscript>
    
    <link rel="stylesheet" href="/assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css?v=<2.1">

    <?php if (isset($sliderImages) && !empty($sliderImages)): 
        // Lógica para obtener el nombre de la versión pequeña/webp igual que en home.php
        $firstImg = $sliderImages[0];
        $nameWithoutExt = pathinfo($firstImg, PATHINFO_FILENAME);
        $preloadImg = 'small_' . $nameWithoutExt . '.webp';
    ?>
        <link rel="preload" as="image" href="/assets/uploads/home/slider/<?php echo rawurlencode($preloadImg); ?>" media="(max-width: 768px)">
        <link rel="preload" as="image" href="/assets/uploads/home/slider/<?php echo rawurlencode($firstImg); ?>" media="(min-width: 769px)">
    <?php endif; ?>
</head>

</head>
<body>

<header class="main-header" id="header">
    <div class="header-container">
        <div class="header-top">
            <?php $first = true; ?>
            <a href="/" class="header-logo">
                <img src="/assets/uploads/logo/<?php echo htmlspecialchars($logoImage); ?>"
                alt="Grupo Caroni Computer"
                width="250" height="78"
                loading="<?php echo $first ? 'eager' : 'lazy'; ?>"
                <?php if($first) echo 'fetchpriority="high"'; ?>
                >
                <?php $first = false; ?>
            </a>

            <form class="header-search" method="GET" action="/productos">
                <input type="text" class="header-search__input" name="search" placeholder="Buscar productos, marcas y más..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="header-search__button">
                    <i class='bx bx-search'></i>
                </button>
            </form>
            
            <div class="header-mobile-toggle" id="mobile-menu-button">
                <i class='bx bx-menu'></i>
            </div>
        </div>

        <div class="header-bottom" id="nav-menu">
            <nav class="header-nav">
                <ul class="nav-list">
                    <li class="nav-item nav-item--location">
                        <a href=<?php echo htmlspecialchars($locationLink); ?> target="_blank" style="text-align: right;">
                            <i class='bx bx-map'></i>
                            <span>Estamos en:<br><b><?php echo htmlspecialchars($locationHeader); ?></b></span>
                        </a>
                    </li>
                    <li class="nav-item"><a href="/" class="nav-link">Inicio</a></li>
                    <li class="nav-item nav-item--dropdown">
                        <a href="#" class="nav-link" data-dropdown-toggle="categories-dropdown">Categorías <i class='bx bx-chevron-down'></i></a>
                        <div id="categories-dropdown" class="dropdown-menu">
                            <ul>
                                <?php if (!empty($headerCategories)): ?>
                                    <?php foreach ($headerCategories as $category): ?>
                                        <li><a href="/productos?search=&filter_choice=cat-<?php echo $category['id_category']; ?>&sort=name-asc"><?php echo htmlspecialchars(ucwords(strtolower($category['name']))); ?></a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item"><a href="/productos?sort=name-asc" class="nav-link">Productos</a></li>
                    <li class="nav-item"><a href="/productos?type=new&sort=name-asc" class="nav-link">Recién llegados</a></li>
                    <li class="nav-item"><a href="/productos?type=offer&sort=name-asc" class="nav-link">Ofertas</a></li>
            </nav>

            <nav class="header-user-nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="/blog" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="/nosotros" class="nav-link">Nosotros</a></li>
                    <li class="nav-item"><a href="/servicios" class="nav-link">Servicios</a></li>
                    <li class="nav-item"><a href="/envios" class="nav-link">Envíos</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

<main class="main-content">