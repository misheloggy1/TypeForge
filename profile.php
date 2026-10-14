<?php
session_start();
include 'db_connect.php';

// Strict session security check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user stats & history (limited to 20 most recent)
$history_query = "SELECT * FROM test_results WHERE user_id = '$user_id' ORDER BY test_date DESC LIMIT 20";
$history_result = mysqli_query($conn, $history_query);

// Fetch personal bests
$pb_query = "SELECT MAX(net_wpm) as best_wpm, MAX(accuracy) as best_acc FROM test_results WHERE user_id = '$user_id'";
$pb_result = mysqli_query($conn, $pb_query);
$pb = mysqli_fetch_assoc($pb_result);

// Fetch chart data (last 25 tests, chronological order)
$chart_query = "SELECT net_wpm, test_date FROM (SELECT net_wpm, test_date FROM test_results WHERE user_id = '$user_id' ORDER BY test_date DESC LIMIT 25) sub ORDER BY test_date ASC";
$chart_result = mysqli_query($conn, $chart_query);

$chart_labels = [];
$chart_data = [];
while($c_row = mysqli_fetch_assoc($chart_result)) {
    $chart_labels[] = date('H:i', strtotime($c_row['test_date'])); // Short time or date format
    $chart_data[] = intval($c_row['net_wpm']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile — TypeForge</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend+Deca:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="top-auth-bar">
        <div class="logo-mini" style="color: #e2b714; font-weight: 700;">⚡ TypeForge</div>
        <div class="auth-links" style="display: flex; align-items: center; gap: 12px;">
            <span class="greeting" style="color: #d1d0c5; font-size: 0.9rem;">Logged in as <strong><?php echo htmlspecialchars($username); ?></strong></span>
            <a href="leaderboard.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">Leaderboard</a>
            <a href="index.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">Back to App</a>
            <a href="settings.php" class="auth-btn outline" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">⚙️ Settings</a>
            <a href="logout.php" class="auth-btn" style="background: #e2b714; color: #1e1e24; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
        </div>
    </div>

    <div class="container" style="margin-top: 40px; margin-bottom: 60px; text-align: left;">
        <header style="text-align: center; margin-bottom: 40px;">
            <h1>Dashboard</h1>
            <p>Your typing history and personal performance</p>
        </header>

        <div class="stats-card" style="margin-bottom: 40px;">
            <div class="stat-box">
                <span class="stat-label">Personal Best (Net WPM)</span>
                <span class="stat-value"><?php echo $pb['best_wpm'] ? $pb['best_wpm'] : '0'; ?></span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Highest Accuracy</span>
                <span class="stat-value"><?php echo $pb['best_acc'] ? $pb['best_acc'] . '%' : '100%'; ?></span>
            </div>
        </div>

        <div class="auth-card" style="max-width: 100%; padding: 24px; margin-bottom: 30px;">
            <h3 style="color: #d1d0c5; margin-bottom: 16px; font-size: 1rem; text-transform: uppercase; letter-spacing: 1px;">WPM Progression (Last 25 Tests)</h3>
            <div style="width: 100%; height: 220px;">
                <canvas id="profileWpmChart"></canvas>
            </div>
        </div>

        <!-- Link to Dedicated Heatmap Page -->
        <div class="auth-card" style="max-width: 100%; padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, rgba(37, 38, 44, 1) 0%, rgba(202, 71, 84, 0.05) 100%); border: 1px solid rgba(202, 71, 84, 0.2);">
            <div>
                <h3 style="color: #d1d0c5; font-size: 1.1rem; margin-bottom: 6px;">Advanced Key Analytics</h3>
                <p style="color: #646669; font-size: 0.85rem; margin: 0;">View your lifetime key fault heatmap and train your weakest fingers.</p>
            </div>
            <a href="heatmap.php" style="background-color: #ca4754; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(202, 71, 84, 0.2);">View Heatmap ➔</a>
        </div>

        <h3 style="color: #d1d0c5; margin-bottom: 16px;">Recent Tests</h3>
        <div class="auth-card" style="max-width: 100%; padding: 20px;">
            <?php if(mysqli_num_rows($history_result) > 0): ?>
                <table style="width: 100%; border-collapse: collapse; text-align: left; color: #d1d0c5;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #646669; font-size: 0.85rem; text-transform: uppercase;">
                            <th style="padding: 12px;">Net WPM</th>
                            <th style="padding: 12px;">Raw WPM</th>
                            <th style="padding: 12px;">Accuracy</th>
                            <th style="padding: 12px;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($history_result)): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 12px; font-weight: bold; color: #e2b714;"><?php echo $row['net_wpm']; ?></td>
                                <td style="padding: 12px;"><?php echo $row['raw_wpm']; ?></td>
                                <td style="padding: 12px;"><?php echo $row['accuracy']; ?>%</td>
                                <td style="padding: 12px; color: #646669; font-size: 0.9rem;"><?php echo $row['test_date']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #646669; text-align: center; padding: 20px;">No tests recorded yet. Complete a typing test to see your history here!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    const ctx = document.getElementById('profileWpmChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(226, 183, 20, 0.35)');
    gradient.addColorStop(1, 'rgba(226, 183, 20, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Net WPM',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#e2b714',
                borderWidth: 3,
                backgroundColor: gradient,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#646669' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#646669' }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    </script>
</body>
</html>
