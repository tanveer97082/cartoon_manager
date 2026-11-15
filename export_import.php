<?php
require_once 'config.php';

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cartoon_studio_database.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Series Name', 'Season Name', 'Episode Num', 'Title', 'YouTube Title', 'Description', 'Tags', 'Script', 'Reels Caption', 'Thumbnail Prompt', 'Status', 'Target Date', 'Created At']);
    $rows = $db->query("SELECT * FROM episodes")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) { fputcsv($out, $r); }
    fclose($out);
    exit;
}

$msg = "";
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($h = fopen($file, "r")) !== FALSE) {
        fgetcsv($h); // skip header
        $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status, target_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $count = 0;
        while (($d = fgetcsv($h, 1000, ",")) !== FALSE) {
            if (count($d) >= 12) {
                $stmt->execute([$d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $d[9], $d[10], $d[11], $d[12] ?? date('Y-m-d')]);
                $count++;
            }
        }
        fclose($h);
        $msg = "Successfully imported $count episodes from CSV file!";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Excel Ribbon & Database Manager</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 900px; margin: auto; }
        .header { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 16px; margin-bottom: 16px; }
        h1 { margin: 0 0 4px 0; font-size: 20px; color: #fff; }
        
        .ribbon { display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap; background: rgba(0,0,0,0.3); padding: 8px; border-radius: 10px; border: 1px solid var(--border); }
        .ribbon-btn { background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: var(--text); padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; }
        .ribbon-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        
        .card-box { background: var(--card); border: 1px solid var(--border); padding: 22px; border-radius: 16px; margin-bottom: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { margin-top: 0; font-size: 16px; color: #fff; }
        p { color: var(--muted); font-size: 13px; }
        .btn { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 12px; border-radius: 10px; cursor: pointer; font-weight: 700; width: 100%; font-size: 14px; text-align: center; display: inline-block; text-decoration: none; box-sizing: border-box; }
        input[type="file"] { width: 100%; padding: 11px; background: rgba(0,0,0,0.4); border: 1px solid var(--border); border-radius: 10px; color: var(--text); margin-bottom: 12px; box-sizing: border-box; }
        .alert { background: rgba(5, 150, 105, 0.2); border: 1px solid rgba(5, 150, 105, 0.4); color: #34d399; padding: 12px; border-radius: 10px; margin-bottom: 14px; font-weight: 600; font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>📂 Excel Ribbon & Database Engine</h1>
            <div class="ribbon">
                <a href="index.php" class="ribbon-btn">📊 Dashboard</a>
                <a href="episodes.php" class="ribbon-btn">🎬 Episodes</a>
                <a href="add_edit.php" class="ribbon-btn">➕ Add Episode</a>
                <a href="export_import.php" class="ribbon-btn active">📂 Excel / Ribbon DB</a>
            </div>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="card-box">
            <h2>📤 Export Database (Excel/CSV)</h2>
            <p>Download your complete studio database as a CSV file to manage or edit rows directly in Microsoft Excel or Google Sheets.</p>
            <a href="export_import.php?export=true" class="btn">📥 Download Database CSV</a>
        </div>

        <div class="card-box">
            <h2>📥 Import Database (Bulk CSV)</h2>
            <p>Upload a formatted CSV file to bulk-import series, seasons, and episodes instantly into your SQLite database.</p>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">🚀 Upload & Import CSV</button>
            </form>
        </div>
    </div>
</body>
</html>
