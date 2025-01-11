<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get sender details - Updated query
$stmt = $conn->prepare("SELECT id, balance, email FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $sender_email);
$stmt->execute();
$sender = $stmt->get_result()->fetch_assoc();

// Get recipient details - Updated query
$stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $recipient_email);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();

// Update balance queries
$stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?");
$stmt->bind_param("dii", $amount, $sender['id'], $amount);

$stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
$stmt->bind_param("di", $amount, $recipient['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Funds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .transfer-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .balance-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="transfer-card">
            <h3 class="mb-4">Transfer Funds</h3>
            
            <div class="balance-display">
                <div class="row">
                    <div class="col">
                        <h6>Available Balance</h6>
                        <select id="currencySelector" class="form-select mb-2">
                            <?php
                            $sql = "SELECT * FROM currencies";
                            $currencies = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
                            foreach($currencies as $currency): ?>
                                <option value="<?php echo $currency['code']; ?>" 
                                        data-symbol="<?php echo $currency['symbol']; ?>"
                                        data-rate="<?php echo $currency['rate']; ?>">
                                    <?php echo $currency['code']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <h3><span id="balanceAmount"><?php echo number_format($user['balance'], 2); ?></span></h3>
                    </div>
                </div>
            </div>

            <form id="transferForm">
                <div class="mb-3">
                    <label class="form-label">Recipient Email</label>
                    <input type="text" class="form-control" name="recipient" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" name="amount" required min="10" >
                    <small class="text-muted">Minimum transfer: 10</small>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Transfer Funds</button>
                    <a href="dashboard.php" class="btn btn-light">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        
    document.addEventListener('DOMContentLoaded', function() {
        const transferForm = document.getElementById('transferForm');
        const currencySelector = document.getElementById('currencySelector');
        const balanceAmount = <?php echo $user['balance']; ?>;

        currencySelector.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const rate = parseFloat(selected.dataset.rate);
            const symbol = selected.dataset.symbol;
            
            document.getElementById('balanceAmount').textContent = 
                symbol + (balanceAmount * rate).toFixed(2);
        });

        transferForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('currency_code', currencySelector.value);
            
            fetch('process_transfer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Transfer completed successfully!');
                    window.location.href = 'dashboard.php';
                } else {
                    alert(data.message || 'Failed to complete transfer');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to complete transfer');
            });
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>