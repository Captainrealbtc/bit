<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Generate referral code if not exists
if (empty($user['referral_code'])) {
    $referral_code = $user['username'] . '_' . substr(md5(uniqid()), 0, 8);
    $stmt = $conn->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
    $stmt->bind_param("si", $referral_code, $user['id']);
    $stmt->execute();
    $user['referral_code'] = $referral_code;
}

$referral_link = "http://" . $_SERVER['HTTP_HOST'] . "/bit/register.php?ref=" . $user['referral_code'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Program</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .referral-card {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .referral-link {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="referral-card">
            <h3 class="mb-4">Referral Program</h3>
            
            <div class="alert alert-info">
                Earn $10 for each new user who registers using your referral link!
            </div>

            <div class="referral-link">
                <label class="form-label">Your Referral Link:</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="referralLink" value="<?php echo $referral_link; ?>" readonly>
                    <button class="btn btn-outline-primary" onclick="copyLink()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="dashboard.php" class="btn btn-light">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <script>
    function copyLink() {
        const linkInput = document.getElementById('referralLink');
        linkInput.select();
        linkInput.setSelectionRange(0, 99999);
        document.execCommand('copy');
        
        const button = event.currentTarget;
        const originalHTML = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-success"></i>';
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>