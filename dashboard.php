<?php
    // Start session and include database connection
    session_start();
    require_once 'db_config.php';

    // Check if user is logged in
    if (!isset($_SESSION['email'])) {
        header('Location: login.php');
        exit();
    }


    // Add this after your session start and database connection
    $email = $_SESSION['email'];
    
    // Get user ID first
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Then fetch notifications for this user
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Count unread notifications
    $sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $unread = $stmt->get_result()->fetch_assoc()['unread'];
    


    // Add this after fetching user data
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Count unread notifications
$sql = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$unread = $stmt->get_result()->fetch_assoc()['unread'];

    // Get user data
    $email = $_SESSION['email'];
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

  // Update the transaction query to include all financial activities
$sql = "SELECT 
'deposit' as source,
d.amount,
d.created_at as transaction_date,
'credit' as type,
CONCAT('Deposit via ', d.payment_method) as description,
d.currency_code,
d.status
FROM deposits d 
WHERE d.user_id = ? AND d.status = 'completed'

UNION ALL

SELECT 
'withdrawal' as source,
w.amount,
w.created_at as transaction_date,
'debit' as type,
CONCAT('Withdrawal via ', w.payment_method) as description,
w.currency_code,
w.status
FROM withdrawals w 
WHERE w.user_id = ? AND w.status = 'completed'

UNION ALL

SELECT 
'loan' as source,
l.amount,
l.created_at as transaction_date,
'credit' as type,
CONCAT('Loan Approved - ', SUBSTRING(l.reason, 1, 30)) as description,
l.currency_code,
l.status
FROM loans l 
WHERE l.user_id = ? AND l.status = 'approved'

ORDER BY transaction_date DESC 
LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user['id'], $user['id'], $user['id']);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> 
 <link rel="stylesheet" href="withdrawal.css">
 <link rel="icon" type="image/x-icon" href="favicon.ico">
 </head>
    <body>

    <nav class="navbar bg-body-tertiary fixed-top">
  <div class="container-fluid">
  <h2> <a >BitCRYPTO</a></h2>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Dashboard</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="dashboard.php">Dashbaord</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="deposit.php">Deposit</a>
          </li>
       
        <form class="d-flex mt-3" role="search">
          <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
          <button class="btn btn-outline-success" type="submit">Search</button>
        </form>
      </div>
    </div>
  </div>
</nav>
                    <!-- Add more nav items as needed -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="offcanvasNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            More
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="offcanvasNavbarDropdown">
                            <li><a class="dropdown-item" href="dashbaord.php">Dashbaord</a></li>
                            <li><a class="dropdown-item" href="deposit.php">Deposit</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        h4{
            color: white;
        }
        .notification-dropdown {
    width: 300px;
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f7ff;
}

.new-indicator {
    width: 8px;
    height: 8px;
    background-color: var(--primary-color);
    border-radius: 50%;
    display: inline-block;
    margin-left: 5px;
}
        :root {
            --color-1: #fff;
    --color-2: rgb(255, 255, 255, .8);
    --color-3: #000;
    --color-4: #00002c;
    --color-5: #000024;
    --color-6: #00b7c5;
    --color-7: #08d3d3;
        }

        .dashboard-header {
            background-color: var(--color-5);
            color: white;
            padding: 20px 0;
            border: 3px solid rgba(255, 255, 255, 0.125);
        }

        .balance-card {
            background: var(--color-4);
            border: 3px solid rgba(255, 255, 255, 0.125);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: whitesmoke;
            
        }
        .balance-card:hover{
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: #00002c;
        }        
       .balance-card img{
            width: 400px no-repeat center;
            height: 100px
            opacity: 50%;
        } 

        .quick-action {
            border: 3px solid rgba(255, 255, 255, 0.125);
            text-align: center;
            padding: 15px;
            background: whitesmoke;
            border-radius: 45px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-action:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: #00002c;
        }

        .quick-action i {
            font-size: 24px;
            color: var(--color-6);
            margin-bottom: 10px;
        }

        .transaction-list {
            background: white;
            border-radius: 10px;
            padding: 15px;
        }

        .transaction-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        body {
            background-color: #f5f5f5;
            background: url('images/background bit.png') no-repeat center fixed;
        }

        #currencySelector {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 5px 10px;
    border-radius: 4px;
}

#currencySelector option {
    background-color: white;
    color: #333;
}

.form-select-sm {
    height: 31px;
    padding-top: 2px;
    padding-bottom: 2px;
}
.transaction-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.transaction-item:last-child {
    border-bottom: none;
}

.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}
.btn, .btn-success .btn-sm{
border-radius: 20px;
}

    </style>
</head>
<body>


<div class="dashboard-header">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2> <a href="index.html">BitCRYPTO</a></h2>
            </div>
            
            <div class="col-md-6 text-end">
                <div class="d-inline-block me-3">
                    <select id="currencySelector" class="form-select form-select-sm">
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
           

<!-- Add this in your header section -->
<div class="col-md-6 text-end">
    <div class="d-inline-block me-3 position-relative">
        <a href="#" class="text-white position-relative" data-bs-toggle="dropdown">
            <i class="fas fa-bell fs-5"></i>
            <?php if($unread > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?php echo $unread; ?>
                </span>
            <?php endif; ?>
        </a>
        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
            <h6 class="dropdown-header">Notifications</h6>
            <div class="notification-list">
                <?php if(!empty($notifications)): ?>
                    <?php foreach($notifications as $notification): ?>
                        <a href="#" class="dropdown-item notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                           data-id="<?php echo $notification['id']; ?>">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small class="text-muted">
                                        <?php echo date('M d, H:i', strtotime($notification['created_at'])); ?>
                                    </small>
                                </div>
                                <?php if(!$notification['is_read']): ?>
                                    <div class="flex-shrink-0">
                                        <span class="new-indicator"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dropdown-item">No notifications</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
    
    <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
</div>
</div>
</div>
</div>
        </div>
    </div>
</div>
<!-- TradingView Widget BEGIN -->
<div class="tradingview-widget-container">
  <div class="tradingview-widget-container__widget"></div>
  <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-ticker-tape.js" async>
  {
  "symbols": [
    {
      "proName": "FOREXCOM:SPXUSD",
      "title": "S&P 500 Index"
    },
    {
      "proName": "FOREXCOM:NSXUSD",
      "title": "US 100 Cash CFD"
    },
    {
      "proName": "FX_IDC:EURUSD",
      "title": "EUR to USD"
    },
    {
      "proName": "BITSTAMP:BTCUSD",
      "title": "Bitcoin"
    },
    {
      "proName": "BITSTAMP:ETHUSD",
      "title": "Ethereum"
    }
  ],
  "showSymbolLogo": true,
  "isTransparent": true,
  "displayMode": "adaptive",
  "colorTheme": "dark",
  "locale": "en"
}
  </script>
</div>
<!-- TradingView Widget END -->
 <h4>Welcome,   <?php echo htmlspecialchars($user['username']); ?>!</h4>
<div class="container mt-4">
    <!-- Balance Section -->
     
    <div class="balance-card"> 
    <h5>Total Balance</h5>
    <h2>$<?php echo number_format($user['balance'], 2); ?><h2>
  <a href="deposit.php" class="btn btn-success btn-sm">Add Money</a>
</div>
                

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="quick-action">
                <i class="fas fa-paper-plane"></i>
               <div> <a href="withdrawal.php" class="btn btn-success btn-sm">Withdraw</a></div>
            </div>
        </div>
        <div class="col-3">
            <div class="quick-action">
                <i class="fas fa-mobile-alt"></i>
                <div><a href="loan.php" class="btn btn-success btn-sm">Loan</a></div>
            </div>
        </div>
        <div class="col-3">
            <div class="quick-action">
                <i class="fas fa-paper-plane"></i>
                <div><a href="transfer.php" class="btn btn-success btn-sm">Transfer</a></div>
            </div>
        </div>
        <div class="col-3">
            <div class="quick-action">
                <i class="fas fa-lightbulb"></i>
                <div><a href="referral.php" class="btn btn-success btn-sm">Referal</a></div>
            </div>
        </div>
    </div>
<!-- TradingView Widget BEGIN -->
<div class="tradingview-widget-container">
  <div class="tradingview-widget-container__widget"></div>
  <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js" async>
  {
  "colorTheme": "dark",
  "dateRange": "12M",
  "showChart": true,
  "locale": "en",
  "width": "100%",
  "height": "100%",
  "largeChartUrl": "",
  "isTransparent": true,
  "showSymbolLogo": true,
  "showFloatingTooltip": true,
  "plotLineColorGrowing": "rgba(255, 0, 0, 1)",
  "plotLineColorFalling": "rgba(32, 18, 77, 1)",
  "gridLineColor": "rgba(42, 46, 57, 0)",
  "scaleFontColor": "rgba(209, 212, 220, 1)",
  "belowLineFillColorGrowing": "rgba(56, 118, 29, 0.12)",
  "belowLineFillColorFalling": "rgba(32, 18, 77, 0.96)",
  "belowLineFillColorGrowingBottom": "rgba(41, 98, 255, 0)",
  "belowLineFillColorFallingBottom": "rgba(41, 98, 255, 0)",
  "symbolActiveColor": "rgba(41, 98, 255, 0.12)",
  "tabs": [
    {
      "title": "Indices",
      "symbols": [
        {
          "s": "FOREXCOM:SPXUSD",
          "d": "S&P 500 Index"
        },
        {
          "s": "FOREXCOM:NSXUSD",
          "d": "US 100 Cash CFD"
        },
        {
          "s": "FOREXCOM:DJI",
          "d": "Dow Jones Industrial Average Index"
        },
        {
          "s": "INDEX:NKY",
          "d": "Japan 225"
        },
        {
          "s": "INDEX:DEU40",
          "d": "DAX Index"
        },
        {
          "s": "FOREXCOM:UKXGBP",
          "d": "FTSE 100 Index"
        }
      ],
      "originalTitle": "Indices"
    },
    {
      "title": "Futures",
      "symbols": [
        {
          "s": "CME_MINI:ES1!",
          "d": "S&P 500"
        },
        {
          "s": "CME:6E1!",
          "d": "Euro"
        },
        {
          "s": "COMEX:GC1!",
          "d": "Gold"
        },
        {
          "s": "NYMEX:CL1!",
          "d": "WTI Crude Oil"
        },
        {
          "s": "NYMEX:NG1!",
          "d": "Gas"
        },
        {
          "s": "CBOT:ZC1!",
          "d": "Corn"
        }
      ],
      "originalTitle": "Futures"
    },
    {
      "title": "Bonds",
      "symbols": [
        {
          "s": "CBOT:ZB1!",
          "d": "T-Bond"
        },
        {
          "s": "CBOT:UB1!",
          "d": "Ultra T-Bond"
        },
        {
          "s": "EUREX:FGBL1!",
          "d": "Euro Bund"
        },
        {
          "s": "EUREX:FBTP1!",
          "d": "Euro BTP"
        },
        {
          "s": "EUREX:FGBM1!",
          "d": "Euro BOBL"
        }
      ],
      "originalTitle": "Bonds"
    },
    {
      "title": "Forex",
      "symbols": [
        {
          "s": "FX:EURUSD",
          "d": "EUR to USD"
        },
        {
          "s": "FX:GBPUSD",
          "d": "GBP to USD"
        },
        {
          "s": "FX:USDJPY",
          "d": "USD to JPY"
        },
        {
          "s": "FX:USDCHF",
          "d": "USD to CHF"
        },
        {
          "s": "FX:AUDUSD",
          "d": "AUD to USD"
        },
        {
          "s": "FX:USDCAD",
          "d": "USD to CAD"
        }
      ],
      "originalTitle": "Forex"
    }
  ]
}
  </script>
</div>
<!-- TradingView Widget END -->
 <div></div>
    <!-- TradingView Widget BEGIN -->
<div class="tradingview-widget-container">
  <div class="tradingview-widget-container__widget"></div>
  
  <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-screener.js" async>
  {
  "width": "100%",
  "height": "100%",
  "defaultColumn": "overview",
  "screener_type": "crypto_mkt",
  "displayCurrency": "USD",
  "colorTheme": "dark",
  "locale": "en",
  "isTransparent": true
}
  </script>
</div>
<!-- TradingView Widget END -->
    
    <div class="transaction-list">
    <h5 class="mb-3">Recent Transactions</h5>
    <?php if(!empty($transactions)): ?>
        <?php foreach($transactions as $transaction): ?>
            <div class="transaction-item">
                <div class="row">
                    <div class="col-8">
                        <div class="transaction-desc">
                            <?php echo htmlspecialchars($transaction['description'] ?? 'Transaction'); ?>
                        </div>
                        <small class="text-muted">
                            <?php echo date('d M Y, H:i', strtotime($transaction['transaction_date'])); ?>
                        </small>
                    </div>
                    <div class="col-4 text-end">
                        <div class="<?php echo ($transaction['type'] ?? '') == 'credit' ? 'text-success' : 'text-danger'; ?>"
                             data-amount="<?php echo $transaction['amount'] ?? 0; ?>">
                            <?php echo ($transaction['type'] ?? '') == 'credit' ? '+' : '-'; ?>
                            <?php echo $transaction['currency_code'] ?? ''; ?> 
                            <?php echo number_format($transaction['amount'] ?? 0, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-muted py-3">
            <p>No transactions found</p>
        </div>
    <?php endif; ?>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const currencySelector = document.getElementById('currencySelector');
    const balanceAmount = <?php echo $user['balance']; ?>;
    
    function updateCurrency(symbol, rate) {
        // Update main balance
        document.querySelector('.balance-card h2').innerHTML = 
            `${symbol}${(balanceAmount * rate).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        
        // Update transaction amounts
        document.querySelectorAll('.transaction-item .text-end div').forEach(el => {
            const amount = parseFloat(el.getAttribute('data-amount') || 0);
            const isCredit = el.classList.contains('text-success');
            el.innerHTML = `${isCredit ? '+' : '-'} ${symbol}${(amount * rate).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        });
    }
    
    currencySelector.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const symbol = selected.dataset.symbol;
        const rate = parseFloat(selected.dataset.rate);
        updateCurrency(symbol, rate);
        
        // Save preference
        localStorage.setItem('preferredCurrency', this.value);
    });
    
    // Load saved preference
    const savedCurrency = localStorage.getItem('preferredCurrency');
    if (savedCurrency) {
        currencySelector.value = savedCurrency;
        const selected = currencySelector.options[currencySelector.selectedIndex];
        updateCurrency(selected.dataset.symbol, parseFloat(selected.dataset.rate));
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.id;
            
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update UI
                    this.classList.remove('unread');
                    const indicator = this.querySelector('.new-indicator');
                    if(indicator) indicator.remove();
                    
                    // Update badge count
                    const badge = document.querySelector('.badge');
                    if(badge) {
                        const currentCount = parseInt(badge.textContent);
                        if(currentCount > 1) {
                            badge.textContent = currentCount - 1;
                        } else {
                            badge.remove();
                        }
                    }
                    
                    // Force refresh admin dashboard if open
                    if(window.opener) {
                        window.opener.location.reload();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
   
</script>
</body>
</html>