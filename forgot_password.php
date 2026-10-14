<?php
session_start();
require_once 'db_connect.php'; // Your database connection file

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!empty($email)) {
        // Check if user exists with this email
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate a secure unique token
            $token = bin2hex(random_bytes(32));
            // Set expiration to 1 hour from now safely
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Save token and expiry to database
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $token, $expires, $email);
            $update_stmt->execute();

            // Build the local reset link (change IP here if testing across devices on local Wi-Fi)
            $reset_link = "http://localhost/TypeForge/reset_password.php?token=" . $token;

            // --- SEND EMAIL VIA BREVO API ---
            $brevo_api_key = $env['BREVO_API_KEY'];
            
            $email_data = [
                "sender" => ["name" => "TypeForge", "email" => "kayanakoji75@gmail.com"], // Change to your verified sender email
                "to" => [["email" => $email, "name" => $user['username']]],
                "subject" => "Password Reset Request — TypeForge",
                "htmlContent" => "
                    <div style='background-color: #1e1e24; color: #d1d0c5; padding: 30px; font-family: sans-serif; border-radius: 12px;'>
                        <h2 style='color: #e2b714;'>Password Reset</h2>
                        <p>Hello <strong>{$user['username']}</strong>,</p>
                        <p>We received a request to reset your TypeForge password. Click the secure button below to choose a new password:</p>
                        <p style='margin: 30px 0;'>
                            <a href='{$reset_link}' style='background-color: #e2b714; color: #1e1e24; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold;'>Reset Password</a>
                        </p>
                        <p style='font-size: 0.85rem; color: #646669;'>This link will expire in 1 hour. If you didn't request this, you can safely ignore this email.</p>
                    </div>
                "
            ];

            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'api-key: ' . $brevo_api_key,
                'content-type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
            
            $response = curl_exec($ch);
            curl_close($ch);
            // ---------------------------------

            $message = "If that email exists in our system, a password reset link has been sent to your inbox.";
        } else {
            // Security safeguard to prevent user email harvesting/enumeration
            $message = "If that email exists in our system, a password reset link has been sent to your inbox.";
        }
    } else {
        $error = "Please enter your email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeForge — Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #1e1e24; font-family: 'Lexend Deca', sans-serif;">
    <div style="background-color: #25262c; padding: 36px; border-radius: 20px; width: 90%; max-width: 400px; border: 1px solid rgba(255,255,255,0.08); text-align: center;">
        <h2 style="color: #d1d0c5; margin-bottom: 8px; font-size: 1.5rem;">Reset Password</h2>
        <p style="color: #646669; font-size: 0.85rem; margin-bottom: 24px;">Enter your account email to receive a secure reset link</p>
        
        <?php if(!empty($message)): ?>
            <p style="color: #e2b714; font-size: 0.85rem; margin-bottom: 16px; background: rgba(226,183,20,0.1); padding: 10px; border-radius: 8px;"><?php echo $message; ?></p>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <p style="color: #ca4754; font-size: 0.85rem; margin-bottom: 16px; background: rgba(202,71,84,0.1); padding: 10px; border-radius: 8px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST" style="text-align: left;">
            <label style="display: block; color: #646669; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Email Address</label>
            <input type="email" name="email" required style="width: 100%; background: #1e1e24; border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; color: #d1d0c5; font-size: 0.95rem; margin-bottom: 20px; outline: none;">
            
            <button type="submit" style="width: 100%; background-color: #e2b714; color: #1e1e24; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.95rem;">Send Reset Link</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.85rem;"><a href="login.php" style="color: #e2b714; text-decoration: none;">← Back to Login</a></p>
    </div>
</body>
</html>
