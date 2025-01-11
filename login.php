<?php
// login.php
include 'db_config.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();

    // Check if the password matches
    if ($password === $storedPassword) {
        // Password is correct, start a session
        $_SESSION['email'] = $email;
        header("Location: dashboard.php"); // Redirect to a welcome page or dashboard
        exit();
    } else {
        echo "Invalid email or password.";
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
    <link rel="stylesheet" href="login.css">
    <title>User Login</title>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Login</h2>
            <form id="loginForm" action="login.php" method="post">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <br>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                
                <button type="submit">Login</button>
                <button onclick="redirectToForgotPassword()">Forgot Password?</button>
               
            </form>
            <!-- Forgot Password Button -->
            
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>

    <script>
        function redirectToForgotPassword() {
            window.location.href = 'forgot_password.php'; // Change this to the actual path of your forgot password page
        }
        
    </script>
</body>
</html>