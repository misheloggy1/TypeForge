<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT * FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Generate 6-digit verification code
            $verification_code = rand(100000, 999999);
            
            // Store details in session temporarily
            $_SESSION['temp_user'] = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'v_code' => $verification_code
            ];

            // Send Email via Brevo API
            $api_key = $env['BREVO_API_KEY'];
            $data = [
                'sender' => ['name' => 'TypeForge', 'email' => 'kayanakoji75@gmail.com'],
                'to' => [['email' => $email]],
                'subject' => 'TypeForge Verification Code',
                'htmlContent' => "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #1e1e24; color: #d1d0c5; border-radius: 10px;'><h2 style='color:#e2b714;'>Welcome to TypeForge!</h2><p>Your 6-digit verification code is:</p><h1 style='color:#e2b714; letter-spacing: 4px;'>$verification_code</h1></div>"
            ];

            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: ' . $api_key,
                'content-type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 201 || $http_code == 200) {
                header("Location: verify.php");
                exit();
            } else {
                $error = "Brevo API Error (" . $http_code . "): " . htmlspecialchars($response);
                unset($_SESSION['temp_user']); // Clear temp data on failure
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — TypeForge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h2>Create Account</h2>
            <p>Start tracking your typing progress</p>

            <?php if($error != ""): ?>
                <div class="auth-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="off">
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required autocomplete="off">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="auth-submit-btn">Continue</button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Log in</a>
                <br><br>
                <a href="index.php" class="back-link">← Back to TypeForge</a>
            </div>
        </div>
    </div>
</body>
</html>
