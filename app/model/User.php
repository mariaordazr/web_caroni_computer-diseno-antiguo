<?php
// app/model/User.php (Actualizado)

class User
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }
    /**
     * Obtiene un usuario por sus credenciales.
     * @param string $username El nombre de usuario.
     * @param string $password La contraseña.
     * @return array|null
     */

    public function getUserByCredentials(string $username, string $password): ?array
    {
        $hashedPassword = $password; // Mantén tu lógica de encriptación actual
        
        // AGREGAMOS: role e is_super_admin a la consulta SQL
        $query = "SELECT id_user, username, name, email, role, is_super_admin FROM users WHERE username = ? AND password = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $username, $hashedPassword);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }

    /**
     * Crea un nuevo usuario administrador.
     * @param array $data Los datos del nuevo usuario.
     * @return bool
     */
    public function create(array $data): ?int
    {
        // Determinamos el valor interno de is_super_admin según el rol
        $is_super_admin = ($data['role'] === 'admin') ? 1 : 0;

        $query = "INSERT INTO users (name, username, email, password, role, is_super_admin, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        // Enlazar los tipos correctamente: s = string, i = integer
        $stmt->bind_param("sssssis", 
            $data['name'], 
            $data['username'], 
            $data['email'], 
            $data['password'], 
            $data['role'], 
            $is_super_admin,
            $data['avatar']
        );
        
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            return $id;
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Obtiene una lista de todos los usuarios.
     * @return array
     */
    public function getAll(array $filters = []): array
    {
        $query = "SELECT u.id_user, u.name, u.username, u.email, u.avatar, u.created_at, u.updated_at, u.is_super_admin, u.role
              FROM users u";
        
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
                    $conditions[] = "(u.name LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR u.created_at LIKE ? OR u.updated_at LIKE ? OR u.role LIKE ?)";
                    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
                    $types .= 'ssssss';
                }
            }
        }

        if (!empty($filters['role'])) {
            $conditions[] = "role = ?";
            $params[] = $filters['role'];
            $types .= "s";
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY u.id_user";

        /// ... Dentro de function getAll ...
        $orderBy = 'u.created_at';
        $orderDir = 'DESC';
        if (!empty($filters['sort'])) {
            list($sortCol, $sortDir) = explode('-', $filters['sort']);
            
            // Nota: en tu código original usabas 'u.name', etc.
            
            if ($sortCol === 'u.name' || $sortCol === 'name') {
                $orderBy = "TRIM(LOWER(u.name))"; // <--- CAMBIO
            } elseif ($sortCol === 'u.username' || $sortCol === 'username') {
                $orderBy = "TRIM(LOWER(u.username))"; // <--- CAMBIO
            } elseif ($sortCol === 'u.email' || $sortCol === 'email') {
                $orderBy = "TRIM(LOWER(u.email))"; // <--- CAMBIO (Opcional para email)
            } elseif ($sortCol === 'u.role' || $sortCol === 'role') {
                $orderBy = "TRIM(LOWER(u.role))"; // <--- CAMBIO (Opcional para role)
            } else {
                 // Fallback para fechas u otros
                 $validCols = ['u.created_at', 'u.updated_at'];
                 if (in_array($sortCol, $validCols)) {
                     $orderBy = $sortCol;
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

    public function getById(int $id): ?array
    {
        $query = "SELECT id_user, username, name, email, avatar, created_at, updated_at, is_super_admin, role
                FROM users 
                WHERE id_user = ?";
                
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function update(int $id, array $data): bool
    {
        // Determinamos el valor interno de is_super_admin según el rol
        $is_super_admin = ($data['role'] === 'admin') ? 1 : 0;

        // Validamos si viene una nueva contraseña o si se mantiene la anterior
        if (!empty($data['password'])) {
            $query = "UPDATE users SET name = ?, username = ?, email = ?, password = ?, role = ?, is_super_admin = ?, avatar = ? WHERE id_user = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sssssisi", $data['name'], $data['username'], $data['email'], $data['password'], $data['role'], $is_super_admin, $data['avatar'], $id);
        } else {
            $query = "UPDATE users SET name = ?, username = ?, email = ?, role = ?, is_super_admin = ?, avatar = ? WHERE id_user = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ssssisi", $data['name'], $data['username'], $data['email'], $data['role'], $is_super_admin, $data['avatar'], $id);
        }

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateAvatar(int $id, string $fileName): bool
    {
        $updateQuery = "UPDATE users SET avatar = ? WHERE id_user = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("si", $fileName, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    public function delete(int $id): bool
    {
        // --- NUEVA LÓGICA: No permitir eliminar al super admin ---
        $user = $this->getById($id);
        if ($user && $user['is_super_admin']) {
            return false; // No se puede eliminar
        }
        
        $query = "DELETE FROM users WHERE id_user = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * --- NUEVO MÉTODO ---
     * Asigna un nuevo Super Usuario.
     * Esto quita el rol al antiguo super admin y se lo da al nuevo.
     * @param int $newSuperUserId El ID del usuario que será el nuevo Super Admin.
     * @return bool
     */
    public function setSuperUser(int $newSuperUserId): bool
    {
        try {
            // Usamos una transacción para asegurar que ambas consultas funcionen
            $this->db->begin_transaction();

            // 1. Quitar el rol a todos los usuarios actuales (por si acaso)
            $this->db->query("UPDATE users SET is_super_admin = 0 WHERE is_super_admin = 1");

            // 2. Asignar el rol al nuevo usuario
            $stmt = $this->db->prepare("UPDATE users SET is_super_admin = 1 WHERE id_user = ?");
            $stmt->bind_param("i", $newSuperUserId);
            $stmt->execute();
            
            $stmt->close();
            
            // 3. Confirmar los cambios
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            // Si algo falla, revertir
            $this->db->rollback();
            return false;
        }
    }
}