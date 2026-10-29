<?php
class Client {
    private $db;

    public function __construct($connection) {
        $this->db = $connection;
    }

    public function getAll(array $filters = []) {
        $query = "SELECT * FROM clients";
        
        // Filtro de búsqueda
        if (!empty($filters['search'])) {
            $search = '%' . $this->db->real_escape_string($filters['search']) . '%';
            $query .= " WHERE client_id_number LIKE '$search' OR client_name LIKE '$search'";
        }
        
        $query .= " ORDER BY id_client DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id_client = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create(array $data) {
        $sql = "INSERT INTO clients (client_id_number, client_name) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $data['client_id_number'], $data['client_name']);
        return $stmt->execute() ? $this->db->insert_id : false;
    }

    public function update(int $id, array $data) {
        $sql = "UPDATE clients SET client_id_number = ?, client_name = ? WHERE id_client = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $data['client_id_number'], $data['client_name'], $id);
        return $stmt->execute();
    }

    public function delete(int $id) {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id_client = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>