<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email']) || !isset($_POST['notification_id'])) {
    exit(json_encode(['success' => false]));
}

// Get user ID first
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if($user) {
    $notification_id = $_POST['notification_id'];
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $notification_id, $user['id']);
    $success = $stmt->execute();

    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found']);
}
?>