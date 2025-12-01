<?php
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true) ?? [];

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Action non spécifiée']);
    exit;
}

if ($data['action'] === 'register') {
    // Inscription d'un nouvel utilisateur puis connexion automatique de ce même utilisateur
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO clients (prenom, nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$data['firstname'], $data['lastname'], $data['email'], $data['phone'], $hashedPassword]);

        // Récupérer l'ID du nouvel utilisateur
        $newUserId = $pdo->lastInsertId();

        // Charger les infos complètes de ce nouvel utilisateur
        $selectStmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
        $selectStmt->execute([$newUserId]);
        $user = $selectStmt->fetch(PDO::FETCH_ASSOC);

        // Sauvegarder dans la session pour que la page de compte utilise CE пользователя
        if ($user) {
            $_SESSION['user_id'] = $user['id_client'];
            // Si la colonne role existe, on la prend, иначе client par défaut
            $_SESSION['user_role'] = $user['role'] ?? 'client';
        }

        echo json_encode(['success' => true, 'user' => $user]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription']);
    }
} elseif ($data['action'] === 'login') {
    // Авторизация существующего пользователя
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['password'], $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id_client'];
        $_SESSION['user_role'] = $user['role'] ?? 'client';
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
} elseif ($data['action'] === 'me') {
    // Вернуть данные ТЕКУЩЕГО пользователя по session, а не первого в таблице
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    }
} elseif ($data['action'] === 'logout') {
    // Déconnexion : détruire la session côté serveur
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    echo json_encode(['success' => true]);
} elseif ($data['action'] === 'topup') {
    // Ajouter des fonds au solde de l'utilisateur connecté
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }

    $amount = isset($data['amount']) ? (float)$data['amount'] : 0;
    if ($amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Montant invalide']);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE clients SET solde = solde + ? WHERE id_client = ?");
    $stmt->execute([$amount, $userId]);

    $select = $pdo->prepare("SELECT solde FROM clients WHERE id_client = ?");
    $select->execute([$userId]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'new_balance' => isset($row['solde']) ? (float)$row['solde'] : null
    ]);
}
?>
