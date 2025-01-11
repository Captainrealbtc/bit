<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $amount = $_POST['amount'];
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    
    // Get user data
    $stmt = $conn->prepare("SELECT id, balance FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($amount > $user['balance']) {
        exit(json_encode(['success' => false, 'message' => 'Insufficient balance']));
    }

    $conn->begin_transaction();
    
    try {
        // Create withdrawal record
        $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, wallet_address, bank_details, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        
        $wallet_address = isset($_POST['wallet_address']) ? $_POST['wallet_address'] : null;
        $bank_details = null;
        
        if ($payment_method === 'bank') {
            $bank_details = json_encode([
                'bank_name' => $_POST['bank_name'],
                'account_number' => $_POST['account_number'],
                'account_name' => $_POST['account_name']
            ]);
        }
        
        $stmt->bind_param("idsss", $user['id'], $amount, $payment_method, $wallet_address, $bank_details);
        $stmt->execute();

        // Add notification
        $message = "Your withdrawal request of " . number_format($amount, 2) . " has been submitted";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user['id'], $message);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Withdrawal request submitted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>