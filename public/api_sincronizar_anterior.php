<?php
// api_sincronizar.php - VERSIÓN DEFINITIVA (Manejo inteligente de precios)
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

$host = "localhost";
$user = "";
$pass = ""; 
$db = "";

// Detectar el dominio actual
$domain = $_SERVER['HTTP_HOST'];

// Lógica condicional
if (strpos($domain, 'corporaciongruponet.com') !== false) {
    $user = "corpora6_admin";
    $pass = "!vz1Y7TouO8F$6";
    $db = "corpora6_gruponet";
    date_default_timezone_set("America/Lima");
} else {
    $user = "caronico_admin";
    $pass = "B20s9#Wu-vyJM8";
    $db = "caronico_gruponet_db";
    date_default_timezone_set("America/Caracas");
}

$TOKEN_SECRETO = "VALERY_A2141_SECURE";

// 1. Seguridad
$headers = getallheaders();
$auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
if ($auth !== $TOKEN_SECRETO) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Acceso denegado"]);
    exit;
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB Error: " . $conn->connect_error]);
    exit;
}


// 2. Detectar nombre de tabla seguro
$nombre_tabla = "products"; 
$check_table = $conn->query("SHOW TABLES LIKE 'products'");
if($check_table && $check_table->num_rows == 0) {
    $nombre_tabla = "productos";
}

// 3. NUEVA LÓGICA DE RESETEO
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    $conn->query("UPDATE $nombre_tabla SET valery_connected = 0");
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // 4. PREPARAMOS DOS CONSULTAS: Una con precio y otra sin precio
    $stmt_con_precio = $conn->prepare("UPDATE $nombre_tabla SET stock = ?, price = ?, valery_connected = 1 WHERE sku = ?");
    $stmt_sin_precio = $conn->prepare("UPDATE $nombre_tabla SET stock = ?, valery_connected = 1 WHERE sku = ?");

    if (!$stmt_con_precio || !$stmt_sin_precio) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Error preparando SQL."]);
        exit;
    }

    $actualizados = 0;
    $no_encontrados = [];

    foreach ($data as $item) {
        $sku = $item['sku'];
        $qty = $item['qty'];
        $price = isset($item['price']) ? (float)$item['price'] : 0.0;
        
        // 5. LÓGICA INTELIGENTE: Si hay precio válido, actualizamos ambos. Si no, solo el stock.
        if ($price > 0) {
            $stmt_con_precio->bind_param("ids", $qty, $price, $sku);
            $stmt_con_precio->execute();
            $afectados = $stmt_con_precio->affected_rows;
        } else {
            $stmt_sin_precio->bind_param("is", $qty, $sku);
            $stmt_sin_precio->execute();
            $afectados = $stmt_sin_precio->affected_rows;
        }
        
        if ($afectados > 0) {
            $actualizados++;
        } else {
            // Verificamos si no se actualizó porque no existe el SKU
            $check = $conn->query("SELECT count(*) FROM $nombre_tabla WHERE sku = '$sku'");
            $row = $check->fetch_row();
            
            if ($row[0] == 0) {
                $no_encontrados[] = $sku;
            } else {
                // Existe, pero el stock y el precio eran iguales. Forzamos la bandera de sincronización.
                if ($price > 0) {
                    $conn->query("UPDATE $nombre_tabla SET valery_connected = 1, price = $price WHERE sku = '$sku'");
                } else {
                    $conn->query("UPDATE $nombre_tabla SET valery_connected = 1 WHERE sku = '$sku'");
                }
                $actualizados++;
            }
        }
    }
    
    echo json_encode([
        "status" => "success",
        "stock_actualizado" => $actualizados,
        "skus_no_encontrados" => $no_encontrados
    ]);
    
    $stmt_con_precio->close();
    $stmt_sin_precio->close();
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Sin datos JSON"]);
}

$conn->close();
?>
