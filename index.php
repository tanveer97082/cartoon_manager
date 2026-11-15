<?php
require_once 'config.php';
$total = $db->query("SELECT COUNT(*) FROM episodes")->fetchColumn();
$ready = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Script Ready'")->fetchColumn();
$prod = $db->query("SELECT COUNT(*) FROM episodes WHERE status='In Production'")->fetchColumn();
$pub = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Published'")->fetchColumn();
$target_goal = 20; 
$progress_pct = min(round(($pub / max($target_goal, 1)) * 100), 100);
$max_phase = max($total, 1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ AI Cartoon Hub Pro - Command Center</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 950px; margin: auto; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 18px; }
        @media(min-width: 650px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
        
        .stat-box { background: var(--card); border: 1px solid var(--border); padding: 18px 14px; border-radius: 16px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5); position: relative; overflow: hidden; }
        .stat-box::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, #8b5cf6, #06b6d4); opacity: 0.7; }
        .stat-box h3 { margin: 0 0 8px 0; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; }
        .stat-box .val { font-size: 26px; font-weight: 900; color: #fff; }

        .target-box { background: linear-gradient(135deg, rgba(139,92,246,0.18), rgba(6,182,212,0.18)); border: 1px solid var(--border); padding: 22px; border-radius: 18px; margin-bottom: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .target-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 14px; font-weight: 700; flex-wrap: wrap; gap: 6px; }
        .progress-bar { background: rgba(255,255,255,0.08); height: 14px; border-radius: 7px; overflow: hidden; border: 1px solid var(--border); }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #8b5cf6, #06b6d4); border-radius: 7px; box-shadow: 0 0 12px rgba(139,92,246,0.6); }

        .analytics-card { background: var(--card); border: 1px solid var(--border); padding: 22px; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .analytics-card h2 { margin: 0 0 16px 0; font-size: 16px; color: #fff; font-weight: 800; display: flex; align-items: center; gap: 8px; }
        .bar-row { margin-bottom: 16px; }
        .bar-row:last-child { margin-bottom: 0; }
        .bar-info { display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 6px; }
        .bar-track { background: rgba(255,255,255,0.06); height: 12px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border); }
        .bar-fill { height: 100%; border-radius: 6px; }
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
                <span style="color: var(--accent);"><?= $pub ?> / <?= $target_goal ?> Published (<?= $progress_pct ?>%)</span>
            </div>
            <div class="progress-bar"><div class="progress-fill" style="width: <?= $progress_pct ?>%;"></div></div>
        </div>

        <div class="analytics-card">
            <h2>📈 Workflow Pipeline Distribution</h2>
            <?php 
                $p1 = ($ready / $max_phase) * 100;
                $p2 = ($prod / $max_phase) * 100;
                $p3 = ($pub / $max_phase) * 100;
            ?>
            <div class="bar-row">
                <div class="bar-info" style="color: #34d399;"><span>Script Ready Phase</span><span><?= $ready ?> Episodes</span></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $p1 ?>%; background: linear-gradient(90deg, #059669, #34d399); box-shadow: 0 0 10px rgba(52,211,153,0.4);"></div></div>
            </div>
            <div class="bar-row">
                <div class="bar-info" style="color: #fbbf24;"><span>In Production Phase</span><span><?= $prod ?> Episodes</span></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $p2 ?>%; background: linear-gradient(90deg, #d97706, #fbbf24); box-shadow: 0 0 10px rgba(251,191,36,0.4);"></div></div>
            </div>
            <div class="bar-row">
                <div class="bar-info" style="color: #60a5fa;"><span>Published Phase</span><span><?= $pub ?> Episodes</span></div>
                <div class="bar-track"><div class="bar-fill" style="width: <?= $p3 ?>%; background: linear-gradient(90deg, #2563eb, #60a5fa); box-shadow: 0 0 10px rgba(96,165,250,0.4);"></div></div>
            </div>
        </div>
    </div>
</body>
</html>
