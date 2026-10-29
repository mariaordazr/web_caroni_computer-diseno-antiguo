<?php

require_once ROOT_PATH . 'app/config/db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminController {
    private $db;
    private $categoryModel;
    private $brandModel;
    private $productModel;
    private $userModel;
    private $offerModel;
    private $newProductModel;
    private $siteContentModel;
    private $settingsModel;
    private $uploadDir;
    private $blogModel;
    private $receiptModel;
    private $clientModel; // <-- Agregar esto

    public function __construct($connection) {
        require_once ROOT_PATH . 'app/model/Client.php';
        require_once ROOT_PATH . 'app/model/Product.php';
        require_once ROOT_PATH . 'app/model/Category.php';
        require_once ROOT_PATH . 'app/model/Brand.php';
        require_once ROOT_PATH . 'app/model/User.php';
        require_once ROOT_PATH . 'app/model/Offer.php';
        require_once ROOT_PATH . 'app/model/NewProduct.php';
        require_once ROOT_PATH . 'app/model/SiteContent.php';
        require_once ROOT_PATH . 'app/model/Settings.php';
        require_once ROOT_PATH . 'app/model/Blog.php';
        require_once ROOT_PATH . 'app/model/Receipt.php';

        $this->db = $connection;
        $this->productModel = new Product($this->db);
        $this->categoryModel = new Category($this->db);
        $this->brandModel = new Brand($this->db);
        $this->userModel = new User($this->db);
        $this->offerModel = new Offer($this->db);
        $this->newProductModel = new NewProduct($this->db);
        $this->siteContentModel = new SiteContent($this->db);
        $this->settingsModel = new Settings($this->db);
        $this->blogModel = new Blog($this->db);
        $this->receiptModel = new Receipt($this->db);
        $this->clientModel = new Client($this->db);     // <-- Agregar esto
        $this->uploadDir = ROOT_PATH . 'public/assets/uploads/';

        // --- AÑADIR ESTO: Asegurarse de que existan las carpetas ---
        $this->ensureDirExists($this->uploadDir . 'home/slider');
        $this->ensureDirExists($this->uploadDir . 'home/banner');
        $this->ensureDirExists($this->uploadDir . 'about-us/banner');
        $this->ensureDirExists($this->uploadDir . 'about-us/body');
        $this->ensureDirExists($this->uploadDir . 'services/banner');
        $this->ensureDirExists($this->uploadDir . 'services/body');
        $this->ensureDirExists($this->uploadDir . 'shipping/banner');
        $this->ensureDirExists($this->uploadDir . 'shipping/body');
        $this->ensureDirExists($this->uploadDir . 'logo');     // Para logo_header
        $this->ensureDirExists($this->uploadDir . 'payments'); // Para payment_logo
        $this->ensureDirExists($this->uploadDir . 'receipts');
        // --- AGREGAR ESTAS LÍNEAS EN TU CONSTRUCTOR EXISTENTE ---
        $this->ensureDirExists($this->uploadDir . 'blog/banner');
        $this->ensureDirExists($this->uploadDir . 'blog/posts');
        $this->ensureDirExists($this->uploadDir . 'blog/videos');

        // Iniciar la sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // Verificar si el usuario está logueado, si no, redirigir al login
        if (empty($_SESSION['active'])) {
            header('location: /');
            exit;
        }

        if (!isset($_SESSION['is_super_admin'])) {
            $userInfo = $this->userModel->getById($_SESSION['idUser']);
            $_SESSION['is_super_admin'] = $userInfo['is_super_admin'] ?? 0;
        } 
        
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    
            // Si NO activó "Mantener sesión activa", vigilamos la inactividad
            if (!isset($_SESSION['remember_me']) || $_SESSION['remember_me'] == false) {
                $maxInactivity = 21600; // 6 horas en segundos
                
                // Calculamos si pasó el límite de inactividad
                if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxInactivity)) {
                    // Expiró: Limpiamos y redirigimos
                    session_unset();
                    session_destroy();
                    header("Location: /login?error=timeout");
                    exit();
                }
            }
            
            // Si todo está bien, actualizamos su última actividad a ESTE momento
            $_SESSION['last_activity'] = time();
            
            // Refrescamos la cookie para que el contador de vida se reinicie con cada clic
            $cookieLifetime = (isset($_SESSION['remember_me']) && $_SESSION['remember_me']) 
                            ? time() + (86400 * 30) 
                            : time() + 21600;
                            // Reemplazar: setcookie(session_name(), session_id(), $cookieLifetime, "/");

            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), $cookieLifetime, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
    }

    private function resizeToWebP($sourcePath, $destPath, $maxWidth = 400, $quality = 80) {
        // 1. Obtener dimensiones y tipo de la imagen original
        list($width, $height, $type) = getimagesize($sourcePath);

        // 2. Calcular nuevas dimensiones manteniendo la relación de aspecto (aspect ratio)
        $ratio = $width / $height;
        $newWidth = $maxWidth;
        $newHeight = $newWidth / $ratio;

        // 3. Cargar la imagen según su tipo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                return false; // Tipo no soportado
        }

        // 4. Crear un lienzo vacío con las nuevas dimensiones
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // 5. Manejar la transparencia (Crucial para PNGs y WebPs)
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // 6. Copiar y redimensionar
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // 7. Guardar como WebP en la ruta de destino
        // Nota: Asegúrate de que el nombre de destino termine en .webp
        imagewebp($newImage, $destPath, $quality);

        // 8. Liberar memoria
        imagedestroy($image);
        imagedestroy($newImage);

        return true;
    }


    /**
     * Función de ayuda para crear directorios si no existen.
     */
    private function ensureDirExists($path) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true); // true para creación recursiva
        }
    }

    /**
     * Obtiene de forma dinámica enlaces de la tabla google_drive (ej. 'api_receipts' o 'folder_link')
     */
    private function getDriveConfig($key = 'api_receipts') {
        $stmt = $this->db->prepare("SELECT value FROM google_drive WHERE name = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['value'] ?? '';
    }

    /**
     * Función de ayuda para guardar un par clave-valor en la BD.
     */
    private function updateContent($key, $value) {
        $this->siteContentModel->save($key, $value);
    }

    protected function handleFileUpload(array $file, string $path, string $prefix): ?string
    {
        // Validación básica
        if (!isset($file['name']) || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // 1. Asegurar la existencia del directorio
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // 2. Generar nombre único para la ORIGINAL
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION); 
        $baseName = $prefix . '_' . time() . '_' . uniqid(); // Nombre sin extensión
        $fileName = $baseName . '.' . $extension;
        $targetFile = $path . $fileName;

        // 3. Mover el archivo subido
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            
            // --- NUEVO: Generar versión Small WebP automáticamente ---
            $smallFileName = 'small_' . $baseName . '.webp';
            $targetSmall = $path . $smallFileName;
            
            // Usamos tu función existente resizeToWebP (400px es ideal para móviles)
            $this->resizeToWebP($targetFile, $targetSmall, 400);
            // ---------------------------------------------------------

            return $fileName; // Retornamos el nombre original para la BD
        } else {
            return null;
        }
    }

    private function handleGalleryUpload($fileInputName, $destinationFolder, $dbKeyToUpdate) {
        if (isset($_FILES[$fileInputName]) && !empty($_FILES[$fileInputName]['name'][0])) {
            
            // 1. Obtener la lista actual de imágenes de la BD
            $jsonImages = $this->siteContentModel->getValue($dbKeyToUpdate);
            $imageList = $jsonImages ? json_decode($jsonImages, true) : [];

            $files = $_FILES[$fileInputName];
            
            // 2. Iterar sobre cada archivo subido
            foreach ($files['tmp_name'] as $key => $tmp_name) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $safeName = preg_replace("/[^a-zA-Z0-9._-]/", "", basename($files['name'][$key]));
                    $fileName = uniqid() . '-' . $safeName;
                    $destinationPath = $this->uploadDir . $destinationFolder . '/' . $fileName;

                    if (move_uploaded_file($tmp_name, $destinationPath)) {
                        // 3. Añadir el nuevo nombre de archivo a la lista
                        $imageList[] = $fileName;
                    }
                }
            }

            // 4. Guardar la lista actualizada (como JSON) en la BD
            $this->updateContent($dbKeyToUpdate, json_encode($imageList));
        }
    }

    //-----------------------PRODUCTOS------------------------//

    public function products() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'full_list';
        $active_menu = 'products';
        $pageTitle = 'Gestión de Productos';
        $buttonAgg = 'Producto';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');
        
        // Recogemos todos los filtros de la URL en un solo array
        $filters = [
            'search'   => $_GET['search'] ?? null,
            'sort'     => $_GET['sort'] ?? null,
            'category' => $_GET['category'] ?? null,
            'brand'    => $_GET['brand'] ?? null,
            'type'     => $_GET['type'] ?? null,
            'status'   => $_GET['status'] ?? null,
        ];

        // Cargar modelos
        $categoryModel = new Category($this->db);
        $brandModel = new Brand($this->db);

        // Obtener datos, pasando el array de filtros
        $products = $this->productModel->getAll($filters);
        $categories = $categoryModel->getAll(['sort' => 'name-asc']);
        $headerCategories = $categoryModel->getAll(['sort' => 'name-asc']);
        $brands = $brandModel->getAll(['sort' => 'name-asc']);
        $headerBrands = $brandModel->getAll(['sort' => 'name-asc']);

        include ROOT_PATH . 'app/views/admin/products.php';
    }

    public function updateProductStatus() {
        header('Content-Type: application/json'); // Indicamos que la respuesta será JSON

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int) $_POST['product_id'] ?? 0;
            $stock = (int) $_POST['stock_value'] ?? 0;

            if ($productId > 0) {
                $success = $this->productModel->updateStock($productId, $stock);

                // Si el producto se acaba de marcar como agotado (stock = 0)
                if ($stock === 0) {
                    // Asegúrate de que los modelos estén disponibles (puedes mover estos require al inicio del archivo)
                    require_once ROOT_PATH . 'app/model/Offer.php';
                    require_once ROOT_PATH . 'app/model/NewProduct.php';

                    $offerModel = new Offer($this->db);
                    $newProductModel = new NewProduct($this->db);

                    // Elimínalo de las tablas de ofertas y nuevos productos
                    $offerModel->deleteByProductId($productId);
                    $newProductModel->delete($productId);
                }


                if ($success) {
                    echo json_encode(['success' => true, 'new_status' => ($stock > 0) ? 'Disponible' : 'Agotado']);
                    exit;
                }
            }
        }

        echo json_encode(['success' => false, 'message' => 'Error en la petición.']);
        exit;
    }

    public function updateProductType() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $type = $_POST['type'] ?? 'none';
        $price = (float) ($_POST['price'] ?? 0);

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de producto no válido.']);
            exit;
        }

        // Cargar modelos necesarios
        $offerModel = new Offer($this->db);
        $newProductModel = new NewProduct($this->db);

        // Lógica para actualizar
        $offerModel->deleteByProductId($productId);
        $newProductModel->delete($productId);

        if ($type === 'offer') {
            if ($price > 0) {
                $offerModel->create(['message' => 'OFERTA ESPECIAL', 'price' => $price, 'product' => $productId]);
            }
        } elseif ($type === 'new') {
            $newProductModel->create($productId);
        }
        
        echo json_encode(['success' => true]);
        exit;
    }

    // Puedes ponerlo después de updateProductStatus()

    public function toggleProductVisibility() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = (int) ($_POST['product_id'] ?? 0);
            $newStatus = isset($_POST['hidden_status']) ? (int)$_POST['hidden_status'] : 0;

            if ($productId > 0) {
                // Si el usuario quiere MOSTRAR el producto ($newStatus == 0)
                if ($newStatus === 0) {
                    // Verificamos el estado de la categoría y la marca
                    $productInfo = $this->productModel->getById($productId);
                    
                    if ($productInfo) {
                        // Si la categoría está oculta
                        if ($productInfo['category_hidden'] == 1) {
                            echo json_encode([
                                'success' => false, 
                                'message' => "No se puede mostrar el producto porque pertenece a la categoría '{$productInfo['category_name']}', la cual está oculta."
                            ]);
                            exit;
                        }

                        // Si la marca está oculta
                        if ($productInfo['brand_hidden'] == 1) {
                            echo json_encode([
                                'success' => false, 
                                'message' => "No se puede mostrar el producto porque pertenece a la marca '{$productInfo['brand_name']}', la cual está oculta."
                            ]);
                            exit;
                        }
                    }
                }

                // Si todo está bien, o si la acción es OCULTAR ($newStatus == 1), procedemos:
                $success = $this->productModel->updateHiddenStatus($productId, $newStatus);

                if ($success) {
                    $_SESSION['flash_message'] = $newStatus ? 'Producto ocultado con éxito' : 'Producto visible con éxito';
                    
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
        }

        echo json_encode(['success' => false, 'message' => 'Error al actualizar visibilidad del producto.']);
        exit; 
    }

    public function createProduct() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $active_menu = 'products';
        $pageTitle = 'Agregar Nuevo Producto';
        $footerJS = '/assets/admin/js/crud.js';
        $pageType = 'crud';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');
        $webhookUrl = $this->getDriveConfig('api_video_products'); // <-- NUEVO: Obtener el enlace de la API de videos

        // Obtenemos las categorías y marcas para los menús desplegables
        $categories = $this->categoryModel->getAllSimple();
        $brands = $this->brandModel->getAllSimple();

        // Cargamos la nueva vista que vamos a crear
        include ROOT_PATH . 'app/views/admin/create-product.php';
    }

    public function storeProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
            // --- 1. LÓGICA DE GARANTÍA ---
            $warranty = ''; // Por defecto vacío
            
            // Verificamos si el usuario seleccionó "Sí" tiene garantía
            if (isset($_POST['has_warranty']) && $_POST['has_warranty'] === 'yes') {
                $length = $_POST['warranty_length'] ?? '';
                $unit   = $_POST['warranty_unit'] ?? '';
                
                // Mapeo para traducir al español en la BD (opcional, pero recomendado)
                $unitsMap = [
                    'days'   => 'días',
                    'months' => 'meses',
                    'years'  => 'años'
                ];
                
                $translatedUnit = $unitsMap[$unit] ?? $unit;

                if (!empty($length) && !empty($unit)) {
                    // Concatenamos: "2" + " " + "años" = "2 años"
                    $warranty = $length . ' ' . $translatedUnit; 
                }
            }

            // --- 2. LÓGICA DE CONDICIÓN ---
            // Mapeamos los values del select (new, used...) a lo que quieres en la BD (Nuevo, Usado...)
            $conditionMap = [
                'new'         => 'Nuevo',
                'used'        => 'Usado',
                'refurbished' => 'Reacondicionado'
            ];
            $rawCondition = $_POST['condition'] ?? 'new';
            $conditionFinal = $conditionMap[$rawCondition] ?? 'Nuevo';

            // --- 3. PREPARAR ARRAY DATA ---
            $data = [
                'sku'             => $_POST['sku'] ?? '0',
                'name'            => $_POST['name'] ?? '',
                'description'     => $_POST['description'] ?? '',
                'ext_description' => $_POST['ext_description'] ?? '',
                'video_title'       => $_POST['video_title'] ?? '',          // <--- NUEVO
                'video_description' => $_POST['video_description'] ?? '',    // <--- NUEVO
                'video_url'         => $_POST['video_url'] ?? '',            // <--- NUEVO
                'price'             => (float) ($_POST['price'] ?? 0.0),
                'stock'             => (int) ($_POST['stock'] ?? 0),
                'category'          => (int) ($_POST['category'] ?? 0),
                'brand'             => (int) ($_POST['brand'] ?? 0),
                'warranty'          => $warranty,
                'condition'         => $conditionFinal,
                'certification'     => $_POST['certification'] ?? 'standard', // <-- NUEVO
            ];
            
            // 1. Crear primero el producto en la BD para obtener su ID
            $id = $this->productModel->create($data);

            if ($id) {
                $targetDir = ROOT_PATH . "public/assets/uploads/products/";
                
                // Mapa para rastrear el nombre original con el nombre físico guardado en el servidor
                $newUploadedFilesMap = []; 

                // 1. SUBIR TODOS LOS ARCHIVOS
                if (!empty($_FILES['product_images']['name'][0])) {
                    $totalFiles = count($_FILES['product_images']['name']);

                    for ($i = 0; $i < $totalFiles; $i++) {
                        if ($_FILES['product_images']['error'][$i] === UPLOAD_ERR_OK) {
                            $originalName = $_FILES['product_images']['name'][$i];
                            $tmpName = $_FILES['product_images']['tmp_name'][$i];
                            
                            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                            $newFileName = time() . '_' . uniqid() . '.' . $ext;
                            $targetFile = $targetDir . $newFileName;

                            if (move_uploaded_file($tmpName, $targetFile)) {
                                // Guardamos por defecto como secundaria (isCover = false)
                                $this->productModel->addImage($id, $newFileName, false);
                                // Alimentamos el mapa para saber cómo quedó guardada
                                $newUploadedFilesMap[$originalName] = $newFileName;
                            }
                        }
                    }
                }

                // 2. APLICAR ORDENAMIENTO (PORTADA Y DRAG & DROP)
                $coverReference = $_POST['cover_reference'] ?? '';
                $imageOrderRaw = $_POST['image_order'] ?? '[]';
                $imageOrderArray = json_decode($imageOrderRaw, true);

                if (is_array($imageOrderArray) && !empty($imageOrderArray)) {
                    // Extraemos la portada del arreglo para darle prioridad #1
                    $coverIndex = array_search($coverReference, $imageOrderArray);
                    if ($coverIndex !== false) {
                        unset($imageOrderArray[$coverIndex]);
                    }

                    // A. Asignar Portada (display_order = 1)
                    $coverName = str_replace('new_', '', $coverReference);
                    if (isset($newUploadedFilesMap[$coverName])) {
                        $this->productModel->updateImageOrderByName($id, $newUploadedFilesMap[$coverName], 1);
                    }

                    // B. Asignar resto de las imágenes según el Drag & Drop (display_order = 2, 3, 4...)
                    $orderCounter = 2;
                    foreach ($imageOrderArray as $item) {
                        if (strpos($item, 'new_') === 0) {
                            $origName = str_replace('new_', '', $item);
                            if (isset($newUploadedFilesMap[$origName])) {
                                $this->productModel->updateImageOrderByName($id, $newUploadedFilesMap[$origName], $orderCounter++);
                            }
                        }
                    }
                }
                
                // Salvaguarda final
                $this->productModel->ensureAtLeastOneCover($id);
            }
        }

        // Interceptor para respuestas AJAX en storeProduct y updateProduct
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'product_id' => $id]);
            exit;
        }
        
        $_SESSION['flash_message'] = '¡Producto agregado con éxito!';
        header('Location: /admin/products');
        exit;
    }
    
    public function editProduct($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $active_menu = 'products';
        $pageTitle = 'Editar Producto';
        $footerJS = '/assets/admin/js/crud.js';
        $pageType = 'crud';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');
        $webhookUrl = $this->getDriveConfig('api_video_products'); // <-- NUEVO: Obtener el enlace de la API de videos

        $productId = (int)$id;
        $product = $this->productModel->getById($productId);
        $images = $this->productModel->getImagesByProductId($productId);
        $categories = $this->categoryModel->getAllSimple();
        $brands = $this->brandModel->getAllSimple();

        if (!$product) {
            header('Location: /admin/products');
            exit;
        }

        include ROOT_PATH . 'app/views/admin/edit-product.php';
    }

    public function updateProduct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) $_POST['id_product'] ?? 0;

            // ----------------------------------------------------
            // 1. ACTUALIZACIÓN DE DATOS TEXTUALES (SKU, Nombre, etc.)
            // ----------------------------------------------------
            
            // Procesar Garantía
            $warranty = '';
            if (isset($_POST['has_warranty']) && $_POST['has_warranty'] === 'yes') {
                $length = $_POST['warranty_length'] ?? '';
                $unit   = $_POST['warranty_unit'] ?? '';
                $unitsMap = ['days' => 'días', 'months' => 'meses', 'years' => 'años'];
                $translatedUnit = $unitsMap[$unit] ?? $unit;
                if (!empty($length) && !empty($unit)) {
                    $warranty = $length . ' ' . $translatedUnit;
                }
            }

            // Procesar Condición
            $conditionMap = ['new' => 'Nuevo', 'used' => 'Usado', 'refurbished' => 'Reacondicionado'];
            $rawCondition = $_POST['condition'] ?? 'new';
            $conditionFinal = $conditionMap[$rawCondition] ?? 'Nuevo';
            
            $data = [
                'sku'             => $_POST['sku'] ?? '0',
                'name'            => $_POST['name'] ?? '',
                'description'     => $_POST['description'] ?? '',
                'ext_description' => $_POST['ext_description'] ?? '',
                'video_title'       => $_POST['video_title'] ?? '',          // <--- NUEVO
                'video_description' => $_POST['video_description'] ?? '',    // <--- NUEVO
                'video_url'         => $_POST['old_video_url'] ?? '',            // <--- NUEVO
                'price'             => (float) ($_POST['price'] ?? 0.0),
                'stock'             => (int) ($_POST['stock'] ?? 0),
                'category'          => (int) ($_POST['category'] ?? 0),
                'brand'             => (int) ($_POST['brand'] ?? 0),
                'warranty'          => $warranty,
                'condition'         => $conditionFinal,
                'hidden'            => isset($_POST['hidden']) ? 1 : 0,
                'certification'     => $_POST['certification'] ?? 'standard', // <-- NUEVO
            ];

            if ($id > 0) {
                $this->productModel->update($id, $data);
            } else {
                // Seguridad: Si no hay ID, salir
                header('Location: /admin/products');
                exit;
            }


            $targetDir = ROOT_PATH . "public/assets/uploads/products/";

            // ==========================================
            // PASO A: ELIMINAR IMÁGENES SELECCIONADAS (Clic en la "X")
            // ==========================================
            if (!empty($_POST['delete_product_images'])) {
                foreach ($_POST['delete_product_images'] as $imageId) {
                    $imageId = (int)$imageId;
                    
                    // 1. Obtener los datos de la imagen desde la BD para saber el nombre de archivo físico
                    $imageRecord = $this->productModel->getImageById($imageId);
                    if ($imageRecord) {
                        $physicalFile = $targetDir . $imageRecord['file_name'];
                        // Borrar el archivo del servidor si existe
                        if (file_exists($physicalFile)) {
                            unlink($physicalFile);
                        }
                        // 2. Borrar el registro en la tabla de imágenes
                        $this->productModel->deleteImage($imageId);
                    }
                }
            }

           // ==========================================
            // PASO B: SUBIR NUEVAS IMÁGENES Y MAPEAR
            // ==========================================
            $newUploadedFilesMap = [];

            if (!empty($_FILES['product_images']['name'][0])) {
                $totalFiles = count($_FILES['product_images']['name']);

                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['product_images']['error'][$i] === UPLOAD_ERR_OK) {
                        $originalName = $_FILES['product_images']['name'][$i];
                        $tmpName = $_FILES['product_images']['tmp_name'][$i];

                        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                        $newFileName = time() . '_' . uniqid() . '.' . $ext;
                        $targetFile = $targetDir . $newFileName;

                        if (move_uploaded_file($tmpName, $targetFile)) {
                            // Subir a BD como imagen secundaria por defecto
                            $this->productModel->addImage($id, $newFileName, false);
                            $newUploadedFilesMap[$originalName] = $newFileName;
                        }
                    }
                }
            }

            // ==========================================
            // PASO C: APLICAR PORTADA Y ORDEN EXACTO (DRAG & DROP)
            // ==========================================
            $coverReference = $_POST['cover_reference'] ?? '';
            $imageOrderRaw = $_POST['image_order'] ?? '[]';
            $imageOrderArray = json_decode($imageOrderRaw, true);

            if (is_array($imageOrderArray) && !empty($imageOrderArray)) {
                // Quitamos la portada del flujo normal
                $coverIndex = array_search($coverReference, $imageOrderArray);
                if ($coverIndex !== false) {
                    unset($imageOrderArray[$coverIndex]);
                }

                // 1. ASIGNAR PORTADA (Orden 1)
                if (strpos($coverReference, 'existing_') === 0) {
                    $imageId = (int)str_replace('existing_', '', $coverReference);
                    $this->productModel->updateImageOrder($imageId, 1);
                } else if (strpos($coverReference, 'new_') === 0) {
                    $coverName = str_replace('new_', '', $coverReference);
                    if (isset($newUploadedFilesMap[$coverName])) {
                        $this->productModel->updateImageOrderByName($id, $newUploadedFilesMap[$coverName], 1);
                    }
                }

                // 2. ASIGNAR RESTO DEL ORDEN (2, 3, 4...)
                $orderCounter = 2;
                foreach ($imageOrderArray as $item) {
                    // Si es una imagen vieja que se movió de posición
                    if (strpos($item, 'existing_') === 0) {
                        $imageId = (int)str_replace('existing_', '', $item);
                        $this->productModel->updateImageOrder($imageId, $orderCounter++);
                    } 
                    // Si es una imagen nueva recién subida
                    else if (strpos($item, 'new_') === 0) {
                        $origName = str_replace('new_', '', $item);
                        if (isset($newUploadedFilesMap[$origName])) {
                            $this->productModel->updateImageOrderByName($id, $newUploadedFilesMap[$origName], $orderCounter++);
                        }
                    }
                }
            }

            $this->productModel->ensureAtLeastOneCover($id);

            // Interceptor para respuestas AJAX en storeProduct y updateProduct
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'product_id' => $id]);
                exit;
            }

            $_SESSION['flash_message'] = '¡Producto actualizado con éxito!';
            header('Location: /admin/products');
            exit;
        }
    }

    public function deleteProduct($id) {
        // 1. Establecemos que la respuesta será JSON
        header('Content-Type: application/json');
        
        $productId = (int) $id;
        $success = false;
        $message = 'ID de producto no válido.'; // Mensaje por defecto

        if ($productId > 0) {
            // 2. Intentamos borrar el producto (esto llama al Modelo)
            $success = $this->productModel->delete($productId);
            
            if ($success) {
                $message = 'Producto eliminado con éxito.';
            } else {
                $message = 'Error: No se pudo eliminar el producto. Revise las llaves foráneas.';
            }
        }

        // 3. Enviamos la respuesta JSON limpia
        echo json_encode(['success' => $success, 'message' => $message]);
        
        // 4. Detenemos la ejecución. SIN REDIRECCIÓN.
        exit;
    }

    //-------------------------------------------------------//
    
    //--------------------RECIÉN LLEGADOS--------------------//

    public function newProducts() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'filtered_list'; 
        $active_menu = 'new-products';
        $pageTitle = 'Productos Recién Llegados'; // Título para la página
        $buttonAgg = 'Recién llegado';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');
        // Recogemos todos los filtros de la URL en un solo array
        $filters = [
            'search'   => $_GET['search'] ?? null,
            'sort'     => $_GET['sort'] ?? null,
            'category' => $_GET['category'] ?? null,
            'brand'    => $_GET['brand'] ?? null,
        ];

        // Cargar modelos
        // Asegúrate de tener los require_once al inicio del archivo
        $newProductModel = new NewProduct($this->db);
        $categoryModel = new Category($this->db);
        $brandModel = new Brand($this->db);

        // Obtener datos usando el NUEVO método y guardarlos en la variable $products
        $products = $newProductModel->getAll($filters);
        $categories = $categoryModel->getAll();
        $headerCategories = $categoryModel->getAllSimple();
        $headerBrands = $brandModel->getAllSimple();
        $brands = $brandModel->getAll();

        include ROOT_PATH . 'app/views/admin/products.php';
    }

    public function createNewProduct() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $active_menu = 'new-products';
        $pageTitle = 'Agregar Recién Llegado';
        $footerJS = '/assets/admin/js/crud.js';
        $pageType = 'crud';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');

        $products = $this->productModel->getAll(); // Obtener todos los productos para el dropdown
        $headerProducts = $this->productModel->getAllPublic(); // Obtener todos los productos para el dropdown
        
        include ROOT_PATH . 'app/views/admin/create-new-product.php';
    }

    public function storeNewProduct() {
        // 1. CORREGIDO: El formulario envía 'product', no 'name'
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['product'])) {
            $productId = (int) $_POST['product'];

            if ($productId > 0) {
                // 2. NUEVO: Eliminar de 'offers' antes de agregar a 'new_products'
                $this->offerModel->deleteByProductId($productId);

                // 3. CORREGIDO: Usar el $productId
                $this->newProductModel->create($productId);
                $_SESSION['flash_message'] = '¡Recién llegado agregado con éxito!';
            }
        }
        header('Location: /admin/new-products');
        exit;
    }

    public function deleteNewProduct($productId) {
        header('Content-Type: application/json');
        
        $productId = (int) $productId;
        $success = false;
        $message = 'ID de producto no válido.'; // Mensaje por defecto

        if ($productId > 0) {
            // Usamos el método que ya tienes en tu modelo
            $success = $this->newProductModel->deleteByProductId($productId);
            
            if ($success) {
                $message = 'Recién llegado eliminado con éxito.';
            } else {
                $message = 'Error: No se pudo eliminar el recién llegado.';
            }
        }

        // Enviamos la respuesta JSON limpia
        echo json_encode(['success' => $success, 'message' => $message]);
        
        // Detenemos la ejecución.
        exit;
    }

    //-------------------------------------------------------//
    
    //------------------------OFERTAS------------------------//

    public function offers() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/admin.js';
        $pageType = 'filtered_list'; 
        $active_menu = 'offers';
        $pageTitle = 'Productos en Oferta'; // Título para la nueva página
        $buttonAgg = 'Oferta';


        $moneySymbol = $this->siteContentModel->getValue('money_symbol');

          // Recogemos todos los filtros de la URL en un solo array
        $filters = [
            'search'   => $_GET['search'] ?? null,
            'sort'     => $_GET['sort'] ?? null,
            'category' => $_GET['category'] ?? null,
            'brand'    => $_GET['brand'] ?? null,
        ];

        // Cargar modelos
        // Asegúrate de tener los require_once al inicio del archivo
        $offerModel = new Offer($this->db);
        $categoryModel = new Category($this->db);
        $brandModel = new Brand($this->db);

        // Obtener datos usando el NUEVO método y guardarlos en la variable $products
        $products = $offerModel->getAll($filters);
        $categories = $categoryModel->getAll();
        $headerCategories = $categoryModel->getAllSimple();
        $headerBrands = $brandModel->getAllSimple();
        $brands = $brandModel->getAll();

        include ROOT_PATH . 'app/views/admin/products.php';
    }

    public function createOffer() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'offers';
        $pageTitle = 'Agregar Nueva Oferta';
        $pageType = 'crud';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');

        $products = $this->productModel->getAll(); // Obtener todos los productos para el dropdown
        $headerProducts = $this->productModel->getAllPublic(); // Obtener todos los productos para el dropdown

        include ROOT_PATH . 'app/views/admin/create-offer.php';
    }

    public function storeOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. Recoger y guardar los datos del producto
            $data = [
                'message'    => $_POST['message'] ?? '',
                'price'      => (float) ($_POST['price'] ?? 0.0),
                'product'    => (int) ($_POST['product'] ?? 0),
            ];

            $productId = $data['product'];

            if ($productId > 0) {
                // 2. NUEVO: Eliminar de 'new_products' antes de agregar a 'offers'
                $this->newProductModel->delete($productId);

                // 3. Crear la nueva oferta
                $this->offerModel->create($data);
                $_SESSION['flash_message'] = '¡Oferta agregada con éxito!';
            }
        }
        header('Location: /admin/offers');
        exit;
    } 

    public function deleteOffer($productId) {
        header('Content-Type: application/json');
        
        $productId = (int) $productId;
        $success = false;
        $message = 'ID de producto no válido.'; // Mensaje por defecto

        if ($productId > 0) {
            // Usamos el método que ya tienes en tu modelo
            $success = $this->offerModel->deleteByProductId($productId);
            
            if ($success) {
                $message = 'Oferta eliminada con éxito.';
            } else {
                $message = 'Error: No se pudo eliminar la oferta.';
            }
        }

        // Enviamos la respuesta JSON limpia
        echo json_encode(['success' => $success, 'message' => $message]);
        
        // Detenemos la ejecución.
        exit;
    }

    //-------------------------------------------------------//
    
    //-----------------------CATEGORIAS----------------------//

    public function categories() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'basic_list'; 
        $active_menu = 'categories';
        $pageTitle = 'Gestión de Categorías';
        $buttonAgg = 'Categoría';

        // --- CORRECCIÓN: Recoger filtros ---
        $filters = [
            'search' => $_GET['search'] ?? null,
            'sort'   => $_GET['sort'] ?? null,
        ];

        // --- CORRECCIÓN: Pasar filtros al modelo ---
        $categories = $this->categoryModel->getAll($filters);

        include ROOT_PATH . 'app/views/admin/categories.php';
    }

    public function createCategory() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'categories';
        $pageTitle = 'Agregar Nueva Categoría';
        $pageType = 'crud';

        include ROOT_PATH . 'app/views/admin/create-category.php';
    }


    public function storeCategory() {
        // Verificamos que se envíe el formulario con un nombre
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
            $data = [
                'name' => $_POST['name'] ?? ''
            ];

            $newCategoryId = $this->categoryModel->create($data);

            // 2. Si la categoría se creó, subir la imagen
            if ($newCategoryId) {
                $uploadDir = ROOT_PATH . 'public/assets/uploads/categories/';

                if (isset($_FILES["image"]) && $_FILES["image"]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES["image"];
                    
                    $baseName = uniqid('cat_') . '-' . pathinfo($file['name'], PATHINFO_FILENAME);
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = $baseName . '.' . $ext;
                    
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        // --- CREAR SMALL ---
                        $smallFile = $uploadDir . 'small_' . $baseName . '.webp';
                        $this->resizeToWebP($targetFile, $smallFile, 400);
                        // -------------------

                        $this->categoryModel->updateImage($newCategoryId, $fileName);
                    }
                }
            }
        }

        // Redirigimos a la lista de categorías con un mensaje de éxito
        $_SESSION['flash_message'] = '¡Categoría agregada con éxito!';
        header('Location: /admin/categories');
        exit;
    }

    public function editCategory($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'categories';
        $pageTitle = 'Editar Categoría';
        $pageType = 'crud';

        $categoryId = (int)$id;
        $category = $this->categoryModel->getById($categoryId);

        if (!$category) {
            header('Location: /admin/categories');
            exit;
        }

        include ROOT_PATH . 'app/views/admin/edit-category.php';
    }

    public function toggleCategoryVisibility() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $newStatus = isset($_POST['hidden_status']) ? (int)$_POST['hidden_status'] : 0;

            if ($categoryId > 0) {
                $success = $this->categoryModel->updateHiddenStatus($categoryId, $newStatus);

                if ($success) {
                    $this->productModel->updateHiddenStatusByCategory($categoryId, $newStatus);

                    // ¡AQUÍ ESTÁ EL CAMBIO! Guardamos el mensaje en la sesión PHP
                    $_SESSION['flash_message'] = $newStatus ? 'Categoría y sus productos ocultados con éxito' : 'Categoría y sus productos ya son visibles';
                    
                    echo json_encode(['success' => true]);
                    exit; 
                }
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Error al actualizar visibilidad de la categoría.']);
        exit; 
    }

    public function updateCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id_category'] ?? 0;
            $name = $_POST['name'] ?? '';
            $deleteImage = $_POST['delete_image'] ?? ''; 
            
            $currentCategory = $this->categoryModel->getById($id);
            if (!$currentCategory) { header('Location: /admin/categories'); exit; }

            $imageName = $currentCategory['image']; 
            $uploadDir = ROOT_PATH . 'public/assets/uploads/categories/';

            // Lógica de Imagen
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Preparar nombres
                $baseName = uniqid('cat_') . '-' . pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFileName = $baseName . '.' . $ext;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFileName)) {
                    // 1. Crear versión Small
                    $smallNewFile = $uploadDir . 'small_' . $baseName . '.webp';
                    $this->resizeToWebP($uploadDir . $newFileName, $smallNewFile, 400);

                    // 2. Borrar imágenes VIEJAS (Original y Small)
                    if (!empty($currentCategory['image'])) {
                        $oldPath = $uploadDir . $currentCategory['image'];
                        $oldBase = pathinfo($currentCategory['image'], PATHINFO_FILENAME);
                        $oldSmall = $uploadDir . 'small_' . $oldBase . '.webp';
                        
                        if (file_exists($oldPath)) unlink($oldPath);
                        if (file_exists($oldSmall)) unlink($oldSmall);
                    }
                    $imageName = $newFileName; 
                }
            }
            elseif (!empty($deleteImage) && !empty($currentCategory['image'])) {
                // Borrar ambas versiones si el usuario dio click a la X
                $oldPath = $uploadDir . $currentCategory['image'];
                $oldBase = pathinfo($currentCategory['image'], PATHINFO_FILENAME);
                $oldSmall = $uploadDir . 'small_' . $oldBase . '.webp';

                if (file_exists($oldPath)) unlink($oldPath);
                if (file_exists($oldSmall)) unlink($oldSmall);
                
                $imageName = ''; 
            }

            if ($id > 0 && !empty($name)) {
                $this->categoryModel->update($id, $name, $imageName);
            }
        }
        $_SESSION['flash_message'] = '¡Categoría actualizada con éxito!';
        header('Location: /admin/categories');
        exit;
    }

    // --- Métodos para obtener el conteo de productos ---
    public function getCategoryProductCount($id) {
        header('Content-Type: application/json');
        $count = $this->categoryModel->countProducts((int)$id);
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    // --- Métodos para eliminar la entidad Y sus productos ---
    public function deleteCategory($id) {
        header('Content-Type: application/json');
        $success = $this->categoryModel->deleteWithProducts((int)$id);
        echo json_encode(['success' => $success]);
        exit;
    }


    //-------------------------------------------------------//
    
    //-------------------------MARCAS------------------------//

    public function brands() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'basic_list'; 
        $active_menu = 'brands';
        $pageTitle = 'Gestión de Marcas';
        $buttonAgg = 'Marca';

        // --- CORRECCIÓN: Recoger filtros ---
        $filters = [
            'search' => $_GET['search'] ?? null,
            'sort'   => $_GET['sort'] ?? null,
        ];

        // --- CORRECCIÓN: Pasar filtros al modelo ---
        $brands = $this->brandModel->getAll($filters);

        include ROOT_PATH . 'app/views/admin/brands.php';
    }

    public function createBrand() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'brands';
        $pageTitle = 'Agregar Nueva Marca';
        $pageType = 'crud';

        include ROOT_PATH . 'app/views/admin/create-brand.php';
    }

    public function storeBrand() {
        // Verificamos que se envíe el formulario con un nombre
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
            
            // 1. CORRECCIÓN: Pasamos un array, no un string
            $data = [
                'name' => $_POST['name'] ?? ''
            ];

            // 2. Obtenemos el ID de la nueva marca
            $newBrandId = $this->brandModel->create($data); //

            // 3. Si la marca se creó, subir la imagen
            if ($newBrandId) {
                $uploadDir = ROOT_PATH . 'public/assets/uploads/brands/';

                // Verificamos que el archivo de imagen se haya subido
                if (isset($_FILES["image"]) && $_FILES["image"]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES["image"];
                    
                    $baseName = uniqid('brand_') . '-' . pathinfo($file['name'], PATHINFO_FILENAME);
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = $baseName . '.' . $ext;
                    $targetFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                        // --- CREAR SMALL ---
                        $smallFile = $uploadDir . 'small_' . $baseName . '.webp';
                        $this->resizeToWebP($targetFile, $smallFile, 400);
                        // -------------------
                        $this->brandModel->updateImage($newBrandId, $fileName);
                    }
                }
            }

            $_SESSION['flash_message'] = '¡Marca agregada con éxito!';
        }

        // Redirigimos a la lista de marcas
        header('Location: /admin/brands');
        exit;
    }

    public function editBrand($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'brands';
        $pageTitle = 'Editar Marca';
        $pageType = 'crud';

        $brandId = (int)$id;
        $brand = $this->brandModel->getById($brandId);

        if (!$brand) {
            header('Location: /admin/brands');
            exit;
        }

        include ROOT_PATH . 'app/views/admin/edit-brand.php';
    }

    public function toggleBrandVisibility() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $brandId = (int) ($_POST['brand_id'] ?? 0);
            $newStatus = isset($_POST['hidden_status']) ? (int)$_POST['hidden_status'] : 0;

            if ($brandId > 0) {
                $success = $this->brandModel->updateHiddenStatus($brandId, $newStatus);

                if ($success) {
                    $this->productModel->updateHiddenStatusByBrand($brandId, $newStatus);

                    // ¡AQUÍ ESTÁ EL CAMBIO! Guardamos el mensaje en la sesión PHP
                    $_SESSION['flash_message'] = $newStatus ? 'Marca y sus productos ocultados con éxito' : 'Marca y sus productos ya son visibles';
                    
                    echo json_encode(['success' => true]);
                    exit; 
                }
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Error al actualizar visibilidad de la marca.']);
        exit; 
    }

    public function updateBrand() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id_brand'] ?? 0;
            $name = $_POST['name'] ?? '';
            $deleteImage = $_POST['delete_image'] ?? ''; 
            
            $currentBrand = $this->brandModel->getById($id);
            if (!$currentBrand) { header('Location: /admin/brands'); exit; }

            $imageName = $currentBrand['image']; 
            $uploadDir = ROOT_PATH . 'public/assets/uploads/brands/';

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Nombres nuevos
                $baseName = uniqid('brand_') . '-' . pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $newFileName = $baseName . '.' . $ext;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFileName)) {
                    // 1. Crear Small
                    $smallNewFile = $uploadDir . 'small_' . $baseName . '.webp';
                    $this->resizeToWebP($uploadDir . $newFileName, $smallNewFile, 400);

                    // 2. Borrar viejas
                    if (!empty($currentBrand['image'])) {
                        $oldPath = $uploadDir . $currentBrand['image'];
                        $oldBase = pathinfo($currentBrand['image'], PATHINFO_FILENAME);
                        $oldSmall = $uploadDir . 'small_' . $oldBase . '.webp';
                        
                        if (file_exists($oldPath)) unlink($oldPath);
                        if (file_exists($oldSmall)) unlink($oldSmall);
                    }
                    $imageName = $newFileName;
                }
            }
            elseif (!empty($deleteImage) && !empty($currentBrand['image'])) {
                // Borrar ambas
                $oldPath = $uploadDir . $currentBrand['image'];
                $oldBase = pathinfo($currentBrand['image'], PATHINFO_FILENAME);
                $oldSmall = $uploadDir . 'small_' . $oldBase . '.webp';

                if (file_exists($oldPath)) unlink($oldPath);
                if (file_exists($oldSmall)) unlink($oldSmall);
                
                $imageName = '';
            }

            if ($id > 0 && !empty($name)) {
                $this->brandModel->update($id, $name); 
                $this->brandModel->updateImage($id, $imageName); 
            }
        }
        $_SESSION['flash_message'] = '¡Marca actualizada con éxito!';
        header('Location: /admin/brands');
        exit;
    }

    public function getBrandProductCount($id) {
        header('Content-Type: application/json');
        $count = $this->brandModel->countProducts((int)$id);
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }

    public function deleteBrand($id) {
        header('Content-Type: application/json');
        $success = $this->brandModel->deleteWithProducts((int)$id);
        echo json_encode(['success' => $success]);
        exit;
    }

    //-------------------------------------------------------//

    //-----------------------USUARIOS------------------------//


    public function users() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'basic_list'; 
        $active_menu = 'users';
        $pageTitle = 'Gestión de Usuarios';
        $buttonAgg = 'Usuario';

        // --- CORRECCIÓN: Recoger filtros ---
        $filters = [
            'search' => $_GET['search'] ?? null,
            'sort'   => $_GET['sort'] ?? null,
            'role'   => $_GET['role'] ?? ''
        ];

        // --- CORRECCIÓN: Pasar filtros al modelo ---
        $users = $this->userModel->getAll($filters);

        include ROOT_PATH . 'app/views/admin/users.php';
    }

    public function createUser() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'users';
        $pageTitle = 'Agregar Nuevo Usuario';
        $pageType = 'crud';

        include ROOT_PATH . 'app/views/admin/create-user.php';
    }

    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $name = $_POST['name'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $pass_confirm = $_POST['password_confirmation'] ?? '';
            
            // Capturamos el rol (admin, seller, tech)
            $role = $_POST['role'] ?? 'seller';

            if (empty($name) || empty($username) || empty($email) || empty($password)) {
                $_SESSION['flash_message'] = 'Error: Todos los campos (incluyendo contraseña) son obligatorios.';
                header('Location: /admin/users/create');
                exit;
            }

            if ($password !== $pass_confirm) {
                $_SESSION['flash_message'] = 'Error: Las contraseñas no coinciden.';
                header('Location: /admin/users/create');
                exit;
            }

            // Preparamos los datos limpios. El is_super_admin lo calcula el Modelo basado en el Rol.
            $data = [
                'name' => $name, 
                'username' => $username, 
                'email' => $email, 
                'password' => $password, 
                'role' => $role,
                'avatar' => '' // Se envía vacío por ahora, se actualiza al subir la imagen
            ];
            
            try {
                $newUserId = $this->userModel->create($data);

                if ($newUserId) {
                    // El usuario se creó, ahora intentamos subir la imagen
                    if (isset($_FILES["image"]) && $_FILES["image"]['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = ROOT_PATH . 'public/assets/uploads/users/';
                        $file = $_FILES["image"];
                        
                        $baseName = uniqid('user_') . '-' . pathinfo($file['name'], PATHINFO_FILENAME);
                        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $fileName = $baseName . '.' . $ext;
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                            $smallFile = $uploadDir . 'small_' . $baseName . '.webp';
                            $this->resizeToWebP($targetFile, $smallFile, 200); 
                            $this->userModel->updateAvatar($newUserId, $fileName);
                            $_SESSION['flash_message'] = '¡Usuario agregado con éxito!';
                        } else {
                            $_SESSION['flash_message'] = 'Usuario creado, pero hubo un error guardando la imagen.';
                        }
                    } else {
                        $_SESSION['flash_message'] = '¡Usuario agregado con éxito (sin imagen)!';
                    }
                    
                    header('Location: /admin/users');
                    exit;

                } else {
                    $_SESSION['flash_message'] = 'Error: No se pudo crear el usuario por un error desconocido.';
                    header('Location: /admin/users/create');
                    exit;
                }
            } catch (\mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    // MAGIA: Analizamos exactamente de dónde vino el rechazo de la base de datos
                    $errorMsg = $e->getMessage();
                    
                    if (strpos($errorMsg, 'email') !== false) {
                        $_SESSION['flash_message'] = "Error: El correo '$email' ya pertenece a otro usuario.";
                    } elseif (strpos($errorMsg, 'username') !== false) {
                        $_SESSION['flash_message'] = "Error: El usuario '$username' ya está en uso. ¡Inventa otro!";
                    } else {
                        $_SESSION['flash_message'] = 'Error de duplicado: ' . $errorMsg;
                    }
                    
                    header('Location: /admin/users/create');
                    exit;
                } else {
                    throw $e;
                }
            }
        }
    }

    // Reemplaza tu método editUser()
    public function editUser($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $footerJS = '/assets/admin/js/crud.js';
        $active_menu = 'users';
        $pageTitle = 'Editar Usuario';
        $pageType = 'crud';

        $userId = (int)$id;
        $user = $this->userModel->getById($userId);

        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        // --- INICIO DE NUEVA LÓGICA DE PERMISOS ---
        // Requisito 2: El super usuario (is_super_admin = 1 y rol admin)
        // solo puede ser editado por él mismo.
        if ($user['is_super_admin'] == 1 && $user['role'] === 'admin' && $_SESSION['idUser'] != $userId) {
            $_SESSION['flash_message'] = 'Error: No tienes permisos para editar este usuario.';
            header('Location: /admin/users');
            exit;
        }
        // --- FIN DE NUEVA LÓGICA DE PERMISOS ---

        include ROOT_PATH . 'app/views/admin/edit-user.php';
    }

    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id_user'] ?? 0;

            // Obtenemos los datos del usuario ANTES de actualizar para NO perder la foto vieja
            $currentUser = $this->userModel->getById($id);
            if (!$currentUser) {
                header('Location: /admin/users');
                exit;
            }

            // Requisito: Si es super admin, solo él mismo puede editarse
            if ($currentUser['is_super_admin'] == 1 && $_SESSION['idUser'] != $id) {
                $_SESSION['flash_message'] = 'Error: No tienes permisos para editar a este Super Administrador.';
                header('Location: /admin/users');
                exit;
            }

            // VALIDACIÓN DE SEGURIDAD: Prevenir que el usuario cambie su propio rol
            $nuevoRol = $_POST['role'] ?? 'vendedor';
            if ($id == $_SESSION['idUser']) {
                $nuevoRol = $currentUser['role']; // Se fuerza a mantener el rol actual
            }

            $data = [
                'name'      => $_POST['name'],
                'username'  => $_POST['username'],
                'email'     => $_POST['email'],
                'password'  => !empty($_POST['password']) ? $_POST['password'] : null,
                'role'      => $nuevoRol,
                'avatar'    => $currentUser['avatar'] // Asignamos la foto que ya tenía por defecto
            ];

            // Si subió una foto nueva, la procesamos y reemplazamos la variable en $data
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . 'public/assets/uploads/users/';
                
                $baseName = uniqid('user_') . '-' . pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $data['avatar'] = $baseName . '.' . $ext; // Sobreescribimos con el nuevo nombre

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $data['avatar'])) {
                     $smallFile = $uploadDir . 'small_' . $baseName . '.webp';
                     $this->resizeToWebP($uploadDir . $data['avatar'], $smallFile, 200);

                     // Borrar avatar viejo
                     if (!empty($currentUser['avatar'])) {
                        $oldPath = $uploadDir . $currentUser['avatar'];
                        $oldBase = pathinfo($currentUser['avatar'], PATHINFO_FILENAME);
                        $oldSmall = $uploadDir . 'small_' . $oldBase . '.webp';
                        
                        if (file_exists($oldPath)) @unlink($oldPath);
                        if (file_exists($oldSmall)) @unlink($oldSmall);
                     }
                }
            }
            
            try {
                if ($id > 0 && !empty($data['name']) && !empty($data['username']) && !empty($data['email'])) {
                    $this->userModel->update($id, $data); 
                }
                
                $_SESSION['flash_message'] = '¡Usuario actualizado con éxito!';
                header('Location: /admin/users');
                exit;

            } catch (\mysqli_sql_exception $e) {
                if ($e->getCode() == 1062) {
                    $errorMsg = $e->getMessage();
                    
                    if (strpos($errorMsg, 'email') !== false) {
                        $_SESSION['flash_message'] = 'Error: Este correo ya pertenece a otro usuario.';
                    } elseif (strpos($errorMsg, 'username') !== false) {
                        $_SESSION['flash_message'] = 'Error: El nombre de usuario ya está ocupado.';
                    } else {
                        $_SESSION['flash_message'] = 'Error de duplicado: ' . $errorMsg;
                    }
                    
                    header("Location: /admin/users/edit/{$id}");
                    exit;
                } else {
                    throw $e;
                }
            }
        }
    }

    // Reemplaza tu método deleteUser()
    public function deleteUser($id) {
        header('Content-Type: application/json');
        $userId = (int)$id;

        // --- INICIO DE NUEVA LÓGICA DE PERMISOS ---
        $user = $this->userModel->getById($userId);
        if ($user && $user['is_super_admin'] == 1 && $user['role'] === 'admin') {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar a este Administrador.']);
            exit;
        }
        // --- FIN DE NUEVA LÓGICA DE PERMISOS ---

        $success = $this->userModel->delete($userId);
        $message = $success ? 'Usuario eliminado con éxito.' : 'Error al eliminar el usuario.';

        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }

    //----------------------AJUSTES------------------------//

    // --- REEMPLAZAR FUNCIÓN settings() ---
    public function settings() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title'); // Opcional si lo usas en header
        
        // Configuramos variables para la vista y el footer
        $pageType = 'crud'; 
        $active_menu = 'settings'; 
        $pageTitle = 'Ajustes del Sitio';

        // Obtenemos los valores actuales. Si devuelve null, asumimos 0 (Ocultar)
        // Forzamos (int) para asegurarnos que la vista reciba un número
        $showPrice = (int) $this->settingsModel->getValueByKey('show_price');
        $showStock = (int) $this->settingsModel->getValueByKey('show_stock');

        include ROOT_PATH . 'app/views/admin/settings.php';
    }

    // --- REEMPLAZAR FUNCIÓN updateSettings() ---
    public function updateSettings() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // Recogemos los datos. Si no se envía nada, asumimos 0.
            $showPrice = $_POST['show_price'] ?? 0;
            $showStock = $_POST['show_stock'] ?? 0;

            // Llamamos al modelo para actualizar
            $successPrice = $this->settingsModel->updateValueByKey('show_price', $showPrice);
            $successStock = $this->settingsModel->updateValueByKey('show_stock', $showStock);

            // Mensajes de Feedback
            if ($successPrice && $successStock) {
                $_SESSION['flash_message'] = '¡Ajustes actualizados con éxito!';
            } else {
                $_SESSION['flash_message'] = 'Hubo un error al guardar algunos ajustes.';
            }
            
            // Redirección
            header('Location: /admin/settings');
            exit;
        }
    }


    //-------------------------------------------------------//
    //------------------ GESTIÓN DE PÁGINAS -----------------//
    //-------------------------------------------------------//

    public function showGeneral() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'crud';
        $pageTitle = 'Personalización General';
        $active_menu = 'pages-general'; // Usaremos esto para el sidebar

        // 1. Cargar datos individuales (Textos y Logo)
        // Usamos getValue para obtener directamente el string, o getContentByKey si necesitamos más datos
        $logoHeader = $this->siteContentModel->getContentByKey('logo_header');
        
        $moneySymbol = $this->siteContentModel->getValue('money_symbol');
        $contactNumber = $this->siteContentModel->getValue('contact_number');
        $locationAddress = $this->siteContentModel->getValue('location_address');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        
        // Redes Sociales
        $facebook = $this->siteContentModel->getValue('facebook_link');
        $instagram = $this->siteContentModel->getValue('instagram_link');
        $youtube = $this->siteContentModel->getValue('youtube_link');
        $tiktok = $this->siteContentModel->getValue('tiktok_link');

        // 2. Cargar lista de Logos de Pago (Múltiples imágenes)
        $paymentLogos = $this->siteContentModel->getOrderedContentByPrefix('payment_logo');

        include ROOT_PATH . 'app/views/admin/admin-general.php';
    }

    public function updateGeneral() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages/general');
            exit;
        }

        $success = true; 
        $uploadDir = ROOT_PATH . 'public/assets/uploads/';
        
        // --- 1. ACTUALIZAR TEXTOS SIMPLES ---
        // Array map: Clave BD => Nombre Input
        $textFields = [
            'money_symbol'    => 'money_symbol',
            'contact_number'   => 'contact_number',
            'location_address' => 'location_address',
            'copyright'        => 'copyright',
            'facebook_link'    => 'facebook_link',
            'instagram_link'   => 'instagram_link',
            'youtube_link'     => 'youtube_link',
            'tiktok_link'      => 'tiktok_link'
        ];

        foreach ($textFields as $dbKey => $inputName) {
            $value = $_POST[$inputName] ?? '';
            // Guardamos siempre (o actualizamos)
            $this->siteContentModel->saveOrUpdateContent($dbKey, $value, 1);
        }

        // --- 2. LOGO DEL HEADER (Imagen Única) ---
        $logoKey = 'logo_header';
        $logoPath = $uploadDir . 'logo/';
        $logoInputName = 'image_logo'; // name en el input file
        
        $currentLogoName = $_POST['current_logo_name'] ?? null; 
        $deleteLogo = $_POST['delete_logo'] ?? null;     

        if (isset($_FILES[$logoInputName]) && $_FILES[$logoInputName]['error'] === UPLOAD_ERR_OK) {
            // Guardar nuevo
            $newFileName = $this->handleFileUpload($_FILES[$logoInputName], $logoPath, 'logo');
            if ($newFileName) {
                // Borrar viejo
                if (!empty($currentLogoName)) @unlink($logoPath . $currentLogoName);
                $this->siteContentModel->saveOrUpdateContent($logoKey, $newFileName, 1);
            }
        } else if (!empty($deleteLogo) && !empty($currentLogoName)) {
            // Borrar físico y de BD
            @unlink($logoPath . $currentLogoName);
            $this->siteContentModel->saveOrUpdateContent($logoKey, '', 1); // Opcional: dejar vacío
        }
        // Si no se tocó nada, no hacemos nada (se mantiene el actual)

        // --- 3. LOGOS DE PAGO (Múltiples imágenes - Lógica dinámica) ---
        $paymentKey = 'payment_logo';
        $paymentPath = $uploadDir . 'payments/';
        
        // Borramos registros previos para reordenar limpiamente
        $this->siteContentModel->deleteContentByKey($paymentKey);

        $displayOrder = 1; 
        
        // Aumentamos el límite a 200 (igual que en Shipping) para capturar índices altos del JS
        for ($i = 0; $i < 200; $i++) {
            
            // CORRECCIÓN: Usamos los nombres estándar que envía el JS (image_slot, slot_image_id)
            // Verificamos si existe algún input para este índice
            if (!isset($_FILES["image_slot_$i"]) && !isset($_POST["slot_image_id_$i"])) {
                continue; 
            }

            $fileKey = "image_slot_$i"; // Antes era payment_slot_$i
            $currentName = $_POST["slot_image_id_$i"] ?? null; // Antes payment_id
            
            // Aceptamos ambas variantes del flag de borrado por seguridad
            $deleteFlag = $_POST["delete_image_$i"] ?? $_POST["delete_slot_$i"] ?? null; 

            $finalName = null;

            // CASO A: Subida nueva
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                // 'pay' es el prefijo del archivo
                $newFileName = $this->handleFileUpload($_FILES[$fileKey], $paymentPath, 'pay'); 
                if ($newFileName) {
                    if (!empty($currentName)) @unlink($paymentPath . $currentName);
                    $finalName = $newFileName;
                }
            } 
            // CASO B: Mantener existente
            elseif (!empty($currentName) && empty($deleteFlag)) {
                $finalName = $currentName;
            } 
            // CASO C: Borrar
            elseif (!empty($deleteFlag) && !empty($currentName)) {
                @unlink($paymentPath . $currentName);
                $finalName = null;
            }

            // Guardar en BD
            if ($finalName) {
                $this->siteContentModel->saveOrUpdateContent($paymentKey, $finalName, $displayOrder++);
            }
        }

        $_SESSION['flash_message'] = 'Configuración general actualizada correctamente.';
        header('Location: /admin/pages/general'); 
        exit;
    }

    public function showHome() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'crud';
        $pageTitle = 'Gestión de Página: Inicio';
        $active_menu = 'pages-home'; 

        $sliderKey = 'home_slider';
        $bannerKey = 'home_banner';
        $featuredCategoriesKey = 'home_category';
        $featuredBrandsKey = 'home_brand';

        $categories = $this->categoryModel->getAllSimple();
        $brands = $this->brandModel->getAllSimple();


        $sliderContent = $this->siteContentModel->getOrderedContentByPrefix($sliderKey);
        $sliderImages = [];

        // Simplemente cargamos los datos. No se guarda nada aquí.
        $bannerImage = $this->siteContentModel->getContentByKey($bannerKey);
        // CORRECCIÓN: Usar getOrderedContentByPrefix para cargar todos los datos de categorías destacadas (no solo IDs)
        $featuredCategoryContent = $this->siteContentModel->getOrderedContentByPrefix($featuredCategoriesKey) ?? []; 
        $featuredBrandContent = $this->siteContentModel->getOrderedContentByPrefix($featuredBrandsKey) ?? []; 
        
        // CORRECCIÓN: Generar el array de IDs para la vista
        $featuredCategoryIds = array_map(function($item) { return $item['content_value']; }, $featuredCategoryContent);
        $featuredBrandIds = array_map(function($item) { return $item['content_value']; }, $featuredBrandContent);
        // FIN DE CORRECCIÓN


        for ($j = 0; $j < 5; $j++) {
            $sliderImages[$j] = null;
        }

        // Ahora llenamos los slots con los datos de la BD
        foreach ($sliderContent as $item) {
            // La validación !empty() asegura que solo procesamos slots con imágenes
            if (!empty($item['content_value'])) {
                // Utilizamos la clave 'display_order' de forma segura
                $index = ($item['display_order'] ?? 1) - 1; // Usamos 1 como fallback, aunque ya debería existir
                
                // Asignamos el item completo (que incluye display_order) al índice correcto (0-4)
                if ($index >= 0 && $index < 5) {
                    $sliderImages[$index] = $item; 
                }
            }
        }

        // -------------------------------------------------------

        include ROOT_PATH . 'app/views/admin/admin-home.php';
    }

    public function updateFeatured($key) {
        // 1. Verificar solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // CORRECCIÓN: Redirigir al método showHome/pages/home
            header('Location: /admin/pages/home'); 
            exit();
        }

        // 2. Obtener los IDs seleccionados.
        if ($key === 'home_category') {
            $selectedItems = $_POST['featured_categories'] ?? [];
        } else {
            $selectedItems = $_POST['featured_brands'] ?? [];
        }
        
        $count = count($selectedItems);
        
        // 3. VALIDACIÓN: Obligatorio seleccionar EXACTAMENTE 7
        if ($count !== 7) {
            $_SESSION['flash_message'] = 'Error: Debe seleccionar exactamente 7 ítems. Se seleccionaron ' . $count . '.';
            // CORRECCIÓN: Redirigir al método showHome/pages/home
            header('Location: /admin/pages/home'); 
            exit();
        }

        // 4. Guardar la lista actualizada. El modelo se encarga del borrado y re-inserción.
        $sanitizedItems = array_map('intval', $selectedItems);
        $saved = $this->siteContentModel->saveContentList($key, $sanitizedItems); 
        
        if ($saved) {
            $_SESSION['flash_message'] = 'Ítems destacados guardados correctamente para la clave: ' . $key;
        } else {
            $_SESSION['flash_message'] = 'Error al guardar los ítems destacados.';
        }

        // 5. Redirigir de vuelta
        header('Location: /admin/pages/home');
        exit();
    }


    public function updateHome() {
        // 1. CHEQUEO DE SEGURIDAD: ¿El envío excedió el límite de PHP?
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['flash_message'] = 'Error: Los archivos son demasiado pesados. Aumente el upload_max_filesize en php.ini';
            header('Location: /admin/pages/home');
            exit;
        }

        $pageName = $this->siteContentModel->getValue('page_title');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages/home');
            exit;
        }

        $success = true; 
        $uploadDir = ROOT_PATH . 'public/assets/uploads/';
        $error_message = ''; 

        // ----------------------------------------------------
        // A. SLIDER PRINCIPAL (home_slider)
        // ----------------------------------------------------
        $sliderKey = 'home_slider';
        $sliderSavePath = $uploadDir . 'home/slider/';
        
        // ESTRATEGIA SEGURA: No borramos todo con deleteContentByKey al inicio.
        // Iremos slot por slot haciendo "Upsert" (Insertar o Actualizar).

        for ($i = 0; $i < 5; $i++) {
            $slotOrder = $i + 1;
            $imageFileKey = "image_slot_{$i}";
            $currentImageName = $_POST["slot_image_id_{$i}"] ?? null; 
            $deleteFlag = $_POST["delete_image_{$i}"] ?? null;       

            // CASO 1: Subida de nueva imagen
            if (isset($_FILES[$imageFileKey]) && $_FILES[$imageFileKey]['error'] === UPLOAD_ERR_OK) {
                $fileName = $this->handleFileUpload($_FILES[$imageFileKey], $sliderSavePath, 'slider');
                
                if ($fileName) {
                    // Si subió bien, borramos la vieja física
                    if (!empty($currentImageName)) {
                        @unlink($sliderSavePath . $currentImageName);
                    }
                    // Y guardamos/actualizamos en la BD
                    $this->siteContentModel->saveOrUpdateContent($sliderKey, $fileName, $slotOrder);
                } else {
                    $success = false;
                    $error_message .= "Fallo al mover imagen (Slot $i). ";
                }
            }
            // CASO 2: Borrado explícito (Click en la X)
            else if (!empty($deleteFlag)) {
                if (!empty($currentImageName)) {
                    @unlink($sliderSavePath . $currentImageName);
                }
                // Aquí sí borramos el registro específico de la BD para mantener el orden limpio
                // Necesitarás añadir este método específico en tu modelo o usar una query directa,
                // pero por ahora, podemos setearlo a vacío para no romper índices.
                // O mejor, usamos updateContentByKeyAndOrder para ponerlo vacío/null.
                $this->siteContentModel->updateContentByKeyAndOrder($sliderKey, '', $slotOrder);
            }
            // CASO 3: No se tocó nada (Mantener)
            else if (!empty($currentImageName)) {
                // Nos aseguramos que esté en la BD (útil si se borró accidentalmente)
                $this->siteContentModel->saveOrUpdateContent($sliderKey, $currentImageName, $slotOrder);
            }
        }

        // ----------------------------------------------------
        // B. CATEGORÍAS DESTACADAS
        // ----------------------------------------------------
        if (isset($_POST['featured_categories'])) {
            $featuredCategories = $_POST['featured_categories'] ?? [];
            $keyCategories = 'home_category';

            if (count($featuredCategories) !== 7) {
                $success = false; 
                $error_message .= 'Debe seleccionar exactamente 7 categorías. ';
            } else {
                // Aquí sí es seguro borrar porque son solo IDs (texto), no pesan.
                $this->siteContentModel->deleteContentByKey($keyCategories);
                $order = 1;
                foreach ($featuredCategories as $categoryId) {
                    $this->siteContentModel->saveOrUpdateContent($keyCategories, (string)$categoryId, $order++);
                }
            }
        }

        // ----------------------------------------------------
        // C. MARCAS DESTACADAS
        // ----------------------------------------------------
        if (isset($_POST['featured_brands'])) { 
            $featuredBrands = $_POST['featured_brands'] ?? [];
            $keyBrands = 'home_brand';

            if (count($featuredBrands) !== 7) {
                $success = false; 
                $error_message .= 'Debe seleccionar exactamente 7 marcas. ';
            } else {
                $this->siteContentModel->deleteContentByKey($keyBrands);
                $order = 1;
                foreach ($featuredBrands as $brandId) {
                    $this->siteContentModel->saveOrUpdateContent($keyBrands, (string)$brandId, $order++);
                }
            }
        }
        
        // ----------------------------------------------------
        // D. BANNER SECUNDARIO
        // ----------------------------------------------------
        $bannerKey = 'home_banner';
        $bannerSavePath = $uploadDir . 'home/banner/';
        
        $currentBannerName = $_POST['slot_image_id_5'] ?? null; 
        $deleteBanner = $_POST['delete_image_5'] ?? null;     
        $bannerFileKey = 'image_slot_5';                    

        if (isset($_FILES[$bannerFileKey]) && $_FILES[$bannerFileKey]['error'] === UPLOAD_ERR_OK) {
            $newFileName = $this->handleFileUpload($_FILES[$bannerFileKey], $bannerSavePath, 'banner');
            if ($newFileName) {
                if (!empty($currentBannerName)) @unlink($bannerSavePath . $currentBannerName);
                $this->siteContentModel->saveOrUpdateContent($bannerKey, $newFileName, 1);
            }
        }
        else if (!empty($deleteBanner) && !empty($currentBannerName)) {
            @unlink($bannerSavePath . $currentBannerName);
            $this->siteContentModel->updateContentByKeyAndOrder($bannerKey, '', 1);
        }

        // ----------------------------------------------------
        // E. RESPUESTA FINAL
        // ----------------------------------------------------
        if ($success && empty($error_message)) {
            $_SESSION['flash_message'] = 'Inicio actualizado con éxito.';
        } else {
             $_SESSION['flash_message'] = 'Error: ' . $error_message;
        }
        header('Location: /admin/pages/home'); 
        exit;
    }

    //------------------------- BLOG ------------------------//

    public function showBlog() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'crud'; 
        $pageTitle = 'Gestión del Blog';
        $active_menu = 'pages-blog'; // Asegúrate de agregar esto a tu sidebar.php si quieres que se ilumine

        $bannerKey = 'blog_banner';
        $titleKey  = 'blog_title';
        $textKey   = 'blog_text';
        $imageKey  = 'blog_image';
        $videoKey  = 'blog_video';
        $linkKey   = 'blog_link';

        // 1. Cargar Banner
        $bannerImage = $this->siteContentModel->getContentByKey($bannerKey);

        // 2. Cargar Posts existentes ordenados
        // Nota: Usamos getOrderedIdsByKey porque en tus vistas admin usas variables como $titleIds
        $titleIds = $this->siteContentModel->getOrderedIdsByKey($titleKey) ?? [];
        $textIds  = $this->siteContentModel->getOrderedIdsByKey($textKey) ?? [];
        $imageIds = $this->siteContentModel->getOrderedIdsByKey($imageKey) ?? [];
        $videoIds = $this->siteContentModel->getOrderedIdsByKey($videoKey) ?? [];
        $linkIds  = $this->siteContentModel->getOrderedIdsByKey($linkKey) ?? [];

        include ROOT_PATH . 'app/views/admin/admin-blog.php';
    }

    public function updateBlog() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages/blog');
            exit;
        }

        $uploadDir = ROOT_PATH . 'public/assets/uploads/';
        
        // Claves de base de datos
        $bannerKey = 'blog_banner';
        $titleKey  = 'blog_title';
        $textKey   = 'blog_text';
        $imageKey  = 'blog_image';
        $videoKey  = 'blog_video';
        $linkKey   = 'blog_link';

        // Rutas de guardado
        $bannerPath = $uploadDir . 'blog/banner/';
        $imagesPath  = $uploadDir . 'blog/images/';
        $videosPath = $uploadDir . 'blog/videos/';

        // ====================================================
        // 1. BANNER DEL BLOG
        // ====================================================
        $this->siteContentModel->deleteContentByKey($bannerKey);
        
        $currentBanner = $_POST['slot_image_id_banner'] ?? null;
        $deleteBanner  = $_POST['delete_image_banner'] ?? null;
        
        if (isset($_FILES['blog_banner_image']) && $_FILES['blog_banner_image']['error'] === UPLOAD_ERR_OK) {
            // Usamos tu función handleFileUpload existente para imágenes
            $newBanner = $this->handleFileUpload($_FILES['blog_banner_image'], $bannerPath, 'blog_bn');
            if ($newBanner) {
                if (!empty($currentBanner)) @unlink($bannerPath . $currentBanner);
                $this->siteContentModel->saveOrUpdateContent($bannerKey, $newBanner, 1);
            }
        } elseif (empty($deleteBanner) && !empty($currentBanner)) {
            $this->siteContentModel->saveOrUpdateContent($bannerKey, $currentBanner, 1);
        } elseif (!empty($deleteBanner) && !empty($currentBanner)) {
            @unlink($bannerPath . $currentBanner);
        }


        $_SESSION['flash_message'] = 'Blog actualizado correctamente.';
        header('Location: /admin/pages/blog');
        exit;
    }

    //------------------------- BLOG POSTS (CRUD) ------------------------//

    public function blogs() {
        // 1. Obtener la información básica requerida por el sidebar/header
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        
        // 2. Configuración de la vista
        $pageType = 'basic_list'; 
        $active_menu = 'blogs';
        $pageTitle = 'Gestión de Blogs';
        $buttonAgg = 'Artículo'; // Texto para el botón "+ Agregar"

        // 3. Recoger filtros (Búsqueda, orden, estado)
        $filters = [
            'search' => $_GET['search'] ?? null,
            'sort'   => $_GET['sort'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        // 4. Obtener todos los artículos usando el modelo
        $blogs = $this->blogModel->getAll($filters);

        // 5. Incluir la vista correcta (la que definimos en la respuesta anterior)
        include ROOT_PATH . 'app/views/admin/blogs.php';
    }

    public function createBlog() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $active_menu = 'blogs';
        $pageTitle = 'Agregar Nuevo Artículo';
        $pageType = 'crud';

        include ROOT_PATH . 'app/views/admin/create-blog.php';
    }

    public function storeBlog() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            // Generamos una URL amigable (slug) basada en el título y un ID único
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . substr(uniqid(), -5);
            
            $data = [
                'title' => $title,
                'author' => $_POST['author'] ?? '',
                'slug' => $slug,
                'content' => $_POST['content'] ?? '',
                'status' => $_POST['status'] ?? 'borrador',
                'cover_image' => 'default_blog.webp' // Imagen por defecto
            ];

            // Si subió una imagen de portada
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . 'public/assets/uploads/blog/posts/';
                $this->ensureDirExists($uploadDir);
                
                $baseName = uniqid('post_') . '-' . pathinfo($_FILES['cover_image']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $data['cover_image'] = $baseName . '.' . $ext;
                
                $targetFile = $uploadDir . $data['cover_image'];
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetFile)) {
                    // NUEVO: Generar versión reducida para la miniatura del admin
                    $smallFile = $uploadDir . 'small_' . $baseName . '.webp';
                    $this->resizeToWebP($targetFile, $smallFile, 400);
                }
            }

            $this->blogModel->create($data);
            $_SESSION['flash_message'] = '¡Artículo publicado con éxito!';
            header('Location: /admin/blogs');
            exit;
        }
    }

    public function editBlog($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $active_menu = 'blogs';
        $pageTitle = 'Editar Artículo';
        $pageType = 'crud';

        $blogId = (int)$id;
        $blog = $this->blogModel->getById($blogId);

        if (!$blog) { header('Location: /admin/blogs'); exit; }

        include ROOT_PATH . 'app/views/admin/edit-blog.php';
    }

    public function updateBlogPost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id_blog'];
            $currentBlog = $this->blogModel->getById($id);
            if(!$currentBlog) { header('Location: /admin/blogs'); exit; }

            $data = [
                'title' => $_POST['title'] ?? '',
                'author' => $_POST['author'] ?? '',
                'slug' => $currentBlog['slug'], // Mantenemos el mismo enlace
                'content' => $_POST['content'] ?? '',
                'status' => $_POST['status'] ?? 'borrador',
                'cover_image' => $currentBlog['cover_image']
            ];

            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . 'public/assets/uploads/blog/posts/';
                $this->ensureDirExists($uploadDir);
                
                $baseName = uniqid('post_') . '-' . pathinfo($_FILES['cover_image']['name'], PATHINFO_FILENAME);
                $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $data['cover_image'] = $baseName . '.' . $ext;
                
                $targetFile = $uploadDir . $data['cover_image'];
                
                if(move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetFile)) {
                    // NUEVO: Crear la miniatura correspondiente
                    $smallFile = $uploadDir . 'small_' . $baseName . '.webp';
                    $this->resizeToWebP($targetFile, $smallFile, 400);

                    // Borrar la imagen original anterior y su miniatura vieja si no es la default
                    if($currentBlog['cover_image'] !== 'default_blog.webp') {
                        $oldFile = $uploadDir . $currentBlog['cover_image'];
                        $oldBase = pathinfo($currentBlog['cover_image'], PATHINFO_FILENAME);
                        $oldSmall = $uploadDir . 'small_' . $oldBase . '.webp';
                        
                        if(file_exists($oldFile)) @unlink($oldFile);
                        if(file_exists($oldSmall)) @unlink($oldSmall);
                    }
                }
            }

            $this->blogModel->update($id, $data);
            $_SESSION['flash_message'] = '¡Artículo actualizado con éxito!';
            header('Location: /admin/blogs');
            exit;
        }
    }

    public function deleteBlog($id) {
        header('Content-Type: application/json');
        $blogId = (int)$id;
        
        $currentBlog = $this->blogModel->getById($blogId);
        // Borrar imagen física
        if($currentBlog && $currentBlog['cover_image'] !== 'default_blog.webp') {
            $uploadDir = ROOT_PATH . 'public/assets/uploads/blog/posts/';
            if(file_exists($uploadDir . $currentBlog['cover_image'])) {
                @unlink($uploadDir . $currentBlog['cover_image']);
            }
        }

        $success = $this->blogModel->delete($blogId);
        echo json_encode(['success' => $success, 'message' => $success ? 'Artículo eliminado.' : 'Error al eliminar.']);
        exit;
    }

//------------------------- ABOUT US ------------------------//


    public function showAbout() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'crud';
        $pageTitle = 'Gestión: Nosotros';
        $active_menu = 'pages-about'; 
        // Cargar datos actuales y pasarlos a la vista

        $bannerKey = 'about_banner';
        $aboutTitleKey = 'about_title';
        $aboutTextKey = 'about_text';
        $aboutImageKey = 'about_image';

        // Simplemente cargamos los datos. No se guarda nada aquí.
        $bannerImage = $this->siteContentModel->getContentByKey($bannerKey);
        $titleIds = $this->siteContentModel->getOrderedIdsByKey($aboutTitleKey) ?? [];
        $textIds = $this->siteContentModel->getOrderedIdsByKey($aboutTextKey) ?? [];
        $imageIds = $this->siteContentModel->getOrderedIdsByKey($aboutImageKey) ?? [];

        
        include ROOT_PATH . 'app/views/admin/admin-about.php';
    }

    public function updateAbout() {
        //-----------------------view------------------------//

        $activePage = 'about-us'; // Para resaltar el menú activo


        $bannerKey = 'about_banner';
        $aboutTitleKey = 'about_title';
        $aboutTextKey = 'about_text';
        $aboutImageKey = 'about_image';

        $bannerImage = $this->siteContentModel->getValue($bannerKey);
        $titleContent = $this->siteContentModel->getOrderedContentByPrefix($aboutTitleKey);
        $textContent = $this->siteContentModel->getOrderedContentByPrefix($aboutTextKey);
        $imageContent = $this->siteContentModel->getOrderedContentByPrefix($aboutImageKey);


        $aboutTitles = array_column($titleContent, 'content_value');
        $aboutTexts = array_column($textContent, 'content_value');
        $aboutImages = array_column($imageContent, 'content_value');

        //---------------------------------------------------//



        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages/about');
            exit;
        }

        $success = true; 
        $uploadDir = ROOT_PATH . 'public/assets/uploads/';

        // ====================================================
        // 1. BANNER PRINCIPAL (Slot 0)
        // ====================================================
        $bannerSavePath = $uploadDir . 'about-us/banner/';
        
        // Borrado preventivo para evitar duplicados
        if (!$this->siteContentModel->deleteContentByKey($bannerKey)) {
            $success = false;
        }

        $currentBannerName = $_POST['slot_image_id_0'] ?? null; 
        $deleteBanner = $_POST['delete_image_0'] ?? null;     
        $bannerFileKey = 'image_slot_0';                    

        // Lógica de subida/borrado idéntica a shipping
        if (isset($_FILES[$bannerFileKey]) && $_FILES[$bannerFileKey]['error'] === UPLOAD_ERR_OK) {
            $newFileName = $this->handleFileUpload($_FILES[$bannerFileKey], $bannerSavePath, 'banner');
            if ($newFileName) {
                if (!empty($currentBannerName)) @unlink($bannerSavePath . $currentBannerName);
                $this->siteContentModel->saveOrUpdateContent($bannerKey, $newFileName, 1);
            }
        }
        else if (!empty($deleteBanner) && !empty($currentBannerName)) {
            @unlink($bannerSavePath . $currentBannerName);
        }
        else if (empty($deleteBanner) && !empty($currentBannerName)) {
            $this->siteContentModel->saveOrUpdateContent($bannerKey, $currentBannerName, 1);
        }

        // ====================================================
        // 2. TEXTOS Y TÍTULOS (Del 0 al 10 para cubrir todas las secciones)
        // ====================================================
        $this->siteContentModel->deleteContentByKey($aboutTitleKey);
        $this->siteContentModel->deleteContentByKey($aboutTextKey);
        
        $titleOrder = 1;
        $textOrder = 1;

        // Aumentamos el límite a 10 para asegurar que guarde Objetivo, Misión, Visión, Valores, Equipo, etc.
        for ($i = 0; $i < 10; $i++) { 
            $titleContent = $_POST["{$aboutTitleKey}_{$i}"] ?? '';
            $textContent = $_POST["{$aboutTextKey}_{$i}"] ?? '';
            
            if (!empty($titleContent)) {
                $this->siteContentModel->saveOrUpdateContent($aboutTitleKey, $titleContent, $titleOrder++);
            }
            if (!empty($textContent)) {
                $this->siteContentModel->saveOrUpdateContent($aboutTextKey, $textContent, $textOrder++);
            }
        }

        // ====================================================
        // 3. IMÁGENES DEL CUERPO (Slots del 1 al 10)
        // ====================================================
        $imageSavePath = $uploadDir . 'about-us/body/';

        if (!$this->siteContentModel->deleteContentByKey($aboutImageKey)) {
             $success = false;
        }

        // Empezamos en 1 (el 0 es el banner) y vamos hasta 10
        for ($i = 1; $i < 10; $i++) {
            $slotOrder = $i;
            $imageFileKey = "image_slot_{$i}";
            $currentImageName = $_POST["slot_image_id_{$i}"] ?? null; 
            $deleteFlag = $_POST["delete_image_{$i}"] ?? null;       

            if (isset($_FILES[$imageFileKey]) && $_FILES[$imageFileKey]['error'] === UPLOAD_ERR_OK) {
                $fileName = $this->handleFileUpload($_FILES[$imageFileKey], $imageSavePath, 'about');
                
                if ($fileName) {
                    if (!empty($currentImageName)) @unlink($imageSavePath . $currentImageName);
                    $this->siteContentModel->saveOrUpdateContent($aboutImageKey, $fileName, $slotOrder);
                }
            }
            else if (!empty($deleteFlag) && !empty($currentImageName)) {
                @unlink($imageSavePath . $currentImageName);
            }
            else if (!empty($currentImageName)) {
                $this->siteContentModel->saveOrUpdateContent($aboutImageKey, $currentImageName, $slotOrder);
            }
        }

        $_SESSION['flash_message'] = 'Página de Nosotros actualizada correctamente.';
        header('Location: /admin/pages/about');
        exit;
    }

    public function showServices() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'crud';
        $pageTitle = 'Gestión: Servicios';
        $active_menu = 'pages-services'; 

        $bannerKey = 'service_banner';
        $serviceTitleKey = 'service_title';
        $serviceTextKey = 'service_text';
        $serviceLinkKey = 'service_link';
        $serviceImageKey = 'service_image';
        
        // NUEVA CLAVE PARA CLIENTES
        $serviceClientKey = 'service_client'; 

        // Cargar datos existentes
        $bannerImage = $this->siteContentModel->getContentByKey($bannerKey);
        $titleIds = $this->siteContentModel->getOrderedIdsByKey($serviceTitleKey) ?? [];
        $textIds = $this->siteContentModel->getOrderedIdsByKey($serviceTextKey) ?? [];
        $imageIds = $this->siteContentModel->getOrderedIdsByKey($serviceImageKey) ?? [];
        $serviceLink = $this->siteContentModel->getContentByKey($serviceLinkKey);

        // NUEVO: Cargar lista de clientes ordenada
        $clientsContent = $this->siteContentModel->getOrderedContentByPrefix($serviceClientKey);

        include ROOT_PATH . 'app/views/admin/admin-services.php';
    }

    public function updateServices() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages/services');
            exit;
        }

        $success = true; 
        $uploadDir = ROOT_PATH . 'public/assets/uploads/';

        $bannerKey = 'service_banner';
        $serviceTitleKey = 'service_title';
        $serviceTextKey = 'service_text';
        $serviceLinkKey = 'service_link';
        $serviceImageKey = 'service_image';
        
        // NUEVA CLAVE
        $serviceClientKey = 'service_client';

        // Rutas
        $bannerSavePath = $uploadDir . 'services/banner/';
        $bodySavePath = $uploadDir . 'services/body/';
        
        // 1. Borramos el registro del banner de la BD
        if (!$this->siteContentModel->deleteContentByKey($bannerKey)) { $success = false; }
        
        $i = 0;
        // Lógica Banner (Resumida para no repetir todo el texto anterior, mantén tu lógica actual aquí)
        $currentBannerName = $_POST["slot_image_id_$i"] ?? null; 
        $deleteBanner = $_POST["delete_image_$i"] ?? null;     
        $bannerFileKey = "image_slot_$i";                    

        if (isset($_FILES[$bannerFileKey]) && $_FILES[$bannerFileKey]['error'] === UPLOAD_ERR_OK) {
            $newFileName = $this->handleFileUpload($_FILES[$bannerFileKey], $bannerSavePath, 'banner');
            if ($newFileName) {
                if (!empty($currentBannerName)) @unlink($bannerSavePath . $currentBannerName);
                $this->siteContentModel->saveOrUpdateContent($bannerKey, $newFileName, 1);
            }
        } else if (empty($deleteBanner) && !empty($currentBannerName)) {
            $this->siteContentModel->saveOrUpdateContent($bannerKey, $currentBannerName, 1);
        } else if (!empty($deleteBanner) && !empty($currentBannerName)) {
            @unlink($bannerSavePath . $currentBannerName);
        }

        // ... (MANTENER LÓGICA DE TITULOS, TEXTOS Y LINK IGUAL) ...
        
        $this->siteContentModel->deleteContentByKey($serviceTitleKey);
        $this->siteContentModel->deleteContentByKey($serviceTextKey);
        $this->siteContentModel->deleteContentByKey($serviceLinkKey);

        $linkContent = $_POST["service_link"] ?? '';
        if (!empty($linkContent)) {
            $this->siteContentModel->saveOrUpdateContent($serviceLinkKey, $linkContent, 1);
        }

        $titleOrder = 1;
        $textOrder = 1;


        for ($i = 0; $i < 5; $i++) { 
            $titleContent = $_POST["{$serviceTitleKey}_{$i}"] ?? '';
            $textContent = $_POST["{$serviceTextKey}_{$i}"] ?? '';

            // Guardamos SIEMPRE (sin if !empty) para mantener el orden estricto de los índices
            // Índice 0 = Aliados
            // Índice 1, 2, 3 = Tarjetas
            // Índice 4 = Video
            $this->siteContentModel->saveOrUpdateContent($serviceTitleKey, $titleContent, $titleOrder++);
            $this->siteContentModel->saveOrUpdateContent($serviceTextKey, $textContent, $textOrder++);
        }

        // ... (MANTENER LÓGICA DE LAS 3 IMÁGENES FIJAS IGUAL: IDs 1, 2, 3) ...
        
        if (!$this->siteContentModel->deleteContentByKey($serviceImageKey)) { $success = false; }

        for ($i = 1; $i < 4; $i++) {
            $slotOrder = $i;
            $imageFileKey = "image_slot_{$i}";
            $currentImageName = $_POST["slot_image_id_{$i}"] ?? null; 
            $deleteFlag = $_POST["delete_image_{$i}"] ?? null;       

            if (isset($_FILES[$imageFileKey]) && $_FILES[$imageFileKey]['error'] === UPLOAD_ERR_OK) {
                $fileName = $this->handleFileUpload($_FILES[$imageFileKey], $bodySavePath, 'service'); // Cambié 'slider' por 'service' para orden
                if ($fileName) {
                    if (!empty($currentImageName)) @unlink($bodySavePath . $currentImageName);
                    $this->siteContentModel->saveOrUpdateContent($serviceImageKey, $fileName, $slotOrder);
                }
            }
            else if (!empty($deleteFlag) && !empty($currentImageName)) {
                @unlink($bodySavePath . $currentImageName);
            }
            else if (!empty($currentImageName)) {
                $this->siteContentModel->saveOrUpdateContent($serviceImageKey, $currentImageName, $slotOrder);
            }
        }

        // ====================================================
        // NUEVA LÓGICA: CLIENTES (ID 4 EN ADELANTE - DINÁMICO)
        // ====================================================
        
        // 1. Limpiamos la tabla para esta clave para reordenar
        $this->siteContentModel->deleteContentByKey($serviceClientKey);

        $clientDisplayOrder = 1; 
        
        // Iniciamos el bucle en 4 (porque 0 es banner, 1-3 son las cards fijas)
        // Llegamos hasta 200 para capturar todos los agregados dinámicamente
        for ($i = 4; $i < 200; $i++) {
            
            // Si no hay input de archivo ni ID oculto, saltamos
            if (!isset($_FILES["image_slot_$i"]) && !isset($_POST["slot_image_id_$i"])) {
                continue; 
            }

            $clientFileKey = "image_slot_$i";
            $currentClientName = $_POST["slot_image_id_$i"] ?? null;
            $deleteClient = $_POST["delete_image_$i"] ?? $_POST["delete_slot_$i"] ?? null;

            $finalFileNameToSave = null;

            // CASO A: Subida de nueva imagen
            if (isset($_FILES[$clientFileKey]) && $_FILES[$clientFileKey]['error'] === UPLOAD_ERR_OK) {
                // Guardamos en la misma carpeta services/body/
                $newFileName = $this->handleFileUpload($_FILES[$clientFileKey], $bodySavePath, 'client');
                if ($newFileName) {
                    if (!empty($currentClientName)) @unlink($bodySavePath . $currentClientName);
                    $finalFileNameToSave = $newFileName;
                }
            } 
            // CASO B: Mantener imagen existente
            elseif (!empty($currentClientName) && empty($deleteClient)) {
                $finalFileNameToSave = $currentClientName;
            } 
            // CASO C: Borrar imagen (físicamente)
            elseif (!empty($deleteClient) && !empty($currentClientName)) {
                @unlink($bodySavePath . $currentClientName);
                $finalFileNameToSave = null;
            }

            // Guardar en BD con orden secuencial
            if ($finalFileNameToSave) {
                $this->siteContentModel->saveOrUpdateContent($serviceClientKey, $finalFileNameToSave, $clientDisplayOrder++);
            }
        }

        $_SESSION['flash_message'] = 'Página de Servicios actualizada.';
        header('Location: /admin/pages/services');
        exit;
    }

    public function showShipping() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $pageType = 'crud';
        $pageTitle = 'Gestión: Envíos';
        $active_menu = 'pages-shipping'; 

        $bannerKey = 'shipping_banner';
        $shippingTitleKey = 'shipping_title';
        $shippingTextKey = 'shipping_text';
        $shippingImageKey = 'shipping_image';
        $shippingBrandKey = 'shipping_brand'; // Prefijo para marcas
        $shippingLinkkey = 'shipping_link';

        // 1. Cargar datos individuales
        $bannerImage = $this->siteContentModel->getContentByKey($bannerKey);
        $shippingImage = $this->siteContentModel->getContentByKey($shippingImageKey); // Imagen del Local
        $shippingLink = $this->siteContentModel->getContentByKey($shippingLinkkey);
        
        $titleIds = $this->siteContentModel->getOrderedIdsByKey($shippingTitleKey) ?? [];
        $textIds = $this->siteContentModel->getOrderedIdsByKey($shippingTextKey) ?? [];

        // 2. Cargar TODAS las marcas dinámicamente (sin limitar a 4)
        // Esto devuelve un array de filas de la BD ordenadas por display_order
        $brandContent = $this->siteContentModel->getOrderedContentByPrefix($shippingBrandKey);

        include ROOT_PATH . 'app/views/admin/admin-shipping.php';
    }

    public function updateShipping() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/pages/shipping');
            exit;
        }

        $success = true; 
        $uploadDir = ROOT_PATH . 'public/assets/uploads/';
        
        // Claves de BD
        $bannerKey = 'shipping_banner';
        $shippingImageKey = 'shipping_image'; // Local
        $shippingBrandKey = 'shipping_brand'; // Marcas

        // Rutas
        $bannerSavePath = $uploadDir . 'shipping/banner/';
        $bodySavePath = $uploadDir . 'shipping/body/';

        // ====================================================
        // 1. BANNER PRINCIPAL (ID 0)
        // ====================================================
        $this->siteContentModel->deleteContentByKey($bannerKey); // Limpiar previo para evitar duplicados
        
        $i = 0; // Slot 0
        $currentBannerName = $_POST["slot_image_id_$i"] ?? null; 
        $deleteBanner = $_POST["delete_image_$i"] ?? null;     
        $bannerFileKey = "image_slot_$i";                    

        if (isset($_FILES[$bannerFileKey]) && $_FILES[$bannerFileKey]['error'] === UPLOAD_ERR_OK) {
            $newFileName = $this->handleFileUpload($_FILES[$bannerFileKey], $bannerSavePath, 'banner');
            if ($newFileName) {
                if (!empty($currentBannerName)) @unlink($bannerSavePath . $currentBannerName);
                $this->siteContentModel->saveOrUpdateContent($bannerKey, $newFileName, 1);
            }
        } else if (empty($deleteBanner) && !empty($currentBannerName)) {
            $this->siteContentModel->saveOrUpdateContent($bannerKey, $currentBannerName, 1);
        } else if (!empty($deleteBanner) && !empty($currentBannerName)) {
            @unlink($bannerSavePath . $currentBannerName);
        }

        // ====================================================
        // 2. IMAGEN DEL LOCAL (ID 1)
        // ====================================================
        $this->siteContentModel->deleteContentByKey($shippingImageKey);

        $i = 1; // Slot 1
        $currentLocalName = $_POST["slot_image_id_$i"] ?? null; 
        $deleteLocal = $_POST["delete_image_$i"] ?? null;     
        $localFileKey = "image_slot_$i";

        if (isset($_FILES[$localFileKey]) && $_FILES[$localFileKey]['error'] === UPLOAD_ERR_OK) {
            $newFileName = $this->handleFileUpload($_FILES[$localFileKey], $bodySavePath, 'local');
            if ($newFileName) {
                if (!empty($currentLocalName)) @unlink($bodySavePath . $currentLocalName);
                $this->siteContentModel->saveOrUpdateContent($shippingImageKey, $newFileName, 1);
            }
        } else if (empty($deleteLocal) && !empty($currentLocalName)) {
            $this->siteContentModel->saveOrUpdateContent($shippingImageKey, $currentLocalName, 1);
        } else if (!empty($deleteLocal) && !empty($currentLocalName)) {
            @unlink($bodySavePath . $currentLocalName);
        }

        // ====================================================
        // 3. MARCAS (ID 2 EN ADELANTE - DINÁMICO)
        // ====================================================
        // Borramos las marcas existentes para reordenar limpiamente
        $this->siteContentModel->deleteContentByKey($shippingBrandKey);

        $brandDisplayOrder = 1; 
        
        // CORRECCIÓN: Aumentamos el límite del bucle de 50 a 200 por seguridad
        // para asegurar que capture inputs incluso si el índice sube mucho.
        for ($i = 2; $i < 200; $i++) {
            
            // Verificamos si existe algún input (Archivo o ID oculto)
            if (!isset($_FILES["image_slot_$i"]) && !isset($_POST["slot_image_id_$i"])) {
                continue; 
            }

            $brandFileKey = "image_slot_$i";
            $currentBrandName = $_POST["slot_image_id_$i"] ?? null;
            
            // Aceptamos ambas formas de flag de borrado (por si JS usa una u otra)
            $deleteBrand = $_POST["delete_image_$i"] ?? $_POST["delete_slot_$i"] ?? null;

            $finalFileNameToSave = null;

            // CASO A: Subida de nueva imagen
            if (isset($_FILES[$brandFileKey]) && $_FILES[$brandFileKey]['error'] === UPLOAD_ERR_OK) {
                $newFileName = $this->handleFileUpload($_FILES[$brandFileKey], $bodySavePath, 'brand');
                if ($newFileName) {
                    if (!empty($currentBrandName)) @unlink($bodySavePath . $currentBrandName);
                    $finalFileNameToSave = $newFileName;
                }
            } 
            // CASO B: Mantener imagen existente
            elseif (!empty($currentBrandName) && empty($deleteBrand)) {
                $finalFileNameToSave = $currentBrandName;
            } 
            // CASO C: Borrar imagen (físicamente)
            elseif (!empty($deleteBrand) && !empty($currentBrandName)) {
                @unlink($bodySavePath . $currentBrandName);
                $finalFileNameToSave = null;
            }

            // Guardar en BD con orden secuencial (1, 2, 3...)
            if ($finalFileNameToSave) {
                $this->siteContentModel->saveOrUpdateContent($shippingBrandKey, $finalFileNameToSave, $brandDisplayOrder++);
            }
        }


        // ====================================================
        // 4. TEXTOS Y LINK
        // ====================================================
        $shippingTitleKey = 'shipping_title';
        $shippingTextKey = 'shipping_text';
        $shippingLinkKey = 'shipping_link';

        $this->siteContentModel->deleteContentByKey($shippingTitleKey);
        $this->siteContentModel->deleteContentByKey($shippingTextKey);
        $this->siteContentModel->deleteContentByKey($shippingLinkKey);

        $linkContent = $_POST["shipping_link"] ?? '';
        if (!empty($linkContent)) {
            $this->siteContentModel->saveOrUpdateContent($shippingLinkKey, $linkContent, 1);
        }

        $titleOrder = 1;
        $textOrder = 1;

        // Loop para textos (0 = Envíos, 1 = Local)
        for ($i = 0; $i < 2; $i++) {
            $titleContent = $_POST["{$shippingTitleKey}_{$i}"] ?? '';
            $textContent = $_POST["{$shippingTextKey}_{$i}"] ?? '';

            if (!empty($titleContent)) {
                $this->siteContentModel->saveOrUpdateContent($shippingTitleKey, $titleContent, $titleOrder++);
            }
            if (!empty($textContent)) {
                $this->siteContentModel->saveOrUpdateContent($shippingTextKey, $textContent, $textOrder++);
            }
        }

        $_SESSION['flash_message'] = 'Página de Envíos actualizada correctamente.';
        header('Location: /admin/pages/shipping'); // O a /admin/pages/shipping
        exit;
    }

    
    // =================================================================
    // NOTAS DE RECIBO (RECEIPTS)
    // =================================================================


    // 1. Vista de la lista
    public function receipts() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        
        $active_menu = 'receipts';
        $pageTitle = 'Notas de Recibo';
        $pageType = 'basic_list'; // CAMBIO IMPORTANTE: Cambiamos a 'filtered_list' para que salga el HTML de filtros

        // RECOGER FILTROS DE LA URL
        $filters = [
            'search' => $_GET['search'] ?? null,
            'sort'   => $_GET['sort'] ?? null,
            'status' => $_GET['status'] ?? null
        ];

        // PASAR LOS FILTROS AL MODELO
        $receipts = $this->receiptModel->getAll($filters);

        include ROOT_PATH . 'app/views/admin/receipts.php';
    }

    // 2. Vista y Guardado de Configuración (Todo en uno)
    public function settingsReceipts() {
        
        // --- 1. LÓGICA DE GUARDADO (Se activa al presionar el botón) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST)) {
                // Guardamos cada campo del formulario en la base de datos
                foreach ($_POST as $key => $value) {
                    $this->receiptModel->updateSetting($key, $value);
                }
                // Preparamos el mensaje verde de éxito
                $_SESSION['flash_message'] = '¡Configuración guardada con éxito!';
            }
            // Recargamos la página limpiamente
            header('Location: /admin/receipts/settings');
            exit;
        }

        // --- 2. LÓGICA PARA MOSTRAR LA VISTA ---
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        
        $active_menu = 'receipts-settings';
        $pageTitle = 'Configuración de Notas';
        
        // Mantenemos 'crud' para que el footer inyecte tu botón "Guardar" y cierre el form
        $pageType = 'crud'; 

        // Obtenemos los datos actualizados de la base de datos
        $settings = $this->receiptModel->getSettings();

        include ROOT_PATH . 'app/views/admin/settings-receipts.php';
    }

    // 4. Vista para Crear Nota
    public function createReceipt() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        
        $active_menu = 'receipts';
        $pageTitle = 'Generar Nota de Entrega';
        $pageType = 'crud';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');

        $clientsListRaw = $this->clientModel->getAll();
        usort($clientsListRaw, function($a, $b) {
            return strcasecmp($a['client_name'], $b['client_name']);
        });
        $clientsList = $clientsListRaw;

        $systemUsers = $this->userModel->getAll([]); // Obtenemos todos los usuarios del sistema

        include ROOT_PATH . 'app/views/admin/create-receipt.php';
    }

    public function storeReceipt() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');

            $settings = $this->receiptModel->getSettings();

            $prefix = $settings['prefix'] ?? 'NOTA-';
            $receiptCode = $_POST['receipt_code'] ?? ($prefix . date('Ymd-His'));
            $accessToken = bin2hex(random_bytes(16));

            $unds = $_POST['und'] ?? [];
            $models = $_POST['model'] ?? [];
            $serials = $_POST['serial'] ?? [];
            $accessories = $_POST['accessories'] ?? [];
            $details = $_POST['details'] ?? [];
            $features = $_POST['features'] ?? [];
            $items = [];
            for ($i = 0; $i < count($unds); $i++) {
                $items[] = [
                    'und' => $unds[$i], 'model' => $models[$i], 'serial' => $serials[$i],
                    'accessories' => $accessories[$i], 'details' => $details[$i], 'features' => $features[$i]
                ];
            }
            $itemsJson = json_encode($items);
            
            
            $budgetDescriptions = $_POST['description'] ?? [];
            $budgetAmounts = $_POST['amount'] ?? [];
            $budgetItems = [];
            for ($i = 0; $i < count($budgetDescriptions); $i++) {
                if (!empty(trim($budgetDescriptions[$i])) || !empty(trim($budgetAmounts[$i]))) {
                    $budgetItems[] = ['description' => $budgetDescriptions[$i], 'amount' => $budgetAmounts[$i]];
                }
            }

            // INICIO DE NUEVA LÓGICA: Presupuesto Tentativo
            $tentativeDescriptions = $_POST['tentative_description'] ?? [];
            $tentativeAmounts = $_POST['tentative_amount'] ?? [];
            $tentativeItems = [];
            for ($i = 0; $i < count($tentativeDescriptions); $i++) {
                if (!empty(trim($tentativeDescriptions[$i])) || !empty(trim($tentativeAmounts[$i]))) {
                    $tentativeItems[] = ['description' => $tentativeDescriptions[$i], 'amount' => $tentativeAmounts[$i]];
                }
            }

            $budgetData = [
                'tentative_items' => $tentativeItems,
                'tentative_final_amount' => trim($_POST['tentative_final_amount'] ?? ''),
                'items' => $budgetItems,
                'final_amount' => $_POST['final_amount'] ?? '00.00'
            ];
            $budgetJson = json_encode($budgetData);
            // FIN DE NUEVA LÓGICA
            
            // --- LIMPIEZA DE DATOS (DNI según dominio) ---
            $isPeru = strpos($_SERVER['HTTP_HOST'], 'corporaciongruponet.com') !== false;
            if ($isPeru) {
                // En Perú guardamos el documento directo (DNI/RUC/CE)
                $clientIdNumber = substr(trim($_POST['client_id_digits'] ?? ''), 0, 20);
            } else {
                // En Venezuela unimos V/J/E/P + números
                $prefixDni = $_POST['client_id_prefix'] ?? 'V';
                $digits = preg_replace('/[^0-9]/', '', $_POST['client_id_digits'] ?? '');
                $clientIdNumber = substr($prefixDni . $digits, 0, 20);
            }

            $clientNameRaw = preg_replace('/\s+/', ' ', trim($_POST['client_name'] ?? ''));
            $clientName = mb_convert_case($clientNameRaw, MB_CASE_TITLE, "UTF-8");

            $this->receiptModel->saveClient($clientIdNumber, $clientName);
            $driveFolderName = $clientIdNumber . ' - ' . $clientName;
            
            $budget = $budgetItems; 

            
            $dataForPdf = [
                'receipt_code' => $receiptCode,
                'access_token' => $accessToken,
                'client_name' => $clientName,
                'client_id_number' => $clientIdNumber,
                'client_phone' => $_POST['client_phone'] ?? '',
                'client_email' => $_POST['client_email'] ?? '',
                'client_address' => $_POST['client_address'] ?? '',
                'notes' => $_POST['notes'] ?? '',
                'created_at' => date('Y-m-d H:i:s'), // Hora exacta VZLA
                'updated_at' => date('Y-m-d H:i:s'),
                'private_notes' => $_POST['private_notes'] ?? '',
                'technician_name' => $_POST['technician_name'] ?? '',
                'created_by' => $_SESSION['idUser'] ?? null,
                'updated_by' => $_SESSION['idUser'] ?? null // Al crearse, es el mismo
            ];

            require_once ROOT_PATH . 'vendor/autoload.php';
            $dompdf = new \Dompdf\Dompdf();
            ob_start();
            $data = $dataForPdf;
            $moneySymbol = $this->siteContentModel->getValue('money_symbol');
            include ROOT_PATH . 'app/views/admin/receipt-pdf-clean.php'; 
            $html = ob_get_clean();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            $preuploadedInitial = $_POST['preuploaded_initial'] ?? [];
            $preuploadedProcess = $_POST['preuploaded_process'] ?? [];

            // Enviar PDF a Drive
            $driveFiles = $this->uploadReceiptToDrive($driveFolderName, $receiptCode, [], [], [], $pdfOutput, null);

            // --- CORRECCIÓN DEFINITIVA DE RECONEXIÓN MYSQL (ACTUALIZADA PARA PHP MODERNOS) ---
            $db_alive = false;
            if ($this->db) {
                try {
                    $db_alive = @$this->db->ping();
                } catch (\mysqli_sql_exception $e) {
                    // Atrapamos la excepción silenciosamente. El servidor se ha desconectado.
                    $db_alive = false; 
                }
            }

            // Si MySQL cerró la conexión por inactividad durante la subida a Drive, reconectamos de forma segura
            if (!$db_alive) {
                @$this->db->close();
                include ROOT_PATH . 'app/config/db_connection.php';
                if (isset($connection) && $connection instanceof \mysqli && !$connection->connect_error) {
                    $this->db = $connection;
                }
            }
            // Refrescamos siempre el modelo con la conexión activa
            $this->receiptModel = new Receipt($this->db);
            // -------------------------------------------------


            // CORRECCIÓN: Ordenamos y aseguramos todas las imágenes en la creación
            $sortedInitial = $this->formatSortedImages($_POST['initial_image_order'] ?? '[]', [], $preuploadedInitial);
            $sortedProcess = $this->formatSortedImages($_POST['process_image_order'] ?? '[]', [], $preuploadedProcess);

            // Respaldo de teléfono por si el input asíncrono no envió el campo oculto
            $clientPhone = !empty($_POST['client_phone']) ? trim($_POST['client_phone']) : trim($_POST['phone_input_raw'] ?? '');

            $dataForPdf['client_phone'] = $clientPhone;
            $dataForPdf['items_json'] = $itemsJson;
            $dataForPdf['budget_json'] = $budgetJson;
            $dataForPdf['images_json'] = json_encode($sortedInitial);
            $dataForPdf['process_images_json'] = json_encode($sortedProcess);
            $dataForPdf['pdf_filename'] = $driveFiles['pdf'] ?? '';

            $receiptId = $this->receiptModel->create($dataForPdf);

            // Si la petición viene vía AJAX/Fetch desde el widget flotante, devolvemos JSON real en vez de redirigir
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                if ($receiptId) {
                    echo json_encode(['success' => true, 'receipt_id' => $receiptId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error de MySQL al insertar: ' . $this->db->error]);
                }
                exit;
            }

            $_SESSION['flash_message'] = $receiptId ? 'Nota guardada en Drive exitosamente.' : 'Error al guardar la nota.';
            header('Location: /admin/receipts');
            exit;
        }
    }                                            




    public function viewReceiptPdf($id) {
        $receipt = $this->receiptModel->getById($id);

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');
        
        if ($receipt) {
            $receipt = $this->autoSyncReceiptImages($receipt);
            // Preparamos los datos para que la vista los lea
            
            $settings = $this->receiptModel->getSettings();
            $items = json_decode($receipt['items_json'], true);

            $budgetRaw = json_decode($receipt['budget_json'] ?? '[]', true) ?: []; 
            if (isset($budgetRaw['items'])) {
                $budget = $budgetRaw['items'];
                $receipt['final_amount'] = $budgetRaw['final_amount'] ?? '00.00';
                $receipt['tentative_items'] = $budgetRaw['tentative_items'] ?? [];
                $receipt['tentative_final_amount'] = $budgetRaw['tentative_final_amount'] ?? '';
            } else {
                $budget = $budgetRaw;
                $receipt['final_amount'] = '00.00';
                $receipt['tentative_items'] = [];
                $receipt['tentative_final_amount'] = '';
            }
            $data = $receipt;
            
            // Incluimos el archivo visual HTML
            include ROOT_PATH . 'app/views/admin/receipt-pdf.php';
        } else {
            echo "La nota de entrega no existe o fue eliminada.";
        }

    }


    // Vista para Editar Nota
    public function editReceipt($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        
        $active_menu = 'receipts';
        $pageTitle = 'Editar Nota de Entrega';
        $pageType = 'crud';

        $moneySymbol = $this->siteContentModel->getValue('money_symbol');

        $clientsListRaw = $this->clientModel->getAll();
        usort($clientsListRaw, function($a, $b) {
            return strcasecmp($a['client_name'], $b['client_name']);
        });
        $clientsList = $clientsListRaw;

        $isPeru = strpos($_SERVER['HTTP_HOST'], 'corporaciongruponet.com') !== false;

        // Obtenemos la nota específica por su ID
        $receipt = $this->receiptModel->getById((int)$id);

        if (!$receipt) {
            header('Location: /admin/receipts');
            exit;
        }

        if ($receipt['is_finished'] == 1) {
            $_SESSION['flash_message'] = 'Esta nota de entrega está finalizada y no puede ser editada.';
            header('Location: /admin/receipts');
            exit;
        }
        

        $receipt = $this->autoSyncReceiptImages($receipt);
        // Decodificamos los items para enviarlos a la vista como un arreglo
        $items = json_decode($receipt['items_json'], true);

        $budgetRaw = json_decode($receipt['budget_json'] ?? '[]', true) ?: [];
        if (isset($budgetRaw['items'])) {
            $budget = $budgetRaw['items'];
            $finalAmount = $budgetRaw['final_amount'] ?? '00.00';
            $tentativeBudget = $budgetRaw['tentative_items'] ?? [];
            $tentativeFinalAmount = $budgetRaw['tentative_final_amount'] ?? '';
        } else {
            $budget = $budgetRaw;
            $finalAmount = '00.00';
            $tentativeBudget = [];
            $tentativeFinalAmount = '';
        }

        $systemUsers = $this->userModel->getAll([]);
        // Buscamos quién creó y quién actualizó la nota (si existen)
        $creator = !empty($receipt['created_by']) ? $this->userModel->getById($receipt['created_by']) : null;
        $updater = !empty($receipt['updated_by']) ? $this->userModel->getById($receipt['updated_by']) : null;


        include ROOT_PATH . 'app/views/admin/edit-receipt.php';
    }

    
    public function updateReceipt() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '300');

            $id = (int)$_POST['id_receipt'];
            $currentReceipt = $this->receiptModel->getById($id);


            if ($currentReceipt['is_finished'] == 1) {
                $_SESSION['flash_message'] = 'Error: No se puede modificar una nota finalizada.';
                header('Location: /admin/receipts');
                exit;
            }

            $unds = $_POST['und'] ?? [];
            $models = $_POST['model'] ?? [];
            $serials = $_POST['serial'] ?? [];
            $accessories = $_POST['accessories'] ?? [];
            $details = $_POST['details'] ?? [];
            $features = $_POST['features'] ?? [];
            $items = [];
            for ($i = 0; $i < count($unds); $i++) {
                $items[] = [
                    'und' => $unds[$i], 'model' => $models[$i], 'serial' => $serials[$i],
                    'accessories' => $accessories[$i], 'details' => $details[$i], 'features' => $features[$i]
                ];
            }
            $itemsJson = json_encode($items);



            $budgetDescriptions = $_POST['description'] ?? [];
            $budgetAmounts = $_POST['amount'] ?? [];
            $budgetItems = [];
            for ($i = 0; $i < count($budgetDescriptions); $i++) {
                if (!empty(trim($budgetDescriptions[$i])) || !empty(trim($budgetAmounts[$i]))) {
                    $budgetItems[] = ['description' => $budgetDescriptions[$i], 'amount' => $budgetAmounts[$i]];
                }
            }

            // INICIO DE NUEVA LÓGICA: Presupuesto Tentativo
            $tentativeDescriptions = $_POST['tentative_description'] ?? [];
            $tentativeAmounts = $_POST['tentative_amount'] ?? [];
            $tentativeItems = [];
            for ($i = 0; $i < count($tentativeDescriptions); $i++) {
                if (!empty(trim($tentativeDescriptions[$i])) || !empty(trim($tentativeAmounts[$i]))) {
                    $tentativeItems[] = ['description' => $tentativeDescriptions[$i], 'amount' => $tentativeAmounts[$i]];
                }
            }

            $budgetData = [
                'tentative_items' => $tentativeItems,
                'tentative_final_amount' => trim($_POST['tentative_final_amount'] ?? ''),
                'items' => $budgetItems,
                'final_amount' => $_POST['final_amount'] ?? '00.00'
            ];
            $budgetJson = json_encode($budgetData);
            // FIN DE NUEVA LÓGICA

            // --- LIMPIEZA DE DATOS ACTUALIZADOS (DNI según dominio) ---
            $isPeru = strpos($_SERVER['HTTP_HOST'], 'corporaciongruponet.com') !== false;
            if ($isPeru) {
                $rawDigits = trim($_POST['client_id_digits'] ?? '');
                $clientIdNumber = !empty($rawDigits) ? substr($rawDigits, 0, 20) : $currentReceipt['client_id_number'];
            } else {
                $prefixDni = $_POST['client_id_prefix'] ?? 'V';
                $digits = preg_replace('/[^0-9]/', '', $_POST['client_id_digits'] ?? '');
                $clientIdNumber = !empty($digits) ? substr($prefixDni . $digits, 0, 20) : $currentReceipt['client_id_number'];
            }

            // 🛡️ BLINDAJE 1: Si el documento llega vacío o es solo una letra, restaurar el original
            if (empty($clientIdNumber) || in_array($clientIdNumber, ['V', 'E', 'J', 'P', 'G'])) {
                $clientIdNumber = $currentReceipt['client_id_number'];
            }

            $clientNameRaw = preg_replace('/\s+/', ' ', trim($_POST['client_name'] ?? ''));
            $clientName = mb_convert_case($clientNameRaw, MB_CASE_TITLE, "UTF-8");

            // 🛡️ BLINDAJE 2: Si el nombre llega vacío, restaurar el original
            if (empty($clientName)) {
                $clientName = $currentReceipt['client_name'];
            }
            
            $this->receiptModel->saveClient($clientIdNumber, $clientName);
            
            $oldClientNameRaw = preg_replace('/\s+/', ' ', trim($currentReceipt['client_name']));
            $oldClientName = mb_convert_case($oldClientNameRaw, MB_CASE_TITLE, "UTF-8");
            
            $driveFolderName = $clientIdNumber . ' - ' . $clientName;
            $oldDriveFolderName = $currentReceipt['client_id_number'] . ' - ' . $oldClientName;

            $receiptCode = $currentReceipt['receipt_code'];
            $budget = $budgetItems;

            // 🛡️ BLINDAJE 3: Restaurar contacto si llega vacío
            $clientPhone = !empty($_POST['client_phone']) ? trim($_POST['client_phone']) : trim($_POST['phone_input_raw'] ?? '');
            if (empty($clientPhone)) $clientPhone = $currentReceipt['client_phone'];

            $clientEmail = !empty($_POST['client_email']) ? trim($_POST['client_email']) : $currentReceipt['client_email'];
            $clientAddress = !empty($_POST['client_address']) ? trim($_POST['client_address']) : $currentReceipt['client_address'];

            $dataForPdf = [
                'receipt_code' => $receiptCode,
                'client_name' => $clientName,
                'client_id_number' => $clientIdNumber,
                'client_phone' => $clientPhone,
                'client_email' => $clientEmail,
                'client_address' => $clientAddress,
                'notes' => $_POST['notes'] ?? '',
                'created_at' => $currentReceipt['created_at'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            require_once ROOT_PATH . 'vendor/autoload.php';
            $dompdf = new \Dompdf\Dompdf();
            ob_start();
            $data = $dataForPdf; $settings = $this->receiptModel->getSettings(); 
            $moneySymbol = $this->siteContentModel->getValue('money_symbol');
            include ROOT_PATH . 'app/views/admin/receipt-pdf-clean.php'; 
            $html = ob_get_clean();
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfOutput = $dompdf->output();

            $linksToDelete = [];
            if (!empty($currentReceipt['pdf_filename'])) {
                $linksToDelete[] = $currentReceipt['pdf_filename'];
            }
            $deletedInitial = $_POST['delete_receipt_images'] ?? [];
            $deletedProcess = $_POST['delete_process_images'] ?? [];
            $linksToDelete = array_merge($linksToDelete, $deletedInitial, $deletedProcess);

            $preuploadedInitial = $_POST['preuploaded_initial'] ?? [];
            $preuploadedProcess = $_POST['preuploaded_process'] ?? [];

            $driveFiles = $this->uploadReceiptToDrive(
                $driveFolderName, $receiptCode, [], [], $linksToDelete, $pdfOutput, $oldDriveFolderName 
            );

            @$this->db->close(); 
            include ROOT_PATH . 'app/config/db_connection.php';
            if (isset($connection)) {
                $this->db = $connection;
                $this->receiptModel = new Receipt($this->db);
            }

            $existingInitial = json_decode($currentReceipt['images_json'] ?? '[]', true) ?: [];
            $existingInitial = array_diff($existingInitial, $deletedInitial); 
            $sortedInitial = $this->formatSortedImages($_POST['initial_image_order'] ?? '[]', $existingInitial, $preuploadedInitial);

            $existingProcess = json_decode($currentReceipt['process_images_json'] ?? '[]', true);
            $existingProcess = array_diff($existingProcess, $deletedProcess);
            $sortedProcess = $this->formatSortedImages($_POST['process_image_order'] ?? '[]', $existingProcess, $preuploadedProcess);

            $updateData = [
                'client_name' => $dataForPdf['client_name'],
                'client_id_number' => $dataForPdf['client_id_number'],
                'client_phone' => $dataForPdf['client_phone'],
                'client_email' => $dataForPdf['client_email'],
                'client_address' => $dataForPdf['client_address'],
                'items_json' => $itemsJson,
                'budget_json' => $budgetJson,
                'notes' => $dataForPdf['notes'],
                'sent_email' => 0,       
                'sent_whatsapp' => 0,
                'images_json' => json_encode($sortedInitial),
                'process_images_json' => json_encode($sortedProcess),
                'pdf_filename' => $driveFiles['pdf'] ?? $currentReceipt['pdf_filename'],
                'private_notes' => $_POST['private_notes'] ?? '',
                'technician_name' => $_POST['technician_name'] ?? '',
                'updated_by' => $_SESSION['idUser'] ?? null,
            ];

            $success = $this->receiptModel->update($id, $updateData);
            $_SESSION['flash_message'] = $success ? 'Nota actualizada en Drive con éxito.' : 'Error al actualizar la nota.';
            header('Location: /admin/receipts');
            exit;
        }
    }

    // Helper para ordenar imágenes asíncronas respetando el Drag & Drop
    private function formatSortedImages($orderJson, $existingLinks, $preuploadedLinks) {
        $orderArray = json_decode($orderJson, true);
        if (!is_array($orderArray) || empty($orderArray)) {
            return array_merge(array_values($existingLinks), $preuploadedLinks);
        }

        $sorted = [];
        $newIndex = 0;
        foreach ($orderArray as $item) {
            if (strpos($item, 'existing_') === 0) {
                $link = substr($item, 9);
                if (in_array($link, $existingLinks)) {
                    $sorted[] = $link;
                }
            } elseif (strpos($item, 'new_') === 0) {
                if (isset($preuploadedLinks[$newIndex])) {
                    $sorted[] = $preuploadedLinks[$newIndex++];
                }
            }
        }
        
        while ($newIndex < count($preuploadedLinks)) {
            $sorted[] = $preuploadedLinks[$newIndex++];
        }
        return $sorted;
    }

    public function deleteReceipt($id) {
        header('Content-Type: application/json');
        
        $receipt = $this->receiptModel->getById($id);
        if ($receipt) {
            // 🛡️ AHORA ENVIAMOS EL NOMBRE DEL CLIENTE PARA EVITAR "Sin_nombre"
            $webhookUrl = $this->getDriveConfig('api_receipts');
            $clientNameFolder = $receipt['client_id_number'] . ' - ' . $receipt['client_name'];

            $payload = [
                'action' => 'deleteReceipt',
                'receiptCode' => $receipt['receipt_code'],
                'clientName' => $clientNameFolder 
            ];
            
            $options = [
                'http' => [
                    'header'  => "Content-Type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($payload),
                    'timeout' => 30
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ];
            $context = stream_context_create($options);
            @file_get_contents($webhookUrl, false, $context);

            if (!empty($receipt['pdf_filename'])) {
                $pdfPath = ROOT_PATH . 'public/assets/uploads/invoices/' . $receipt['pdf_filename'];
                if (file_exists($pdfPath)) @unlink($pdfPath);
            }
        }

        $success = $this->receiptModel->delete($id);
        echo json_encode(['success' => $success, 'message' => $success ? 'Nota eliminada de Base de Datos y de Drive correctamente.' : 'Error al eliminar.']);
        exit;
    }


    public function toggleReceiptStatus() {
        header('Content-Type: application/json');
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        
        if (isset($jsonInput['id']) && isset($jsonInput['status'])) {
            $id = (int)$jsonInput['id'];
            $status = (int)$jsonInput['status'];
            
            $success = $this->receiptModel->updateFinishedStatus($id, $status);
            
            // --- NUEVO: Setear el mensaje flash si fue exitoso ---
            if ($success) {
                if ($status == 1) {
                    $_SESSION['flash_message'] = 'Nota finalizada exitosamente.';
                } else {
                    $_SESSION['flash_message'] = 'Nota reabierta exitosamente.';
                }
            }
            
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        }
        exit;
    }



    public function sendPdfEmail() {
        ob_start(); // Iniciamos buffer seguro
        header('Content-Type: application/json; charset=utf-8'); 

        try {
            $jsonInput = json_decode(file_get_contents('php://input'), true);

            if (!$jsonInput || !isset($jsonInput['id'])) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Faltan datos para enviar.']);
                exit;
            }

            $id = $jsonInput['id'];
            $customTo = $jsonInput['to_email'] ?? '';
            $customSubject = $jsonInput['subject'] ?? '';
            $customMessageBody = $jsonInput['message'] ?? '';

            $receipt = $this->receiptModel->getById($id);
            $settings = $this->receiptModel->getSettings();

            if (!$receipt) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'La nota no existe.']);
                exit;
            }

            $to = !empty($customTo) ? $customTo : $receipt['client_email'];
            $subject = !empty($customSubject) ? $customSubject : "Nota de Entrega - " . $receipt['receipt_code'];
            $fromName = $settings['company_name'] ?? '';
            
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                ob_clean();
                echo json_encode(['success' => false, 'message' => 'Librería PHPMailer no encontrada.']);
                exit;
            }

            $mail = new PHPMailer(true);
            $host = $_SERVER['SMTP_HOST'] ?? $_ENV['SMTP_HOST'] ?? '';
            $user = $_SERVER['SMTP_USER'] ?? $_ENV['SMTP_USER'] ?? '';
            $pass = trim($_SERVER['SMTP_PASS'] ?? $_ENV['SMTP_PASS'] ?? '', '"\'');
            $port = $_SERVER['SMTP_PORT'] ?? $_ENV['SMTP_PORT'] ?? 465;

            $mail->isSMTP();                                            
            $mail->Host       = $host; 
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = $user; 
            $mail->Password   = $pass;      
            $mail->SMTPSecure = ($port == 587) ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS; 
            $mail->Port       = $port;                         
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($user, $fromName);
            $mail->addAddress($to, $receipt['client_name']);
            // --- AGREGA ESTAS LÍNEAS ---
            // Verifica que exista un correo configurado para la empresa y envía una copia oculta
            if (!empty($settings['company_email'])) {
                $mail->addBCC($settings['company_email'], 'Copia Sistema - ' . $fromName);
            }

            // En lugar del ID, usamos el access_token para el link
            $token = $receipt['access_token'];
            // Si es una nota vieja que no tiene token, por seguridad no enviamos nada o generamos uno (fallback)
            if (empty($token)) {
                echo json_encode(['success' => false, 'message' => 'Esta nota antigua no tiene token de seguridad. Créela de nuevo.']);
                exit;
            }

            $linkUrl = "https://" . htmlspecialchars($_SERVER['HTTP_HOST']) . "/nota/" . $token;

            $htmlBody = "<div style='font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>";
            $htmlBody .= "<h2 style='color: #198754; text-align: center; border-bottom: 2px solid #198754; padding-bottom: 10px;'>Su nota de recibo está lista</h2>";
            $htmlBody .= "<p style='font-size: 16px;'>" . nl2br(htmlspecialchars($customMessageBody)) . "</p>";
            $htmlBody .= "<div style='text-align: center; margin: 40px 0;'>";
            $htmlBody .= "<a href='{$linkUrl}' style='background-color: #198754; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px; display: inline-block;'>Ver / Descargar Recibo</a>";
            $htmlBody .= "</div>";
            $htmlBody .= "<p style='font-size: 12px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 20px;'>Si el botón no funciona, copie y pegue el siguiente enlace en su navegador:<br><a href='{$linkUrl}' style='color:#198754;'>{$linkUrl}</a></p>";
            $htmlBody .= "</div>";

            $mail->isHTML(true);                                  
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = strip_tags($customMessageBody) . "\n\nEnlace a su recibo: " . $linkUrl;

            $mail->send();
            
            // Actualizamos el estado en la BD [cite: 1]
            $this->receiptModel->updateSentStatus($id, 'sent_email', 1);
            
            ob_clean(); // Limpiamos la pantalla por si el servidor imprimió algo raro
            echo json_encode(['success' => true, 'message' => '¡Enlace enviado con éxito al cliente!']);

            
        } catch (\Throwable $e) { 
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
        }
        exit;
    }

    public function markReceiptWhatsappSent() {
        header('Content-Type: application/json');
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        
        if (isset($jsonInput['id'])) {
            $this->receiptModel->updateSentStatus($jsonInput['id'], 'sent_whatsapp', 1);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    private function processDynamicReceiptImages($inputName, $orderInputName, $deleteInputName, $existingJson) {
        $targetDir = ROOT_PATH . "public/assets/uploads/receipts/";
        $existingImages = json_decode($existingJson, true) ?: [];

        // 1. Borrar las imágenes que el usuario quitó
        $deleteArray = $_POST[$deleteInputName] ?? [];
        if (!empty($deleteArray)) {
            foreach ($deleteArray as $imgToDelete) {
                $key = array_search($imgToDelete, $existingImages);
                if ($key !== false) {
                    unset($existingImages[$key]);
                    if (file_exists($targetDir . $imgToDelete)) unlink($targetDir . $imgToDelete);
                }
            }
            $existingImages = array_values($existingImages);
        }

        // 2. Subir archivos nuevos y guardarlos en un mapa
        $newUploadedFilesMap = [];
        if (!empty($_FILES[$inputName]['name'][0])) {
            $totalFiles = count($_FILES[$inputName]['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES[$inputName]['error'][$i] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES[$inputName]['name'][$i];
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newFileName = time() . '_' . uniqid() . '.' . $ext;
                    
                    if (move_uploaded_file($_FILES[$inputName]['tmp_name'][$i], $targetDir . $newFileName)) {
                        $newUploadedFilesMap[$originalName] = $newFileName;
                    }
                }
            }
        }

        // 3. Reorganizar usando el JSON generado por el Drag & Drop
        $orderArray = json_decode($_POST[$orderInputName] ?? '[]', true);
        $finalSortedImages = [];

        if (is_array($orderArray) && !empty($orderArray)) {
            foreach ($orderArray as $item) {
                if (strpos($item, 'existing_') === 0) {
                    $origName = str_replace('existing_', '', $item);
                    if (in_array($origName, $existingImages)) $finalSortedImages[] = $origName;
                } else if (strpos($item, 'new_') === 0) {
                    $origName = str_replace('new_', '', $item);
                    if (isset($newUploadedFilesMap[$origName])) $finalSortedImages[] = $newUploadedFilesMap[$origName];
                }
            }
        } else {
            // Salvaguarda: si falla el Drag & Drop, las adjunta al final
            $finalSortedImages = array_merge($existingImages, array_values($newUploadedFilesMap));
        }

        return $finalSortedImages;
    }

    private function uploadReceiptToDrive($clientName, $receiptCode, $initialFiles, $processFiles, $deletedDriveLinks = [], $pdfData = null, $oldClientName = null) {
        $webhookUrl = $this->getDriveConfig('api_receipts');
        $payload = [
            'clientName' => $clientName,
            'oldClientName' => $oldClientName, 
            'receiptCode' => $receiptCode,
            'deleteFileIds' => [],
            'files' => []
        ];
        
        foreach ($deletedDriveLinks as $link) {
            if (preg_match('/id=([^&]+)/', $link, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $link, $matches)) {
                $payload['deleteFileIds'][] = $matches[1];
            }
        }

        if ($pdfData !== null) {
            $payload['files'][] = [
                'fileName' => $receiptCode . '.pdf',
                'mimeType' => 'application/pdf',
                'base64' => base64_encode($pdfData),
                'folderType' => 'pdf'
            ];
        }

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'follow_location' => 1,
                'ignore_errors' => true,
                'timeout' => 60
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ];
        $context  = stream_context_create($options);
        $response = file_get_contents($webhookUrl, false, $context);
        $result = json_decode($response, true);
        $driveLinks = ['iniciales' => [], 'proceso' => [], 'pdf' => ''];
        
        if (isset($result['status']) && $result['status'] === 'success') {
            foreach ($result['files'] as $file) {
                if ($file['folderType'] === 'pdf') {
                    $driveLinks['pdf'] = $file['viewLink'];
                }
            }
        }
        return $driveLinks;
    }

    public function checkClientAjax() {
        $dni = $_GET['dni'] ?? '';
        $dni = preg_replace('/[^a-zA-Z0-9]/', '', $dni);
        if (!empty($dni)) {
            $client = $this->receiptModel->getClientByDni($dni);
            if ($client) {
                echo json_encode(['success' => true, 'name' => $client['client_name']]);
                exit;
            }
        }
        echo json_encode(['success' => false]);
        exit;
    }


    public function uploadAsyncImage() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $clientIdNumber = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['client_id_number'] ?? '');
            $clientIdNumber = substr($clientIdNumber, 0, 20);

            $clientNameRaw = preg_replace('/\s+/', ' ', trim($_POST['clientName'] ?? ''));
            $clientName = mb_convert_case($clientNameRaw, MB_CASE_TITLE, "UTF-8");
            
            $driveFolderName = $clientIdNumber . ' - ' . $clientName;
            
            $oldClientNameRaw = preg_replace('/\s+/', ' ', trim($_POST['oldClientName'] ?? ''));
            $oldClientName = mb_convert_case($oldClientNameRaw, MB_CASE_TITLE, "UTF-8");
            $oldDriveFolderName = (!empty($_POST['old_client_id_number'])) ? 
                (preg_replace('/[^a-zA-Z0-9]/', '', $_POST['old_client_id_number']) . ' - ' . $oldClientName) : null;
            
            $receiptCode = $_POST['receiptCode'] ?? 'Sin_Codigo';
            $folderType = $_POST['folderType'] ?? 'iniciales';

            $file = $_FILES['file'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'Error al recibir archivo.']);
                exit;
            }

            $webhookUrl = $this->getDriveConfig('api_receipts');
            $rootFolderId = $this->getDriveConfig('folder_link');
            
            $payload = [
                'clientName' => $driveFolderName,
                'oldClientName' => $oldDriveFolderName,
                'receiptCode' => $receiptCode,
                'deleteFileIds' => [],
                'files' => [
                    [
                        'fileName' => uniqid($folderType == 'iniciales' ? 'ini_' : 'proc_') . '_' . $file['name'],
                        'mimeType' => $file['type'],
                        'base64' => base64_encode(file_get_contents($file['tmp_name'])),
                        'folderType' => $folderType
                    ]
                ]
            ];

            $options = [
                'http' => [
                    'header'  => "Content-Type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($payload),
                    'follow_location' => 1,
                    'ignore_errors' => true,
                    'timeout' => 60
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ];
            $context  = stream_context_create($options);
            $response = file_get_contents($webhookUrl, false, $context);
            $result = json_decode($response, true);

            if (isset($result['status']) && $result['status'] === 'success') {
                $link = '';
                foreach ($result['files'] as $f) { $link = $f['viewLink']; }
                echo json_encode(['success' => true, 'link' => $link]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error de Drive']);
            }
            exit;
        }
    }

    // ==========================================================
    // SINCRONIZADOR "ON-THE-FLY" (AL VUELO) CON GOOGLE DRIVE
    // ==========================================================
    private function autoSyncReceiptImages($receipt) {
        $webhookUrl = $this->getDriveConfig('api_receipts');
        $payload = [
            'action' => 'sync',
            'receiptCode' => $receipt['receipt_code']
        ];
        
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($payload),
                'timeout' => 5 // Máximo 5 segundos para no colgar la página
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($webhookUrl, false, $context);
        
        if ($response) {
            $result = json_decode($response, true);
            if (isset($result['status']) && $result['status'] === 'success') {
                $iniLinks = $result['iniciales'] ?? [];
                $procLinks = $result['proceso'] ?? [];

                $currentIni = json_decode($receipt['images_json'] ?? '[]', true) ?: [];
                $currentProc = json_decode($receipt['process_images_json'] ?? '[]', true) ?: [];

                // Lógica de Inteligencia: 
                // 1. Mantiene el orden de las que ya existen.
                // 2. Elimina las que fueron borradas directamente en Drive.
                // 3. Añade al final las nuevas que se subieron por fuera.
                $finalIni = array_values(array_merge(array_intersect($currentIni, $iniLinks), array_diff($iniLinks, $currentIni)));
                $finalProc = array_values(array_merge(array_intersect($currentProc, $procLinks), array_diff($procLinks, $currentProc)));

                // Si detecta alguna diferencia, actualiza la Base de Datos automáticamente
                if (serialize($finalIni) !== serialize($currentIni) || serialize($finalProc) !== serialize($currentProc)) {
                    
                    $db_alive = false;
                    if ($this->db) {
                        try {
                            $db_alive = @$this->db->ping();
                        } catch (\mysqli_sql_exception $e) {
                            $db_alive = false;
                        }
                    }

                    if (!$db_alive) {
                        @$this->db->close();
                        include ROOT_PATH . 'app/config/db_connection.php';
                        if (isset($connection)) { $this->db = $connection; $this->receiptModel = new Receipt($this->db); }
                    }

                    // 🛡️ CORRECCIÓN: PASAMOS TODOS LOS DATOS PARA EVITAR QUE SE BORREN
                    $updateData = [
                        'client_name' => $receipt['client_name'],
                        'client_id_number' => $receipt['client_id_number'],
                        'client_phone' => $receipt['client_phone'],
                        'client_email' => $receipt['client_email'],
                        'client_address' => $receipt['client_address'],
                        'items_json' => $receipt['items_json'],
                        'budget_json' => $receipt['budget_json'],
                        'notes' => $receipt['notes'],
                        'sent_email' => $receipt['sent_email'],
                        'sent_whatsapp' => $receipt['sent_whatsapp'],
                        'images_json' => json_encode($finalIni),
                        'process_images_json' => json_encode($finalProc),
                        'pdf_filename' => $receipt['pdf_filename']
                    ];

                    $this->receiptModel->update($receipt['id_receipt'], $updateData);

                    // Actualizamos la variable en memoria para que la vista cargue las fotos nuevas
                    $receipt['images_json'] = json_encode($finalIni);
                    $receipt['process_images_json'] = json_encode($finalProc);

                    // Actualizamos la variable en memoria para que la vista cargue las fotos nuevas
                    $receipt['images_json'] = json_encode($finalIni);
                    $receipt['process_images_json'] = json_encode($finalProc);
                }
            }
        }
        return $receipt;
    }

    
    // =================================================================
    // MÓDULO DE CLIENTES
    // =================================================================

    public function clients() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $active_menu = 'clients';
        $pageTitle = 'Gestión de Clientes';
        $pageType = 'basic_list'; 

        $filters = ['search' => $_GET['search'] ?? null];
        $clients = $this->clientModel->getAll($filters);
        include ROOT_PATH . 'app/views/admin/clients.php';
    }

    public function createClient() {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $active_menu = 'clients';
        $pageTitle = 'Agregar Nuevo Cliente';
        $pageType = 'crud';
        include ROOT_PATH . 'app/views/admin/create-client.php';
    }

    public function storeClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'client_id_number' => preg_replace('/[^a-zA-Z0-9]/', '', $_POST['client_id_number'] ?? ''),
                'client_name' => mb_convert_case(trim($_POST['client_name'] ?? ''), MB_CASE_TITLE, "UTF-8")
            ];

            if ($this->clientModel->create($data)) {
                $_SESSION['flash_message'] = 'Cliente creado exitosamente.';
            } else {
                $_SESSION['flash_message'] = 'Error al crear el cliente. (Quizás el DNI ya existe)';
            }
            header('Location: /admin/clients');
            exit;
        }
    }

    public function editClient($id) {
        $userInfo = $this->userModel->getById($_SESSION['idUser']);
        $pageName = $this->siteContentModel->getValue('page_title');
        $active_menu = 'clients';
        $pageTitle = 'Editar Cliente';
        $pageType = 'crud';
        
        $client = $this->clientModel->getById($id);
        if (!$client) {
            header('Location: /admin/clients');
            exit;
        }
        include ROOT_PATH . 'app/views/admin/edit-client.php';
    }

    public function updateClient() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id_client'];
            $data = [
                'client_id_number' => preg_replace('/[^a-zA-Z0-9]/', '', $_POST['client_id_number'] ?? ''),
                'client_name' => mb_convert_case(trim($_POST['client_name'] ?? ''), MB_CASE_TITLE, "UTF-8")
            ];

            if ($this->clientModel->update($id, $data)) {
                $_SESSION['flash_message'] = 'Cliente actualizado exitosamente.';
            } else {
                $_SESSION['flash_message'] = 'Error al actualizar el cliente.';
            }
            header('Location: /admin/clients');
            exit;
        }
    }

    public function deleteClient($id) {
        if ($this->clientModel->delete($id)) {
            $_SESSION['flash_message'] = 'Cliente eliminado exitosamente.';
        } else {
            $_SESSION['flash_message'] = 'Error al eliminar el cliente.';
        }
        header('Location: /admin/clients');
        exit;
    }

    public function uploadAsyncProductVideo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $productId = (int)($_POST['product_id'] ?? 0);
            $webhookUrl = $this->getDriveConfig('api_video_products');

            // FASE 1: Solicitar URL para subida
            if ($action === 'init_upload') {
                $productName = mb_convert_case(trim($_POST['product_name'] ?? 'Producto'), MB_CASE_TITLE, "UTF-8");
                $oldProductName = mb_convert_case(trim($_POST['old_product_name'] ?? ''), MB_CASE_TITLE, "UTF-8");
                $folderName = $productId . ' - ' . $productName;
                $oldFolderName = (!empty($oldProductName)) ? ($productId . ' - ' . $oldProductName) : null;
                
                $payload = [
                    'action' => 'initResumableUpload',
                    'productFolder' => $folderName,
                    'oldProductFolder' => $oldFolderName,
                    'fileName' => $_POST['file_name'],
                    'mimeType' => $_POST['mime_type'],
                    'deleteFileIds' => []
                ];

                $oldUrl = $_POST['old_video_url'] ?? '';
                if (!empty($oldUrl) && (preg_match('/id=([^&]+)/', $oldUrl, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $oldUrl, $matches))) {
                    $payload['deleteFileIds'][] = $matches[1];
                }

                $options = [
                    'http' => [
                        'header'  => "Content-Type: application/json\r\n",
                        'method'  => 'POST',
                        'content' => json_encode($payload),
                        'follow_location' => 1,          // <-- CORRECCIÓN
                        'ignore_errors' => true,         // <-- CORRECCIÓN
                        'timeout' => 60                  // Aumentado a 60s
                    ], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                ];
                
                $response = @file_get_contents($webhookUrl, false, stream_context_create($options));
                echo $response;
                exit;
            }

            // FASE 2: Guardar enlace final en base de datos
            if ($action === 'save_link') {
                $fileId = $_POST['file_id'] ?? '';
                if (empty($fileId)) { echo json_encode(['success' => false]); exit; }

                $payload = [ 'action' => 'finalizeVideo', 'fileId' => $fileId ];
                $options = [
                    'http' => [
                        'header'  => "Content-Type: application/json\r\n",
                        'method'  => 'POST',
                        'content' => json_encode($payload),
                        'follow_location' => 1,          // <-- CORRECCIÓN
                        'ignore_errors' => true,         // <-- CORRECCIÓN
                        'timeout' => 60
                    ], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                ];
                
                $response = @file_get_contents($webhookUrl, false, stream_context_create($options));
                $result = json_decode($response, true);

                if (isset($result['status']) && $result['status'] === 'success') {
                    $this->productModel->updateVideoUrl($productId, $result['fileLink']);
                    echo json_encode(['success' => true, 'link' => $result['fileLink']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error aplicando permisos en Drive.']);
                }
                exit;
            }
        }
    }

    public function saveProductVideoLink() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            $productId = (int)($_POST['product_id'] ?? 0);
            $videoUrl = $_POST['video_url'] ?? '';

            if ($productId > 0 && !empty($videoUrl)) {
                $this->productModel->updateVideoUrl($productId, $videoUrl);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            }
            exit;
        }
    }


    public function deleteProductVideo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            // 🛡️ CORRECCIÓN: Leer JSON raw de php://input además de $_POST
            $jsonInput = json_decode(file_get_contents('php://input'), true);
            $productId = (int)($_POST['product_id'] ?? $jsonInput['product_id'] ?? 0);
            $videoUrl = $_POST['video_url'] ?? $jsonInput['video_url'] ?? '';

            if ($productId <= 0 || empty($videoUrl)) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos para procesar la solicitud.']);
                exit;
            }

            // Enviar solicitud de eliminación a Google Drive
            $webhookUrl = $this->getDriveConfig('api_video_products');
            
            $payload = [
                'action' => 'deleteVideo',
                'deleteFileIds' => []
            ];

            if (preg_match('/id=([^&]+)/', $videoUrl, $matches) || preg_match('/d\/([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                $payload['deleteFileIds'][] = $matches[1];
            }

            $options = [
                'http' => [
                    'header'  => "Content-Type: application/json\r\n",
                    'method'  => 'POST',
                    'content' => json_encode($payload),
                    'timeout' => 30
                ],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ];
            
            @file_get_contents($webhookUrl, false, stream_context_create($options));

            // Limpiar el registro en la base de datos
            $success = $this->productModel->updateVideoUrl($productId, '');

            if ($success) {
                $this->db->query("UPDATE products SET video_title = '', video_description = '' WHERE id_product = $productId");
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al limpiar la base de datos.']);
            }
            exit;
        }
    }
    
}
?>