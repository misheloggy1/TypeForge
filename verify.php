<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['temp_user'])) {
    header("Location: register.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = trim($_POST['code']);

    if ($entered_code == $_SESSION['temp_user']['v_code']) {
        $username = $_SESSION['temp_user']['username'];
        $email = $_SESSION['temp_user']['email'];
        $password = $_SESSION['temp_user']['password'];

        $insert_query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        
        if (mysqli_query($conn, $insert_query)) {
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['username'] = $username;
            unset($_SESSION['temp_user']);
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Email — TypeForge</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Verify Email</h2>
            <p>We sent a 6-digit code to <strong><?php echo htmlspecialchars($_SESSION['temp_user']['email']); ?></strong></p>
            
            <?php if($error != ""): ?>
                <div class="auth-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label>6-Digit Code</label>
                    <input type="text" name="code" required autocomplete="off" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 5px;">
                </div>
                <button type="submit" class="auth-submit-btn">Verify & Create Account</button>
            </form>
        </div>
    </div>
</body>
</html>
