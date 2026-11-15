<?php
// Turn off error display for clean operations
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

$msg = "";

// Handle Inline Excel Data Update / Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_grid'])) {
    if (isset($_POST['row_id']) && is_array($_POST['row_id'])) {
        $stmt =$db->prepare("UPDATE episodes SET series_name=?, season_name=?, episode_num=?, title=?, yt_title=?, status=?, target_date=? WHERE id=?");
        
        for ($i = 0; $i < count($_POST['row_id']);$i++) {
            $id =$_POST['row_id'][$i];$series = $_POST['series_name'][$i];
            $season =$_POST['season_name'][$i];$ep_num = $_POST['episode_num'][$i];
            $title =$_POST['title'][$i];$yt_title = $_POST['yt_title'][$i];
            $status =$_POST['status'][$i];$target_date = $_POST['target_date'][$i];

            $stmt->execute([$series,$season, $ep_num,$title, $yt_title,$status, $target_date,$id]);
        }
        $msg = "Excel grid changes successfully saved to database!";
    }
}

// Handle Add New Row from Excel Grid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_row'])) {
    $stmt =$db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, status, target_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['new_series'] ?? 'Munki & Trunk',
        $_POST['new_season'] ?? 'Season 1',$_POST['new_ep'] ?? 'S1E1',
        $_POST['new_title'] ?? 'New Episode',$_POST['new_yt'] ?? 'YouTube Title',
        'Script Ready',
        date('Y-m-d')
    ]);
    $msg = "New row added successfully!";
}

// Handle Row Delete from Grid
if (isset($_GET['delete_id'])) {
    $stmt =$db->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: export_import.php");
    exit;
}

$rows =$db->query("SELECT * FROM episodes ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Studio Excel Spreadsheet Editor</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 1100px; margin: auto; }
        
        .excel-ribbon { background: var(--card); border: 1px solid var(--border); padding: 14px; border-radius: 14px; margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .ribbon-group { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .excel-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text); padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .excel-btn:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-save { background: linear-gradient(135deg, #059669, #10b981); border-color: #059669; color: white; }

        .spreadsheet-container { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; white-space: nowrap; }
        th { background: rgba(15,23,42,0.95); color: var(--accent); padding: 12px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        td { padding: 8px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .cell-input { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid transparent; color: var(--text); padding: 6px 8px; border-radius: 6px; font-size: 13px; box-sizing: border-box; }
        .cell-input:focus { outline: none; background: rgba(15,23,42,0.9); border-color: var(--primary); }

        .alert { background: rgba(5, 150, 105, 0.2); border: 1px solid rgba(5, 150, 105, 0.4); color: #34d399; padding: 10px; border-radius: 8px; margin-bottom: 14px; font-size: 13px; font-weight: 600; }
        .del-cell-btn { background: rgba(220,38,38,0.2); color: #f87171; border: 1px solid rgba(220,38,38,0.4); padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600; }
        .del-cell-btn:hover { background: #dc2626; color: #fff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>

        <?php if (!empty($msg)): ?>
            <div class="alert"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="excel-ribbon">
                <div class="ribbon-group">
                    <span style="font-weight: 700; color: #fff; font-size: 14px;">📊 Excel Spreadsheet Suite</span>
                </div>
                <div class="ribbon-group">
                    <button type="submit" name="save_grid" class="excel-btn btn-save">💾 Save Table Changes</button>
                    <a href="export_import.php?add_blank=true" onclick="event.preventDefault(); document.getElementById('addRowModal').style.display='flex';" class="excel-btn">➕ Insert New Row</a>
                </div>
            </div>

            <div class="spreadsheet-container">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">ID</th>
                            <th>Series Name</th>
                            <th>Season</th>
                            <th>Ep No</th>
                            <th>Episode Title</th>
                            <th>YouTube Title</th>
                            <th>Status</th>
                            <th>Target Date</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows): foreach ($rows as$r): ?>
                        <tr>
                            <td>
                                <strong><?= $r['id'] ?></strong>
                                <input type="hidden" name="row_id[]" value="<?= $r['id'] ?>">
                            </td>
                            <td><input type="text" name="series_name[]" class="cell-input" value="<?= htmlspecialchars($r['series_name']) ?>"></td>
                            <td><input type="text" name="season_name[]" class="cell-input" value="<?= htmlspecialchars($r['season_name']) ?>" style="width: 80px;"></td>
                            <td><input type="text" name="episode_num[]" class="cell-input" value="<?= htmlspecialchars($r['episode_num']) ?>" style="width: 70px;"></td>
                            <td><input type="text" name="title[]" class="cell-input" value="<?= htmlspecialchars($r['title']) ?>"></td>
                            <td><input type="text" name="yt_title[]" class="cell-input" value="<?= htmlspecialchars($r['yt_title']) ?>"></td>
                            <td>
                                <select name="status[]" class="cell-input" style="width: 120px;">
                                    <option value="Script Ready" <?= $r['status']=='Script Ready'?'selected':'' ?>>Script Ready</option>
                                    <option value="In Production" <?= $r['status']=='In Production'?'selected':'' ?>>In Production</option>
                                    <option value="Published" <?= $r['status']=='Published'?'selected':'' ?>>Published</option>
                                </select>
                            </td>
                            <td><input type="date" name="target_date[]" class="cell-input" value="<?= htmlspecialchars($r['target_date']) ?>" style="width: 130px;"></td>
                            <td style="text-align: right;">
                                <a href="export_import.php?delete_id=<?= $r['id'] ?>" class="del-cell-btn" onclick="return confirm('Delete this row?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="9" style="text-align: center; color: var(--muted); padding: 30px;">No records found in spreadsheet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- Quick Add Row Modal -->
    <div id="addRowModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(3,7,18,0.85); backdrop-filter:blur(5px); align-items:center; justify-content:center; z-index:100;">
        <div style="background:#1e293b; padding:24px; border-radius:16px; border:1px solid var(--border); width:100%; max-width:450px;">
            <h2 style="margin-top:0; color:#fff; font-size:18px;">➕ Insert New Spreadsheet Row</h2>
            <form method="POST">
                <div style="margin-bottom: 12px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">SERIES NAME</label><input type="text" name="new_series" value="Munki & Trunk" class="cell-input" required></div>
                <div style="margin-bottom: 12px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">SEASON NAME</label><input type="text" name="new_season" value="Season 1" class="cell-input" required></div>
                <div style="margin-bottom: 12px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">EPISODE NUMBER</label><input type="text" name="new_ep" placeholder="S1E5" class="cell-input" required></div>
                <div style="margin-bottom: 12px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">EPISODE TITLE</label><input type="text" name="new_title" placeholder="Title" class="cell-input" required></div>
                <div style="margin-bottom: 16px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">YOUTUBE TITLE</label><input type="text" name="new_yt" placeholder="YT Title" class="cell-input" required></div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="add_row" class="excel-btn btn-save" style="flex:1; justify-content:center;">Insert Row</button>
                    <button type="button" onclick="document.getElementById('addRowModal').style.display='none';" class="excel-btn" style="flex:1; justify-content:center; background:rgba(255,255,255,0.1);">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>