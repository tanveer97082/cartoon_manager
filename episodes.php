<?php
require_once 'config.php';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

if ($action === 'delete' && $id) {
    $stmt = $db->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: episodes.php");
    exit;
}

if ($action === 'export_txt' && $id) {
    $stmt = $db->prepare("SELECT * FROM episodes WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $content = "SERIES: {$row['series_name']} | SEASON: {$row['season_name']}\nEPISODE: {$row['episode_num']} - {$row['title']}\nSTATUS: {$row['status']}\nTARGET UPLOAD: {$row['target_date']}\n\n" .
                   "--- YOUTUBE TITLE ---\n{$row['yt_title']}\n\n--- DESCRIPTION ---\n{$row['yt_desc']}\n\n--- TAGS ---\n{$row['yt_tags']}\n\n--- SCRIPT ---\n{$row['script']}\n\n--- REELS CAPTION ---\n{$row['reels_tiktok_caption']}\n\n--- THUMBNAIL PROMPT ---\n{$row['thumbnail_prompt']}";
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="'.$row['episode_num'].'_script.txt"');
        echo $content;
        exit;
    }
}

// Sorting logic
$sort = $_GET['sort'] ?? 'target_asc';
$order_sql = "ORDER BY target_date ASC";
if ($sort === 'az') $order_sql = "ORDER BY title ASC";
elseif ($sort === 'za') $order_sql = "ORDER BY title DESC";
elseif ($sort === 'recent') $order_sql = "ORDER BY id DESC";
elseif ($sort === 'target_desc') $order_sql = "ORDER BY target_date DESC";

$stmt = $db->query("SELECT * FROM episodes $order_sql");
$episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Professional Episode Manager Table</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 1000px; margin: auto; }
        .header { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 16px; margin-bottom: 16px; }
        h1 { margin: 0 0 4px 0; font-size: 20px; color: #fff; }
        
        .ribbon { display: flex; gap: 8px; margin-top: 16px; flex-wrap: wrap; background: rgba(0,0,0,0.3); padding: 8px; border-radius: 10px; border: 1px solid var(--border); }
        .ribbon-btn { background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: var(--text); padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; }
        .ribbon-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }

        .toolbar { display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-input { flex-grow: 1; padding: 12px 14px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; color: #fff; font-size: 14px; }
        .sort-select { padding: 12px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; color: #fff; font-size: 13px; font-weight: 600; }

        /* Table Design */
        .table-responsive { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; white-space: nowrap; }
        th { background: rgba(15,23,42,0.95); color: var(--accent); padding: 14px 12px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
        td { padding: 14px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-ready { background: rgba(5, 150, 105, 0.2); color: #34d399; border: 1px solid rgba(5, 150, 105, 0.4); }
        .status-prod { background: rgba(217, 119, 6, 0.2); color: #fbbf24; border: 1px solid rgba(217, 119, 6, 0.4); }
        .status-pub { background: rgba(37, 99, 235, 0.2); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.4); }

        .actions { display: flex; gap: 5px; }
        .btn-act { padding: 6px 10px; font-size: 11px; border-radius: 6px; text-decoration: none; color: white; font-weight: 600; }
        .bg-view { background: #059669; } .bg-edit { background: #d97706; } .bg-del { background: #dc2626; } .bg-copy { background: #8b5cf6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>🎬 Professional Studio Episode Manager</h1>
            <div class="ribbon">
                <a href="index.php" class="ribbon-btn">📊 Dashboard</a>
                <a href="episodes.php" class="ribbon-btn active">🎬 Episodes</a>
                <a href="add_edit.php" class="ribbon-btn">➕ Add Episode</a>
                <a href="export_import.php" class="ribbon-btn">📂 Excel / Ribbon DB</a>
            </div>
        </div>

        <div class="toolbar">
            <input type="text" id="searchBox" class="search-input" placeholder="🔍 Search series, season, title..." onkeyup="filterTable()">
            <select class="sort-select" onchange="location = this.value;">
                <option value="episodes.php?sort=target_asc" <?= $sort=='target_asc'?'selected':'' ?>>📅 Target Date (Upcoming)</option>
                <option value="episodes.php?sort=target_desc" <?= $sort=='target_desc'?'selected':'' ?>>📅 Target Date (Latest)</option>
                <option value="episodes.php?sort=az" <?= $sort=='az'?'selected':'' ?>>🔤 Title (A to Z)</option>
                <option value="episodes.php?sort=za" <?= $sort=='za'?'selected':'' ?>>🔤 Title (Z to A)</option>
                <option value="episodes.php?sort=recent" <?= $sort=='recent'?'selected':'' ?>>⏱️ Recently Added</option>
            </select>
        </div>

        <div class="table-responsive">
            <table id="epTable">
                <thead>
                    <tr>
                        <th>Sr No</th>
                        <th>Series Name</th>
                        <th>Season</th>
                        <th>Ep No</th>
                        <th>Episode Title</th>
                        <th>Status</th>
                        <th>Target Upload Date</th>
                        <th>Added Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($episodes): $sr = 1; foreach ($episodes as $ep): ?>
                    <tr class="ep-row" data-search="<?= htmlspecialchars(strtolower($ep['series_name'].' '.$ep['season_name'].' '.$ep['episode_num'].' '.$ep['title'])) ?>">
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
                        <td>
                            <div class="actions">
                                <a href="add_edit.php?action=view&id=<?= $ep['id'] ?>" class="btn-act bg-view" title="View & Copy">View</a>
                                <button onclick="copyMetadata(<?= htmlspecialchars(json_encode($ep)) ?>)" class="btn-act bg-copy" title="Quick Copy All">Copy</button>
                                <a href="add_edit.php?action=edit&id=<?= $ep['id'] ?>" class="btn-act bg-edit">Edit</a>
                                <a href="episodes.php?action=delete&id=<?= $ep['id'] ?>" class="btn-act bg-del" onclick="return confirm('Delete this episode?');">Del</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="9" style="text-align: center; color: var(--muted); padding: 30px;">No episodes found in database.</td></tr>
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
        function copyMetadata(ep) {
            let text = `=== YOUTUBE TITLE ===\n${ep.yt_title}\n\n=== DESCRIPTION ===\n${ep.yt_desc}\n\n=== TAGS ===\n${ep.yt_tags}\n\n=== SCRIPT ===\n${ep.script}\n\n=== REELS CAPTION ===\n${ep.reels_tiktok_caption}\n\n=== THUMBNAIL PROMPT ===\n${ep.thumbnail_prompt}`;
            navigator.clipboard.writeText(text).then(() => alert(`🚀 Copied all metadata for ${ep.episode_num}!`));
        }
    </script>
</body>
</html>
