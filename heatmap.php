<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch Lifetime Key Faults
$faults_query = "SELECT missed_key, error_count FROM user_key_faults WHERE user_id = '$user_id'";
$faults_result = mysqli_query($conn, $faults_query);
$key_faults = [];
while($f_row = mysqli_fetch_assoc($faults_result)) {
    $key_faults[$f_row['missed_key']] = (int)$f_row['error_count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Heatmap — TypeForge</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-auth-bar" style="max-width: 900px; margin: 0 auto;">
        <div class="logo-mini" style="color: #e2b714; font-weight: 700;">⚡ TypeForge</div>
        <div class="auth-links" style="display: flex; gap: 12px;">
            <a href="profile.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">← Back to Profile</a>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; max-width: 800px;">
        <header style="text-align: center; margin-bottom: 40px;">
            <h1>Key Fault Heatmap</h1>
            <p>A visual breakdown of your lifetime typing errors</p>
        </header>

        <div class="auth-card" style="max-width: 100%; padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <p style="color: #646669; font-size: 0.9rem; margin: 0;">Keys glowing red indicate a high lifetime error rate.</p>
                <button onclick="window.location.href='train.php'" style="background-color: #e2b714; color: #1e1e24; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.85rem; font-family: 'Lexend Deca', sans-serif;">Train Weak Keys</button>
            </div>
            
            <div class="keyboard-layout" id="profile-keyboard-heatmap" style="padding: 20px; background: #1e1e24; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                <!-- Number & Symbol Row -->
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
                    <div class="kb-key split-key" data-key)"><div class="kb-half kb-sym-half" data-subkey=")">)</div><div class="kb-half kb-num-half" data-subkey="0">0</div></div>
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
                <!-- Row 4 -->
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
    </div>
    
    <script>
    const lifetimeErrors = <?php echo json_encode($key_faults); ?>;

    function renderLifetimeHeatmap() {
        // 1. Find the highest number of errors on a single key
        const errorValues = Object.values(lifetimeErrors);
        const maxErrors = errorValues.length > 0 ? Math.max(...errorValues) : 0;

        // If the user hasn't made any errors yet, stop here
        if (maxErrors === 0) return;

        // 2. Set dynamic thresholds based on THEIR specific max
        // High error = Top 30% of their worst mistakes
        // Low error = Top 60% of their worst mistakes
        const highThreshold = maxErrors * 0.70; 
        const lowThreshold = maxErrors * 0.40;

        // Highlight full keys
        document.querySelectorAll('.kb-key:not(.split-key)').forEach(keyEl => {
            const char = keyEl.dataset.key;
            if(!char) return;
            
            // Check both lowercase and uppercase variations
            const errors = (lifetimeErrors[char] || 0) + (lifetimeErrors[char.toUpperCase()] || 0);
            
            if (errors >= highThreshold) keyEl.classList.add('error-high');
            else if (errors >= lowThreshold) keyEl.classList.add('error-low');
        });

        // Highlight split key halves (Numbers & Symbols)
        document.querySelectorAll('.kb-half').forEach(halfEl => {
            const subChar = halfEl.dataset.subkey;
            if(!subChar) return;
            
            const errors = lifetimeErrors[subChar] || 0;
            
            if (errors >= highThreshold) halfEl.classList.add('error-high');
            else if (errors >= lowThreshold) halfEl.classList.add('error-low');
        });
    }

    renderLifetimeHeatmap();
    </script>
</body>
</html>
