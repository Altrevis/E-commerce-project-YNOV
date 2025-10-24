<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    // Get single product
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id_produit = ?");
    $stmt->execute([$_GET['id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($product);
} else {
    // Get all products
    $stmt = $pdo->query("SELECT * FROM produits ORDER BY id_produit");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
}
?>
