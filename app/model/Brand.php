<?php
// app/model/Brand.php

class Brand
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
     * Crea una nueva marca en la base de datos.
     * Corresponde a la lógica de agregar_marca.php
     * @param string $name El nombre de la marca.
     * @return bool
     */
    
    public function create(array $data): ?int
    {
        $insertQuery = "INSERT INTO brands (name) VALUES ( ?)";
        $stmt = $this->db->prepare($insertQuery);
        $stmt->bind_param("s", $data['name']);

        if ($stmt->execute()) {
            $newId = $this->db->insert_id; // Obtenemos el ID recién creado
            $stmt->close();
            return $newId; // Devolvemos el ID
        }
        
        $stmt->close();
        return null; // Devolvemos null si falla
    }


    public function getAllSimple(): array
    {
        // Añadimos WHERE b.hidden = 0
        $query = "SELECT 
                    b.id_brand, 
                    b.name, 
                    b.image,
                    b.created_at, 
                    b.updated_at 
                FROM brands b
                WHERE b.hidden = 0
                ORDER BY TRIM(LOWER(b.name)) ASC";

        $conditions = [];
        $params = [];
        $types = '';
        
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todas las marcas de la base de datos.
     * Corresponde a la lógica de lista_marcas.php
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $query = "SELECT 
                    b.id_brand, 
                    b.name, 
                    b.image, 
                    b.hidden,
                    b.created_at, 
                    b.updated_at 
              FROM brands b";

        $conditions = [];
        $params = [];
        $types = '';

        // 2. Añadimos la condición de búsqueda si existe
        if (!empty($filters['search'])) {
            $searchWords = explode(' ', $filters['search']);
            foreach ($searchWords as $word) {
                if (!empty($word)) {
                    $searchTerm = "%{$word}%";
                    // Buscamos solo en los campos de texto relevantes
                    $conditions[] = "(b.name LIKE ? OR b.created_at LIKE ? OR b.updated_at LIKE ?)";
                    array_push($params, $searchTerm, $searchTerm, $searchTerm);
                    $types .= 'sss';
                }
            }
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY b.id_brand";

        /// ... Dentro de function getAll ...
        $orderBy = 'b.created_at';
        $orderDir = 'DESC';
        if (!empty($filters['sort'])) {
            list($sortCol, $sortDir) = explode('-', $filters['sort']);
            $validCols = ['name', 'created_at', 'updated_at'];
            
            if ($sortCol === 'name') {
                $orderBy = "TRIM(LOWER(b.name))"; // <--- CAMBIO AQUÍ
            } elseif (in_array($sortCol, $validCols)) {
                $orderBy = 'b.' . $sortCol;
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
     * Obtiene una marca por su ID.
     * Corresponde a la lógica de editar_marca.php
     * @param int $id El ID de la marca.
     * @return array|null
     */

    // En app/model/Brand.php

    /**
     * Obtiene todas las marcas y cuenta cuántos productos están asociados a cada una.
     * @return array
     */
    public function getAllWithProductCount(): array
    {
        // Usamos LEFT JOIN para asegurarnos de que se listen todas las marcas,
        // incluso si no tienen productos (contarán como 0).
        $query = "SELECT 
                    b.id_brand,
                    b.name,
                    b.image,
                    COUNT(p.id_product) AS product_count
                FROM brands b
                LEFT JOIN products p ON b.id_brand = p.brand
                GROUP BY b.id_brand, b.name
                ORDER BY b.name ASC";
        
        $result = $this->db->query($query);
        $brands = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $brands[] = $row;
            }
        }
        return $brands;
    }


    public function getBrandsByCategoryId(int $categoryId): array
    {
        $query = "SELECT DISTINCT b.id_brand, b.name, b.hidden
                FROM brands b
                INNER JOIN products p ON b.id_brand = p.brand
                WHERE p.category = ? AND b.hidden = 0
                ORDER BY TRIM(LOWER(b.name)) ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
        $result = $stmt->get_result();
        $brands = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $brands;
    }

    public function getById(int $id): ?array
    {
        $query = "SELECT id_brand, name, image, created_at, updated_at FROM brands WHERE id_brand = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    /**
     * Actualiza el nombre de una marca.
     * Corresponde a la lógica de editar_marca.php
     * @param int $id El ID de la marca.
     * @param string $name El nuevo nombre.
     * @return bool
     */
    public function update(int $id, string $name): bool
    {
        $updateQuery = "UPDATE brands SET name = ? WHERE id_brand = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("si", $name, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Elimina una marca de la base de datos.
     * Corresponde a la lógica de eliminar_marca.php
     * @param int $id El ID de la marca a eliminar.
     * @return bool
     */
    public function delete(int $id): bool
    {
        $deleteQuery = "DELETE FROM brands WHERE id_brand = ?";
        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateImage(int $id, string $fileName): bool
    {
        $updateQuery = "UPDATE brands SET image = ? WHERE id_brand = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("si", $fileName, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function getFeaturedBrands(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        // Creamos los placeholders (?) para la consulta preparada
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        // Esta subconsulta busca el nombre de archivo de la primera imagen que encuentra
        // para un producto de la categoría correspondiente.
        $query = "SELECT 
                    b.id_brand, 
                    b.name,
                    b.image
                FROM brands b
                WHERE b.id_brand IN ($placeholders)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Añade este método para contar productos
    public function countProducts(int $id): int
    {
        $query = "SELECT COUNT(id_product) as total FROM products WHERE brand = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)$result['total'];
    }

    // Reemplaza tu método 'delete' actual por este
    public function deleteWithProducts(int $id): bool
    {
        $this->db->begin_transaction();
        try {
            // 1. Borra los productos asociados
            $productDeleteQuery = "DELETE FROM products WHERE brand = ?";
            $stmt = $this->db->prepare($productDeleteQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            // 2. Borra la marca
            $brandDeleteQuery = "DELETE FROM brands WHERE id_brand = ?";
            $stmt = $this->db->prepare($brandDeleteQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function updateHiddenStatus(int $brandId, int $status): bool
    {
        $query = "UPDATE brands SET `hidden` = ? WHERE id_brand = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $status, $brandId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}