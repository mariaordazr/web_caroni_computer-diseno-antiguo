<?php

class Product {
    protected $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function create(array $data): ?int
    {   
        // Se agregan sku, warranty y `condition`
        $query = "INSERT INTO products (sku, name, description, ext_description, video_title, video_description, video_url, price, stock, category, brand, warranty, `condition`, certification) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        
        // Tipos: 8 strings, 1 double, 3 enteros, 2 strings (Total: ssssssssdiiisss)
        $stmt->bind_param("sssssssdiiisss", 
            $data['sku'], $data['name'], $data['description'], $data['ext_description'],
            $data['video_title'], $data['video_description'], $data['video_url'],
            $data['price'], $data['stock'], $data['category'], $data['brand'],
            $data['warranty'], $data['condition'], $data['certification']
        );


        if ($stmt->execute()) {
            $newId = $this->db->insert_id;
            $stmt->close();
            return $newId;
        }
        
        $stmt->close();
        return null;
    }
    

    public function getById(int $id): ?array
    {
        $query = "SELECT 
                    p.id_product, p.sku, p.name, p.description, 
                    p.ext_description, p.price AS original_price,
                    p.video_title, p.video_description, p.video_url,
                    p.stock, p.warranty, p.`condition`, p.`hidden` as product_hidden,
                    p.valery_connected, p.certification,
                    o.price AS offer_price,
                    np.id_new_product,
                    o.id_offer,
                    p.category, p.brand,
                    p.created_at, p.updated_at,
                    c.name as category_name, c.`hidden` as category_hidden,
                    b.name as brand_name, b.`hidden` as brand_hidden
                FROM products p
                LEFT JOIN offers o ON p.id_product = o.product
                LEFT JOIN new_products np ON p.id_product = np.product
                LEFT JOIN categories c ON p.category = c.id_category
                LEFT JOIN brands b ON p.brand = b.id_brand
                WHERE p.id_product = ?";
                
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    

    public function getBySku(string $sku): ?array
    {
        // Buscamos solo productos que no estén ocultos
        $sql = "SELECT id_product FROM products WHERE sku = ? AND hidden = 0 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $sku);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc(); // Devuelve el ID si lo encuentra, o null si no
        }
        return null;
    }


    public function update(int $id, array $data): bool
    {
        // Se agregan sku, warranty, condition y certification al SET
        $query = "UPDATE products SET sku = ?, name = ?, description = ?, ext_description = ?, video_title = ?, video_description = ?, video_url = ?, price = ?, stock = ?, brand = ?, category = ?, warranty = ?, `condition` = ?, certification = ? WHERE id_product = ?";
        
        $stmt = $this->db->prepare($query);
        
        // Tipos: 8 strings, 1 double, 2 enteros, 2 strings, 1 entero (id) -> ssssssssdiiisssi
        $stmt->bind_param("sssssssdiiisssi", 
            $data['sku'], $data['name'], $data['description'], $data['ext_description'],
            $data['video_title'], $data['video_description'], $data['video_url'],
            $data['price'], $data['stock'], $data['brand'], $data['category'], 
            $data['warranty'], $data['condition'], $data['certification'], $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function delete(int $id): bool
    {
        // 1. (Opcional) Borrar archivos físicos del servidor
        $images = $this->getImagesByProductId($id);
        foreach ($images as $image) {
            $filePath = ROOT_PATH . 'public/assets/uploads/products/' . $image['file_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // 2. Iniciar una transacción para asegurar que todo se borre
        //    o nada se borre.
        $this->db->begin_transaction();
        
        try {
            // 3. Borrar registros de tablas secundarias
            
            // Borrar de product_images
            $stmtImg = $this->db->prepare("DELETE FROM product_images WHERE product = ?");
            $stmtImg->bind_param("i", $id);
            $stmtImg->execute();
            $stmtImg->close();

            // Borrar de offers
            $stmtOffer = $this->db->prepare("DELETE FROM offers WHERE product = ?");
            $stmtOffer->bind_param("i", $id);
            $stmtOffer->execute();
            $stmtOffer->close();
            
            // Borrar de new_products
            $stmtNew = $this->db->prepare("DELETE FROM new_products WHERE product = ?");
            $stmtNew->bind_param("i", $id);
            $stmtNew->execute();
            $stmtNew->close();

            // 4. Ahora sí, borrar el producto principal
            $stmtProd = $this->db->prepare("DELETE FROM products WHERE id_product = ?");
            $stmtProd->bind_param("i", $id);
            $stmtProd->execute();
            $stmtProd->close();

            // 5. Si todo salió bien, confirmar los cambios
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // 6. Si algo falló, revertir todos los cambios
            $this->db->rollback();
            // Opcional: registrar el error $e->getMessage()
            return false;
        }
    }
    

    public function getAllPublic(array $filters = []): array
    {
        $query = "SELECT 
                    p.id_product, p.sku, p.name, 
                    p.description, p.ext_description, p.certification,
                    p.video_title, p.video_description, p.video_url,
                    p.price AS original_price, p.stock,
                    p.warranty, p.`condition`, p.`hidden`,
                    c.name AS category_name,
                    b.name AS brand_name,
                    p.category, p.brand, pi.file_name,
                    o.price AS offer_price,
                    np.id_new_product, o.id_offer,
                    p.created_at, p.updated_at
                FROM products p
                LEFT JOIN categories c ON p.category = c.id_category
                LEFT JOIN brands b ON p.brand = b.id_brand
                LEFT JOIN offers o ON p.id_product = o.product
                LEFT JOIN new_products np ON p.id_product = np.product
                LEFT JOIN product_images pi ON p.id_product = pi.product AND pi.display_order = 1";

        $conditions = [];
        $params = []; 
        $types = '';
        $scoreFormula = "0"; 

        $conditions = [];
        $whereParams = []; 
        $whereTypes = '';
        
        $orderParams = [];
        $orderTypes = '';
        $scoreFormula = "0"; 

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $words = explode(' ', $search);
            $whereConditions = [];

            foreach ($words as $word) {
                $word = trim($word);
                if ($word !== '') {
                    $likePrefix = "{$word}%"; 
                    $likeAnywhere = "%{$word}%";
                    
                    $regexExact = "\\b" . preg_quote($word) . "\\b"; 
                    
                    // 1. Filtro base WHERE (Forzando COLLATE para ignorar acentos y mayúsculas)
                    $whereConditions[] = "(
                        p.name COLLATE utf8mb4_unicode_ci LIKE ? OR 
                        p.description COLLATE utf8mb4_unicode_ci LIKE ? OR 
                        c.name COLLATE utf8mb4_unicode_ci LIKE ? OR 
                        b.name COLLATE utf8mb4_unicode_ci LIKE ? OR 
                        p.sku COLLATE utf8mb4_unicode_ci LIKE ?
                    )";
                    
                    // Parámetros SOLO para el WHERE
                    for ($i = 0; $i < 5; $i++) { 
                        $whereParams[] = $likeAnywhere; 
                        $whereTypes .= "s"; 
                    }

                    // 2. SISTEMA DE SCORING (Parámetros SOLO para el ORDER BY)
                    $scoreFormula .= " + (CASE WHEN p.name COLLATE utf8mb4_unicode_ci REGEXP ? THEN 500 ELSE 0 END)";
                    $orderParams[] = $regexExact; 
                    $orderTypes .= "s";

                    $scoreFormula .= " + (CASE WHEN c.name COLLATE utf8mb4_unicode_ci REGEXP ? OR b.name COLLATE utf8mb4_unicode_ci REGEXP ? THEN 300 ELSE 0 END)";
                    $orderParams[] = $regexExact; 
                    $orderParams[] = $regexExact; 
                    $orderTypes .= "ss";
                    
                    $scoreFormula .= " + (CASE WHEN p.name COLLATE utf8mb4_unicode_ci LIKE ? THEN 100 ELSE 0 END)";
                    $orderParams[] = $likePrefix; 
                    $orderTypes .= "s";
                }
            }

            if (!empty($whereConditions)) {
                $conditions[] = "(" . implode(" AND ", $whereConditions) . ")";
            }
        }

        // --- Filtros estándar ---
        if (!empty($filters['category'])) { 
            $conditions[] = "p.category = ?"; 
            $whereParams[] = $filters['category']; 
            $whereTypes .= 'i'; 
        }
        if (!empty($filters['brand'])) { 
            $conditions[] = "p.brand = ?"; 
            $whereParams[] = $filters['brand']; 
            $whereTypes .= 'i'; 
        }
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'offer') 
                $conditions[] = "o.id_offer IS NOT NULL";
            if ($filters['type'] === 'new')
                $conditions[] = "np.id_new_product IS NOT NULL";
        }

        $conditions[] = "p.`hidden` = 0 AND c.`hidden` = 0 AND b.`hidden` = 0";
        
        if (!empty($conditions)) { 
            $query .= " WHERE " . implode(" AND ", $conditions); 
        }
        $query .= " GROUP BY p.id_product";

        // ORDENAMIENTO
        $orderClauses = [];
        if ($scoreFormula !== "0") { $orderClauses[] = "($scoreFormula) DESC"; }
        $orderClauses[] = "(p.stock > 0) DESC"; 

        $orderBy = 'TRIM(LOWER(p.name))';
        $orderDir = 'ASC';
        if (!empty($filters['sort'])) {
            list($sortCol, $sortDir) = explode('-', $filters['sort']);
            if ($sortCol === 'price') $orderBy = "p.price";
            if ($sortCol === 'created_at') $orderBy = "p.created_at";
            $orderDir = (strtolower($sortDir) === 'desc') ? 'DESC' : 'ASC';
        }
        $orderClauses[] = "$orderBy $orderDir";

        $query .= " ORDER BY " . implode(", ", $orderClauses);

        // --- FUSIÓN FINAL DE PARÁMETROS ---
        // Primero los parámetros del WHERE, luego los del ORDER BY (Scoring)
        $finalParams = array_merge($whereParams, $orderParams);
        $finalTypes = $whereTypes . $orderTypes;

        $stmt = $this->db->prepare($query);
        if (!empty($finalParams)) { 
            $stmt->bind_param($finalTypes, ...$finalParams); 
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll(array $filters = []): array
    {
        $query = "SELECT 
                    p.id_product, p.sku, p.name, 
                    IF(p.`hidden` = 1 OR c.`hidden` = 1 OR b.`hidden` = 1, 1, 0) as `hidden`,
                    p.description, p.ext_description, p.certification,
                    p.video_title, p.video_description, p.video_url,
                    p.price AS original_price, p.stock,
                    p.warranty, p.`condition`,
                    c.name AS category_name,
                    b.name AS brand_name,
                    p.category,
                    p.brand,
                    pi.file_name,
                    o.price AS offer_price,
                    np.id_new_product,
                    p.created_at, p.updated_at
                FROM products p
                LEFT JOIN categories c ON p.category = c.id_category
                LEFT JOIN brands b ON p.brand = b.id_brand
                LEFT JOIN offers o ON p.id_product = o.product
                LEFT JOIN new_products np ON p.id_product = np.product
                LEFT JOIN product_images pi ON p.id_product = pi.product AND pi.display_order = 1";

        $conditions = [];
        $params = [];
        $types = '';

        // --- LÓGICA DE BÚSQUEDA AVANZADA CON PONDERACIÓN (SCORING) ---
        $isSearchActive = false;
        $scoreFormula = ""; 
        $scoreParams = [];
        $scoreTypes = "";

        if (!empty($filters['search'])) {
            $isSearchActive = true;
            $searchWords = explode(' ', $filters['search']);
            
            // 1. Construcción del WHERE (Filtro base: debe coincidir en ALGO)
            $wordConditions = [];

            foreach ($searchWords as $word) {
                if (!empty($word)) {
                    $searchTerm = "%{$word}%";
                    
                    // Condición WHERE: Buscamos en todos los campos solicitados
                    // Nota: p.`condition` lleva backticks por ser palabra reservada
                    $wordConditions[] = "(
                        c.name LIKE ? OR 
                        b.name LIKE ? OR 
                        p.sku LIKE ? OR 
                        p.name LIKE ? OR 
                        p.description LIKE ? OR 
                        p.`condition` LIKE ? OR 
                        p.warranty LIKE ? OR 
                        p.ext_description LIKE ? OR 
                        p.price LIKE ? OR 
                        p.stock LIKE ?
                    )";
                    
                    // Agregamos params para el WHERE (10 campos)
                    // ssssssssss
                    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                    $types .= 'ssssssssss';

                    // 2. Construcción del SISTEMA DE PUNTOS (ORDER BY)
                    // Jerarquía: Cat+Marca > Cat > Marca > SKU > Name > Desc > Cond > Warr > Ext > Price > Stock
                    
                    // Categoría: 5000 pts
                    $scoreParts[] = "(CASE WHEN c.name LIKE ? THEN 5000 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Marca: 2000 pts
                    $scoreParts[] = "(CASE WHEN b.name LIKE ? THEN 2000 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // SKU: 1000 pts
                    $scoreParts[] = "(CASE WHEN p.sku LIKE ? THEN 1000 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Nombre: 500 pts
                    $scoreParts[] = "(CASE WHEN p.name LIKE ? THEN 500 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Descripción: 100 pts
                    $scoreParts[] = "(CASE WHEN p.description LIKE ? THEN 100 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Condition: 50 pts
                    $scoreParts[] = "(CASE WHEN p.`condition` LIKE ? THEN 50 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Warranty: 20 pts
                    $scoreParts[] = "(CASE WHEN p.warranty LIKE ? THEN 20 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Ext Description: 10 pts
                    $scoreParts[] = "(CASE WHEN p.ext_description LIKE ? THEN 10 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Price: 5 pts
                    $scoreParts[] = "(CASE WHEN p.price LIKE ? THEN 5 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';

                    // Stock: 1 pt
                    $scoreParts[] = "(CASE WHEN p.stock LIKE ? THEN 1 ELSE 0 END)";
                    array_push($scoreParams, $searchTerm); $scoreTypes .= 's';
                }
            }

            if (!empty($wordConditions)) {
                // Usamos AND para ser estrictos (todas las palabras deben aparecer en algún lugar)
                // O puedes cambiar a OR si quieres resultados más amplios.
                $conditions[] = "(" . implode(" AND ", $wordConditions) . ")";
            }

            // Unimos las partes de la fórmula
            if (!empty($scoreParts)) {
                $scoreFormula = implode(" + ", $scoreParts);
            }
        }

        // --- Filtros estándar (Dropdowns del Admin) ---
        if (!empty($filters['category'])) {
            $conditions[] = "p.category = ?"; 
            $params[] = $filters['category'];
            $types .= 'i';
        }

        if (!empty($filters['brand'])) {
            $conditions[] = "p.brand = ?"; 
            $params[] = $filters['brand'];
            $types .= 'i';
        }

        // Filtros de Tipo (Oferta/Nuevo)
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'offer') $conditions[] = "o.id_offer IS NOT NULL";
            if ($filters['type'] === 'new') $conditions[] = "np.id_new_product IS NOT NULL";
            if ($filters['type'] === 'none') $conditions[] = "o.id_offer IS NULL AND np.id_new_product IS NULL";
        }

        // Filtro de Estado (Activo/Agotado)
        if (!empty($filters['status'])) { 
            if ($filters['status'] === 'active') { 
                $conditions[] = "p.stock > 0";
            }
            if ($filters['status'] === 'sold_out') { 
                $conditions[] = "p.stock = 0";
            }
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " GROUP BY p.id_product";

        // --- ORDENAMIENTO ---
        
        // 1. Si hay búsqueda activa, usamos el PUNTAJE
        if ($isSearchActive && !empty($scoreFormula)) {
            // Ordenamos por relevancia descendente, luego alfabéticamente por nombre
            $query .= " ORDER BY ($scoreFormula) DESC, TRIM(LOWER(p.name)) ASC";
            
            // FUSIONAR PARÁMETROS: 
            // Los del WHERE ya están en $params. 
            // Ahora añadimos los del ORDER BY al final.
            foreach ($scoreParams as $sp) {
                $params[] = $sp;
            }
            $types .= $scoreTypes;

        } else {
            // 2. Ordenamiento normal (Sin búsqueda o usando los filtros de columna)
            $orderBy = 'p.created_at';
            $orderDir = 'DESC';
            
            if (!empty($filters['sort'])) {
                list($sortCol, $sortDir) = explode('-', $filters['sort']);
                $validCols = ['name', 'created_at', 'updated_at', 'price', 'stock'];
                
                if (in_array($sortCol, $validCols)) {
                    if ($sortCol === 'name') {
                        $orderBy = "TRIM(LOWER(p.name))";
                    } else {
                        $orderBy = 'p.' . $sortCol;
                    }
                }
                if (in_array(strtolower($sortDir), ['asc', 'desc'])) {
                    $orderDir = strtoupper($sortDir);
                }
            }
            $query .= " ORDER BY $orderBy $orderDir";
        }

        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updateStock(int $productId, int $stock): bool
    {
        $query = "UPDATE products SET stock = ? WHERE id_product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $stock, $productId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } 


    
    public function getTotalProductsCount(string $searchTerm = ''): int
    {
        $query = "SELECT COUNT(*) as total FROM products p JOIN categories c ON p.category = c.id_category JOIN brands b ON p.brand = b.id_brand";

        if (!empty($searchTerm)) {
            $searchTerm = "%" . $this->db->real_escape_string($searchTerm) . "%";
            $query .= " WHERE p.name LIKE '{$searchTerm}' OR c.name LIKE '{$searchTerm}' OR b.name LIKE '{$searchTerm}'";
        }

        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function countTotalProducts(): int
    {
        $result = $this->db->query("SELECT COUNT(id_product) as total FROM products");
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }

    public function countNewArrivals(): int
    {
        // Si tu tabla 'new_products' no tiene 'created_at', esta consulta fallará.
        // Una alternativa sería contar todos los registros de 'new_products'.
        $result = $this->db->query("SELECT COUNT(id_new_product) as total FROM new_products");
        if (!$result) return 0; // Manejo de error si la columna no existe
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }


    public function countOutOfStock(): int
    {
        $result = $this->db->query("SELECT COUNT(id_product) as total FROM products WHERE stock = 0");
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }

    public function countLowStock(): int
    {
        $result = $this->db->query("SELECT COUNT(id_product) as total FROM products WHERE stock > 0 AND stock < 10");
        $row = $result->fetch_assoc();
        return (int) $row['total'];
    }

    public function getImagesByProductId(int $productId): array
    {
        // CORRECCIÓN: Añadimos 'display_order' al SELECT y un 'ORDER BY'
        $query = "SELECT id_image, file_name, display_order 
                  FROM product_images 
                  WHERE product = ? 
                  ORDER BY display_order ASC"; // <-- AÑADIDO
                  
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $images;
    }

    /**
     * Obtiene todas las categorías y, para cada una,
     * una lista de las marcas que tienen productos en esa categoría.
     * @return array
     */
    public function getCategoriesWithBrands(): array
    {
        $sql = "SELECT
                    c.id_category, c.name AS category_name,
                    b.id_brand, b.name AS brand_name
                FROM products p
                JOIN categories c ON p.category = c.id_category
                JOIN brands b ON p.brand = b.id_brand
                WHERE p.`hidden` = 0 AND c.`hidden` = 0 AND b.`hidden` = 0
                GROUP BY c.id_category, b.id_brand
                ORDER BY c.name, b.name";

        $result = $this->db->query($sql);
        $categories = [];

        while($row = $result->fetch_assoc()) {
            // Si es la primera vez que vemos esta categoría, la creamos
            if (!isset($categories[$row['id_category']])) {
                $categories[$row['id_category']] = [
                    'id_category' => $row['id_category'],
                    'name' => $row['category_name'],
                    'brands' => [] // Preparamos el array para sus marcas
                ];
            }
            // Añadimos la marca a la lista de marcas de esa categoría
            $categories[$row['id_category']]['brands'][] = [
                'id_brand' => $row['id_brand'],
                'name' => $row['brand_name']
            ];
        }
        return array_values($categories); // Devolvemos un array limpio
    }

    public function updateHiddenStatus(int $productId, int $status): bool
    {
        $query = "UPDATE products SET `hidden` = ? WHERE id_product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $status, $productId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateHiddenStatusByCategory(int $categoryId, int $status): bool {
        $query = "UPDATE products SET `hidden` = ? WHERE category = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $status, $categoryId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateHiddenStatusByBrand(int $brandId, int $status): bool {
        $query = "UPDATE products SET `hidden` = ? WHERE brand = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $status, $brandId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // ===============================================================
    // MÉTODOS PARA EL GESTOR DINÁMICO DE IMÁGENES DE PRODUCTOS
    // ===============================================================

    /**
     * Agrega una nueva imagen. Si $isCover es true, le asigna display_order = 1, sino 2.
     */
    public function addImage(int $productId, string $fileName, bool $isCover = false): bool 
    {
        $displayOrder = $isCover ? 1 : 2;
        $query = "INSERT INTO product_images (product, file_name, display_order) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        // Tipos: i (int product), s (string file_name), i (int display_order)
        $stmt->bind_param("isi", $productId, $fileName, $displayOrder);
        
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Obtiene los datos de una imagen específica (útil para saber qué archivo borrar del disco)
     */
    public function getImageById(int $imageId): ?array 
    {
        $query = "SELECT * FROM product_images WHERE id_image = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result ?: null;
    }

    /**
     * Elimina el registro de una imagen en la base de datos
     */
    public function deleteImage(int $imageId): bool 
    {
        $query = "DELETE FROM product_images WHERE id_image = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $imageId);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Define qué imagen es la portada usando la columna 'display_order'
     */
    public function setProductCover(int $productId, int $coverImageId): bool 
    {
        // 1. Convertimos TODAS las imágenes de este producto a secundarias (display_order = 2)
        $query1 = "UPDATE product_images SET display_order = 2 WHERE product = ?";
        $stmt1 = $this->db->prepare($query1);
        $stmt1->bind_param("i", $productId);
        $stmt1->execute();
        $stmt1->close();

        // 2. Asignamos la prioridad de portada (display_order = 1) a la imagen seleccionada
        $query2 = "UPDATE product_images SET display_order = 1 WHERE id_image = ?";
        $stmt2 = $this->db->prepare($query2);
        $stmt2->bind_param("i", $coverImageId);
        
        $result = $stmt2->execute();
        $stmt2->close();

        return $result;
    }

    /**
     * Salvaguarda: Asegura que el producto tenga al menos una imagen con display_order = 1
     */
    public function ensureAtLeastOneCover(int $productId): void 
    {
        // Verificamos si existe alguna imagen con display_order = 1
        $query = "SELECT id_image FROM product_images WHERE product = ? AND display_order = 1 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasCover = $result->num_rows > 0;
        $stmt->close();

        // Si no hay ninguna portada (ej. el usuario borró la que era portada y no eligió otra), 
        // forzamos a que la primera imagen disponible asuma el rol.
        if (!$hasCover) {
            $updateQuery = "UPDATE product_images SET display_order = 1 WHERE product = ? ORDER BY id_image ASC LIMIT 1";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bind_param("i", $productId);
            $updateStmt->execute();
            $updateStmt->close();
        }
    }

    /**
     * Actualiza el orden de una imagen existente usando su ID
     */
    public function updateImageOrder(int $imageId, int $order): bool 
    {
        $query = "UPDATE product_images SET display_order = ? WHERE id_image = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $order, $imageId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Actualiza el orden de una imagen recién subida usando su nombre de archivo
     */
    public function updateImageOrderByName(int $productId, string $fileName, int $order): bool 
    {
        $query = "UPDATE product_images SET display_order = ? WHERE product = ? AND file_name = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iis", $order, $productId, $fileName);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateVideoUrl(int $id, string $url): bool 
    {
        $stmt = $this->db->prepare("UPDATE products SET video_url = ? WHERE id_product = ?");
        $stmt->bind_param("si", $url, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}