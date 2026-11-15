<?php
require_once 'config.php';
$action = $_GET['action'] ?? 'add';
$id = $_GET['id'] ?? null;
$item = null;

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
    $target_date = $_POST['target_date'];

    if (!empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE episodes SET series_name=?, season_name=?, episode_num=?, title=?, yt_title=?, yt_desc=?, yt_tags=?, script=?, reels_tiktok_caption=?, thumbnail_prompt=?, status=?, target_date=? WHERE id=?");
        $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status, $target_date, $_POST['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status, target_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status, $target_date]);
    }
    header("Location: episodes.php");
    exit;
}

if ($id) {
    $stmt = $db->prepare("SELECT * FROM episodes WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Studio Episode Form</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 800px; margin: auto; }
        .card-box { background: var(--card); border: 1px solid var(--border); padding: 24px; border-radius: 18px; box-shadow: 0 15px 40px rgba(0,0,0,0.6); }
        h2 { margin-top: 0; color: #fff; font-size: 18px; }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 5px; font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        input, textarea, select { width: 100%; padding: 11px 14px; background: rgba(0,0,0,0.4); border: 1px solid var(--border); border-radius: 10px; color: var(--text); box-sizing: border-box; font-size: 14px; }
        textarea { height: 95px; resize: vertical; }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 10px rgba(139,92,246,0.3); }
        .btn-submit { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 14px; border-radius: 10px; cursor: pointer; font-weight: 700; width: 100%; font-size: 15px; }
        .back-btn { display: inline-block; margin-bottom: 14px; color: var(--accent); text-decoration: none; font-weight: 600; font-size: 13px; }
        
        .meta-card { background: rgba(0,0,0,0.25); border: 1px solid var(--border); padding: 16px; border-radius: 12px; margin-bottom: 14px; }
        .meta-card h3 { margin: 0 0 8px 0; color: var(--accent); font-size: 12px; text-transform: uppercase; display: flex; justify-content: space-between; align-items: center; }
        .copy-btn { background: rgba(139,92,246,0.2); color: #c084fc; border: 1px solid rgba(139,92,246,0.4); padding: 4px 10px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600; }
        pre { white-space: pre-wrap; font-family: inherit; margin: 0; color: #e2e8f0; font-size: 13px; max-height: 140px; overflow-y: auto; }
        .bulk-btn { background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; border: none; padding: 12px; border-radius: 10px; font-weight: 700; cursor: pointer; width: 100%; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <a href="episodes.php" class="back-btn">&larr; Back to Episode Manager</a>
        
        <div class="card-box">
            <?php if ($action === 'view' && $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <span style="font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($item['series_name']) ?> | <?= htmlspecialchars($item['season_name']) ?></span>
                        <h2 style="margin:2px 0 0 0; color: #fff;"><?= htmlspecialchars($item['episode_num']) ?> - <?= htmlspecialchars($item['title']) ?></h2>
                    </div>
                    <a href="episodes.php?action=export_txt&id=<?= $item['id'] ?>" class="btn-act" style="background:#2563eb; padding:8px 14px; border-radius:8px; text-decoration:none; font-weight:600; color:#fff; font-size:13px;">📥 Export txt</a>
                </div>
                <button class="bulk-btn" onclick="copyAll()">🚀 Copy All Metadata (All-in-One)</button>
                
                <div class="meta-card"><h3>YouTube Title <button class="copy-btn" onclick="cp('yt_t')">Copy</button></h3><pre id="yt_t"><?= htmlspecialchars($item['yt_title']) ?></pre></div>
                <div class="meta-card"><h3>YouTube Description <button class="copy-btn" onclick="cp('yt_d')">Copy</button></h3><pre id="yt_d"><?= htmlspecialchars($item['yt_desc'] ?: 'None') ?></pre></div>
                <div class="meta-card"><h3>YouTube Tags <button class="copy-btn" onclick="cp('yt_tag')">Copy</button></h3><pre id="yt_tag"><?= htmlspecialchars($item['yt_tags'] ?: 'None') ?></pre></div>
                <div class="meta-card"><h3>5-Min Script Summary <button class="copy-btn" onclick="cp('scr')">Copy</button></h3><pre id="scr"><?= htmlspecialchars($item['script'] ?: 'None') ?></pre></div>
                <div class="meta-card"><h3>Reels / TikTok Caption <button class="copy-btn" onclick="cp('cap')">Copy</button></h3><pre id="cap"><?= htmlspecialchars($item['reels_tiktok_caption'] ?: 'None') ?></pre></div>
                <div class="meta-card"><h3>Thumbnail Prompt <button class="copy-btn" onclick="cp('thumb')">Copy</button></h3><pre id="thumb"><?= htmlspecialchars($item['thumbnail_prompt'] ?: 'None') ?></pre></div>

                <script>
                    function cp(id) { navigator.clipboard.writeText(document.getElementById(id).innerText).then(() => alert('Copied!')); }
                    function copyAll() {
                        let t = `YT TITLE:\n${document.getElementById('yt_t').innerText}\n\nDESC:\n${document.getElementById('yt_d').innerText}\n\nTAGS:\n${document.getElementById('yt_tag').innerText}\n\nSCRIPT:\n${document.getElementById('scr').innerText}`;
                        navigator.clipboard.writeText(t).then(() => alert('All Metadata Copied Successfully!'));
                    }
                </script>
            <?php else: ?>
                <h2><?= $item ? 'Edit Episode' : 'Add New Episode' ?></h2>
                <form method="POST" action="add_edit.php">
                    <input type="hidden" name="id" value="<?= $item['id'] ?? '' ?>">
                    <div class="form-group"><label>Series Name</label><input type="text" name="series_name" value="<?= htmlspecialchars($item['series_name'] ?? 'Munki & Trunk') ?>" required></div>
                    <div class="form-group"><label>Season Name</label><input type="text" name="season_name" value="<?= htmlspecialchars($item['season_name'] ?? 'Season 1') ?>" required></div>
                    <div class="form-group"><label>Episode Number</label><input type="text" name="episode_num" value="<?= htmlspecialchars($item['episode_num'] ?? '') ?>" placeholder="e.g. S1E1" required></div>
                    <div class="form-group"><label>Episode Title</label><input type="text" name="title" value="<?= htmlspecialchars($item['title'] ?? '') ?>" placeholder="Episode Title" required></div>
                    <div class="form-group"><label>YouTube Title</label><input type="text" name="yt_title" value="<?= htmlspecialchars($item['yt_title'] ?? '') ?>" required></div>
                    <div class="form-group"><label>YouTube Description</label><textarea name="yt_desc"><?= htmlspecialchars($item['yt_desc'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>YouTube Tags</label><input type="text" name="yt_tags" value="<?= htmlspecialchars($item['yt_tags'] ?? '') ?>"></div>
                    <div class="form-group"><label>5-Min Script Summary</label><textarea name="script"><?= htmlspecialchars($item['script'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Reels / TikTok Caption</label><textarea name="reels_tiktok_caption"><?= htmlspecialchars($item['reels_tiktok_caption'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Thumbnail Prompt (AI Image)</label><textarea name="thumbnail_prompt"><?= htmlspecialchars($item['thumbnail_prompt'] ?? '') ?></textarea></div>
                    <div class="form-group"><label>Target Upload Date</label><input type="date" name="target_date" value="<?= htmlspecialchars($item['target_date'] ?? date('Y-m-d')) ?>" required></div>
                    <div class="form-group">
                        <label>Production Status</label>
                        <select name="status">
                            <option value="Script Ready" <?= ($item && $item['status']=='Script Ready')?'selected':'' ?>>Script Ready</option>
                            <option value="In Production" <?= ($item && $item['status']=='In Production')?'selected':'' ?>>In Production</option>
                            <option value="Published" <?= ($item && $item['status']=='Published')?'selected':'' ?>>Published</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit"><?= $item ? 'Update Episode' : 'Save Episode' ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
