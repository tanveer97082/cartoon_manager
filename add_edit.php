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

    if (!empty($_POST['id'])) {
        $stmt = $db->prepare("UPDATE episodes SET series_name=?, season_name=?, episode_num=?, title=?, yt_title=?, yt_desc=?, yt_tags=?, script=?, reels_tiktok_caption=?, thumbnail_prompt=?, status=? WHERE id=?");
        $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status, $_POST['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status]);
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
    <title>✨ Episode Form</title>
    <style>
        :root { --bg-deep: #030712; --card-bg: rgba(30, 41, 59, 0.85); --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --text-muted: #9ca3af; --border: rgba(255, 255, 255, 0.12); }
        body { font-family: system-ui, sans-serif; background: var(--bg-deep); color: var(--text); margin: 0; padding: 15px; min-height: 100vh; }
        .container { max-width: 820px; margin: auto; background: var(--card-bg); backdrop-filter: blur(16px); padding: 22px; border-radius: 18px; border: 1px solid var(--border); box-shadow: 0 25px 50px rgba(0,0,0,0.7); }
        h1, h2 { color: #fff; margin-top: 0; }
        .btn { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; font-size: 15px; }
        .form-group { margin-bottom: 14px; }
        label { display: block; margin-bottom: 6px; font-size: 12px; color: var(--accent); font-weight: 700; text-transform: uppercase; }
        input, textarea, select { width: 100%; padding: 11px; background: rgba(15, 23, 42, 0.9); border: 1px solid var(--border); border-radius: 8px; color: var(--text); box-sizing: border-box; font-size: 14px; }
        textarea { height: 85px; resize: vertical; }
        .back-link { display: inline-block; margin-bottom: 15px; color: var(--accent); text-decoration: none; font-weight: 600; }
        .card { background: rgba(15, 23, 42, 0.85); border: 1px solid var(--border); padding: 16px; border-radius: 12px; margin-bottom: 16px; }
        .card h3 { margin-top: 0; color: var(--accent); font-size: 14px; display: flex; justify-content: space-between; align-items: center; text-transform: uppercase; }
        .copy-btn { background: rgba(139, 92, 246, 0.2); color: #c084fc; border: 1px solid rgba(139, 92, 246, 0.4); padding: 5px 10px; border-radius: 6px; cursor: pointer; font-size: 12px; }
        .bulk-copy-btn { background: linear-gradient(135deg, #06b6d4, #3b82f6); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; margin-bottom: 16px; }
        pre { white-space: pre-wrap; font-family: inherit; margin: 8px 0 0 0; color: #e2e8f0; font-size: 13px; background: rgba(30, 41, 59, 0.6); padding: 12px; border-radius: 8px; border: 1px solid var(--border); max-height: 160px; overflow-y: auto; }
        .btn-action { padding: 6px 12px; font-size: 12px; border-radius: 6px; text-decoration: none; color: white; font-weight: 600; display: inline-block; }
        .bg-export { background: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <a href="episodes.php" class="back-link">&larr; Back to Episode Manager</a>

        <?php if ($action === 'view' && $item): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <div>
                    <span style="font-size: 11px; color: var(--accent); font-weight: 700; text-transform: uppercase;"><?= htmlspecialchars($item['series_name']) ?> | <?= htmlspecialchars($item['season_name']) ?></span>
                    <h2 style="margin:2px 0 0 0; font-size: 17px; color: #fff;"><?= htmlspecialchars($item['episode_num']) ?> - <?= htmlspecialchars($item['title']) ?></h2>
                </div>
                <a href="episodes.php?action=export_txt&id=<?= $item['id'] ?>" class="btn-action bg-export">📥 Export .txt</a>
            </div>
            <button class="bulk-copy-btn" onclick="copyAllData()">🚀 Copy All Metadata</button>
            <div class="card"><h3>YouTube Title <button class="copy-btn" onclick="copyText('yt_t')">📋 Copy</button></h3><pre id="yt_t"><?= htmlspecialchars($item['yt_title']) ?></pre></div>
            <div class="card"><h3>YouTube Description <button class="copy-btn" onclick="copyText('yt_d')">📋 Copy</button></h3><pre id="yt_d"><?= htmlspecialchars($item['yt_desc']) ?></pre></div>
            <div class="card"><h3>YouTube Tags <button class="copy-btn" onclick="copyText('yt_tag')">📋 Copy</button></h3><pre id="yt_tag"><?= htmlspecialchars($item['yt_tags']) ?></pre></div>
            <div class="card"><h3>5-Min Script <button class="copy-btn" onclick="copyText('scr')">📋 Copy</button></h3><pre id="scr"><?= htmlspecialchars($item['script']) ?></pre></div>
            <div class="card"><h3>Reels Caption <button class="copy-btn" onclick="copyText('cap')">📋 Copy</button></h3><pre id="cap"><?= htmlspecialchars($item['reels_tiktok_caption']) ?></pre></div>
            <div class="card"><h3>Thumbnail Prompt <button class="copy-btn" onclick="copyText('thumb')">📋 Copy</button></h3><pre id="thumb"><?= htmlspecialchars($item['thumbnail_prompt']) ?></pre></div>

        <?php else: ?>
            <h2><?= $item ? 'Edit Episode' : 'Add New Episode' ?></h2>
            <form method="POST" action="add_edit.php">
                <input type="hidden" name="id" value="<?= $item['id'] ?? '' ?>">
                <div class="form-group"><label>Series Name</label><input type="text" name="series_name" value="<?= htmlspecialchars($item['series_name'] ?? 'Munki & Trunk') ?>" required></div>
                <div class="form-group"><label>Season Name</label><input type="text" name="season_name" value="<?= htmlspecialchars($item['season_name'] ?? 'Season 1') ?>" required></div>
                <div class="form-group"><label>Episode Number</label><input type="text" name="episode_num" value="<?= htmlspecialchars($item['episode_num'] ?? '') ?>" placeholder="S1E1" required></div>
                <div class="form-group"><label>Episode Title</label><input type="text" name="title" value="<?= htmlspecialchars($item['title'] ?? '') ?>" placeholder="Title" required></div>
                <div class="form-group"><label>YouTube Title</label><input type="text" name="yt_title" value="<?= htmlspecialchars($item['yt_title'] ?? '') ?>" required></div>
                <div class="form-group"><label>YouTube Description</label><textarea name="yt_desc"><?= htmlspecialchars($item['yt_desc'] ?? '') ?></textarea></div>
                <div class="form-group"><label>YouTube Tags</label><input type="text" name="yt_tags" value="<?= htmlspecialchars($item['yt_tags'] ?? '') ?>"></div>
                <div class="form-group"><label>5-Min Script Summary</label><textarea name="script" style="height: 120px;"><?= htmlspecialchars($item['script'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Reels / TikTok Caption</label><textarea name="reels_tiktok_caption"><?= htmlspecialchars($item['reels_tiktok_caption'] ?? '') ?></textarea></div>
                <div class="form-group"><label>Thumbnail Prompt</label><textarea name="thumbnail_prompt"><?= htmlspecialchars($item['thumbnail_prompt'] ?? '') ?></textarea></div>
                <div class="form-group">
                    <label>Production Status</label>
                    <select name="status">
                        <option value="Script Ready" <?= ($item && $item['status']=='Script Ready')?'selected':'' ?>>Script Ready</option>
                        <option value="In Production" <?= ($item && $item['status']=='In Production')?'selected':'' ?>>In Production</option>
                        <option value="Published" <?= ($item && $item['status']=='Published')?'selected':'' ?>>Published</option>
                    </select>
                </div>
                <button type="submit" class="btn"><?= $item ? 'Update Episode' : 'Save Episode' ?></button>
            </form>
        <?php endif; ?>
    </div>
    <script>
        function copyText(id) { navigator.clipboard.writeText(document.getElementById(id).innerText).then(() => alert('Copied!')); }
        function copyAllData() {
            let text = `YT TITLE: ${document.getElementById('yt_t').innerText}\n\nDESC:\n${document.getElementById('yt_d').innerText}\n\nTAGS:\n${document.getElementById('yt_tag').innerText}\n\nSCRIPT:\n${document.getElementById('scr').innerText}`;
            navigator.clipboard.writeText(text).then(() => alert('All Copied!'));
        }
    </script>
</body>
</html>
