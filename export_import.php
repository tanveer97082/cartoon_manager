<?php
require_once 'config.php';

// Handle Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cartoons_database_backup.csv');
    $output = fopen('php://output', 'w');
    
    // CSV Header
    fputcsv($output, ['ID', 'Series Name', 'Season Name', 'Episode Num', 'Title', 'YouTube Title', 'Description', 'Tags', 'Script', 'Reels Caption', 'Thumbnail Prompt', 'Status']);
    
    $rows = $db->query("SELECT * FROM episodes")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Handle Import from CSV
$import_msg = "";
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        fgetcsv($handle); // Skip header row
        $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Assuming CSV columns match our table order (excluding ID)
            if (count($data) >= 12) {
                $stmt->execute([$data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9], $data[10], $data[11]]);
                $count++;
            }
        }
        fclose($handle);
        $import_msg = "Successfully imported $count episodes from CSV!";
    } else {
        $import_msg = "Failed to open uploaded CSV file.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ CSV Import & Export</title>
    <style>
        :root { --bg-deep: #030712; --card-bg: rgba(30, 41, 59, 0.85); --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --text-muted: #9ca3af; --border: rgba(255, 255, 255, 0.12); }
        body { font-family: system-ui, sans-serif; background: var(--bg-deep); color: var(--text); margin: 0; padding: 15px; min-height: 100vh; }
        .container { max-width: 820px; margin: auto; background: var(--card-bg); backdrop-filter: blur(16px); padding: 22px; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 25px 50px rgba(0,0,0,0.7); }
        h1, h2 { color: #fff; margin-top: 0; }
        .nav-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .nav-btn { background: rgba(15, 23, 42, 0.9); color: var(--accent); border: 1px solid var(--border); padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; }
        .nav-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .card { background: rgba(15, 23, 42, 0.8); border: 1px solid var(--border); padding: 20px; border-radius: 12px; margin-bottom: 20px; }
        .btn { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; font-size: 15px; text-align: center; display: inline-block; text-decoration: none; box-sizing: border-box; }
        input[type="file"] { width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border); border-radius: 8px; color: var(--text); margin-bottom: 15px; box-sizing: border-box; }
        .msg { background: rgba(5, 150, 105, 0.2); border: 1px solid rgba(5, 150, 105, 0.4); color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✨ AI Cartoon Hub Pro</h1>
        <div class="nav-bar">
            <a href="index.php" class="nav-btn">📊 Dashboard Analytics</a>
            <a href="episodes.php" class="nav-btn">🎬 Episode Manager</a>
            <a href="add_edit.php" class="nav-btn">➕ Add New Episode</a>
            <a href="export_import.php" class="nav-btn active">📥 CSV Import / Export</a>
        </div>

        <?php if (!empty($import_msg)): ?>
            <div class="msg"><?= htmlspecialchars($import_msg) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>📤 Export Database to Excel/CSV</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Download your entire database as a CSV file to open, edit, or manage in Microsoft Excel or Google Sheets.</p>
            <a href="export_import.php?export=true" class="btn">📥 Download Database CSV</a>
        </div>

        <div class="card">
            <h2>📥 Import Database from CSV</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Upload a formatted CSV file to bulk-import episodes into your studio database.</p>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="csv_file" accept=".csv" required>
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">🚀 Upload & Import CSV</button>
            </form>
        </div>
    </div>
</body>
</html>
