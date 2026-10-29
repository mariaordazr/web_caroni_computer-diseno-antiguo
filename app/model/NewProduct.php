<?php
// app/model/NewProduct.php

class NewProduct
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
     * Crea una nueva entrada en la tabla 'new_products' para marcar un producto como nuevo.
     * @param int $productId El ID del producto existente.
     * @return bool
     */
    public function create(int $productId): bool
    {
        // Se utiliza la transacción para asegurar que la operación sea atómica.
        $this->db->begin_transaction();
        try {
            // Se verifica si el producto ya está marcado como nuevo para evitar duplicados.
            $checkQuery = "SELECT id_new_product FROM new_products WHERE product = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bind_param("i", $productId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $this->db->rollback();
                return false; // El producto ya existe como 'nuevo'.
            }
            $checkStmt->close();

            // Si no existe, se inserta el nuevo registro.
            $insertQuery = "INSERT INTO new_products (product) VALUES (?)";
            $stmt = $this->db->prepare($insertQuery);
            $stmt->bind_param("i", $productId);
            if (!$stmt->execute()) {
                throw new Exception("Error al marcar el producto como nuevo.");
            }
            $stmt->close();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Obtiene una entrada de producto nuevo por su ID de registro en la tabla 'new_products'.
     * @param int $id El ID del registro en 'new_products'.
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $query = "SELECT * FROM new_products WHERE id_new_product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    /**
     * Obtiene todos los productos marcados como "nuevos", incluyendo su información del producto principal.
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $query = "SELECT 
                p.id_product, p.sku, p.name, p.price AS original_price, p.stock,
                p.description, p.ext_description, p.warranty, p.`condition`, p.`hidden`,
                c.name AS category_name,
                b.name AS brand_name,
                pi.file_name,
                o.price AS offer_price,
                p.created_at, p.updated_at,
                np.id_new_product
              FROM new_products np
              INNER JOIN products p ON np.product = p.id_product
              LEFT JOIN categories c ON p.category = c.id_category
              LEFT JOIN brands b ON p.brand = b.id_brand
              LEFT JOIN product_images pi ON p.id_product = pi.product
              LEFT JOIN offers o ON p.id_product = o.product";
        
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
                    $conditions[] = "(p.name LIKE ? OR c.name LIKE ? OR b.name LIKE ? OR p.price LIKE ? OR o.price LIKE ?)";
                    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                    $types .= 'sssss';
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
        $orderBy = 'p.created_at';
        $orderDir = 'DESC';
        if (!empty($filters['sort'])) {
            list($sortCol, $sortDir) = explode('-', $filters['sort']);
            
            if ($sortCol === 'name') {
                $orderBy = "TRIM(LOWER(p.name))"; // <--- CAMBIO
            } else {
                $validCols = ['created_at', 'updated_at', 'price', 'stock'];
                if (in_array($sortCol, $validCols)) {
                    $orderBy = 'p.' . $sortCol;
                }
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

    /**
     * Elimina el registro de un producto de la tabla 'new_products'.
     * @param int $id El ID del registro en 'new_products'.
     * @return bool
     */
    public function delete(int $id): bool
    {
        $deleteQuery = "DELETE FROM new_products WHERE product = ?";
        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function deleteByProductId(int $productId): bool
    {
        $query = "DELETE FROM new_products WHERE product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}