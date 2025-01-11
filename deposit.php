<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$email = $_SESSION['email'];
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch active payment methods
$sql = "SELECT * FROM payment_methods WHERE status = 'active'";
$payment_methods = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Funds</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .deposit-card {
            max-width: 450px;
            filter: drop-shadow(0 30px 10px rgba(0, 0, 0, 0.125) );
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0px 10px 10px 10px rgba(250, 248, 248, 0.1);
            border-radius: 10px;
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.125);
            background: url(images/up-and-down-trend-with-arrows-isolated-on-dark-vector-51264088.avif);
          
        }
        .payment-option {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .payment-option:hover {
            background-color:rgb(160, 198, 236);
        }
        .payment-option.selected {
            border-color: #1eb15a;
            background-color:rgb(203, 212, 206);
        }
        .modal-body {
    padding: 20px;
    
}

.copy-btn {
    min-width: 50px;
}

.copy-btn:active {
    transform: scale(0.95);
}

.input-group {
    margin-bottom: 10px;
}

.input-group input {
    background-color:rgb(29, 38, 48);
}

.modal-header {
    background-color:rgb(1, 8, 15);
    border-bottom: 1px solid #dee2e6;
}

.fw-bold {
    margin-bottom: 5px;
    display: block;
}

.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    border-radius: 4px;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transform: translateX(100%);
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 9999;
}

.toast-notification.show {
    transform: translateX(0);
    opacity: 1;
}

.toast-notification.success {
    background: #4caf50;
    color: white;
}

.toast-notification.error {
    background: #f44336;
    color: white;
}

.copy-btn {
    transition: all 0.2s ease;
}

.copy-btn:active {
    transform: scale(0.95);
}

.copy-btn .fa-check {
    color: #4caf50;
}
body{
    background: url('images/body_bg.jpg') no-repeat center center fixed;
}
.mb-4{
text-align: center;
}
marquee{
    font-size: 20px;
    background-color: rgba(17, 25, 40, 0.25);
    filter: drop-shadow(0 30px 10px rgba(0, 0, 0, 0.125) );
}
    </style>
</head>
<body>
    
   

    <!-- Add this before the payment options -->
    
        <div class="deposit-card">
            <h3 class="mb-4">Add Funds</h3>
            <marquee behavior="" direction="">Make your deposit seemlessly and securely, Enjoy our services....</marquee>

            <div class="mb-3">
    <label class="form-label">Select Currency</label>
    <select id="currencySelector" class="form-select">
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
</div>

            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
    <input type="hidden" name="currency_code" id="currencyCode" value="NGN">
    <input type="hidden" name="payment_method" id="selectedMethod">
    <div class="container">
            <div class="mb-3">
        <label class="form-label">Amount <span id="currencySymbol">$</span></label>
        <input type="number" class="form-control" id="amount" name="amount" min="50" required>
        <small class="text-muted">Minimum deposit: <span id="minDeposit">$50</span></small>
    </div>

               <!-- Update the payment methods section in deposit.php -->
                
<div class="mb-3">
    <label class="form-label">Select Payment Method</label>
    
    <div class="payment-option" data-method="crypto" data-bs-toggle="modal" data-bs-target="#cryptoModal">
        <div class="d-flex align-items-center">
            <i class="fab fa-bitcoin me-2"></i>
            <div>
                <strong>Crypto Wallet</strong>
                <div class="text-muted small">Bitcoin, ETH, USDT</div>
            </div>
        </div>
    </div>

    <div class="payment-option" data-method="paypal" data-bs-toggle="modal" data-bs-target="#paypalModal">
        <div class="d-flex align-items-center">
            <i class="fab fa-paypal me-2"></i>
            <div>
                <strong>PayPal</strong>
                <div class="text-muted small">Send via PayPal</div>
            </div>
        </div>
    </div>

    <div class="payment-option" data-method="bank" data-bs-toggle="modal" data-bs-target="#bankModal">
        <div class="d-flex align-items-center">
            <i class="fas fa-university me-2"></i>
            <div>
                <strong>Bank Deposit</strong>
                <div class="text-muted small">Direct bank transfer</div>
            </div>
        </div>
    </div>

    <div class="payment-option" data-method="western" data-bs-toggle="modal" data-bs-target="#westernModal">
        <div class="d-flex align-items-center">
            <i class="fas fa-money-bill-wave me-2"></i>
            <div>
                <strong>Western Union</strong>
                <div class="text-muted small">International money transfer</div>
            </div>
        </div>
    </div>

    <div class="payment-option" data-method="skrill" data-bs-toggle="modal" data-bs-target="#skrillModal">
        <div class="d-flex align-items-center">
            <i class="fas fa-wallet me-2"></i>
            <div>
                <strong>Skrill</strong>
                <div class="text-muted small">E-wallet transfer</div>
            </div>
        </div>
        <div id="verificationSection" style="display: none;">
    <div class="mb-3">
        </div>
        </div>
        </div>
        <label class="form-label">Transaction Reference</label>
        <input type="text" class="form-control" name="reference" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Additional Notes</label>
        <textarea class="form-control" name="notes" rows="3"></textarea>
    </div>
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-success">Submit Verification</button>
        <a href="dashboard.php" class="btn btn-light">Back to Dashboard</a>
    </div>
</div>
    </div>
</div>

</form>

<!-- Add these modals before closing body tag -->
<!-- Crypto Modal -->
<!-- Update the Crypto Modal -->
<div class="modal fade" id="cryptoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crypto Wallet Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">Bitcoin (BTC)</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="btcAddress" value="bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('btcAddress')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Ethereum (ETH)</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="ethAddress" value="0x742d35Cc6634C0532925a3b844Bc454e4438f44e" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('ethAddress')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PayPal Modal -->
<div class="modal fade" id="paypalModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PayPal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <input type="text" class="form-control" id="paypalEmail" value="payments@yourcompany.com" readonly>
                    <button class="btn btn-outline-primary" onclick="copyToClipboard('paypalEmail')">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Bank Modal -->
<div class="modal fade" id="bankModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bank Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">Bank Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="bankName" value="Access Bank" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('bankName')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Account Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="accountName" value="BitWallet Trading" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('accountName')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Account Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="accountNumber" value="0765432109" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('accountNumber')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Western Union Modal -->
<div class="modal fade" id="westernModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Western Union Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">Recipient Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="wuName" value="John Smith" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('wuName')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Country</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="wuCountry" value="United States" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('wuCountry')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">City</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="wuCity" value="New York" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('wuCity')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Skrill Modal -->
<div class="modal fade" id="skrillModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Skrill Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">Skrill Email</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="skrillEmail" value="payments@bitwallet.com" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('skrillEmail')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Account Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="skrillName" value="BitWallet Trading" readonly>
                        <button class="btn btn-outline-primary" onclick="copyToClipboard('skrillName')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="d-grid gap-2">
    
</div>

<!-- Add clipboard.js and initialize copy functionality -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>

<script>

    

    document.addEventListener('DOMContentLoaded', function() {
    const currencySelector = document.getElementById('currencySelector');
    const currencySymbol = document.getElementById('currencySymbol');
    const minDeposit = document.getElementById('minDeposit');
    const currencyCode = document.getElementById('currencyCode');
    const depositForm = document.getElementById('depositForm');
    const verificationSection = document.getElementById('verificationSection');
    
    // Currency selection handling
    function updateCurrency() {
        const selected = currencySelector.options[currencySelector.selectedIndex];
        const symbol = selected.dataset.symbol;
        const rate = parseFloat(selected.dataset.rate);
        
        currencySymbol.textContent = symbol;
        minDeposit.textContent = `${symbol}50`;
        currencyCode.value = selected.value;
    }
    
    currencySelector.addEventListener('change', updateCurrency);
    
    // Payment method selection
    const paymentOptions = document.querySelectorAll('.payment-option');
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            document.getElementById('selectedMethod').value = this.dataset.method;
            verificationSection.style.display = 'block';
        });
    });
    
    // Form submission
    depositForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!document.getElementById('selectedMethod').value) {
            showToast('Please select a payment method', 'error');
            return;
        }
        
        const formData = new FormData(this);
        
        fetch('process_deposit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showToast('Deposit verification submitted successfully!');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
            } else {
                showToast(data.message || 'Failed to submit verification', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred', 'error');
        });
    });
});

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices

    try {
        // Execute copy command
        document.execCommand('copy');
        
        // Find the button that was clicked
        const button = event.currentTarget;
        const originalHTML = button.innerHTML;
        
        // Change button icon to checkmark
        button.innerHTML = '<i class="fas fa-check text-success"></i>';
        
        // Show toast
        showToast('Copied to clipboard!');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    } catch (err) {
        showToast('Failed to copy!', 'error');
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2000);
    }, 100);
}

// Payment option selection
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        this.classList.add('selected');
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize clipboard.js for all copy buttons
    const clipboard = new ClipboardJS('.copy-btn');

    // Success feedback for copy
    clipboard.on('success', function(e) {
        const button = e.trigger;
        const originalHTML = button.innerHTML;
        
        // Change button icon to checkmark
        button.innerHTML = '<i class="fas fa-check text-success"></i>';
        
        // Show toast notification
        showToast('Copied to clipboard!');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
        
        e.clearSelection();
    });

    // Error handling
    clipboard.on('error', function(e) {
        showToast('Failed to copy!', 'error');
    });

    // Payment option selection without form submission
    const paymentOptions = document.querySelectorAll('.payment-option');
    
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
});

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2000);
    }, 100);
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize clipboard.js
    new ClipboardJS('.copy-btn');

    // Add copy success feedback
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                this.innerHTML = originalText;
            }, 2000);
        });
    });

    // Payment option selection
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentMethodInput = document.getElementById('payment_method');

    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            paymentMethodInput.value = this.dataset.method;
        });
    });
});
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>