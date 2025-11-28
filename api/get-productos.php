<?php
require_once dirname(__DIR__) . '/config.php';

header('Content-Type: application/json');

$productos = [];
$categorias = [];

$prod_query = "SELECT p.*, c.nombre as categoria_nombre 
               FROM productos p 
               LEFT JOIN categoria c ON p.categoria_id = c.id 
               ORDER BY p.nombre";
$prod_result = $conn->query($prod_query);
if ($prod_result) {
    $productos = $prod_result->fetch_all(MYSQLI_ASSOC);
}

$cat_query = "SELECT * FROM categoria ORDER BY nombre";
$cat_result = $conn->query($cat_query);
if ($cat_result) {
    $categorias = $cat_result->fetch_all(MYSQLI_ASSOC);
}

echo json_encode([
    'productos' => $productos,
    'categorias' => $categorias
]);
?>

