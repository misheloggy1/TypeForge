<?php
session_start();
require_once 'db_connect.php';

$tab = $_GET['tab'] ?? 'alltime';

// Advanced SQL using CTE and ROW_NUMBER() to fetch the exact row of the highest score
if ($tab === 'weekly') {
    $query = "WITH RankedScores AS (
                SELECT u.username, t.net_wpm as max_wpm, t.accuracy as max_acc, t.test_date, t.difficulty_mode,
                       ROW_NUMBER() OVER(PARTITION BY t.user_id ORDER BY t.net_wpm DESC, t.id DESC) as rn
                FROM test_results t
                JOIN users u ON t.user_id = u.id
                WHERE t.challenge_date IS NULL AND t.test_date >= NOW() - INTERVAL 7 DAY
              )
              SELECT * FROM RankedScores WHERE rn = 1 ORDER BY max_wpm DESC LIMIT 10";
    $title = "Weekly Top Typists";
} elseif ($tab === 'daily_challenge') {
    $today = date('Y-m-d');
    $query = "WITH RankedScores AS (
                SELECT u.username, t.net_wpm as max_wpm, t.accuracy as max_acc, t.test_date, t.difficulty_mode,
                       ROW_NUMBER() OVER(PARTITION BY t.user_id ORDER BY t.net_wpm DESC, t.id DESC) as rn
                FROM test_results t
                JOIN users u ON t.user_id = u.id
                WHERE t.challenge_date = '$today'
              )
              SELECT * FROM RankedScores WHERE rn = 1 ORDER BY max_wpm DESC LIMIT 10";
    $title = "Daily Challenge Leaderboard (" . $today . ")";
} else {
    $query = "WITH RankedScores AS (
                SELECT u.username, t.net_wpm as max_wpm, t.accuracy as max_acc, t.test_date, t.difficulty_mode,
                       ROW_NUMBER() OVER(PARTITION BY t.user_id ORDER BY t.net_wpm DESC, t.id DESC) as rn
                FROM test_results t
                JOIN users u ON t.user_id = u.id
                WHERE t.challenge_date IS NULL
              )
              SELECT * FROM RankedScores WHERE rn = 1 ORDER BY max_wpm DESC LIMIT 10";
    $title = "All-Time Global Leaderboard";
}

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TypeForge — Leaderboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Lexend+Deca:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .legend-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(255, 42, 95, 0.08);
            border: 1px solid rgba(255, 42, 95, 0.4);
            color: #ff3b6b;
            box-shadow: 
              0 0 10px rgba(255, 42, 95, 0.25),
              inset 0 0 8px rgba(255, 42, 95, 0.15);
            position: relative;
            overflow: hidden;
            cursor: default;
            transition: all 0.3s ease;
            margin-left: 10px;
            vertical-align: middle;
        }

        .legend-badge .badge-text {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-shadow: 0 0 8px rgba(255, 59, 107, 0.4);
        }

        .legend-badge .badge-icon {
            width: 14px;
            height: 14px;
            filter: drop-shadow(0 0 3px rgba(255, 59, 107, 0.6));
        }

        .legend-badge:hover {
            background: rgba(255, 42, 95, 0.15);
            border-color: rgba(255, 42, 95, 0.8);
            box-shadow: 
              0 0 15px rgba(255, 42, 95, 0.4),
              inset 0 0 10px rgba(255, 42, 95, 0.25);
            transform: translateY(-1px);
        }

        .legend-badge::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
              to right,
              rgba(255, 255, 255, 0) 0%,
              rgba(255, 255, 255, 0.2) 50%,
              rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-20deg);
            animation: sweep 5s infinite;
        }

        @keyframes sweep {
            0% { left: -100%; }
            15% { left: 200%; }
            100% { left: 200%; } /* Pauses the shine before looping */
        }
        .pro-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(30, 224, 107, 0.08); /* Subtle neon green tint */
            border: 1px solid rgba(30, 224, 107, 0.4);
            color: #1ee06b; /* Vibrant neon green */
            box-shadow: 
              0 0 10px rgba(30, 224, 107, 0.25),
              inset 0 0 8px rgba(30, 224, 107, 0.15);
            position: relative;
            overflow: hidden;
            cursor: default;
            transition: all 0.3s ease;
            margin-left: 10px;
            vertical-align: middle;
        }

        .pro-badge .badge-text {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-shadow: 0 0 8px rgba(30, 224, 107, 0.4);
        }

        .pro-badge .badge-icon {
            width: 14px;
            height: 14px;
            filter: drop-shadow(0 0 3px rgba(30, 224, 107, 0.6));
        }

        .pro-badge:hover {
            background: rgba(30, 224, 107, 0.15);
            border-color: rgba(30, 224, 107, 0.8);
            box-shadow: 
              0 0 15px rgba(30, 224, 107, 0.4),
              inset 0 0 10px rgba(30, 224, 107, 0.25);
            transform: translateY(-1px);
        }

        .pro-badge::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(
              to right,
              rgba(255, 255, 255, 0) 0%,
              rgba(255, 255, 255, 0.2) 50%,
              rgba(255, 255, 255, 0) 100%
            );
            transform: skewX(-20deg);
            animation: sweep 5s infinite;
        }
    </style>
</head>
<body style="background-color: #1e1e24; color: #d1d0c5; font-family: 'Lexend Deca', sans-serif; margin: 0; padding: 20px;">
    <div class="top-auth-bar" style="display: flex; justify-content: space-between; align-items: center; max-width: 900px; margin: 0 auto 30px auto; padding: 10px 20px;">
        <div style="color: #e2b714; font-weight: 700; font-size: 1.2rem;">⚡ TypeForge</div>
        <div style="display: flex; gap: 12px;">
            <a href="index.php" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">← Back to App</a>
            <a href="settings.php" style="background: transparent; color: #d1d0c5; border: 1px solid rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">⚙️ Settings</a>
        </div>
    </div>

    <div style="max-width: 900px; margin: 0 auto; background-color: #25262c; border-radius: 16px; padding: 30px; border: 1px solid rgba(255,255,255,0.06);">
        <h2 style="text-align: center; color: #d1d0c5; margin-bottom: 24px; font-size: 1.6rem;"><?php echo $title; ?></h2>
        
        <!-- Tabs -->
        <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 30px;">
            <a href="leaderboard.php?tab=alltime" style="padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; background: <?php echo $tab === 'alltime' ? '#e2b714' : '#1e1e24'; ?>; color: <?php echo $tab === 'alltime' ? '#1e1e24' : '#646669'; ?>;">All-Time</a>
            <a href="leaderboard.php?tab=weekly" style="padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; background: <?php echo $tab === 'weekly' ? '#e2b714' : '#1e1e24'; ?>; color: <?php echo $tab === 'weekly' ? '#1e1e24' : '#646669'; ?>;">Weekly</a>
            <a href="leaderboard.php?tab=daily_challenge" style="padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; background: <?php echo $tab === 'daily_challenge' ? '#e2b714' : '#1e1e24'; ?>; color: <?php echo $tab === 'daily_challenge' ? '#1e1e24' : '#646669'; ?>;">Daily Challenge</a>
        </div>

        <!-- Table -->
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="color: #646669; font-size: 0.75rem; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.06);">
                    <th style="padding: 12px;">Rank</th>
                    <th style="padding: 12px;">Username</th>
                    <th style="padding: 12px;">Net WPM</th>
                    <th style="padding: 12px;">Accuracy</th>
                    <th style="padding: 12px;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                if($result && mysqli_num_rows($result) > 0):
                    while($row = mysqli_fetch_assoc($result)):
                        $badge_color = '#d1d0c5';
                        if($rank == 1) $badge_color = '#e2b714'; 
                        elseif($rank == 2) $badge_color = '#a9a9b2'; 
                        elseif($rank == 3) $badge_color = '#cb7544'; 
                ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                    <td style="padding: 14px; font-weight: 700; color: <?php echo $badge_color; ?>;">#<?php echo $rank++; ?></td>
                    <td style="padding: 14px; font-weight: 600; display: flex; align-items: center;">
                        <?php echo htmlspecialchars($row['username']); ?>
                        <?php if($row['difficulty_mode'] === 'legend'): ?>
                            <div class="legend-badge">
                                <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5"></polyline>
                                    <line x1="13" y1="19" x2="19" y2="13"></line>
                                    <line x1="16" y1="16" x2="20" y2="20"></line>
                                    <line x1="19" y1="21" x2="21" y2="19"></line>
                                    <polyline points="14.5 6.5 18 3 21 3 21 6 17.5 9.5"></polyline>
                                    <line x1="5" y1="14" x2="11" y2="20"></line>
                                    <line x1="5" y1="19" x2="3" y2="21"></line>
                                    <line x1="4" y1="16" x2="8" y2="20"></line>
                                </svg>
                                <span class="badge-text">Legend</span>
                            </div>
                        <?php elseif($row['difficulty_mode'] === 'pro'): ?>
                            <div class="pro-badge">
                                <svg class="badge-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                </svg>
                                <span class="badge-text">Pro</span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 14px; color: #e2b714; font-weight: 700;"><?php echo $row['max_wpm']; ?></td>
                    <td style="padding: 14px;"><?php echo $row['max_acc']; ?>%</td>
                    <td style="padding: 14px; color: #646669; font-size: 0.85rem;"><?php echo date('Y-m-d', strtotime($row['test_date'])); ?></td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: #646669;">No scores recorded yet for this category.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
