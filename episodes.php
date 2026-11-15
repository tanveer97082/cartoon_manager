<?php
// Turn off error display for clean CSV exports
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Handle Single Delete
if ($action === 'delete' && $id) {
    $stmt = $db->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: episodes.php");
    exit;
}

// Handle Bulk Actions (Delete Selected)
if (isset($_POST['bulk_action']) && $_POST['bulk_action'] === 'delete_selected' && !empty($_POST['selected_ids'])) {
    $ids = $_POST['selected_ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $db->prepare("DELETE FROM episodes WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    header("Location: episodes.php");
    exit;
}

// Handle Export CSV Template Download
if (isset($_GET['template'])) {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cartoon_import_template.csv');
    
    $out = fopen('php://output', 'w');
    fputcsv($out, ['series_name', 'season_name', 'episode_num', 'title', 'yt_title', 'yt_desc', 'yt_tags', 'script', 'reels_tiktok_caption', 'thumbnail_prompt', 'status', 'target_date'], ',', '"', '\\');
    fputcsv($out, ['Munki & Trunk', 'Season 1', 'S1E10', 'Sample Episode', 'Munki & Trunk | Sample | Episode 10', 'Description here...', 'tag1, tag2', 'Scene 1: ...', 'Viral caption 🐒', 'Thumbnail prompt here...', 'Script Ready', '2026-12-01'], ',', '"', '\\');
    fclose($out);
    exit;
}

// Handle Export All to CSV
if (isset($_GET['export_csv'])) {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=cartoon_studio_database.csv');
    
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Series Name', 'Season Name', 'Episode Num', 'Title', 'YouTube Title', 'Description', 'Tags', 'Script', 'Reels Caption', 'Thumbnail Prompt', 'Status', 'Target Date', 'Created At'], ',', '"', '\\');
    
    $rows = $db->query("SELECT * FROM episodes")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $r) { 
        fputcsv($out, $r, ',', '"', '\\'); 
    }
    fclose($out);
    exit;
}

// Handle CSV Import
$import_msg = "";
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (($h = fopen($file, "r")) !== FALSE) {
        $header = fgetcsv($h, 1000, ",", '"', '\\'); // skip header
        $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status, target_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $count = 0;
        while (($d = fgetcsv($h, 2000, ",", '"', '\\')) !== FALSE) {
            if (count($d) >= 11) {
                if (is_numeric($d[0])) {
                    $stmt->execute([$d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $d[9], $d[10], $d[11] ?? 'Script Ready', $d[12] ?? date('Y-m-d')]);
                } else {
                    $stmt->execute([$d[0], $d[1], $d[2], $d[3], $d[4], $d[5], $d[6], $d[7], $d[8], $d[9], $d[10] ?? 'Script Ready', $d[11] ?? date('Y-m-d')]);
                }
                $count++;
            }
        }
        fclose($h);
        $import_msg = "Successfully imported $count episodes!";
    }
}

// Sorting logic with arrows
$sort = $_GET['sort'] ?? 'target_asc';
$order_sql = "ORDER BY target_date ASC";
if ($sort === 'series_asc') $order_sql = "ORDER BY series_name ASC";
elseif ($sort === 'series_desc') $order_sql = "ORDER BY series_name DESC";
elseif ($sort === 'season_asc') $order_sql = "ORDER BY season_name ASC";
elseif ($sort === 'season_desc') $order_sql = "ORDER BY season_name DESC";
elseif ($sort === 'ep_asc') $order_sql = "ORDER BY episode_num ASC";
elseif ($sort === 'ep_desc') $order_sql = "ORDER BY episode_num DESC";
elseif ($sort === 'title_asc') $order_sql = "ORDER BY title ASC";
elseif ($sort === 'title_desc') $order_sql = "ORDER BY title DESC";
elseif ($sort === 'status_asc') $order_sql = "ORDER BY status ASC";
elseif ($sort === 'status_desc') $order_sql = "ORDER BY status DESC";
elseif ($sort === 'target_desc') $order_sql = "ORDER BY target_date DESC";
elseif ($sort === 'added_asc') $order_sql = "ORDER BY created_at ASC";
elseif ($sort === 'added_desc') $order_sql = "ORDER BY created_at DESC";

$stmt = $db->query("SELECT * FROM episodes $order_sql");
$episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Professional Episode Manager</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 1100px; margin: auto; }
        
        .toolbar { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between; }
        .search-input { flex-grow: 1; padding: 11px 14px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; color: #fff; font-size: 14px; min-width: 200px; }
        .search-input:focus { outline: none; border-color: var(--primary); }

        .toolbar-btns { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .tool-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text); padding: 10px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; }
        .tool-btn:hover { background: var(--primary); border-color: var(--primary); color: #fff; }

        .table-responsive { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; white-space: nowrap; }
        th { background: rgba(15,23,42,0.95); color: var(--accent); padding: 14px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        th a { color: var(--accent); text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 5px; }
        th a:hover { color: #fff; }
        td { padding: 12px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-ready { background: rgba(5, 150, 105, 0.2); color: #34d399; border: 1px solid rgba(5, 150, 105, 0.4); }
        .status-prod { background: rgba(217, 119, 6, 0.2); color: #fbbf24; border: 1px solid rgba(217, 119, 6, 0.4); }
        .status-pub { background: rgba(37, 99, 235, 0.2); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.4); }

        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background: var(--primary); color: white; padding: 6px 12px; font-size: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .dropdown-content { display: none; position: absolute; right: 0; background: #1e293b; min-width: 120px; box-shadow: 0px 8px 16px rgba(0,0,0,0.5); z-index: 10; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        .dropdown-content a, .dropdown-content button { color: var(--text); padding: 8px 12px; text-decoration: none; display: block; font-size: 12px; background: none; border: none; width: 100%; text-align: left; cursor: pointer; }
        .dropdown-content a:hover, .dropdown-content button:hover { background: var(--primary); color: #fff; }
        .dropdown:hover .dropdown-content { display: block; }

        .alert { background: rgba(5, 150, 105, 0.2); border: 1px solid rgba(5, 150, 105, 0.4); color: #34d399; padding: 10px; border-radius: 8px; margin-bottom: 14px; font-size: 13px; font-weight: 600; }
        #deleteBtn:disabled { opacity: 0.4; cursor: not-allowed; background: rgba(220,38,38,0.05); border-color: rgba(220,38,38,0.1); color: var(--muted); }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>

        <?php if (!empty($import_msg)): ?>
            <div class="alert"><?= htmlspecialchars($import_msg) ?></div>
        <?php endif; ?>

        <div class="toolbar">
            <input type="text" id="searchBox" class="search-input" placeholder="🔍 Search series, season, title..." onkeyup="filterTable()">
            
            <div class="toolbar-btns">
                <!-- Bulk Delete Form -->
                <form method="POST" id="bulkForm" style="display:inline;">
                    <input type="hidden" name="bulk_action" value="delete_selected">
                    <button type="submit" id="deleteBtn" onclick="return confirm('Delete selected episodes?');" class="tool-btn" style="background: rgba(220,38,38,0.2); border-color: rgba(220,38,38,0.4); color: #f87171;" disabled>🗑️ Delete Selected</button>
                </form>

                <a href="episodes.php?template=true" class="tool-btn">📄 Template CSV</a>
                <a href="episodes.php?export_csv=true" class="tool-btn">📥 Export CSV</a>

                <!-- Separate Import Form -->
                <form method="POST" enctype="multipart/form-data" style="display:inline;">
                    <label class="tool-btn" style="cursor: pointer; margin:0;">
                        📤 Import CSV <input type="file" name="csv_file" accept=".csv" onchange="this.form.submit();" style="display:none;">
                    </label>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table id="epTable">
                <thead>
                    <tr>
                        <th style="width: 30px;"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                        <th>Sr No</th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='series_asc')?'series_desc':'series_asc' ?>">
                                Series Name <span><?= ($sort=='series_asc')?'↑':(($sort=='series_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='season_asc')?'season_desc':'season_asc' ?>">
                                Season <span><?= ($sort=='season_asc')?'↑':(($sort=='season_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='ep_asc')?'ep_desc':'ep_asc' ?>">
                                Ep No <span><?= ($sort=='ep_asc')?'↑':(($sort=='ep_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='title_asc')?'title_desc':'title_asc' ?>">
                                Episode Title <span><?= ($sort=='title_asc')?'↑':(($sort=='title_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='status_asc')?'status_desc':'status_asc' ?>">
                                Status <span><?= ($sort=='status_asc')?'↑':(($sort=='status_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='target_asc')?'target_desc':'target_asc' ?>">
                                Target Date <span><?= ($sort=='target_asc')?'↑':(($sort=='target_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th>
                            <a href="episodes.php?sort=<?= ($sort=='added_asc')?'added_desc':'added_asc' ?>">
                                Added Date <span><?= ($sort=='added_asc')?'↑':(($sort=='added_desc')?'↓':'↕') ?></span>
                            </a>
                        </th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($episodes): $sr = 1; foreach ($episodes as $ep): ?>
                    <tr class="ep-row" data-search="<?= htmlspecialchars(strtolower($ep['series_name'].' '.$ep['season_name'].' '.$ep['episode_num'].' '.$ep['title'])) ?>">
                        <td><input type="checkbox" name="selected_ids[]" value="<?= $ep['id'] ?>" form="bulkForm" onclick="checkSelection()"></td>
                        <td><strong><?= $sr++ ?></strong></td>
                        <td><span style="color: var(--accent); font-weight:650;"><?= htmlspecialchars($ep['series_name']) ?></span></td>
                        <td><?= htmlspecialchars($ep['season_name']) ?></td>
                        <td><strong style="color: #fff;"><?= htmlspecialchars($ep['episode_num']) ?></strong></td>
                        <td style="font-weight:600;"><?= htmlspecialchars($ep['title']) ?></td>
                        <td>
                            <?php 
                                $st = $ep['status'] ?: 'Script Ready';
                                $bc = ($st == 'Published') ? 'status-pub' : (($st == 'In Production') ? 'status-prod' : 'status-ready');
                            ?>
                            <span class="badge <?= $bc ?>"><?= $st ?></span>
                        </td>
                        <td><span style="color: #fbbf24; font-weight:600;"><?= htmlspecialchars($ep['target_date'] ?: 'Not Set') ?></span></td>
                        <td style="color: var(--muted); font-size: 11px;"><?= htmlspecialchars(substr($ep['created_at'], 0, 10)) ?></td>
                        <td style="text-align: right;">
                            <div class="dropdown">
                                <button type="button" class="dropbtn">Actions ▾</button>
                                <div class="dropdown-content">
                                    <a href="add_edit.php?action=view&id=<?= $ep['id'] ?>">👁️ View</a>
                                    <button type="button" onclick="copyMetadata(<?= htmlspecialchars(json_encode($ep)) ?>)">📋 Copy</button>
                                    <a href="add_edit.php?action=edit&id=<?= $ep['id'] ?>">✏️ Edit</a>
                                    <a href="episodes.php?action=delete&id=<?= $ep['id'] ?>" onclick="return confirm('Delete this episode?');" style="color:#f87171;">🗑️ Delete</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="10" style="text-align: center; color: var(--muted); padding: 30px;">No episodes found in database.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function filterTable() {
            let val = document.getElementById('searchBox').value.toLowerCase();
            let rows = document.getElementsByClassName('ep-row');
            for (let r of rows) {
                let text = r.getAttribute('data-search');
                r.style.display = text.includes(val) ? "" : "none";
            }
        }
        function toggleAll(source) {
            let checkboxes = document.getElementsByName('selected_ids[]');
            for(let cb of checkboxes) { cb.checked = source.checked; }
            checkSelection();
        }
        function checkSelection() {
            let checkboxes = document.getElementsByName('selected_ids[]');
            let checkedCount = 0;
            for(let cb of checkboxes) { if(cb.checked) checkedCount++; }
            let delBtn = document.getElementById('deleteBtn');
            delBtn.disabled = (checkedCount === 0);
        }
        function copyMetadata(ep) {
            let text = `=== YOUTUBE TITLE ===\n${ep.yt_title}\n\n=== DESCRIPTION ===\n${ep.yt_desc}\n\n=== TAGS ===\n${ep.yt_tags}\n\n=== SCRIPT ===\n${ep.script}\n\n=== REELS CAPTION ===\n${ep.reels_tiktok_caption}\n\n=== THUMBNAIL PROMPT ===\n${ep.thumbnail_prompt}`;
            navigator.clipboard.writeText(text).then(() => alert(`🚀 Copied all metadata for ${ep.episode_num}!`));
        }
    </script>
</body>
</html>
