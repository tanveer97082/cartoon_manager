<?php
require_once 'config.php';

if (php_sapi_name() !== 'cli') {
    die("Access denied.");
}

$series = $argv[1] ?? 'Munki & Trunk';
$season = $argv[2] ?? 'Season 1';
$ep_num = $argv[3] ?? 'S1E1';
$title = $argv[4] ?? 'Title';
$yt_title = $argv[5] ?? '';
$yt_desc = $argv[6] ?? '';
$yt_tags = $argv[7] ?? '';
$script = $argv[8] ?? '';
$caption = $argv[9] ?? '';
$thumb = $argv[10] ?? '';
$status = $argv[11] ?? 'Script Ready';
$target_date = $argv[12] ?? date('Y-m-d'); // Target upload date

try {
    $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status, target_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status, $target_date]);
    echo "[+] Success! Episode {$ep_num} ({$title}) added with target date {$target_date}.\n";
} catch (PDOException $e) {
    echo "[-] Error: " . $e->getMessage() . "\n";
}
?>
