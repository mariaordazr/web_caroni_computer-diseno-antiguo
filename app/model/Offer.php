<?php

class Offer
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    public function create(array $data=[]): ? int
    {
        
        $query = "INSERT INTO offers (message, price, product) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sdi", 
            $data['message'], 
            $data['price'], 
            $data['product']
        );

        if ($stmt->execute()) {
            $newId = $this->db->insert_id;
            $stmt->close();
            return $newId;
        }
        
        $stmt->close();
        return null;

    }

    /**
     * Obtiene una oferta específica por su ID.
     * @param int $id El ID de la oferta.
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $query = "SELECT id_offer, message, price, product FROM offers WHERE id_offer = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    /**
     * Obtiene todas las ofertas, o solo las de un producto específico.
     * @param int|null $productId El ID del producto (opcional).
     * @return array
     */
    public function getOfferedProducts(string $searchTerm = '', ?int $categoryId = null, ?int $brandId = null, bool $showOutOfStock = false): array
    {
        // Se eliminó p.model de la lista de columnas
        $query = "SELECT 
                p.id_product, p.sku, p.name, p.price AS original_price, 
                p.description, p.ext_description, p.stock,
                p.warranty, p.`condition`, p.`hidden`,
                o.price AS offer_price,
                o.id_offer,
                p.category, p.brand,
                p.created_at, p.updated_at,
                pi.file_name,
                c.name as category_name, 
                b.name as brand_name
                FROM offers o
                INNER JOIN products p ON o.product = p.id_product
                LEFT JOIN categories c ON p.category = c.id_category
                LEFT JOIN brands b ON p.brand = b.id_brand
                LEFT JOIN product_images pi ON p.id_product = pi.product";

        $conditions = [];
        $params = [];
        $types = '';

        // Búsqueda por término (modificado para buscar solo en el nombre)
        if (!empty($searchTerm)) {
            $conditions[] = "p.name LIKE ?"; // Se eliminó la condición OR p.model LIKE ?
            $likeTerm = "%{$searchTerm}%";
            $params[] = $likeTerm;
            $types .= 's';
        }

        // Filtro por categoría
        if ($categoryId !== null) {
            $conditions[] = "p.category = ?";
            $params[] = $categoryId;
            $types .= 'i';
        }

        // Filtro por marca
        if ($brandId !== null) {
            $conditions[] = "p.brand = ?";
            $params[] = $brandId;
            $types .= 'i';
        }
        
        // Filtro por stock agotado
        if ($showOutOfStock) {
            $conditions[] = "p.stock = 0";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " GROUP BY p.id_product ORDER BY p.id_product DESC";

        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        $stmt->close();
        return $products;
    }

    public function findByProductId(int $productId): ?array
    {
        $query = "SELECT * FROM offers WHERE product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }
    /**
     * Actualiza una oferta existente.
     * @param int $id El ID de la oferta.
     * @param array $data Los nuevos datos.
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $updateQuery = "UPDATE offers SET message = ?, price = ? WHERE id_offer = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("sdi", $data['message'], $data['price'], $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Elimina una oferta por su ID.
     * @param int $idOffer El ID de la oferta.
     * @return bool
     */
    public function delete(int $idOffer): bool
    {
        $deleteQuery = "DELETE FROM offers WHERE id_offer = ?";
        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bind_param("i", $idOffer);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAll(array $filters=[]): array
    {
        $query = "SELECT 
                p.id_product, p.sku,  p.name, p.price AS original_price, p.stock,
                c.name AS category_name,
                b.name AS brand_name,
                pi.file_name,
                o.price AS offer_price,
                o.id_offer,
                p.id_product, p.sku, p.name, p.price AS original_price, 
                p.description, p.ext_description, p.stock,
                p.warranty, p.`condition`, p.`hidden`,
                o.price AS offer_price,
                p.category, p.brand,
                p.created_at, p.updated_at
              FROM offers o
              INNER JOIN products p ON o.product = p.id_product
              LEFT JOIN categories c ON p.category = c.id_category
              LEFT JOIN brands b ON p.brand = b.id_brand
              LEFT JOIN product_images pi ON p.id_product = pi.product";
        
        $conditions = [];
        $params = [];
        $types = '';

        // --- LÓGICA DE BÚSQUEDA CORREGIDA Y OPTIMIZADA ---
        if (!empty($filters['search'])) {
            $searchWords = explode(' ', $filters['search']);
            foreach ($searchWords as $word) {
                if (!empty($word)) {
                    $searchTerm = "%{$word}%";
                    // Buscamos solo en los campos de texto relevantes
                    $conditions[] = "(p.name LIKE ? OR c.name LIKE ? OR b.name LIKE ? OR p.price LIKE ? OR o.price LIKE ? OR o.message LIKE ?)";
                    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                    $types .= 'ssssss';
                }
            }
        }

        // --- (El resto de tus filtros de categoría, marca y tipo no cambian) ---
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

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY p.id_product";

        // ... 
        $orderBy = 'o.created_at';
        $orderDir = 'DESC';
        if (!empty($filters['sort'])) {
            list($sortCol, $sortDir) = explode('-', $filters['sort']);
            
            // Validamos el nombre del producto
            if ($sortCol === 'name') {
                $orderBy = "TRIM(LOWER(p.name))"; // <--- CAMBIO
            } else {
                $validCols = ['created_at', 'updated_at', 'price', 'stock'];
                if (in_array($sortCol, $validCols)) {
                    $orderBy = $sortCol; // OJO: aquí ya incluían el alias en el array $validCols
                }
                // Si el sortCol viene sin alias (ej: 'created_at'), asegúrate de añadirlo 'o.' 
                // pero según tu código previo, parece que usas 'o.created_at' directamente.
            }

            if (in_array(strtolower($sortDir), ['asc', 'desc'])) {
                $orderDir = strtoupper($sortDir);
            }
        }
        $query .= " ORDER BY $orderBy $orderDir";

        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteByProductId(int $productId): bool
    {
        $query = "DELETE FROM offers WHERE product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}