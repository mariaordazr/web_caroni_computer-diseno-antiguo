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
    <meta name="description" content="Venta de repuestos, accesorios y servicio técnico y electrónico especializado para equipos digitales: monitores, tv, tablets, celulares, computadoras, portátiles y mucho más">
    <meta name="keywords" content="computadoras, laptops, Perú, Venezuela, servicio técnico, hardware">
    <meta name="author" content="<?php echo htmlspecialchars($pageName);?>">
    
    <link rel="icon" type="image/x-icon" href="/assets/uploads/logo/gruponet.webp">
    
    <title><?php echo htmlspecialchars($pageName);?><?php echo isset($pageTitle) ? ' | ' . $pageTitle : ''; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Raleway:wght@100..900&display=swap" rel="stylesheet">
    <link rel='stylesheet' href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css'>
    
    <link rel="stylesheet" href="/assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css?v=<2.1">
</head>
<body style="
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    ">

<header class="main-header" id="header">
    <div class="header-container">
        <div class="header-top">

        </div>
    </div>
</header>

<main class="login-page-background">
    <div class="login-container">
        <a href="/" class="login-logo">
            <img src="/assets/uploads/logo/gruponet.webp" alt="Logo Gruponet">
        </a>
        <div class="login-card">
            <h1>¡Hola! Ingresa a tu cuenta</h1>
            <p class="login-subtitle">Ingresa tu usuario y contraseña.</p>

            <form action="/auth/login_gestion_2025" method="POST">
                
                <?php if (!empty($alert)): ?>
                    <div class="alert-error"><?php echo $alert; ?></div>
                <?php endif; ?>

                <div class="input-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>

                <div class="input-group">
                    <label for="clave">Contraseña</label>
                    <input type="password" id="clave" name="clave" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                    <input type="checkbox" name="remember_me" id="remember_me" value="1" style="width: auto; margin: 0; cursor: pointer;">
                    <label for="remember_me" style="margin: 0; cursor: pointer; font-size: 14px; color: #555;">Mantener sesión activa</label>
                </div>

                <button type="submit" class="btn-submit">Iniciar sesión</button>
            </form>
        </div>
    </div>

</main>

<footer class="main-footer-login">
    <div class="footer-copyright">
        <div class="header-container">
            <span>Copyright ©<?php echo date('Y'); ?> <?php echo htmlspecialchars($copyrightText);?><C class="A"></C>. Todos los derechos reservados.</span>
        </div>
    </div>
</footer>

</body>
</html>