<?php
require_once 'config.php';
$total = $db->query("SELECT COUNT(*) FROM episodes")->fetchColumn();
$ready = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Script Ready'")->fetchColumn();
$prod = $db->query("SELECT COUNT(*) FROM episodes WHERE status='In Production'")->fetchColumn();
$pub = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Published'")->fetchColumn();
$target_goal = 20;
$progress_pct = min(round(($pub / max($target_goal, 1)) * 100), 100);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ AI Cartoon Hub Pro - Dashboard</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 950px; margin: auto; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
        @media(min-width: 650px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
        .stat-box { background: var(--card); border: 1px solid var(--border); padding: 16px; border-radius: 14px; text-align: center; }
        .stat-box h3 { margin: 0 0 6px 0; font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-box .val { font-size: 24px; font-weight: 800; color: #fff; }

        .target-box { background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(6,182,212,0.15)); border: 1px solid var(--border); padding: 20px; border-radius: 16px; margin-bottom: 16px; }
        .target-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-size: 13px; font-weight: 700; flex-wrap: wrap; gap: 5px; }
        .progress-bar { background: rgba(255,255,255,0.08); height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border); }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #8b5cf6, #06b6d4); border-radius: 6px; }

        .analytics-card { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 16px; }
        .analytics-card h2 { margin: 0 0 14px 0; font-size: 15px; color: #fff; }
        .bar-row { margin-bottom: 12px; }
        .bar-info { display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; margin-bottom: 4px; }
        .bar-track { background: rgba(255,255,255,0.05); height: 10px; border-radius: 5px; overflow: hidden; border: 1px solid var(--border); }
        .bar-fill { height: 100%; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>

        <div class="stats-grid">
            <div class="stat-box"><h3>Total Episodes</h3><div class="val"><?= $total ?></div></div>
            <div class="stat-box"><h3>Script Ready</h3><div class="val" style="color: #34d399;"><?= $ready ?></div></div>
            <div class="stat-box"><h3>In Production</h3><div class="val" style="color: #fbbf24;"><?= $prod ?></div></div>
            <div class="stat-box"><h3>Published</h3><div class="val" style="color: #60a5fa;"><?= $pub ?></div></div>
        </div>

        <div class="target-box">
            <div class="target-header">
                <span>🎯 Monthly Production Target</span>
                <span><?= $pub ?> / <?= $target_goal ?> Published (<?= $progress_pct ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width: <?= $progress_pct ?>%;"></div></div>
        </div>

        <div class="analytics-card">
            <h2>📈 Workflow Pipeline Distribution</h2>
            <?php 
                $max = max($total, 1);
                $p1 = ($ready / $max) * 100;
                $p2 = ($prod / $max) * 100;
                $p3 = ($pub / $max) * 100;
            ?>
            <div class="bar-row">
                <div class="bar-info" style="color: #34d399;"><span>Script Ready Phase</span><span><?= $ready ?></span></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $p1 ?>%; background: #34d399;"></div></div>
            </div>
            <div class="bar-row">
                <div class="bar-info" style="color: #fbbf24;"><span>In Production Phase</span><span><?= $prod ?></span></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $p2 ?>%; background: #fbbf24;"></div></div>
            </div>
            <div class="bar-row">
                <div class="bar-info" style="color: #60a5fa;"><span>Published Phase</span><span><?= $pub ?></span></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $p3 ?>%; background: #60a5fa;"></div></div>
            </div>
        </div>
    </div>
</body>
</html>
