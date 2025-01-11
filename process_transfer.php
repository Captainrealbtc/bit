<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['email'])) {
    exit(json_encode(['success' => false, 'message' => 'Please login to continue']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$sender_email = $_SESSION['email'];
$recipient_email = trim($_POST['recipient']);
$amount = floatval($_POST['amount']);

// Validate inputs
if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
    exit(json_encode(['success' => false, 'message' => 'Invalid recipient email']));
}

if ($amount <= 0) {
    exit(json_encode(['success' => false, 'message' => 'Invalid amount']));
}

if ($sender_email === $recipient_email) {
    exit(json_encode(['success' => false, 'message' => 'Cannot transfer to yourself']));
}

try {
    $conn->begin_transaction();

    // Get sender details
    $stmt = $conn->prepare("SELECT id, balance FROM users WHERE email = ? FOR UPDATE");
    $stmt->bind_param("s", $sender_email);
    $stmt->execute();
    $sender = $stmt->get_result()->fetch_assoc();

    if (!$sender) {
        throw new Exception('Sender account not found');
    }

    if ($amount > $sender['balance']) {
        throw new Exception('Insufficient balance');
    }

    // Get recipient details
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? FOR UPDATE");
    $stmt->bind_param("s", $recipient_email);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();

    if (!$recipient) {
        throw new Exception('Recipient account not found');
    }

    // Update sender's balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->bind_param("di", $amount, $sender['id']);
    $stmt->execute();

    // Update recipient's balance
    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
    $stmt->bind_param("di", $amount, $recipient['id']);
    $stmt->execute();

    // Record transaction for sender
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'debit', ?, ?)");
    $description = "Transfer to " . $recipient_email;
    $stmt->bind_param("ids", $sender['id'], $amount, $description);
    $stmt->execute();

    // Record transaction for recipient
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'credit', ?, ?)");
    $description = "Transfer from " . $sender_email;
    $stmt->bind_param("ids", $recipient['id'], $amount, $description);
    $stmt->execute();

    // Add notification for recipient
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $message = "You received " . number_format($amount, 2) . " from " . $sender_email;
    $stmt->bind_param("is", $recipient['id'], $message);
    $stmt->execute();

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Transfer completed successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>