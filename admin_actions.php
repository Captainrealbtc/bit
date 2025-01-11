<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['admin_email'])) {
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'edit_user':
        $user_id = $_POST['user_id'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $balance = $_POST['balance'];
        
        $stmt = $conn->prepare("UPDATE users SET email = ?, password = ?, balance = ? WHERE id = ?");
        $stmt->bind_param("ssdi", $email, $password, $balance, $user_id);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
        break;

    case 'add_user':
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $password);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add user']);
        }
        break;

    case 'delete_user':
        $user_id = $_POST['user_id'];
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        break;

    case 'send_notification':
        $users = $_POST['users'];
        $message = $_POST['message'];
        $success = true;
        
        foreach($users as $user_id) {
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $message);
            
            if(!$stmt->execute()) {
                $success = false;
                break;
            }
        }
        
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        case 'delete_notification':
            if(isset($_POST['notification_id'])) {
                $notification_id = $_POST['notification_id'];
                
                $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
                $stmt->bind_param("i", $notification_id);
                
                if($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
            }
            break;

            case 'approve_deposit':
                $deposit_id = $_POST['deposit_id'];
                $user_id = $_POST['user_id'];
                $amount = $_POST['amount'];
                
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    // Update deposit status
                    $stmt = $conn->prepare("UPDATE deposits SET status = 'completed' WHERE id = ?");
                    $stmt->bind_param("i", $deposit_id);
                    $stmt->execute();
                    
                    // Update user balance
                    $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->bind_param("di", $amount, $user_id);
                    $stmt->execute();
                    
                    // Add notification
                    $message = "Your deposit of " . number_format($amount, 2) . " has been approved,<br>
                    send your payment to your manager!";
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmt->bind_param("is", $user_id, $message);
                    $stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;
            
            case 'reject_deposit':
                $deposit_id = $_POST['deposit_id'];
                $user_id = $_POST['user_id'];
                
                $conn->begin_transaction();
                
                try {
                    // Update deposit status
                    $stmt = $conn->prepare("UPDATE deposits SET status = 'rejected' WHERE id = ?");
                    $stmt->bind_param("i", $deposit_id);
                    $stmt->execute();
                    
                    // Add notification
                    $message = "Your deposit request has been rejected";
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $stmt->bind_param("is", $user_id, $message);
                    $stmt->execute();
                    
                    $conn->commit();
                    echo json_encode(['success' => true]);
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;

                case 'add_payment_method':
                    $name = $_POST['name'];
                    $details = $_POST['details'];
                    
                    $stmt = $conn->prepare("INSERT INTO payment_methods (name, details) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $details);
                    
                    if($stmt->execute()) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to add payment method']);
                    }
                    break;

                    case 'approve_withdrawal':
                        $withdrawal_id = $_POST['withdrawal_id'];
                        $user_id = $_POST['user_id'];
                        $amount = $_POST['amount'];
                        
                        $conn->begin_transaction();
                        
                        try {
                            // Update withdrawal status
                            $stmt = $conn->prepare("UPDATE withdrawals SET status = 'completed' WHERE id = ?");
                            $stmt->bind_param("i", $withdrawal_id);
                            $stmt->execute();
                            
                            // Update user balance
                            $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
                            $stmt->bind_param("di", $amount, $user_id);
                            $stmt->execute();
                            
                            // Add notification
                            $message = "Your withdrawal of " . number_format($amount, 2) . " has been approved";
                            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                            $stmt->bind_param("is", $user_id, $message);
                            $stmt->execute();
                            
                            $conn->commit();
                            echo json_encode(['success' => true]);
                        } catch (Exception $e) {
                            $conn->rollback();
                            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                        }
                        break;
                    
                    case 'reject_withdrawal':
                        $withdrawal_id = $_POST['withdrawal_id'];
                        $user_id = $_POST['user_id'];
                        
                        $stmt = $conn->prepare("UPDATE withdrawals SET status = 'rejected' WHERE id = ?");
                        $stmt->bind_param("i", $withdrawal_id);
                        
                        if($stmt->execute()) {
                            // Add notification
                            $message = "Your withdrawal request has been rejected";
                            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                            $stmt->bind_param("is", $user_id, $message);
                            $stmt->execute();
                            
                            echo json_encode(['success' => true]);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Failed to reject withdrawal']);
                        }
                       
                        case 'approve_loan':
                            $loan_id = $_POST['loan_id'];
                            $user_id = $_POST['user_id'];
                            $amount = $_POST['amount'];
                            
                            $conn->begin_transaction();
                            
                            try {
                                // Update loan status
                                $stmt = $conn->prepare("UPDATE loans SET status = 'approved' WHERE id = ?");
                                $stmt->bind_param("i", $loan_id);
                                $stmt->execute();
                                
                                // Update user balance
                                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                                $stmt->bind_param("di", $amount, $user_id);
                                $stmt->execute();
                                
                                // Add notification
                                $message = "Your loan request of " . number_format($amount, 2) . " has been approved";
                                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                                $stmt->bind_param("is", $user_id, $message);
                                $stmt->execute();
                                
                                $conn->commit();
                                echo json_encode(['success' => true]);
                            } catch (Exception $e) {
                                $conn->rollback();
                                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                            }
                            break;
                        
                        case 'reject_loan':
                            $loan_id = $_POST['loan_id'];
                            $user_id = $_POST['user_id'];
                            
                            try {
                                // Update loan status
                                $stmt = $conn->prepare("UPDATE loans SET status = 'rejected' WHERE id = ?");
                                $stmt->bind_param("i", $loan_id);
                                $stmt->execute();
                                
                                // Add notification
                                $message = "Your loan request has been rejected";
                                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                                $stmt->bind_param("is", $user_id, $message);
                                $stmt->execute();
                                
                                echo json_encode(['success' => true]);
                            } catch (Exception $e) {
                                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                            }
                            break;
}


?>