<?php

class Settings
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    // Obtiene el valor. Devuelve el valor directo o null si no existe.
    public function getValueByKey($setting_key) 
    {
        // Usamos backticks en `setting_key` y `setting_value` por seguridad
        $query = "SELECT `setting_value` FROM settings WHERE `setting_key` = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $setting_key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $data = $result->fetch_assoc(); 
        $stmt->close();
        
        // Retornamos el valor. Si es null, devolvemos null.
        return $data ? $data['setting_value'] : null;
    }

    // Actualiza el valor.
    public function updateValueByKey($setting_key, $setting_value)
    {
        // Forzamos que value sea entero porque tu BD lo define como INT
        $intValue = (int) $setting_value;

        $query = "UPDATE settings SET `setting_value` = ? WHERE `setting_key` = ?";

        $stmt = $this->db->prepare($query);
        // "is" significa: integer (valor), string (clave)
        $stmt->bind_param("is", $intValue, $setting_key);
        
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
}