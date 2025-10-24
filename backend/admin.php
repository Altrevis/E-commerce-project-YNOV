<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'vendeur') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'products':
        $stmt = $pdo->query("SELECT * FROM produits ORDER BY id_produit");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($products);
        break;

    case 'orders':
        $stmt = $pdo->query("SELECT c.*, cl.prenom, cl.nom FROM commandes c JOIN clients cl ON c.id_client = cl.id_client ORDER BY c.date_commande DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($orders);
        break;

    case 'users':
        $stmt = $pdo->query("SELECT id_client, prenom, nom, email, role FROM clients ORDER BY id_client");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>
