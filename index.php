<?php
session_start();
require_once 'db_connect.php';

$user_sound = 'off';
$user_diff = 'beginner';

if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT sound_pref, difficulty_pref FROM users WHERE id = $uid");
    if($row = mysqli_fetch_assoc($res)) {
        $user_sound = $row['sound_pref'] ?? 'off';
        $user_diff = $row['difficulty_pref'] ?? 'beginner';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeForge — Version 4</title>
    <script>
        // Pass database settings to the frontend
        window.TYPEFORGE_SETTINGS = {
            sound: "<?php echo $user_sound; ?>",
            difficulty: "<?php echo $user_diff; ?>"
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Lexend+Deca:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="top-auth-bar">
        <div class="logo-mini" style="color: #e2b714; font-weight: 700;">⚡ TypeForge</div>
        <div class="auth-links" style="display: flex; align-items: center; gap: 12px;">
            <?php if(isset($_SESSION['username'])): ?>
                <span class="greeting" style="color: #d1d0c5; font-size: 0.9rem;">Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="leaderboard.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">Leaderboard</a>
                <a href="profile.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">Profile</a>
                <a href="settings.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">⚙️ Settings</a>
                <a href="logout.php" class="auth-btn" style="background: #e2b714; color: #1e1e24; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
            <?php else: ?>
                <a href="login.php" class="auth-btn" style="background: #e2b714; color: #1e1e24; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem;">Log in</a>
                <a href="register.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">Sign up</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <header>
            <h1>⚡ TypeForge</h1>
            <p>Master your typing speed & accuracy</p>
        </header>

        <!-- Test Modes & Custom Mode Row -->
        <div class="modes-wrapper" style="display: flex; gap: 16px; justify-content: center; align-items: center; margin-bottom: 24px;">
            <div class="test-modes" style="margin-bottom: 0;">
                <button class="mode-btn" data-time="15">15s</button>
                <button class="mode-btn" data-time="30">30s</button>
                <button class="mode-btn active" data-time="60">60s</button>
                <button class="mode-btn" data-time="120">120s</button>
                <button class="mode-btn" id="daily-challenge-btn" style="color: #e2b714; border-color: #e2b714;">Daily Challenge</button>
            </div>
            
            <!-- Modern Sliding Toggle Switch -->
            <div class="custom-toggle-container" style="display: flex; align-items: center; gap: 8px;">
                <span style="color: #646669; font-size: 0.85rem; font-weight: 600;">custom</span>
                <label class="switch">
                    <input type="checkbox" id="custom-switch-input">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>

        <!-- Hidden Custom Categories Panel -->
        <div id="custom-panel" class="custom-panel hidden" style="margin-bottom: 24px;">
            <div class="word-categories" style="background-color: #2c2d35; display: inline-flex; padding: 6px; border-radius: 12px; gap: 6px;">
                <button class="cat-btn active" data-category="words" style="background: transparent; color: #e2b714; border: none; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-family: 'Lexend Deca', sans-serif; font-size: 0.8rem; font-weight: 600;">words</button>
                <button class="cat-btn" data-category="punctuations" style="background: transparent; color: #646669; border: none; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-family: 'Lexend Deca', sans-serif; font-size: 0.8rem; font-weight: 600;">numbers & symbols</button>
                <button class="cat-btn" data-category="pangrams" style="background: transparent; color: #646669; border: none; padding: 6px 14px; border-radius: 8px; cursor: pointer; font-family: 'Lexend Deca', sans-serif; font-size: 0.8rem; font-weight: 600;">pangrams</button>
            </div>
        </div>

        <!-- Live Stats Display -->
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

        <!-- Typing Area -->
        <div class="typing-box" id="text-display"></div>

        <!-- Hidden input field -->
        <input type="text" id="hidden-input" autofocus autocomplete="off">

        <!-- Controls -->
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
