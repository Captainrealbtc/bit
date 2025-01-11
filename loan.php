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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Loan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .loan-card {
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
        .loan-history {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="loan-card">
            <h3 class="mb-4">Apply for Loan</h3>
            
            <div class="balance-display">
                <div class="row">
                    <div class="col">
                        <h6>Current Balance</h6>
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

            <form id="loanForm">
                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" name="amount" required min="10" max="100000">
                    <small class="text-muted">Maximum loan amount: 1,000</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason for Loan</label>
                    <textarea class="form-control" name="reason" rows="3" required></textarea>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Submit Loan Request</button>
                    <a href="dashboard.php" class="btn btn-light">Back to Dashboard</a>
                </div>
            </form>

            <div class="loan-history">
                <h5>Loan History</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM loans WHERE user_id = ? ORDER BY created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $user['id']);
                            $stmt->execute();
                            $loans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            
                            foreach($loans as $loan): ?>
                            <tr>
                                <td><?php echo $loan['currency_code'] . ' ' . number_format($loan['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $loan['status'] == 'approved' ? 'success' : 
                                            ($loan['status'] == 'rejected' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($loan['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loanForm = document.getElementById('loanForm');
        const currencySelector = document.getElementById('currencySelector');
        const balanceAmount = <?php echo $user['balance']; ?>;

        currencySelector.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const rate = parseFloat(selected.dataset.rate);
            const symbol = selected.dataset.symbol;
            
            document.getElementById('balanceAmount').textContent = 
                symbol + (balanceAmount * rate).toFixed(2);
        });

        loanForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('currency_code', currencySelector.value);
            
            fetch('process_loan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Loan request submitted successfully!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to submit loan request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting your request');
            });
        });
    });

    // Example usage in a form submission
document.getElementById('yourForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showSpinner();
    
    fetch('your_endpoint.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(response => response.json())
    .then(data => {
        hideSpinner();
        // Handle response
    })
    .catch(error => {
        hideSpinner();
        // Handle error
    });
});
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>