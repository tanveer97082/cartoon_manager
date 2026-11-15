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
        $content = "SERIES: {$row['series_name']} | SEASON: {$row['season_name']}\nEPISODE: {$row['episode_num']} - {$row['title']}\nSTATUS: {$row['status']}\n\n" .
                   "--- YOUTUBE TITLE ---\n{$row['yt_title']}\n\n" .
                   "--- DESCRIPTION ---\n{$row['yt_desc']}\n\n" .
                   "--- TAGS ---\n{$row['yt_tags']}\n\n" .
                   "--- SCRIPT ---\n{$row['script']}\n\n" .
                   "--- REELS / TIKTOK CAPTION ---\n{$row['reels_tiktok_caption']}\n\n" .
                   "--- THUMBNAIL PROMPT ---\n{$row['thumbnail_prompt']}\n";
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="'.$row['episode_num'].'_script.txt"');
        echo $content;
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Episode Manager</title>
    <style>
        :root { --bg-deep: #030712; --card-bg: rgba(30, 41, 59, 0.85); --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --text-muted: #9ca3af; --border: rgba(255, 255, 255, 0.12); }
        body { font-family: system-ui, sans-serif; background: var(--bg-deep); color: var(--text); margin: 0; padding: 15px; min-height: 100vh; }
        .container { max-width: 850px; margin: auto; background: var(--card-bg); backdrop-filter: blur(16px); padding: 22px; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 25px 50px rgba(0,0,0,0.7); }
        h1, h2 { color: #fff; margin-top: 0; }
        .nav-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .nav-btn { background: rgba(15, 23, 42, 0.9); color: var(--accent); border: 1px solid var(--border); padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 13px; }
        .nav-btn.active { background: var(--primary); color: white; border-color: var(--primary); }
        .search-box { width: 100%; padding: 12px 15px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border); border-radius: 10px; color: white; font-size: 14px; margin-bottom: 15px; box-sizing: border-box; }
        .episode-card { background: rgba(15, 23, 42, 0.75); border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .ep-info { flex-grow: 1; }
        .ep-meta { font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase; margin-bottom: 3px; display: flex; gap: 8px; }
        .ep-title { font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 6px; }
        .actions { display: flex; gap: 5px; flex-shrink: 0; }
        .btn-action { padding: 6px 10px; font-size: 12px; border-radius: 6px; text-decoration: none; color: white; font-weight: 600; }
        .bg-view { background: #059669; } .bg-edit { background: #d97706; } .bg-delete { background: #dc2626; } .bg-export { background: #2563eb; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-ready { background: rgba(5, 150, 105, 0.25); color: #34d399; }
        .status-prod { background: rgba(217, 119, 6, 0.25); color: #fbbf24; }
        .status-pub { background: rgba(37, 99, 235, 0.25); color: #60a5fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✨ AI Cartoon Hub Pro</h1>
        <div class="nav-bar">
            <a href="index.php" class="nav-btn">📊 Dashboard Analytics</a>
            <a href="episodes.php" class="nav-btn active">🎬 Episode Manager</a>
            <a href="add_edit.php" class="nav-btn">➕ Add New Episode</a>
            <a href="export_import.php" class="nav-btn">📥 CSV Import / Export</a>
        </div>

        <h2>🎬 All Episodes Directory</h2>
        <input type="text" id="searchBox" class="search-box" placeholder="🔍 Search series, season, episode or title..." onkeyup="filterEpisodes()">
        
        <div id="episodeList">
            <?php
            $stmt = $db->query("SELECT * FROM episodes ORDER BY id DESC");
            $episodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($episodes):
                foreach ($episodes as $ep):
            ?>
                <div class="episode-card" data-text="<?= htmlspecialchars($ep['series_name'].' '.$ep['season_name'].' '.$ep['episode_num'].' '.$ep['title']) ?>">
                    <div class="ep-info">
                        <div class="ep-meta">
                            <span><?= htmlspecialchars($ep['series_name']) ?></span> &bull; <span><?= htmlspecialchars($ep['season_name']) ?></span> &bull; <strong style="color: #fff;"><?= htmlspecialchars($ep['episode_num']) ?></strong>
                        </div>
                        <div class="ep-title"><?= htmlspecialchars($ep['title']) ?></div>
                        <?php 
                            $st = $ep['status'] ?: 'Script Ready';
                            $badge_class = ($st == 'Published') ? 'status-pub' : (($st == 'In Production') ? 'status-prod' : 'status-ready');
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= $st ?></span>
                    </div>
                    <div class="actions">
                        <a href="add_edit.php?action=view&id=<?= $ep['id'] ?>" class="btn-action bg-view">View</a>
                        <a href="add_edit.php?action=edit&id=<?= $ep['id'] ?>" class="btn-action bg-edit">Edit</a>
                        <a href="episodes.php?action=delete&id=<?= $ep['id'] ?>" class="btn-action bg-delete" onclick="return confirm('Delete this episode?');">Del</a>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p style="text-align: center; color: var(--text-muted); padding: 30px;">No episodes found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function filterEpisodes() {
            let input = document.getElementById('searchBox').value.toLowerCase();
            let cards = document.getElementsByClassName('episode-card');
            for (let c of cards) {
                let text = c.getAttribute('data-text').toLowerCase();
                c.style.display = text.includes(input) ? "flex" : "none";
            }
        }
    </script>
</body>
</html>
