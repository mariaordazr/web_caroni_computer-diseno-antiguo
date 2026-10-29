<?php
class Blog {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getAll($filters = []) {
        $sql = "SELECT * FROM blogs WHERE 1=1";
        $params = [];
        $types = ""; // Para MySQLi necesitamos definir los tipos de datos (s = string)

        if (!empty($filters['search'])) {
            $sql .= " AND (title LIKE ? OR content LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "ss"; // Dos strings para los dos LIKE
        }

        // Orden dinámico ajustado a los values de filterbar.php
        $sort = $filters['sort'] ?? 'created_at-desc';
        switch ($sort) {
            case 'title-asc': 
                $sql .= " ORDER BY title ASC"; break;
            case 'title-desc': 
                $sql .= " ORDER BY title DESC"; break;
            case 'updated_at-asc': 
                $sql .= " ORDER BY updated_at ASC"; break;
            case 'updated_at-desc': 
                $sql .= " ORDER BY updated_at DESC"; break;
            case 'created_at-asc': 
                $sql .= " ORDER BY created_at ASC"; break;
            default: 
                $sql .= " ORDER BY created_at DESC"; break;
        }

        $stmt = $this->db->prepare($sql);
        
        // En MySQLi vinculamos los parámetros así
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        
        // Obtenemos el resultado de la consulta preparada
        $result = $stmt->get_result();
        
        // Retornamos usando MYSQLI_ASSOC en lugar de PDO
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }

    public function create($data) {
        $sql = "INSERT INTO blogs (title, author, slug, content, cover_image, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", $data['title'], $data['author'], $data['slug'], $data['content'], $data['cover_image'], $data['status']);
        
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public function getById($id) {
        $sql = "SELECT * FROM blogs WHERE id_blog = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $data) {
        $sql = "UPDATE blogs SET title=?, author=?, slug=?, content=?, cover_image=?, status=? WHERE id_blog=?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssssi", $data['title'], $data['author'], $data['slug'], $data['content'], $data['cover_image'], $data['status'], $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM blogs WHERE id_blog = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getBySlug($slug) {
        // Buscamos por slug, y nos aseguramos de que solo se pueda ver si está "publicado"
        $sql = "SELECT * FROM blogs WHERE slug = ? AND status = 'publicado'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            return $result->fetch_assoc();
        }
        return false;
    }
}
?>