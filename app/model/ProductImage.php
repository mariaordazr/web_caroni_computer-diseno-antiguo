<?php
// app/model/ProductImage.php

class ProductImage
{
    protected $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }


    // En app/model/ProductImage.php

// ... (tus otros métodos existentes: create, delete, etc.) ...

    // AGREGA ESTE MÉTODO QUE FALTABA
    public function getImagesByProductId(int $productId): array
    {
        // Importante: ORDER BY display_order ASC asegura que procesemos el orden correctamente
        $query = "SELECT * FROM product_images WHERE product = ? ORDER BY display_order ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        $stmt->close();
        return $images;
    }

    public function create(int $productId, string $fileName, int $order): bool {
        // CORRECCIÓN: Añadimos display_order a la consulta
        $query = "INSERT INTO product_images (product, file_name, display_order) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        // CORRECCIÓN: Añadimos "i" (integer) para el nuevo parámetro de orden
        $stmt->bind_param("isi", $productId, $fileName, $order);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getById(int $imageId): ?array {
        $query = "SELECT * FROM product_images WHERE id_image = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $imageId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function delete(int $imageId): bool {
        $query = "DELETE FROM product_images WHERE id_image = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $imageId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Obtiene todas las imágenes de un producto específico.
     * @param int $productId El ID del producto.
     * @return array
     */
    public function getAllForProduct(int $productId): array
    {
        $query = "SELECT id_image, file_name, file_route FROM product_images WHERE product = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
        $stmt->close();
        return $images;
    }

    /**
     * Actualiza el registro de una imagen.
     * @param int $id El ID de la imagen.
     * @param string $fileName El nuevo nombre de archivo.
     * @param string $fileRoute La nueva ruta.
     * @return bool
     */
    public function update(int $id, string $fileName, string $fileRoute): bool
    {
        $updateQuery = "UPDATE product_images SET file_name = ?, file_route = ? WHERE id_image = ?";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("ssi", $fileName, $fileRoute, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateOrder(int $imageId, int $order): bool {
        $query = "UPDATE product_images SET display_order = ? WHERE id_image = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $order, $imageId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

}