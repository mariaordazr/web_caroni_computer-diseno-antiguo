<?php
class Receipt {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    // --- MÉTODOS PARA LA CONFIGURACIÓN ---
    public function getSettings() {
        $sql = "SELECT setting_key, setting_value FROM receipt_settings";
        $result = $this->db->query($sql);
        $settings = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Esto crea un arreglo tipo ['company_name' => 'Valor', 'phone' => 'Valor']
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
        return $settings;
    }

    public function updateSetting($key, $value) {
        $sql = "INSERT INTO receipt_settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $key, $value, $value);
        return $stmt->execute();
    }

    public function getAll(array $filters = []) {
        $query = "SELECT r.*, u.name AS creator_name FROM receipts r LEFT JOIN users u ON r.created_by = u.id_user";
        $conditions = [];
        $params = [];
        $types = '';

        // 1. Filtro de Búsqueda por Texto (Avanzado: Múltiples palabras sin importar orden ni mayúsculas/minúsculas)
        if (!empty($filters['search'])) {
            // Limpiamos los espacios, convertimos todo a minúsculas (soportando acentos) y dividimos
            $searchTermRaw = mb_strtolower(trim(preg_replace('/\s+/', ' ', $filters['search'])), 'UTF-8');
            $words = explode(' ', $searchTermRaw);
            
            $wordConditions = [];
            foreach ($words as $word) {
                // Usamos LOWER() en cada campo de la BD para compararlo con nuestra palabra en minúsculas
                $wordConditions[] = "(LOWER(r.receipt_code) LIKE ? OR LOWER(r.client_name) LIKE ? OR LOWER(r.client_phone) LIKE ? OR LOWER(r.items_json) LIKE ? OR LOWER(r.notes) LIKE ? OR LOWER(r.private_notes) LIKE ? OR LOWER(r.budget_json) LIKE ? OR LOWER(r.technician_name) LIKE ? OR LOWER(u.name) LIKE ?)";
                
                $paramValue = "%" . $word . "%";
                // Ahora son 8 parámetros ('s') por cada palabra
                $types .= "sssssssss";
                array_push($params, $paramValue, $paramValue, $paramValue, $paramValue, $paramValue, $paramValue, $paramValue, $paramValue, $paramValue);
            }
            
            // Unimos las condiciones de cada palabra con 'AND'
            $conditions[] = "(" . implode(" AND ", $wordConditions) . ")";
        }

        // 2. Filtro de Estado de Envío
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'sent_wa':
                    $conditions[] = "sent_whatsapp = 1";
                    break;
                case 'sent_email':
                    $conditions[] = "sent_email = 1";
                    break;
                case 'not_sent':
                    // No enviado = No se ha enviado por WA Y tampoco por Email
                    $conditions[] = "(sent_whatsapp = 0 AND sent_email = 0)";
                    break;
                case 'edited':
                    // Editado = Fecha de creación distinta a fecha de actualización
                    $conditions[] = "created_at != updated_at";
                    break;
                case 'in_progress':
                    // En proceso = No está finalizado
                    $conditions[] = "is_finished = 0";
                    break;
                case 'finished':
                    // Finalizado = Está finalizado
                    $conditions[] = "is_finished = 1";
                    break;
            }
        }

        // Ensamblar la consulta con las condiciones
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // 3. Ordenamiento
        $orderBy = "created_at DESC"; // Por defecto, las más recientes primero
        if (!empty($filters['sort'])) {
            if ($filters['sort'] === 'created_at-asc') {
                $orderBy = "created_at ASC";
            } elseif ($filters['sort'] === 'updated_at-desc') {
                $orderBy = "updated_at DESC";
            } elseif ($filters['sort'] === 'updated_at-asc') {
                $orderBy = "updated_at ASC";
            }
        }
        $query .= " ORDER BY " . $orderBy;

        // Ejecutar la consulta
        if (!empty($params)) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            // Si no hay parámetros (ej: carga inicial sin búsqueda), usamos query simple
            $result = $this->db->query($query);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
    }

    public function getById($id) {
        $sql = "SELECT * FROM receipts WHERE id_receipt = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM receipts WHERE access_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function create($data) {
        // Se añaden los 4 campos nuevos antes del pdf_filename
        $sql = "INSERT INTO receipts (receipt_code, access_token, client_name, client_id_number, client_phone, client_email, client_address, items_json, budget_json, images_json, process_images_json, notes, private_notes, technician_name, created_by, updated_by, pdf_filename) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) { return false; }
        
        // s = string, i = integer (Total: 15 's', 2 'i')
        $stmt->bind_param("ssssssssssssssiis", 
            $data['receipt_code'], $data['access_token'], $data['client_name'], 
            $data['client_id_number'], $data['client_phone'], $data['client_email'], 
            $data['client_address'], $data['items_json'], $data['budget_json'],
            $data['images_json'], $data['process_images_json'], $data['notes'], 
            $data['private_notes'], $data['technician_name'], $data['created_by'], 
            $data['updated_by'], $data['pdf_filename']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id; 
        } else {
            error_log("Error ejecutando inserción de recibo: " . $stmt->error);
            return false;
        }
    }

    public function update($id, $data) {
        $sql = "UPDATE receipts SET 
                client_name = ?, client_id_number = ?, client_phone = ?, 
                client_email = ?, client_address = ?, items_json = ?, 
                budget_json = ?, images_json = ?, process_images_json = ?, 
                notes = ?, private_notes = ?, technician_name = ?, updated_by = ?,
                sent_email = ?, sent_whatsapp = ?
                WHERE id_receipt = ?";
                
        $stmt = $this->db->prepare($sql);
        
        // 12 strings ('s') y 4 ints ('i')
        $stmt->bind_param("ssssssssssssiiii", 
            $data['client_name'], $data['client_id_number'], $data['client_phone'], 
            $data['client_email'], $data['client_address'], $data['items_json'], 
            $data['budget_json'], $data['images_json'], $data['process_images_json'],
            $data['notes'], $data['private_notes'], $data['technician_name'], 
            $data['updated_by'], $data['sent_email'], $data['sent_whatsapp'], $id
        );
        
        return $stmt->execute();
    }



    public function delete($id) {
        $sql = "DELETE FROM receipts WHERE id_receipt = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateSentStatus($id, $column, $value) {
        // Validamos que la columna sea una de las permitidas para evitar inyecciones
        if (!in_array($column, ['sent_email', 'sent_whatsapp'])) return false;
        
        $sql = "UPDATE receipts SET $column = ? WHERE id_receipt = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $value, $id);
        return $stmt->execute();
    }

    public function updateFinishedStatus($id, $status) {
        $sql = "UPDATE receipts SET is_finished = ? WHERE id_receipt = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $status, $id);
        return $stmt->execute();
    }

    // --- NUEVOS MÉTODOS PARA CLIENTES ---
    public function saveClient($dni, $name) {
        // Inserta el cliente. Si el DNI ya existe, actualiza el nombre por si hubo correcciones.
        $sql = "INSERT INTO clients (client_id_number, client_name) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE client_name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $dni, $name, $name);
        return $stmt->execute();
    }

    public function getClientByDni($dni) {
        $sql = "SELECT client_name FROM clients WHERE client_id_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>

