<?php
$db_dir = 'database';
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
}
$db_file = $db_dir . '/cartoons.db';
$db = new PDO('sqlite:' . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create Table if not exists
$db->exec("CREATE TABLE IF NOT EXISTS episodes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    series_name TEXT DEFAULT 'Munki & Trunk',
    season_name TEXT DEFAULT 'Season 1',
    episode_num TEXT,
    title TEXT,
    yt_title TEXT,
    yt_desc TEXT,
    yt_tags TEXT,
    script TEXT,
    reels_tiktok_caption TEXT,
    thumbnail_prompt TEXT,
    status TEXT DEFAULT 'Script Ready'
)");

$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Handle Add / Update POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $series = $_POST['series_name'] ?? 'Munki & Trunk';
    $season = $_POST['season_name'] ?? 'Season 1';
    $ep_num = $_POST['episode_num'];
    $title = $_POST['title'];
    $yt_title = $_POST['yt_title'];
    $yt_desc = $_POST['yt_desc'];
    $yt_tags = $_POST['yt_tags'];
    $script = $_POST['script'];
    $caption = $_POST['reels_tiktok_caption'];
    $thumb = $_POST['thumbnail_prompt'];
    $status = $_POST['status'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE episodes SET series_name=?, season_name=?, episode_num=?, title=?, yt_title=?, yt_desc=?, yt_tags=?, script=?, reels_tiktok_caption=?, thumbnail_prompt=?, status=? WHERE id=?");
        $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status, $_POST['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status]);
    }
    header("Location: index.php");
    exit;
}

if ($action === 'delete' && $id) {
    $stmt = $db->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: index.php");
    exit;
}

if ($action === 'export' && $id) {
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
    <title>✨ AI Cartoon Hub Pro (PHP)</title>
    <style>
        :root { 
            --bg-deep: #030712; --card-bg: rgba(30, 41, 59, 0.85); 
            --primary: #8b5cf6; --primary-glow: rgba(139, 92, 246, 0.4);
            --accent: #06b6d4; --text: #f3f4f6; --text-muted: #9ca3af; --border: rgba(255, 255, 255, 0.12); 
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg-deep); background-image: radial-gradient(circle at 10% 20%, rgba(139, 92, 246, 0.12) 0%, transparent 40%), radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.12) 0%, transparent 40%); color: var(--text); margin: 0; padding: 12px; min-height: 100vh; }
        .container { max-width: 820px; margin: auto; background: var(--card-bg); backdrop-filter: blur(16px); padding: 20px; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 25px 50px rgba(0,0,0,0.7); }
        h1, h2 { color: #fff; margin-top: 0; }
        .btn { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; font-size: 15px; box-shadow: 0 4px 15px var(--primary-glow); }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 6px; font-size: 12px; color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        input, textarea, select { width: 100%; padding: 11px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border); border-radius: 8px; color: var(--text); box-sizing: border-box; font-size: 14px; }
        textarea { height: 85px; resize: vertical; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 10px var(--primary-glow); }
        .search-box { width: 100%; padding: 12px 15px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border); border-radius: 10px; color: white; font-size: 14px; margin-bottom: 15px; box-sizing: border-box; }
        .episode-card { background: rgba(15, 23, 42, 0.75); border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; transition: transform 0.2s; }
        .episode-card:hover { border-color: var(--primary); transform: translateY(-2px); }
        .ep-info { flex-grow: 1; }
        .ep-meta { font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase; margin-bottom: 3px; display: flex; gap: 8px; align-items: center; }
        .ep-title { font-size: 15px; font-weight: 700; color: #fff; margin-bottom: 6px; }
        .actions { display: flex; gap: 5px; flex-shrink: 0; }
        .btn-action { padding: 6px 10px; font-size: 12px; border-radius: 6px; text-decoration: none; color: white; font-weight: 600; display: inline-block; }
        .bg-view { background: #059669; } .bg-edit { background: #d97706; } .bg-delete { background: #dc2626; } .bg-export { background: #2563eb; }
        .back-link { display: inline-block; margin-bottom: 15px; color: var(--accent); text-decoration: none; font-weight: 600; font-size: 14px; }
        .card { background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border); padding: 16px; border-radius: 12px; margin-bottom: 16px; }
        .card h3 { margin-top: 0; color: var(--accent); font-size: 14px; display: flex; justify-content: space-between; align-items: center; text-transform: uppercase; }
        .copy-btn { background: rgba(139, 92, 246, 0.2); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.4); padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .bulk-copy-btn { background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; margin-bottom: 16px; font-size: 14px; }
        pre { white-space: pre-wrap; font-family: inherit; margin: 8px 0 0 0; color: #e2e8f0; font-size: 13px; background: rgba(30, 41, 59, 0.6); padding: 12px; border-radius: 8px; border: 1px solid var(--border); max-height: 160px; overflow-y: auto; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px; }
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status-ready { background: rgba(5, 150, 105, 0.25); color: #34d399; border: 1px solid rgba(5, 150, 105, 0.4); }
        .status-prod { background: rgba(217, 119, 6, 0.25); color: #fbbf24; border: 1px solid rgba(217, 119, 6, 0.4); }
        .status-pub { background: rgba(37, 99, 235, 0.25); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.4); }
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(8px); align-items: center; justify-content: center; padding: 15px; box-sizing: border-box; }
        .modal-content { background: #1e293b; width: 100%; max-width: 600px; padding: 25px; border-radius: 18px; border: 1px solid var(--border); max-height: 90vh; overflow-y: auto; }
        .close-btn { color: var(--text-muted); float: right; font-size: 24px; font-weight: bold; cursor: pointer; background: none; border: none; }
        .add-btn-header { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; padding: 8px 16px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-flex">
            <div>
                <h1 style="margin:0; font-size: 20px;">✨ AI Cartoon Hub Pro (PHP)</h1>
                <span style="font-size: 11px; color: var(--text-muted);">Studio Media Planner</span>
            </div>
            <button onclick="openModal()" class="add-btn-header">+ Add New</button>
        </div>

        <?php if ($action === 'edit' && $id): 
            $stmt = $db->prepare("SELECT * FROM episodes WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
            <a href="index.php" class="back-link">&larr; Back to Dashboard</a>
            <h2>Edit Episode (<?= htmlspecialchars($item['episode_num']) ?>)</h2>
            <form method="POST" action="index.php">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <div class="form-group"><label>Series Name</label><input type="text" name="series_name" value="<?= htmlspecialchars($item['series_name']) ?>" required></div>
                <div class="form-group"><label>Season Name</label><input type="text" name="season_name" value="<?= htmlspecialchars($item['season_name']) ?>" required></div>
                <div class="form-group"><label>Episode Number</label><input type="text" name="episode_num" value="<?= htmlspecialchars($item['episode_num']) ?>" required></div>
                <div class="form-group"><label>Episode Title</label><input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required></div>
                <div class="form-group"><label>YouTube Title</label><input type="text" name="yt_title" value="<?= htmlspecialchars($item['yt_title']) ?>" required></div>
                <div class="form-group"><label>YouTube Description</label><textarea name="yt_desc"><?= htmlspecialchars($item['yt_desc']) ?></textarea></div>
                <div class="form-group"><label>YouTube Tags</label><input type="text" name="yt_tags" value="<?= htmlspecialchars($item['yt_tags']) ?>"></div>
                <div class="form-group"><label>5-Min Script Summary</label><textarea name="script" style="height: 120px;"><?= htmlspecialchars($item['script']) ?></textarea></div>
                <div class="form-group"><label>Reels / TikTok Caption</label><textarea name="reels_tiktok_caption"><?= htmlspecialchars($item['reels_tiktok_caption']) ?></textarea></div>
                <div class="form-group"><label>Thumbnail Prompt</label><textarea name="thumbnail_prompt"><?= htmlspecialchars($item['thumbnail_prompt']) ?></textarea></div>
                <div class="form-group">
                    <label>Production Status</label>
                    <select name="status">
                        <option value="Script Ready" <?= $item['status']=='Script Ready'?'selected':'' ?>>Script Ready</option>
                        <option value="In Production" <?= $item['status']=='In Production'?'selected':'' ?>>In Production</option>
                        <option value="Published" <?= $item['status']=='Published'?'selected':'' ?>>Published</option>
                    </select>
                </div>
                <button type="submit" class="btn">Update Metadata</button>
            </form>

        <?php elseif ($action === 'view' && $id): 
            $stmt = $db->prepare("SELECT * FROM episodes WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
            <a href="index.php" class="back-link">&larr; Back to Dashboard</a>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div>
                    <span style="font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($item['series_name']) ?> | <?= htmlspecialchars($item['season_name']) ?></span>
                    <h2 style="margin:2px 0 0 0; font-size: 17px; color: #fff;"><?= htmlspecialchars($item['episode_num']) ?> - <?= htmlspecialchars($item['title']) ?></h2>
                </div>
                <a href="index.php?action=export&id=<?= $item['id'] ?>" class="btn-action bg-export" style="padding: 8px 14px; font-size: 13px;">📥 Export .txt</a>
            </div>
            <button class="bulk-copy-btn" onclick="copyAllData()">🚀 Copy All Metadata (All-in-One)</button>
            
            <div class="card"><h3>YouTube Title <button class="copy-btn" onclick="copyText('yt_t')">📋 Copy</button></h3><pre id="yt_t"><?= htmlspecialchars($item['yt_title']) ?></pre></div>
            <div class="card"><h3>YouTube Description <button class="copy-btn" onclick="copyText('yt_d')">📋 Copy</button></h3><pre id="yt_d"><?= htmlspecialchars($item['yt_desc'] ?: 'No description.') ?></pre></div>
            <div class="card"><h3>YouTube Tags <button class="copy-btn" onclick="copyText('yt_tag')">📋 Copy</button></h3><pre id="yt_tag"><?= htmlspecialchars($item['yt_tags'] ?: 'No tags.') ?></pre></div>
            <div class="card"><h3>5-Minute Script <button class="copy-btn" onclick="copyText('scr')">📋 Copy</button></h3><pre id="scr"><?= htmlspecialchars($item['script'] ?: 'No script.') ?></pre></div>
            <div class="card"><h3>Reels / TikTok Caption <button class="copy-btn" onclick="copyText('cap')">📋 Copy</button></h3><pre id="cap"><?= htmlspecialchars($item['reels_tiktok_caption'] ?: 'No caption.') ?></pre></div>
            <div class="card"><h3>Thumbnail Prompt <button class="copy-btn" onclick="copyText('thumb')">📋 Copy</button></h3><pre id="thumb"><?= htmlspecialchars($item['thumbnail_prompt'] ?: 'No thumbnail prompt.') ?></pre></div>

        <?php else: ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h2 style="margin:0; font-size: 17px;">Studio Episodes Dashboard</h2>
            </div>
            <input type="text" id="searchBox" class="search-box" placeholder="🔍 Search by series, season, ep or title..." onkeyup="filterEpisodes()">
            
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
                            <a href="index.php?action=view&id=<?= $ep['id'] ?>" class="btn-action bg-view">View</a>
                            <a href="index.php?action=edit&id=<?= $ep['id'] ?>" class="btn-action bg-edit">Edit</a>
                            <a href="index.php?action=delete&id=<?= $ep['id'] ?>" class="btn-action bg-delete" onclick="return confirm('Delete this episode?');">Del</a>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 40px 0;">No episodes in studio database. Click "+ Add New" above!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="addModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">&times;</button>
            <h2 style="margin-bottom: 15px; font-size: 18px; color: #a78bfa;">✨ Add New Studio Episode</h2>
            <form method="POST" action="index.php">
                <div class="form-group"><label>Series Name</label><input type="text" name="series_name" value="Munki & Trunk" required></div>
                <div class="form-group"><label>Season Name</label><input type="text" name="season_name" value="Season 1" required></div>
                <div class="form-group"><label>Episode Number</label><input type="text" name="episode_num" placeholder="e.g., S1E1" required></div>
                <div class="form-group"><label>Episode Title</label><input type="text" name="title" placeholder="Episode Title" required></div>
                <div class="form-group"><label>YouTube Title</label><input type="text" name="yt_title" placeholder="Catchy YT Title" required></div>
                <div class="form-group"><label>YouTube Description</label><textarea name="yt_desc" placeholder="Description & Hashtags..."></textarea></div>
                <div class="form-group"><label>YouTube Tags</label><input type="text" name="yt_tags" placeholder="tag1, tag2, tag3"></div>
                <div class="form-group"><label>5-Min Script Summary</label><textarea name="script" placeholder="Scene-by-scene script..."></textarea></div>
                <div class="form-group"><label>Reels / TikTok Caption</label><textarea name="reels_tiktok_caption" placeholder="Viral caption with emojis..."></textarea></div>
                <div class="form-group"><label>Thumbnail Prompt</label><textarea name="thumbnail_prompt" placeholder="AI image generator prompt for thumbnail..."></textarea></div>
                <div class="form-group">
                    <label>Production Status</label>
                    <select name="status">
                        <option value="Script Ready" selected>Script Ready</option>
                        <option value="In Production">In Production</option>
                        <option value="Published">Published</option>
                    </select>
                </div>
                <button type="submit" class="btn">Save to Database</button>
            </form>
        </div>
    </div>

      <script>
        function openModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('addModal').style.display = 'none'; }
        window.onclick = function(event) {
            let modal = document.getElementById('addModal');
            if (event.target == modal) { modal.style.display = "none"; }
        }
        function copyText(id) {
            navigator.clipboard.writeText(document.getElementById(id).innerText).then(() => alert('✨ Copied to clipboard!'));
        }
        function copyAllData() {
            let text = `=== YOUTUBE TITLE ===\n${document.getElementById('yt_t').innerText}\n\n=== DESCRIPTION ===\n${document.getElementById('yt_d').innerText}\n\n=== TAGS ===\n${document.getElementById('yt_tag').innerText}\n\n=== SCRIPT ===\n${document.getElementById('scr').innerText}\n\n=== REELS CAPTION ===\n${document.getElementById('cap').innerText}\n\n=== THUMBNAIL PROMPT ===\n${document.getElementById('thumb').innerText}`;
            navigator.clipboard.writeText(text).then(() => alert('🚀 All Metadata Copied Successfully!'));
        }
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