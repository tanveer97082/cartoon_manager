<?php
require_once 'config.php';

// Check if run from Termux/CLI
if (php_sapi_name() !== 'cli') {
    die("Access denied. This script can only be run from the terminal.");
}

$series = $argv[1] ?? 'Munki & Trunk';
$season = $argv[2] ?? 'Season 1';
$ep_num = $argv[3] ?? 'S1E1';
$title = $argv[4] ?? 'The Swinging Vine Rescue';
$yt_title = $argv[5] ?? 'Munki & Trunk | The Swinging Vine Rescue | Episode 1';
$yt_desc = $argv[6] ?? 'Join Munki and Trunk in the very first episode! #CartoonsForKids';
$yt_tags = $argv[7] ?? 'cartoons, munki and trunk';
$script = $argv[8] ?? 'Scene 1: Canopy stunts. Scene 2: Baby bird nest rescue.';
$caption = $argv[9] ?? 'When you try to help your friend! 🐒🐘 #MunkiAndTrunk';
$thumb = $argv[10] ?? 'Munki hanging from a vibrant green vine, 4k animation';
$status = $argv[11] ?? 'Published';

try {
    $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$series, $season, $ep_num, $title, $yt_title, $yt_desc, $yt_tags, $script, $caption, $thumb, $status]);
    echo "[+] Success! Episode {$ep_num} ({$title}) added via CLI to database.\n";
} catch (PDOException $e) {
    echo "[-] Error inserting data: " . $e->getMessage() . "\n";
}
?>
