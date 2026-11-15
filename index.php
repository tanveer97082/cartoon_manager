<?php
require_once 'config.php';

// Fetch Analytics Data
$total_eps = $db->query("SELECT COUNT(*) FROM episodes")->fetchColumn();
$ready = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Script Ready'")->fetchColumn();
$in_prod = $db->query("SELECT COUNT(*) FROM episodes WHERE status='In Production'")->fetchColumn();
$published = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Published'")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ AI Cartoon Hub Pro - Studio Dashboard</title>
    <style>
        :root { --bg-deep: #030712; --card-bg: rgba(30, 41, 59, 0.85); --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --text-muted: #9ca3af; --border: rgba(255, 255, 255, 0.12); }
        body { font-family: system-ui, sans-serif; background: var(--bg-deep); color: var(--text); margin: 0; padding: 15px; min-height: 100vh; }
        .container { max-width: 850px; margin: auto; background: var(--card-bg); backdrop-filter: blur(16px); padding: 22px; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 25px 50px rgba(0,0,0,0.7); }
        h1, h2 { color: #fff; margin-top: 0; }
        .nav-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .nav-btn { background: rgba(15, 23, 42, 0.9); color: var(--accent); border: 1px solid var(--border); padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; }
        .nav-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 25px; }
        .stat-card { background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border); padding: 16px; border-radius: 12px; text-align: center; }
        .stat-card h3 { margin: 0 0 5px 0; font-size: 12px; color: var(--text-muted); text-transform: uppercase; }
        .stat-card .val { font-size: 24px; font-weight: 800; color: #fff; }
        .chart-container { background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border); padding: 16px; border-radius: 12px; margin-bottom: 20px; }
        .bar-wrap { margin-bottom: 12px; }
        .bar-label { display: flex; justify-content: font-size: 13px; margin-bottom: 4px; font-weight: 600; }
        .bar-bg { background: rgba(255,255,255,0.05); border-radius: 6px; height: 12px; overflow: hidden; border: 1px solid var(--border); }
        .bar-fill { height: 100%; border-radius: 6px; transition: width 0.5s ease; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✨ AI Cartoon Hub Pro</h1>
        <span style="font-size: 12px; color: var(--text-muted);">Master Studio Analytics & Command Center</span>

        <div class="nav-bar" style="margin-top: 20px;">
            <a href="index.php" class="nav-btn active">📊 Dashboard Analytics</a>
            <a href="episodes.php" class="nav-btn">🎬 Episode Manager</a>
            <a href="add_edit.php" class="nav-btn">➕ Add New Episode</a>
            <a href="export_import.php" class="nav-btn">📥 CSV Import / Export</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><h3>Total Episodes</h3><div class="val"><?= $total_eps ?></div></div>
            <div class="stat-card"><h3>Script Ready</h3><div class="val" style="color: #34d399;"><?= $ready ?></div></div>
            <div class="stat-card"><h3>In Production</h3><div class="val" style="color: #fbbf24;"><?= $in_prod ?></div></div>
            <div class="stat-card"><h3>Published</h3><div class="val" style="color: #60a5fa;"><?= $published ?></div></div>
        </div>

        <div class="chart-container">
            <h2>📈 Workflow Status Breakdown</h2>
            <?php 
                $max = max($total_eps, 1);
                $p_ready = ($ready / $max) * 100;
                $p_prod = ($in_prod / $max) * 100;
                $p_pub = ($published / $max) * 100;
            ?>
            <div class="bar-wrap">
                <div class="bar-label" style="color: #34d399;"><span>Script Ready</span> <span><?= $ready ?></span></div>
                <div class="bar-bg"><div class="bar-fill" style="width: <?= $p_ready ?>%; background: #34d399;"></div></div>
            </div>
            <div class="bar-wrap">
                <div class="bar-label" style="color: #fbbf24;"><span>In Production</span> <span><?= $in_prod ?></span></div>
                <div class="bar-bg"><div class="bar-fill" style="width: <?= $p_prod ?>%; background: #fbbf24;"></div></div>
            </div>
            <div class="bar-wrap">
                <div class="bar-label" style="color: #60a5fa;"><span>Published</span> <span><?= $published ?></span></div>
                <div class="bar-bg"><div class="bar-fill" style="width: <?= $p_pub ?>%; background: #60a5fa;"></div></div>
            </div>
        </div>
    </div>
</body>
</html>
