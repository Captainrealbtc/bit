<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['email'];
    $amount = $_POST['amount'];
    $reason = $_POST['reason'];
    $currency_code = $_POST['currency_code'];
    
    // Get user data
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    try {
        // Create loan request
        $stmt = $conn->prepare("INSERT INTO loans (user_id, amount, currency_code, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user['id'], $amount, $currency_code, $reason);
        
        if($stmt->execute()) {
            // Add notification
            $message = "Your loan request of " . number_format($amount, 2) . " " . $currency_code . " is pending approval";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->bind_param("is", $user['id'], $message);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to submit loan request');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>