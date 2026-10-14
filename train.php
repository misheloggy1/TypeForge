<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$user_sound = 'off';
$user_diff = 'beginner';

// Fetch global settings
$res = mysqli_query($conn, "SELECT sound_pref, difficulty_pref FROM users WHERE id = $uid");
if($row = mysqli_fetch_assoc($res)) {
    $user_sound = $row['sound_pref'] ?? 'off';
    $user_diff = $row['difficulty_pref'] ?? 'beginner';
}

// Fetch top 4 most missed alphabetical keys
$weak_keys = [];
$wk_query = "SELECT missed_key FROM user_key_faults 
             WHERE user_id = '$uid' AND missed_key REGEXP '^[a-zA-Z]$' 
             ORDER BY error_count DESC LIMIT 4";
$wk_res = mysqli_query($conn, $wk_query);
while($row = mysqli_fetch_assoc($wk_res)) {
    $weak_keys[] = strtolower($row['missed_key']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Train Weak Keys — TypeForge</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script>
        // Pass settings and weak keys to the frontend
        window.TYPEFORGE_SETTINGS = {
            sound: "<?php echo $user_sound; ?>",
            difficulty: "<?php echo $user_diff; ?>",
            isTraining: true,
            weakKeys: <?php echo json_encode($weak_keys); ?>
        };
    </script>
</head>
<body>
    <!-- Clean Minimal Navigation -->
    <div class="top-auth-bar" style="display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 0 auto 30px auto; padding: 10px 20px;">
        <div style="color: #e2b714; font-weight: 700; font-size: 1.2rem;">⚡ TypeForge</div>
        <a href="profile.php" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">← Back to Profile</a>
    </div>

    <div class="container" style="margin-top: 20px;">
        <!-- Targeting Badge & Timers -->
        <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 30px;">
            <div style="background: rgba(202, 71, 84, 0.15); color: #ca4754; padding: 10px 20px; border-radius: 8px; font-size: 0.95rem; font-weight: 700; border: 1px solid rgba(202, 71, 84, 0.3); display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <span>🎯 TARGETING WEAK KEYS:</span> 
                <span style="color: #fff; background: #ca4754; padding: 2px 8px; border-radius: 4px; font-size: 1rem;">
                    <?php echo !empty($weak_keys) ? strtoupper(implode(', ', $weak_keys)) : 'NONE'; ?>
                </span>
            </div>
            
            <div class="test-modes" style="display: flex; gap: 10px; margin-bottom: 0;">
                <button class="mode-btn" data-time="15">15s</button>
                <button class="mode-btn" data-time="30">30s</button>
                <button class="mode-btn active" data-time="60">60s</button>
                <button class="mode-btn" data-time="120">120s</button>
            </div>
        </div>

        <!-- The rest of your standard typing test HTML goes here -->
        <div class="stats-card">
            <div class="stat-box">
                <span class="stat-label">Time</span>
                <span id="timer" class="stat-value">60s</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">WPM</span>
                <span id="wpm" class="stat-value">0</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Accuracy</span>
                <span id="accuracy" class="stat-value">100%</span>
            </div>
        </div>

        <div class="typing-box" id="text-display"></div>

        <input type="text" id="hidden-input" autofocus autocomplete="off">

        <div class="controls">
            <button id="restart-btn">🔄 Restart Test</button>
        </div>
        
        <!-- Modern Glassmorphism Results Modal -->
        <div id="results-modal" class="modal hidden">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Test Complete</h2>
                </div>
                
                <div class="results-grid">
                    <div class="hero-stat">
                        <span class="hero-label">Net WPM</span>
                        <span id="modal-net-wpm" class="hero-value">0</span>
                    </div>
                    
                    <div class="sub-stats">
                        <div class="m-stat">
                            <span class="m-label">Raw WPM</span>
                            <span id="modal-raw-wpm" class="m-val">0</span>
                        </div>
                        <div class="m-stat">
                            <span class="m-label">Accuracy</span>
                            <span id="modal-accuracy" class="m-val">100%</span>
                        </div>
                        <div class="m-stat">
                            <span class="m-label">Characters (Correct / Err / Extra)</span>
                            <span id="modal-chars" class="m-val">0 / 0 / 0</span>
                        </div>
                    </div>
                </div>

                <div class="virtual-keyboard-container" style="margin: 20px 0; padding: 12px; background: #1e1e24; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <p style="color: #646669; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12px; text-align: center; font-weight: 700;">KEY FAULT HEATMAP</p>
                    <div class="keyboard-layout" id="keyboard-heatmap">
                        <!-- Split Number & Symbol Row -->
                        <div class="kb-row">
                            <div class="kb-key split-key" data-key="!"><div class="kb-half kb-sym-half" data-subkey="!">!</div><div class="kb-half kb-num-half" data-subkey="1">1</div></div>
                            <div class="kb-key split-key" data-key="@"><div class="kb-half kb-sym-half" data-subkey="@">@</div><div class="kb-half kb-num-half" data-subkey="2">2</div></div>
                            <div class="kb-key split-key" data-key="#"><div class="kb-half kb-sym-half" data-subkey="#">#</div><div class="kb-half kb-num-half" data-subkey="3">3</div></div>
                            <div class="kb-key split-key" data-key="$"><div class="kb-half kb-sym-half" data-subkey="$">$</div><div class="kb-half kb-num-half" data-subkey="4">4</div></div>
                            <div class="kb-key split-key" data-key="%"><div class="kb-half kb-sym-half" data-subkey="%">%</div><div class="kb-half kb-num-half" data-subkey="5">5</div></div>
                            <div class="kb-key split-key" data-key="^"><div class="kb-half kb-sym-half" data-subkey="^">^</div><div class="kb-half kb-num-half" data-subkey="6">6</div></div>
                            <div class="kb-key split-key" data-key="&"><div class="kb-half kb-sym-half" data-subkey="&">&</div><div class="kb-half kb-num-half" data-subkey="7">7</div></div>
                            <div class="kb-key split-key" data-key="*"><div class="kb-half kb-sym-half" data-subkey="*">*</div><div class="kb-half kb-num-half" data-subkey="8">8</div></div>
                            <div class="kb-key split-key" data-key="("><div class="kb-half kb-sym-half" data-subkey="(">(</div><div class="kb-half kb-num-half" data-subkey="9">9</div></div>
                            <div class="kb-key split-key" data-key=")"><div class="kb-half kb-sym-half" data-subkey=")">)</div><div class="kb-half kb-num-half" data-subkey="0">0</div></div>
                            <div class="kb-key split-key" data-key="_"><div class="kb-half kb-sym-half" data-subkey="_">_</div><div class="kb-half kb-num-half" data-subkey="-">-</div></div>
                            <div class="kb-key split-key" data-key="+"><div class="kb-half kb-sym-half" data-subkey="+">+</div><div class="kb-half kb-num-half" data-subkey="=">=</div></div>
                        </div>
                        <!-- Row 1 -->
                        <div class="kb-row">
                            <div class="kb-key kb-func" style="width: 45px;">Tab</div>
                            <div class="kb-key" data-key="q">Q</div><div class="kb-key" data-key="w">W</div><div class="kb-key" data-key="e">E</div><div class="kb-key" data-key="r">R</div><div class="kb-key" data-key="t">T</div><div class="kb-key" data-key="y">Y</div><div class="kb-key" data-key="u">U</div><div class="kb-key" data-key="i">I</div><div class="kb-key" data-key="o">O</div><div class="kb-key" data-key="p">P</div>
                            <div class="kb-key" data-key="[">[</div><div class="kb-key" data-key="]">]</div>
                            <div class="kb-key kb-func" style="width: 40px;">Bksp</div>
                        </div>
                        <!-- Row 2 -->
                        <div class="kb-row">
                            <div class="kb-key kb-func" style="width: 55px;">Caps</div>
                            <div class="kb-key" data-key="a">A</div><div class="kb-key" data-key="s">S</div><div class="kb-key" data-key="d">D</div><div class="kb-key" data-key="f">F</div><div class="kb-key" data-key="g">G</div><div class="kb-key" data-key="h">H</div><div class="kb-key" data-key="j">J</div><div class="kb-key" data-key="k">K</div><div class="kb-key" data-key="l">L</div>
                            <div class="kb-key" data-key=";">;</div><div class="kb-key" data-key="'">'</div>
                            <div class="kb-key kb-func" style="flex-grow: 1;">Enter</div>
                        </div>
                        <!-- Row 3 -->
                        <div class="kb-row">
                            <div class="kb-key kb-func" style="width: 75px;">Shift</div>
                            <div class="kb-key" data-key="z">Z</div><div class="kb-key" data-key="x">X</div><div class="kb-key" data-key="c">C</div><div class="kb-key" data-key="v">V</div><div class="kb-key" data-key="b">B</div><div class="kb-key" data-key="n">N</div><div class="kb-key" data-key="m">M</div>
                            <div class="kb-key" data-key=",">,</div><div class="kb-key" data-key=".">.</div><div class="kb-key" data-key="/">/</div>
                            <div class="kb-key kb-func" style="flex-grow: 1;">Shift</div>
                        </div>
                        <!-- Row 4 (Space) -->
                        <div class="kb-row">
                            <div class="kb-key kb-func" style="width: 45px;">Ctrl</div>
                            <div class="kb-key kb-func" style="width: 35px;">Win</div>
                            <div class="kb-key kb-func" style="width: 45px;">Alt</div>
                            <div class="kb-key space-key" data-key=" ">Space</div>
                            <div class="kb-key kb-func" style="width: 45px;">Alt</div>
                            <div class="kb-key kb-func" style="width: 35px;">Fn</div>
                            <div class="kb-key kb-func" style="width: 45px;">Ctrl</div>
                        </div>
                    </div>
                </div>

                <div class="modal-controls">
                    <button id="modal-restart-btn">Try Again</button>
                    <p class="shortcut-hint">Press <kbd>Enter</kbd> to restart</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>
