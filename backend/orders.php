<?php
require_once 'db_connect.php';
require_once __DIR__ . '/helpers/pdf_generator.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/mailer_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
        exit;
    }

    $cartItems = $data['cart'] ?? [];
    if (empty($cartItems)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Votre panier est vide.']);
        exit;
    }

    $shipping = $data['shipping'] ?? [];
    $userId = (int) $_SESSION['user_id'];

    try {
        $pdo->beginTransaction();

        $userStmt = $pdo->prepare("SELECT * FROM clients WHERE id_client = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new RuntimeException('Utilisateur introuvable');
        }

        $productStmt = $pdo->prepare("SELECT id_produit, nom_produit, prix, stock FROM produits WHERE id_produit = ?");
        $orderItems = [];
        $total = 0.0;

        foreach ($cartItems as $item) {
            $productId = (int) ($item['id'] ?? 0);
            $quantity = max(1, (int) ($item['quantity'] ?? 0));

            $productStmt->execute([$productId]);
            $product = $productStmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                throw new RuntimeException("Produit $productId introuvable.");
            }

            if ((int)$product['stock'] < $quantity) {
                throw new RuntimeException("Stock insuffisant pour {$product['nom_produit']}");
            }

            $lineTotal = (float)$product['prix'] * $quantity;
            $total += $lineTotal;

            $orderItems[] = [
                'id' => (int)$product['id_produit'],
                'name' => $product['nom_produit'],
                'unit_price' => (float)$product['prix'],
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ];
        }

        $userBalance = (float)($user['solde'] ?? 0);
        if ($userBalance < $total) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Solde insuffisant pour régler cette commande.'
            ]);
            exit;
        }

        $orderStmt = $pdo->prepare("INSERT INTO commandes (id_client, statut) VALUES (?, 'en cours')");
        $orderStmt->execute([$userId]);
        $orderId = (int)$pdo->lastInsertId();

        $lineStmt = $pdo->prepare("INSERT INTO lignes_commandes (id_commande, id_produit, quantite, prix_unitaire) VALUES (?, ?, ?, ?)");
        $stockUpdateStmt = $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id_produit = ?");
        foreach ($orderItems as $line) {
            $lineStmt->execute([$orderId, $line['id'], $line['quantity'], $line['unit_price']]);
            $stockUpdateStmt->execute([$line['quantity'], $line['id']]);
        }

        $paymentStmt = $pdo->prepare("INSERT INTO paiements (id_commande, montant, mode_paiement, statut_paiement) VALUES (?, ?, 'CB', 'validé')");
        $paymentStmt->execute([$orderId, $total]);

        $newBalance = $userBalance - $total;
        $balanceStmt = $pdo->prepare("UPDATE clients SET solde = ? WHERE id_client = ?");
        $balanceStmt->execute([$newBalance, $userId]);

        $pdo->commit();

        $orderMeta = [
            'id' => $orderId,
            'total' => $total,
            'remaining_balance' => $newBalance,
            'date' => date('d/m/Y H:i'),
        ];

        $storageDir = __DIR__ . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $pdfPath = $storageDir . "/commande-{$orderId}.pdf";
        generateOrderPdf($orderMeta, $orderItems, $user, $shipping, $pdfPath);

        $emailSent = sendOrderEmail(
            $user['email'],
            "Votre commande #{$orderId}",
            buildOrderEmailBody($user, $orderMeta),
            $pdfPath
        );

        echo json_encode([
            'success' => true,
            'order_id' => $orderId,
            'remaining_balance' => $newBalance,
            'email_sent' => $emailSent
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    $stmt = $pdo->query("SELECT c.*, cl.prenom, cl.nom FROM commandes c JOIN clients cl ON c.id_client = cl.id_client ORDER BY c.date_commande DESC");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($orders);
}

function buildOrderEmailBody(array $user, array $order): string
{
    $fullName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
    return sprintf(
        "Bonjour %s,\n\nMerci pour votre commande #%d. Vous trouverez en pièce jointe votre reçu au format PDF.\nMontant réglé : %.2f €.\nSolde restant : %.2f €.\n\nÀ très bientôt sur E-commerce YNOV !",
        $fullName,
        $order['id'],
        $order['total'],
        $order['remaining_balance']
    );
}

function sendOrderEmail(string $to, string $subject, string $body, string $pdfPath): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $body;

        if (is_readable($pdfPath)) {
            $mail->addAttachment($pdfPath);
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}