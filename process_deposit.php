<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $currency_code = $_POST['currency_code'];
    $payment_method = $_POST['payment_method'];
    $reference = $_POST['reference'];
    $notes = $_POST['notes'];

    try {
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, currency_code, payment_method, reference, notes, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idssss", $user_id, $amount, $currency_code, $payment_method, $reference, $notes);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to submit deposit');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}