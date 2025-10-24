<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($data['action'] === 'register') {
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO clients (prenom, nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$data['firstname'], $data['lastname'], $data['email'], $data['phone'], $hashedPassword]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
    }
} elseif ($data['action'] === 'login') {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id_client'];
        $_SESSION['user_role'] = $user['role'];
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
}
?>
