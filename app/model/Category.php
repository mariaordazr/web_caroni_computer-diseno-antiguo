<?php
// app/model/Category.php

class Category
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    /**
     * Crea una nueva categoría en la base de datos.
     * Corresponde a la lógica de agregar_categoria.php
     * @param string $name El nombre de la categoría.
     * @return bool
     */
    // En app/model/Category.php

    /**
     * Obtiene todas las categorías y cuenta cuántos productos están asociados a cada una.
     * @return array
     */
    public function getAllWithProductCount(): array
    {
        // Usamos LEFT JOIN para asegurarnos de que se listen todas las categorías,
        // incluso si no tienen productos (contarán como 0).
        $query = "SELECT 
                    c.id_category,
                    c.name,
                    c.image,
                    COUNT(p.id_product) AS product_count
                FROM categories c
                LEFT JOIN products p ON c.id_category = p.category
                GROUP BY c.id_category, c.name
                ORDER BY c.name ASC";
        
        $result = $this->db->query($query);
        $categories = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        return $categories;
    }
    
    public function create(array $data): ?int
    {
        $insertQuery = "INSERT INTO categories (name) VALUES ( ?)";
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
        // 1. Empezamos con la consulta base, SIN 'ORDER BY'
        $query = "SELECT 
                    c.id_category, 
                    c.name, 
                    c.hidden,
                    c.created_at, 
                    c.updated_at,
                    c.image
                FROM categories c
                WHERE c.hidden = 0
                ORDER BY TRIM(LOWER(c.name)) ASC";

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
    
    

    public function getAll(array $filters = []): array
    {
        // 1. Empezamos con la consulta base, SIN 'ORDER BY'
        $query = "SELECT 
                    c.id_category, 
                    c.name, 
                    c.hidden,
                    c.created_at, 
                    c.updated_at,
                    c.image
                FROM categories c";

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
                    $conditions[] = "(c.name LIKE ? OR c.created_at LIKE ? OR c.updated_at LIKE ?)";
                    array_push($params, $searchTerm, $searchTerm, $searchTerm);
                    $types .= 'sss';
                }
            }
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY c.id_category";

        // --- Lógica de ordenamiento modificada ---
        $orderBy = 'c.created_at';
        $orderDir = 'DESC';
        
        if (!empty($filters['sort'])) {
            list($sortCol, $sortDir) = explode('-', $filters['sort']);
            $validCols = ['name', 'created_at', 'updated_at'];
            
            // Lógica específica para ignorar mayúsculas/espacios en el nombre
            if ($sortCol === 'name') {
                $orderBy = "TRIM(LOWER(c.name))";
            } elseif (in_array($sortCol, $validCols)) {
                $orderBy = 'c.' . $sortCol;
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
     * Obtiene una categoría por su ID.
     * Corresponde a la lógica de editar_categoria.php
     * @param int $id El ID de la categoría.
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $query = "SELECT id_category, name, image, created_at, updated_at FROM categories WHERE id_category = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function update(int $id, string $name, string $imageName): bool
    {
        $query = "UPDATE categories SET name = ?, image = ? WHERE id_category = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $name, $imageName, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function updateImage(int $id, string $fileName): bool
    {
        $updateQuery = "UPDATE categories SET image = ? WHERE id_category = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("si", $fileName, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool
    {
        $query = "DELETE FROM categories WHERE id_category = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // En app/model/Category.php

    /**
     * Obtiene una lista de categorías específicas por sus IDs,
     * incluyendo una imagen de producto para cada una.
     * @param array $ids Array de IDs de las categorías a obtener.
     * @return array
     */
    public function getFeaturedCategories(array $ids): array
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
                    c.id_category, 
                    c.name,
                    c.image
                FROM categories c
                WHERE c.id_category IN ($placeholders)";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Añade este método para contar productos
    public function countProducts(int $id): int
    {
        $query = "SELECT COUNT(id_product) as total FROM products WHERE category = ?";
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
            $productDeleteQuery = "DELETE FROM products WHERE category = ?";
            $stmt = $this->db->prepare($productDeleteQuery);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            
            // 2. Borra la categoría
            $categoryDeleteQuery = "DELETE FROM categories WHERE id_category = ?";
            $stmt = $this->db->prepare($categoryDeleteQuery);
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

    public function updateHiddenStatus(int $categoryId, int $status): bool
    {
        $query = "UPDATE categories SET `hidden` = ? WHERE id_category = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $status, $categoryId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}