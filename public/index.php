<?php
// public/index.php

// 1. Activar errores para depuración (puedes quitar esto en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Definir la constante y la conexión
define('ROOT_PATH', dirname(__DIR__) . '/');

// 1. CONFIGURACIÓN DEL TIEMPO DE VIDA (GARBAGE COLLECTOR)
ini_set('session.gc_maxlifetime', 86400 * 30);
ini_set('session.cookie_lifetime', 0);

// 2. AISLAR LAS SESIONES PARA EVITAR QUE EL SERVIDOR LAS BORRE
$sessionPath = __DIR__ . '/sessions_storage';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// 3. INICIO DE LA SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- INICIALIZAR COMPOSER Y VARIABLES DE ENTORNO ---
require_once ROOT_PATH . 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();

require_once ROOT_PATH . 'app/config/db_connection.php';

// 3. Cargar controladores principales
require_once ROOT_PATH . 'app/controller/PublicController.php';
require_once ROOT_PATH . 'app/controller/AuthController.php';
require_once ROOT_PATH . 'app/controller/AdminController.php';
require_once ROOT_PATH . 'app/model/Settings.php';

// 4. Obtener la URI
// ... (código anterior) ...
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// --- DIAGNÓSTICO DE RUTAS (TEMPORAL) ---
if (strpos($_SERVER['REQUEST_URI'], 'api_sincronizar.php') !== false) {
    echo "<h1>DIAGNOSTICO DE RUTA</h1>";
    echo "<b>Carpeta actual del index.php (__DIR__):</b> " . __DIR__ . "<br>";
    echo "<b>Buscando archivo aqui:</b> " . __DIR__ . '/api_sincronizar.php' . "<br>";
    
    if (file_exists(__DIR__ . '/api_sincronizar.php')) {
        echo "<h2 style='color:green'>¡ENCONTRADO AQUÍ!</h2>";
        require_once __DIR__ . '/api_sincronizar.php';
        exit;
    } else {
        echo "<h2 style='color:red'>[X] NO ESTÁ AQUÍ</h2>";
    }

    echo "<hr>";
    echo "<b>Intento 2 (Carpeta superior):</b> " . dirname(__DIR__) . '/api_sincronizar.php' . "<br>";
    
    if (file_exists(dirname(__DIR__) . '/api_sincronizar.php')) {
        echo "<h2 style='color:green'>¡ENCONTRADO ARRIBA!</h2>";
        require_once dirname(__DIR__) . '/api_sincronizar.php';
        exit;
    } else {
         echo "<h2 style='color:red'>[X] TAMPOCO ESTÁ ARRIBA</h2>";
         echo "<p>Por favor, sube el archivo api_sincronizar.php exactamente a la ruta que aparece arriba en negrita.</p>";
         exit; // Detenemos todo para que leas el mensaje y no cargue la web
    }
}
// ----------------------------------------
// ----------------------------------------

// ... (Aquí siguen tus if/elseif normales de auth, admin, etc.) ...
$segments = explode('/', $uri);

// 5. Lógica de enrutamiento
if ($segments[0] === 'auth') {
    // Rutas de autenticación
    $controller = new AuthController($connection);
    $action = $segments[1] ?? 'login_gestion_2025';
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        http_response_code(404);
        echo "Página no encontrada.";
    }

} elseif ($segments[0] === 'admin') {
    // ================== BLOQUE MODIFICADO Y CORREGIDO ==================
    $controller = new AdminController($connection);
    
    $section = $segments[1] ?? 'products';
    $action = $segments[2] ?? 'index'; // 'index' (listar) es la acción por defecto
    $id = $segments[3] ?? null;
    
    // --- MANEJO DE RUTAS AJAX (cuando se envían datos) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($section === 'updateProduct') { 
            $controller->updateProduct(); exit; 
        }
        // ... (aquí van tus otras rutas POST como updateProductStatus, updateProductType, etc.)
        if ($section === 'storeProduct') { 
            $controller->storeProduct(); exit; 
        }

        if ($section === 'uploadAsyncProductVideo') {
            $controller->uploadAsyncProductVideo();
            exit;
        }
        
        // --- NUEVA RUTA PARA ELIMINAR EL VIDEO ---
        if ($section === 'deleteProductVideo') {
            $controller->deleteProductVideo();
            exit;
        }
        // ------------------------------------------
        
        if ($section === 'storeCategory') { 
            $controller->storeCategory(); exit; 
        }
        if ($section === 'storeUser') { 
            $controller->storeUser(); exit; 
        }
        if ($section === 'products' && $action === 'toggle-visibility') {
            $controller->toggleProductVisibility(); exit;
        }
        
        if ($section === 'brands' && $action === 'toggle-visibility') {
            $controller->toggleBrandVisibility(); exit;
        }
        
        if ($section === 'categories' && $action === 'toggle-visibility') {
            $controller->toggleCategoryVisibility(); exit;
        }
        // --- AGREGA ESTO AQUÍ ---
        if ($section === 'settings' && $action === 'update') {
            $controller->updateSettings(); 
            exit;
        }
        if ($section === 'receipts' && $action === 'send-pdf') {
            $controller->sendPdfEmail(); 
            exit;
        }
        if ($section === 'receipts' && $action === 'mark-wa-sent') {
            $controller->markReceiptWhatsappSent();
            exit;
        }
        if ($section === 'receipts' && $action === 'toggle-status') {
            $controller->toggleReceiptStatus();
            exit;
        }
        // --- NUEVA RUTA PARA SUBIDA ASÍNCRONA DE IMÁGENES ---
        if ($section === 'receipts' && $action === 'upload-async') {
            $controller->uploadAsyncImage();
            exit;
        }

        if ($section === 'receipts' && $action === 'sync') {
            $controller->syncDriveImages();
            exit;
        }
            
    }

    // --- RUTA PARA AUTocompletar cliente (Petición GET) ---
    if ($section === 'receipts' && $action === 'check-client') {
        $controller->checkClientAjax();
        exit;
    }
    // RUTAS DE CLIENTES
    if ($section === 'clients') {
        switch ($action) {
            case 'index': $controller->clients(); break;
            case 'create': $controller->createClient(); break;
            case 'store': $controller->storeClient(); break;
            case 'edit': $controller->editClient($id); break;
            case 'update': $controller->updateClient(); break;
            case 'delete': $controller->deleteClient($id); break;
            default: header('Location: /admin/clients'); exit;
        }
        exit;
    }

    if ($section === 'pages' && $action === 'general') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateGeneral(); exit;
        }
        $controller->showGeneral(); exit;
    }
    if ($section === 'pages' && $action === 'home') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateHome(); exit;
        }
        $controller->showHome(); exit;
    }
    if ($section === 'pages' && $action === 'about') {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateAbout(); exit;
        }
        $controller->showAbout(); exit;
    }
    if ($section === 'pages' && $action === 'services') {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateServices(); exit;
        }
        $controller->showServices(); exit;
    }
    if ($section === 'pages' && $action === 'shipping') {
         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateShipping(); exit;
        }
        $controller->showShipping(); exit;
    }
    if ($section === 'pages' && $action === 'blog') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateBlog(); exit;
        }
        $controller->showBlog(); exit;
    }
    
    if ($section === 'uploadSiteImage') { $controller->uploadSiteImage(); exit; }
    if ($section === 'deleteSiteImage') { $controller->deleteSiteImage(); exit; }

    if ($section === 'storeCategory') { $controller->storeCategory(); exit; }
    if ($section === 'storeBrand') { $controller->storeBrand(); exit; }



    if ($section === 'users' && $action === 'edit' && $id) {
        $controller->editUser($id); exit;
    }
    if ($section === 'products' && $action === 'edit' && $id) {
        $controller->editProduct($id); // Llama al nuevo método para mostrar la vista de edición
        exit;
    }

    if ($section === 'categories' && $action === 'create') {
        $controller->createCategory(); exit;
    }
    
    if ($section === 'brands' && $action === 'create') {
        $controller->createBrand(); exit;
    }
    if ($section === 'users' && $action === 'create') {
        $controller->createUser(); exit;
    }
    if ($section === 'offers' && $action === 'create') {
        $controller->createOffer(); exit;
    }
    if ($section === 'products' && $action === 'create') {
        $controller->createProduct(); exit;
    }
    if ($section === 'new-products' && $action === 'create') {
        $controller->createNewProduct(); exit;
    }
    if ($section === 'blogs' && $action === 'create') {
        $controller->createBlog(); exit;
    }
    if ($section === 'blogs' && $action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->storeBlog(); exit; 
    }
    if ($section === 'blogs' && $action === 'edit' && $id) {
        $controller->editBlog($id); exit;
    }
    if ($section === 'blogs' && $action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->updateBlogPost(); exit; 
    }
    if ($section === 'blogs' && $action === 'delete' && $id) {
        $controller->deleteBlog($id); exit;
    }
    // --- RUTAS PARA NOTAS DE RECIBO ---
    if ($section === 'receipts' && $action === 'settings') {
        // Corregido: segments[3] es donde llega la palabra 'update'
        if (isset($segments[3]) && $segments[3] === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateSettingsReceipts(); 
        } else {
            $controller->settingsReceipts(); 
        }
        exit;
    }

    if ($section === 'receipts' && $action === 'create') {
        $controller->createReceipt(); exit;
    }
    if ($section === 'receipts' && $action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->storeReceipt(); exit;
    }
    
    if ($section === 'receipts' && $action === 'edit' && $id) {
        $controller->editReceipt($id); exit;
    }
    if ($section === 'receipts' && $action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->updateReceipt(); exit;
    }

    if ($section === 'receipts' && $action === 'delete' && $id) {
        $controller->deleteReceipt($id); exit;
    }
    if ($section === 'receipts' && $action === 'view' && $id) {
        $controller->viewReceiptPdf($id); exit;
    }


    if ($section === 'categories' && $action === 'productCount' && $id) {
        $controller->getCategoryProductCount($id); exit;
    }
    if ($section === 'brands' && $action === 'productCount' && $id) {
        $controller->getBrandProductCount($id); exit;
    }   

    if ($section === 'categories' && $action === 'edit' && $id) {
        $controller->editCategory($id); // Llama al nuevo método para mostrar la vista de edición
        exit;
    }
    if ($section === 'brands' && $action === 'edit' && $id) {
        $controller->editBrand($id); // Llama al nuevo método para mostrar la vista de edición
        exit;
    }



    if ($section === 'products' && $action === 'delete' && $id) {
        $controller->deleteProduct($id); exit;
    }
    if ($section === 'categories' && $action === 'delete' && $id) {
        $controller->deleteCategory($id); exit;
    }
    if ($section === 'brands' && $action === 'delete' && $id) {
        $controller->deleteBrand($id); exit;
    }

    if ($section === 'offers' && $action === 'delete' && $id) {
        $controller->deleteOffer($id); exit;
    }

    if ($section === 'new-products' && $action === 'delete' && $id) {
        $controller->deleteNewProduct($id); exit;
    }

    if ($section === 'users' && $action === 'delete' && $id) {
        $controller->deleteUser($id); exit;
    }


    // Ruta especial AJAX para actualizar estado: POST /admin/updateProductStatus
    if ($section === 'updateProductStatus') {
        $controller->updateProductStatus();
        exit;
    }
    if ($section === 'updateProductType' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->updateProductType();
        exit;
    }

    if ($section === 'products' && $action === 'toggle-visibility') {
            $controller->toggleProductVisibility(); exit;
        }
    
    

    // Convertimos la ruta de la sección a un método (ej. new-products -> newProducts)
    $method = lcfirst(str_replace('-', '', ucwords($section, '-')));

    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        http_response_code(404);
        echo "Sección de administración no encontrada: " . htmlspecialchars($method);
    }
    // ------------------------------------------------

} elseif ($segments[0] === 'nosotros') {
    // Rutas públicas
    $controller = new PublicController($connection);
    $controller->aboutUs();
} elseif ($segments[0] === 'productos') { // O 'products' si lo prefieres
    $controller = new PublicController($connection);
    $controller->products();

    
} elseif ($segments[0] === 'producto' && isset($segments[1])) {
    $controller = new PublicController($connection);
    $controller->showProduct($segments[1]); // Llama al nuevo método

} elseif ($segments[0] === 'servicios') { // <-- AÑADE ESTA RUTA
    $controller = new PublicController($connection);
    $controller->services();

} elseif ($segments[0] === 'envios') { // <-- AÑADE ESTA RUTA
    $controller = new PublicController($connection);
    $controller->shipping();

} elseif ($segments[0] === 'nota' && isset($segments[1])) {
    $controller = new PublicController($connection);
    $controller->viewPublicReceipt($segments[1]);

} elseif ($segments[0] === 'blog') { 
    $controller = new PublicController($connection);
    
    // Si la URL tiene un slug (ej. /blog/mi-articulo-genial)
    if (isset($segments[1]) && !empty($segments[1])) {
        $controller->showBlogPost($segments[1]);
    } else {
        // Si solo es /blog, muestra la lista normal
        $controller->blog();
    }
    

// } elseif ($segments[0] === 'api_sincronizar.php') {
//     // --- RUTA NUEVA PARA SINCRONIZAR ---
//     // Si la URL pide el sincronizador, cargamos el archivo y detenemos el resto de la web.
//     require_once 'api_sincronizar.php';
//     exit;

} else {
    // Ruta principal (homepage)
    $controller = new PublicController($connection);
    $controller->index();
}

// Cierre de la conexión
if (isset($connection)) {
    $connection->close();
}
?>