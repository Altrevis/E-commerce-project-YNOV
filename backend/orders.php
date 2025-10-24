<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Assuming user is logged in
    $userId = $_SESSION['user_id'] ?? 1; // Default to first user for demo

    // Insert order
    $stmt = $pdo->prepare("INSERT INTO commandes (id_client, statut) VALUES (?, 'en cours')");
    $stmt->execute([$userId]);
    $orderId = $pdo->lastInsertId();

    // Insert order lines
    foreach ($data['cart'] as $item) {
        $stmt = $pdo->prepare("INSERT INTO lignes_commandes (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
    }

    // Insert payment (simplified)
    $total = array_sum(array_map(function($item) { return $item['price'] * $item['quantity']; }, $data['cart']));
    $stmt = $pdo->prepare("INSERT INTO paiements (id_commande, montant, mode_paiement) VALUES (?, ?, 'CB')");
    $stmt->execute([$orderId, $total]);

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} else {
    // Get orders for admin
    $stmt = $pdo->query("SELECT c.*, cl.prenom, cl.nom FROM commandes c JOIN clients cl ON c.id_client = cl.id_client ORDER BY c.date_commande DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
}
?>
