<?php
session_start();
require_once 'db_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_email'])) {
    header('Location: admin_login.php');
    exit();
}

// Rest of your code...

// Fetch all users
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all notifications with user details
$sql = "SELECT notifications.*, users.username, users.email 
        FROM notifications 
        JOIN users ON notifications.user_id = users.id 
        ORDER BY notifications.created_at DESC";
$notifications = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Update this query at the top of your Admin.php file
$sql = "SELECT n.*, u.email as username 
        FROM notifications n 
        LEFT JOIN users u ON n.user_id = u.id 
        ORDER BY n.created_at DESC";
$notifications = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-sidebar {
            min-height: 100vh;
            background: #1eb15a;
            color: white;
        }
        .nav-link {
            color: white;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .content-area {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 admin-sidebar p-0">
            <div class="p-3">
                <h4>Admin Panel</h4>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link active" href="#users" data-bs-toggle="tab">Users Management</a>
                <a class="nav-link" href="#notifications" data-bs-toggle="tab">Notifications</a>
                <a class="nav-link" href="#deposits" data-bs-toggle="tab">Deposits</a>
                <a class="nav-link" href="#withdrawals" data-bs-toggle="tab">Withdrawals</a>
                <a class="nav-link" href="#loans" data-bs-toggle="tab">Loans</a>
                <a class="nav-link" href="admin_logout.php">Logout</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <div class="content-area">
                <div class="tab-content">
                    <!-- Users Management Tab -->
                    <div class="tab-pane fade show active" id="users">
                        <h3>Users Management</h3>
                        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus"></i> Add New User
                        </button>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Balance</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($users as $user): ?>
                                        <tr>
    <td><?php echo $user['id']; ?></td>
    <td><?php echo htmlspecialchars($user['email']); ?></td>
    <td><?php echo $user['password']; ?></td>
    <td><?php echo number_format($user['balance'], 2); ?></td>
    <td><?php echo $user['created_at']; ?></td>
    <td>
        <button class="btn btn-sm btn-primary edit-user" 
                data-id="<?php echo $user['id']; ?>"
                data-email="<?php echo htmlspecialchars($user['email']); ?>"
                data-password="<?php echo htmlspecialchars($user['password']); ?>"
                data-balance="<?php echo $user['balance']; ?>"
                data-bs-toggle="modal" 
                data-bs-target="#editUserModal">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
<!-- Add this to your tab content -->
<div class="tab-pane fade" id="deposits">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Payment Methods</h5>
                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                        <i class="fas fa-plus"></i> Add Method
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM payment_methods ORDER BY name";
                                $methods = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
                                foreach($methods as $method): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($method['name']); ?></td>
                                    <td><?php echo htmlspecialchars($method['details']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $method['status'] == 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($method['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-method" 
                                                data-id="<?php echo $method['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($method['name']); ?>"
                                                data-details="<?php echo htmlspecialchars($method['details']); ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMethodModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    <!-- Deposit Requests Section -->
    <div class="card">
        <div class="card-header">
            <h5>Deposit Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT d.*, u.email FROM deposits d 
                                JOIN users u ON d.user_id = u.id 
                                ORDER BY d.created_at DESC";
                        $deposits = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
                        foreach($deposits as $deposit): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($deposit['email']); ?></td>
                            <td><?php echo number_format($deposit['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($deposit['payment_method']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $deposit['status'] == 'completed' ? 'success' : 
                                        ($deposit['status'] == 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($deposit['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($deposit['created_at'])); ?></td>
                            <td>
                                <?php if($deposit['status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success approve-deposit" 
                                            data-id="<?php echo $deposit['id']; ?>"
                                            data-user-id="<?php echo $deposit['user_id']; ?>"
                                            data-amount="<?php echo $deposit['amount']; ?>">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger reject-deposit" 
                                            data-id="<?php echo $deposit['id']; ?>"
                                            data-user-id="<?php echo $deposit['user_id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawals Tab -->
 


<!-- Add this tab content -->
<div class="tab-pane fade" id="withdrawals">
    <div class="card">
        <div class="card-header">
            <h5>Withdrawal Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT w.*, u.email 
                                FROM withdrawals w 
                                JOIN users u ON w.user_id = u.id 
                                ORDER BY w.created_at DESC";
                        $withdrawals = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
                        foreach($withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($withdrawal['email']); ?></td>
                                <td><?php echo $withdrawal['currency_code'] ?? ''; ?> 
                                    <?php echo number_format($withdrawal['amount'], 2); ?>
                                </td>
                                <td><?php echo htmlspecialchars($withdrawal['payment_method']); ?></td>
                                <td>
                                    <?php if($withdrawal['payment_method'] == 'crypto'): ?>
                                        Wallet: <?php echo htmlspecialchars($withdrawal['wallet_address']); ?>
                                    <?php else: ?>
                                        <?php 
                                        $bank_details = json_decode($withdrawal['bank_details'], true);
                                        if($bank_details): ?>
                                            Bank: <?php echo htmlspecialchars($bank_details['bank_name']); ?><br>
                                            Acc: <?php echo htmlspecialchars($bank_details['account_number']); ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $withdrawal['status'] == 'completed' ? 'success' : 
                                            ($withdrawal['status'] == 'rejected' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($withdrawal['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($withdrawal['created_at'])); ?></td>
                                <td>
                                    <?php if($withdrawal['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-success approve-withdrawal" 
                                                data-id="<?php echo $withdrawal['id']; ?>"
                                                data-user-id="<?php echo $withdrawal['user_id']; ?>"
                                                data-amount="<?php echo $withdrawal['amount']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger reject-withdrawal" 
                                                data-id="<?php echo $withdrawal['id']; ?>"
                                                data-user-id="<?php echo $withdrawal['user_id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>




<!-- Add this tab content -->
<div class="tab-pane fade" id="loans">
    <div class="card">
        <div class="card-header">
            <h5>Loan Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT l.*, u.email 
                                FROM loans l 
                                JOIN users u ON l.user_id = u.id 
                                ORDER BY l.created_at DESC";
                        $loans = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
                        foreach($loans as $loan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['email']); ?></td>
                                <td><?php echo $loan['currency_code'] . ' ' . number_format($loan['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($loan['reason']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $loan['status'] == 'approved' ? 'success' : 
                                            ($loan['status'] == 'rejected' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($loan['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($loan['created_at'])); ?></td>
                                <td>
                                    <?php if($loan['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-success approve-loan" 
                                                data-id="<?php echo $loan['id']; ?>"
                                                data-user-id="<?php echo $loan['user_id']; ?>"
                                                data-amount="<?php echo $loan['amount']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger reject-loan" 
                                                data-id="<?php echo $loan['id']; ?>"
                                                data-user-id="<?php echo $loan['user_id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>







<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addMethodForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Method Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label>Details</label>
                        <textarea class="form-control" name="details" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Method</button>
                </div>
            </form>
        </div>
    </div>
</div>
                    
                    <!-- Notifications Tab -->
<div class="tab-pane fade" id="notifications">
    <h3>Notifications Management</h3>
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#sendNotificationModal">
        <i class="fas fa-bell"></i> Send New Notification
    </button>
    
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($notifications as $notification): ?>
                <tr>
                    <td><?php echo $notification['id']; ?></td>
                    <td><?php echo htmlspecialchars($notification['username']); ?></td>
                    <td><?php echo htmlspecialchars($notification['message']); ?></td>
                    <td>
                        <?php if($notification['is_read'] == 1): ?>
                            <span class="badge bg-success">Read</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Unread</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></td>
                    <td>
                        <button class="btn btn-sm btn-danger delete-notification" data-id="<?php echo $notification['id']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Add User Modal -->
<div class="modal fade" id="addUserModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addUserForm">
                <div class="modal-body">
                <div class="mb-3">
                        <label>username</label>
                        <input type="email" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotificationModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="sendNotificationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Select Users</label>
                        <select class="form-select" name="users[]" multiple required>
                            <?php foreach($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea class="form-control" name="message" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="text" class="form-control" name="password" id="edit_password" required>
                    </div>
                    <div class="mb-3">
                        <label>Balance </label>
                        <input type="number" step="0.01" class="form-control" name="balance" id="edit_balance" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Add this JavaScript for delete functionality -->
<script>
document.querySelectorAll('.delete-notification').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to delete this notification?')) {
            const notificationId = this.dataset.id;
            fetch('admin_actions.php?action=delete_notification', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        }
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add User
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('admin_actions.php?action=add_user', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    });

    // Delete User
    document.querySelectorAll('.delete-user').forEach(btn => {
        btn.addEventListener('click', function() {
            if(confirm('Are you sure you want to delete this user?')) {
                const userId = this.dataset.id;
                fetch('admin_actions.php?action=delete_user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
            }
        });
    });

    // Edit User
document.querySelectorAll('.edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.dataset.id;
        const email = this.dataset.email;
        const password = this.dataset.password;
        const balance = this.dataset.balance;
        
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_password').value = password;
        document.getElementById('edit_balance').value = balance;
    });
});

document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('admin_actions.php?action=edit_user', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
});

    // Send Notification
    document.getElementById('sendNotificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('admin_actions.php?action=send_notification', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        });
    });
});

// Add Payment Method
document.getElementById('addMethodForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('admin_actions.php?action=add_payment_method', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to add payment method');
        }
    });
});

// Handle Deposits
document.querySelectorAll('.approve-deposit, .reject-deposit').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.classList.contains('approve-deposit') ? 'approve' : 'reject';
        const message = `Are you sure you want to ${action} this deposit?`;
        
        if(confirm(message)) {
            const depositId = this.dataset.id;
            const userId = this.dataset.userId;
            const amount = this.dataset.amount;
            
            fetch(`admin_actions.php?action=${action}_deposit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `deposit_id=${depositId}&user_id=${userId}&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || `Failed to ${action} deposit`);
                }
            });
        }
    });
});

// Handle Withdrawal Actions
document.querySelectorAll('.approve-withdrawal').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to approve this withdrawal?')) {
            const withdrawalId = this.dataset.id;
            const userId = this.dataset.userId;
            const amount = this.dataset.amount;
            
            fetch('admin_actions.php?action=approve_withdrawal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `withdrawal_id=${withdrawalId}&user_id=${userId}&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to approve withdrawal');
                }
            });
        }
    });
});

document.querySelectorAll('.reject-withdrawal').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to reject this withdrawal?')) {
            const withdrawalId = this.dataset.id;
            const userId = this.dataset.userId;
            
            fetch('admin_actions.php?action=reject_withdrawal', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `withdrawal_id=${withdrawalId}&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to reject withdrawal');
                }
            });
        }
    });
});

// Handle Loan Actions
document.querySelectorAll('.approve-loan').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to approve this loan?')) {
            const loanId = this.dataset.id;
            const userId = this.dataset.userId;
            const amount = this.dataset.amount;
            
            fetch('admin_actions.php?action=approve_loan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `loan_id=${loanId}&user_id=${userId}&amount=${amount}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to approve loan');
                }
            });
        }
    });
});

document.querySelectorAll('.reject-loan').forEach(btn => {
    btn.addEventListener('click', function() {
        if(confirm('Are you sure you want to reject this loan?')) {
            const loanId = this.dataset.id;
            const userId = this.dataset.userId;
            
            fetch('admin_actions.php?action=reject_loan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `loan_id=${loanId}&user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to reject loan');
                }
            });
        }
    });
});
</script>
</body>
</html>