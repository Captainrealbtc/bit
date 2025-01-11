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
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .withdrawal-card {
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
        .payment-method {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .payment-method.selected {
            border-color: #1eb15a;
            background-color: #f0fff4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="withdrawal-card">
            <h3 class="mb-4">Withdraw Funds</h3>
            
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

            <form id="withdrawalForm">
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" name="amount" required min="1" >
                    <small class="text-muted">Minimum withdrawal: 50</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Withdrawal Method</label>
                    <div class="payment-method" data-method="crypto">
                        <i class="fab fa-bitcoin"></i> Crypto Wallet
                    </div>
                    <div class="payment-method" data-method="bank">
                        <i class="fas fa-university"></i> Bank Transfer
                    </div>
                </div>

                <div id="cryptoFields" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Wallet Address</label>
                        <input type="text" class="form-control" name="wallet_address">
                    </div>
                </div>

                <div id="bankFields" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Bank Name</label>
                        <input type="text" class="form-control" name="bank_name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" class="form-control" name="account_number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" class="form-control" name="account_name">
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Submit Withdrawal</button>
                    <a href="dashboard.php" class="btn btn-light">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

    <script>
            withdrawalForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const selectedMethod = document.querySelector('.payment-method.selected');
    if (!selectedMethod) {
        alert('Please select a payment method');
        return;
    }
    
    const formData = new FormData(this);
    formData.append('payment_method', selectedMethod.dataset.method);
    
    fetch('process_withdrawal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            window.location.href = 'dashboard.php';
        } else {
            alert(data.message || 'Failed to submit withdrawal request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request');
    });
});


    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('.payment-method');
        const cryptoFields = document.getElementById('cryptoFields');
        const bankFields = document.getElementById('bankFields');
        const withdrawalForm = document.getElementById('withdrawalForm');
        const currencySelector = document.getElementById('currencySelector');
        const balanceAmount = <?php echo $user['balance']; ?>;

        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                paymentMethods.forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                
                const methodType = this.dataset.method;
                cryptoFields.style.display = methodType === 'crypto' ? 'block' : 'none';
                bankFields.style.display = methodType === 'bank' ? 'block' : 'none';
            });
        });

        withdrawalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('process_withdrawal.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Withdrawal request submitted successfully!');
                    window.location.href = 'dashboard.php';
                } else {
                    alert(data.message || 'Failed to submit withdrawal request');
                }
            });
        });

        // Currency conversion
        currencySelector.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const rate = parseFloat(selected.dataset.rate);
            const symbol = selected.dataset.symbol;
            
            document.getElementById('balanceAmount').textContent = 
                symbol + (balanceAmount * rate).toFixed(2);
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>