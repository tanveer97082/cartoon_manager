<?php
$db_dir = 'database';
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
}
$db_file = $db_dir . '/cartoons.db';
try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
