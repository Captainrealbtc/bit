<?php
// register.php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    // Store the password without hashing (not recommended)
    $plainPassword = $password;

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $username, $email, $plainPassword);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to login page after successful registration
        header("Location: login.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $referral_code = isset($_GET['ref']) ? $_GET['ref'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $referred_by = $_POST['referred_by'];

    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    $conn->begin_transaction();

    try {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, referred_by) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $referred_by);
        $stmt->execute();
        
        if ($referred_by) {
            // Get referrer's ID
            $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->bind_param("s", $referred_by);
            $stmt->execute();
            $referrer = $stmt->get_result()->fetch_assoc();
            
            if ($referrer) {
                // Add bonus to referrer's balance
                $bonus = 10; // $10 bonus
                $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->bind_param("di", $bonus, $referrer['id']);
                $stmt->execute();
                
                // Add notification
                $message = "You received $10 bonus for referring a new user!";
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmt->bind_param("is", $referrer['id'], $message);
                $stmt->execute();
            }
        }
        
        $conn->commit();
        header("Location: login.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
    // Close the statement and connection
    $stmt->close();
    $conn->close();




}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <title>User Registration</title>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Register</h2>
            <form id="registrationForm" action="register.php" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required>

                <input type="hidden" name="referred_by" value="<?php echo htmlspecialchars($referral_code); ?>">

                <button type="submit">Register</button>
                <button type="button" onclick="redirectToLogin()">Login</button>
            </form>
        </div>
        <div class="slider-container">
            <div class="slider">
                <div class="slide">
                    <img src="images/country1.jpg" alt="Country 1">
                    <p>USA: Description</p>
                </div>
                <div class="slide">
                    <img src="images/country2.jpg" alt="Country 2">
                    <p>Canada: Description</p>
                </div>
                <div class="slide">
                    <img src="images/country3.jpg" alt="Country 3">
                    <p>Morroco: Description</p>
                </div>
            </div>
        </div>
    </div>
    <script>
        function redirectToLogin() {
            window.location.href = 'login.php';
        }
    </script>
    <script src="script.js"></script>
</body>
</html>