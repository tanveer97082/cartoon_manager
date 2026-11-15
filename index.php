<?php
require_once 'config.php';
$total = $db->query("SELECT COUNT(*) FROM episodes")->fetchColumn();
$ready = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Script Ready'")->fetchColumn();
$prod = $db->query("SELECT COUNT(*) FROM episodes WHERE status='In Production'")->fetchColumn();
$pub = $db->query("SELECT COUNT(*) FROM episodes WHERE status='Published'")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ AI Cartoon Hub Pro - Studio</title>
    <style>
        :root { 
            --bg: #090d16; --card: #111827; --card-border: rgba(255, 255, 255, 0.08);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 16px; }
        .wrapper { max-width: 900px; margin: auto; }
        .header { background: var(--card); border: 1px solid var(--card-border); padding: 20px; border-radius: 16px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { margin: 0 0 5px 0; font-size: 22px; color: #fff; display: flex; align-items: center; gap: 8px; }
        .subtitle { font-size: 13px; color: var(--muted); }
        
        /* Modern Clean Navigation Tabs */
        .nav-tabs { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-top: 20px; }
        .nav-tab { background: rgba(255,255,255,0.03); border: 1px solid var(--card-border); color: var(--text); padding: 12px 14px; border-radius: 10px; text-align: center; text-decoration: none; font-weight: 600; font-size: 13px; transition: all 0.2s; }
        .nav-tab:hover, .nav-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 4px 15px rgba(139,92,246,0.4); }

        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; }
        @media(min-width: 600px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
        .stat-box { background: var(--card); border: 1px solid var(--card-border); padding: 18px; border-radius: 14px; text-align: center; }
        .stat-box h3 { margin: 0 0 8px 0; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-box .val { font-size: 26px; font-weight: 800; color: #fff; }

        /* Analytics Chart Container */
        .chart-box { background: var(--card); border: 1px solid var(--card-border); padding: 20px; border-radius: 16px; }
        .chart-box h2 { margin: 0 0 15px 0; font-size: 16px; color: #fff; }
        .bar-row { margin-bottom: 14px; }
        .bar-info { display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
        .bar-track { background: rgba(255,255,255,0.05); height: 10px; border-radius: 6px; overflow: hidden; border: 1px solid var(--card-border); }
        .bar-fill { height: 100%; border-radius: 6px; transition: width 0.4s; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>✨ AI Cartoon Hub Pro</h1>
            <div class="subtitle">YouTube & Social Media Production Command Center</div>
            
            <div class="nav-tabs">
                <a href="index.php" class="nav-tab active">📊 Analytics</a>
                <a href="episodes.php" class="nav-tab">🎬 Episodes</a>
                <a href="add_edit.php" class="nav-tab">➕ Add Episode</a>
                <a href="export_import.php" class="nav-tab">📥 CSV / Excel</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box"><h3>Total Episodes</h3><div class="val"><?= $total ?></div></div>
            <div class="stat-box"><h3>Script Ready</h3><div class="val" style="color: #34d399;"><?= $ready ?></div></div>
            <div class="stat-box"><h3>In Production</h3><div class="val" style="color: #fbbf24;"><?= $prod ?></div></div>
            <div class="stat-box"><h3>Published</h3><div class="val" style="color: #60a5fa;"><?= $pub ?></div></div>
        </div>

        <div class="chart-box">
            <h2>📈 Workflow Pipeline Progress</h2>
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
