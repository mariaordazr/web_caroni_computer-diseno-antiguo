<?php

class SiteContent
{
    protected $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    // Fichero: app/model/SiteContent.php (Dentro de la clase SiteContent)

    public function getValue(string $key): ?string
    {
        // Reutiliza el método getContentByKey
        $data = $this->getContentByKey($key); 
        // Devuelve el valor o null si $data es null o la clave no existe
        return $data['content_value'] ?? null; 
    }

    public function getContentByKey(string $key): ?array 
    {
        $query = "SELECT content_key, 
                        content_value,
                        display_order 
                FROM site_content 
                WHERE content_key = ?
                ORDER BY display_order";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        // CLAVE: La firma ?array permite devolver null si fetch_assoc() no encuentra nada.
        $data = $result->fetch_assoc(); 
        $stmt->close();
        return $data; 
    }

    // Fichero: app/model/SiteContent.php (Añadir dentro de la clase SiteContent)

    /**
     * Recupera una lista ordenada de content_value (IDs) para una content_key exacta.
     * Devuelve un array de IDs (como strings).
     */
    public function getOrderedIdsByKey(string $key): array
    {
        $query = "SELECT content_value 
                  FROM site_content 
                  WHERE content_key = ?
                  ORDER BY display_order ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            // Se devuelve como string, lo que facilita la comparación en la vista
            $ids[] = strval($row['content_value']); 
        }
        
        $stmt->close();
        return $ids;
    }

    /**
     * Elimina todos los registros de site_content para una clave específica.
     */
    /**
     * Elimina todos los registros de site_content para una clave específica.
     */
    public function deleteContentByKey(string $key): bool
    {
        $query = "DELETE FROM site_content WHERE content_key = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $key);
        $result = $stmt->execute();
        
        // --- CORRECCIÓN: CERRAR LA SENTENCIA ---
        $stmt->close(); 
        // ---------------------------------------
        
        return $result;
    }

    /**
     * Actualiza el content_value de un registro específico por key y order.
     * Útil para 'borrar' una imagen de un slot (estableciendo el valor a vacío o NULL).
     */
    public function updateContentByKeyAndOrder(string $key, ?string $value, int $displayOrder): bool
    {
        // Si el valor es NULL, lo guardamos como cadena vacía para compatibilidad si la columna es NOT NULL
        $valueToUse = $value ?? ''; 
        
        // Verifica si el registro existe antes de actualizar (opcional, tu saveOrUpdateContent puede manejar esto)
        $query = "UPDATE site_content SET content_value = ? 
                  WHERE content_key = ? AND display_order = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $valueToUse, $key, $displayOrder);
        
        return $stmt->execute();
    }

    /**
     * Guarda una lista ordenada de IDs: elimina los viejos e inserta los nuevos secuencialmente.
     */
    public function saveContentList(string $key, array $ids): bool
    {
        // 1. Eliminar los registros antiguos
        if (!$this->deleteContentByKey($key)) {
            return false;
        }

        if (empty($ids)) {
            return true; // Éxito si solo se borraron y no hay nuevos que guardar
        }

        // 2. Insertar los nuevos IDs en orden secuencial
        $insertQuery = "INSERT INTO site_content (content_key, content_value, display_order) 
                        VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($insertQuery);
        
        $displayOrder = 1;
        foreach ($ids as $id) {
            $idString = strval($id); 
            $stmt->bind_param("ssi", $key, $idString, $displayOrder);
            
            if (!$stmt->execute()) {
                $stmt->close();
                return false; 
            }
            $displayOrder++;
        }
        
        $stmt->close();
        return true;
    }


    public function getOrderedContentByPrefix(string $prefix): array
    {
        $searchKey = $prefix . '%';
        
        // LA CLAVE: Incluir content_key y display_order en la sentencia SELECT
        $query = "SELECT content_value, content_key, display_order 
                FROM site_content 
                WHERE content_key LIKE ? 
                AND content_value != ''
                ORDER BY display_order ASC"; 
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $searchKey);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC); 
        $stmt->close();
        
        return $data;
    }

    // public function updateValueByKey(string $key, string $value): bool
    // {
    //     // 1. Intenta actualizar (reemplazar) el content_value
    //     $updateQuery = "UPDATE site_content SET content_value = ? WHERE content_key = ?";
    //     $stmt = $this->db->prepare($updateQuery);
    //     $stmt->bind_param("ss", $value, $key);
    //     $stmt->execute();
        
    //     // 2. Si no se actualizó (el registro no existía), inserta uno nuevo
    //     if ($this->db->affected_rows === 0) {
    //         $insertQuery = "INSERT INTO site_content (content_key, content_value) VALUES (?, ?)";
    //         $stmt->prepare($insertQuery);
    //         $stmt->bind_param("ss", $key, $value);
    //         return $stmt->execute();
    //     }
        
    //     $stmt->close();
    //     return true;
    // }

    public function updateValueByKey(string $key, string $value): bool
    {
        // 1. Verificar PRIMERO si ya existe alguna fila con esa clave
        $checkQuery = "SELECT id FROM site_content WHERE content_key = ?";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->store_result();
        
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            // 2. Si existe, hacemos UPDATE a TODOS los registros con esa clave.
            // Esto corrige el problema si ya tienes duplicados creados por el error anterior.
            $updateQuery = "UPDATE site_content SET content_value = ? WHERE content_key = ?";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bind_param("ss", $value, $key);
            return $stmt->execute();
        } else {
            // 3. Si NO existe, hacemos INSERT
            $insertQuery = "INSERT INTO site_content (content_key, content_value, display_order) VALUES (?, ?, 1)";
            $stmt = $this->db->prepare($insertQuery);
            $stmt->bind_param("ss", $key, $value);
            return $stmt->execute();
        }
    }


    // public function saveOrUpdateContent(string $key, string $value, int $displayOrder): bool
    // {
    //     // 1. Intentar actualizar
    //     $updateQuery = "UPDATE site_content SET content_value = ? 
    //                     WHERE content_key = ? AND display_order = ?";
    //     $stmt = $this->db->prepare($updateQuery);
    //     $stmt->bind_param("ssi", $value, $key, $displayOrder);
    //     $stmt->execute();
        
    //     // Guardamos el número de filas afectadas antes de cerrar
    //     $affectedRows = $this->db->affected_rows;

    //     // IMPORTANTE: Cerrar la sentencia del UPDATE antes de preparar una nueva
    //     $stmt->close();

    //     // 2. Si no se actualizó ninguna fila (porque no existe), insertamos
    //     if ($affectedRows === 0) {
    //         $insertQuery = "INSERT INTO site_content (content_key, content_value, display_order) 
    //                         VALUES (?, ?, ?)";
            
    //         $stmt = $this->db->prepare($insertQuery);
    //         // Si falla la preparación (ej. error de sintaxis), devolver false
    //         if (!$stmt) {
    //             return false;
    //         }
            
    //         $stmt->bind_param("ssi", $key, $value, $displayOrder);
    //         $result = $stmt->execute();
    //         $stmt->close(); // Cerramos también esta sentencia
    //         return $result;
    //     }

    //     return true;
    // }

    public function saveOrUpdateContent(string $key, string $value, int $displayOrder): bool
    {
        // 1. Verificar PRIMERO si ya existe la fila con esa clave y orden
        $checkQuery = "SELECT id FROM site_content WHERE content_key = ? AND display_order = ?";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->bind_param("si", $key, $displayOrder);
        $stmt->execute();
        $stmt->store_result();
        
        // Si num_rows > 0, significa que YA EXISTE
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            // 2. Si existe, hacemos UPDATE (incluso si el valor es el mismo, no pasará nada malo)
            $updateQuery = "UPDATE site_content SET content_value = ? 
                            WHERE content_key = ? AND display_order = ?";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bind_param("ssi", $value, $key, $displayOrder);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } else {
            // 3. Si NO existe, hacemos INSERT
            $insertQuery = "INSERT INTO site_content (content_key, content_value, display_order) 
                            VALUES (?, ?, ?)";
            
            $stmt = $this->db->prepare($insertQuery);
            if (!$stmt) {
                return false;
            }
            
            $stmt->bind_param("ssi", $key, $value, $displayOrder);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }
    }

    public function updateContentByKey(string $key, string $content, int $displayOrder): bool
    {
        $query = "UPDATE site_content SET content_value = ?, display_order = ? WHERE content_key = ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sis", $content, $displayOrder, $key);
        return $stmt->execute();
    }
}