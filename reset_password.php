<?php
session_start();
require_once 'db_connect.php';

$error = "";
$success = "";
$token_valid = false;
$token = $_GET['token'] ?? '';

if (!empty($token)) {
    // Select user by token only first
    $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        $db_expires = $user['reset_expires'];

        // Compare expiration using PHP timestamp logic (avoids SQL timezone mismatch)
        if (strtotime($db_expires) >= time()) {
            $token_valid = true;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                if ($password === $confirm_password) {
                    if (strlen($password) >= 6) {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Update password and clear out the token
                        $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                        $update->bind_param("si", $hashed_password, $user_id);
                        $update->execute();

                        $success = "Password successfully reset! You can now <a href='login.php' style='color: #e2b714;'>log in</a>.";
                        $token_valid = false; // Hide form
                    } else {
                        $error = "Password must be at least 6 characters long.";
                    }
                } else {
                    $error = "Passwords do not match.";
                }
            }
        } else {
            $error = "This password reset link has expired. Please request a new one.";
        }
    } else {
        $error = "Invalid password reset link.";
    }
} else {
    $error = "No reset token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeForge — Set New Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #1e1e24; font-family: 'Lexend Deca', sans-serif;">
    <div style="background-color: #25262c; padding: 36px; border-radius: 20px; width: 90%; max-width: 400px; border: 1px solid rgba(255,255,255,0.08); text-align: center;">
        <h2 style="color: #d1d0c5; margin-bottom: 8px; font-size: 1.5rem;">Create New Password</h2>
        <p style="color: #646669; font-size: 0.85rem; margin-bottom: 24px;">Please enter your secure new password</p>

        <?php if(!empty($success)): ?>
            <p style="color: #e2b714; font-size: 0.85rem; margin-bottom: 16px; background: rgba(226,183,20,0.1); padding: 10px; border-radius: 8px;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <p style="color: #ca4754; font-size: 0.85rem; margin-bottom: 16px; background: rgba(202,71,84,0.1); padding: 10px; border-radius: 8px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if($token_valid): ?>
        <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" style="text-align: left;">
            <label style="display: block; color: #646669; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">New Password</label>
            <input type="password" name="password" required style="width: 100%; background: #1e1e24; border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; color: #d1d0c5; font-size: 0.95rem; margin-bottom: 16px; outline: none;">
            
            <label style="display: block; color: #646669; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Confirm Password</label>
            <input type="password" name="confirm_password" required style="width: 100%; background: #1e1e24; border: 1px solid rgba(255,255,255,0.08); padding: 12px; border-radius: 8px; color: #d1d0c5; font-size: 0.95rem; margin-bottom: 20px; outline: none;">
            
            <button type="submit" style="width: 100%; background-color: #e2b714; color: #1e1e24; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.95rem;">Update Password</button>
        </form>
        <?php endif; ?>
        
        <p style="margin-top: 20px; font-size: 0.85rem;"><a href="login.php" style="color: #e2b714; text-decoration: none;">← Return to Login</a></p>
    </div>
</body>
</html>
