<?php
session_start();
require_once 'db_connect.php';

$sound_pref = 'off';
$diff_pref = 'beginner';

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT sound_pref, difficulty_pref FROM users WHERE id = $user_id");
    if($row = mysqli_fetch_assoc($res)) {
        $sound_pref = $row['sound_pref'] ?? 'off';
        $diff_pref = $row['difficulty_pref'] ?? 'beginner';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeForge — Settings</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Lexend+Deca:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body style="background-color: #1e1e24; color: #d1d0c5; font-family: 'Lexend Deca', sans-serif; margin: 0; padding: 20px;">
    <div class="top-auth-bar" style="display: flex; justify-content: space-between; align-items: center; max-width: 800px; margin: 0 auto 30px auto; padding: 10px 20px;">
        <div style="color: #e2b714; font-weight: 700; font-size: 1.2rem;">⚡ TypeForge</div>
        <a href="index.php" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">← Back to App</a>
    </div>

    <div style="max-width: 800px; margin: 0 auto; background-color: #25262c; border-radius: 16px; padding: 36px; border: 1px solid rgba(255,255,255,0.06);">
        <h2 style="color: #d1d0c5; margin-bottom: 8px; font-size: 1.6rem;">Preferences & Settings</h2>
        <p style="color: #646669; font-size: 0.85rem; margin-bottom: 30px;">Customize your typing environment and challenge rules.</p>
        
        <!-- Sound Preferences -->
        <div style="margin-bottom: 30px; padding-bottom: 24px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <h4 style="color: #d1d0c5; font-size: 1rem; margin-bottom: 6px;">Keypress Audio Effect</h4>
            <p style="color: #646669; font-size: 0.8rem; margin-bottom: 16px;">Play instant audio feedback when pressing keys.</p>
            
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="sound-pill active" data-sound="off" style="background: #1e1e24; color: #e2b714; border: 1px solid #e2b714; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Off</button>
                <button class="sound-pill" data-sound="click" style="background: #1e1e24; color: #646669; border: 1px solid rgba(255,255,255,0.05); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Click</button>
                <button class="sound-pill" data-sound="pop" style="background: #1e1e24; color: #646669; border: 1px solid rgba(255,255,255,0.05); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Pop</button>
                <button class="sound-pill" data-sound="beep" style="background: #1e1e24; color: #646669; border: 1px solid rgba(255,255,255,0.05); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">Beep</button>
            </div>
        </div>

        <!-- Difficulty Mode -->
        <div style="margin-bottom: 30px;">
            <h4 style="color: #d1d0c5; font-size: 1rem; margin-bottom: 6px;">Difficulty Mode</h4>
            <p style="color: #646669; font-size: 0.8rem; margin-bottom: 16px;">Set error tolerance rules for tests.</p>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                <div class="diff-card active" data-diff="beginner" style="background: #1e1e24; padding: 16px; border-radius: 10px; border: 1px solid #e2b714; cursor: pointer; text-align: center;">
                    <div style="font-weight: 700; color: #e2b714; font-size: 0.95rem; margin-bottom: 4px;">Beginner</div>
                    <div style="font-size: 0.75rem; color: #646669;">Standard backspacing allowed</div>
                </div>
                <div class="diff-card" data-diff="pro" style="background: #1e1e24; padding: 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); cursor: pointer; text-align: center;">
                    <div style="font-weight: 700; color: #d1d0c5; font-size: 0.95rem; margin-bottom: 4px;">Pro</div>
                    <div style="font-size: 0.75rem; color: #646669;">Strict penalty tracking</div>
                </div>
                <div class="diff-card" data-diff="legend" style="background: #1e1e24; padding: 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); cursor: pointer; text-align: center;">
                    <div style="font-weight: 700; color: #ca4754; font-size: 0.95rem; margin-bottom: 4px;">Legend</div>
                    <div style="font-size: 0.75rem; color: #646669;">1 typo & test fails instantly</div>
                </div>
            </div>
        </div>

        <button id="save-settings-btn" style="background-color: #e2b714; color: #1e1e24; border: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: 'Lexend Deca', sans-serif;">Save Preferences</button>
    </div>

    <script>
        let selectedSound = "<?php echo $sound_pref; ?>";
        let selectedDiff = "<?php echo $diff_pref; ?>";

        document.querySelectorAll('.sound-pill').forEach(btn => {
            if(btn.dataset.sound === selectedSound) {
                document.querySelectorAll('.sound-pill').forEach(b => { b.style.color='#646669'; b.style.borderColor='rgba(255,255,255,0.05)'; });
                btn.style.color = '#e2b714'; btn.style.borderColor = '#e2b714';
            }
            btn.addEventListener('click', () => {
                document.querySelectorAll('.sound-pill').forEach(b => { b.style.color='#646669'; b.style.borderColor='rgba(255,255,255,0.05)'; });
                btn.style.color = '#e2b714'; btn.style.borderColor = '#e2b714';
                selectedSound = btn.dataset.sound;
            });
        });

        document.querySelectorAll('.diff-card').forEach(card => {
            if(card.dataset.diff === selectedDiff) {
                document.querySelectorAll('.diff-card').forEach(c => { c.style.borderColor='rgba(255,255,255,0.05)'; });
                card.style.borderColor = '#e2b714';
            }
            card.addEventListener('click', () => {
                document.querySelectorAll('.diff-card').forEach(c => { c.style.borderColor='rgba(255,255,255,0.05)'; });
                card.style.borderColor = '#e2b714';
                selectedDiff = card.dataset.diff;
            });
        });

        document.getElementById('save-settings-btn').addEventListener('click', () => {
            fetch('update_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ sound: selectedSound, difficulty: selectedDiff })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Settings saved successfully!');
                    window.location.href = 'index.php';
                } else {
                    alert('Error saving settings. Please log in.');
                }
            });
        });
    </script>
</body>
</html>
