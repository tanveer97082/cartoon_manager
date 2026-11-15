<?php
// Determine active page for highlighting ribbon button
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="header" style="background: var(--card); border: 1px solid var(--border); padding: 18px; border-radius: 16px; margin-bottom: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
    <h1 style="margin: 0 0 4px 0; font-size: 20px; color: #fff; display: flex; align-items: center; gap: 8px;">✨ AI Cartoon Hub Pro</h1>
    <div style="font-size: 12px; color: var(--muted); margin-bottom: 14px;">Enterprise Studio Analytics & Command Center</div>
    
    <div class="ribbon" style="display: flex; gap: 8px; flex-wrap: wrap; background: rgba(0,0,0,0.3); padding: 8px; border-radius: 10px; border: 1px solid var(--border);">
        <a href="index.php" class="ribbon-btn <?= ($current_page == 'index.php') ? 'active' : '' ?>" style="background: <?= ($current_page == 'index.php') ? 'var(--primary)' : 'rgba(255,255,255,0.04)' ?>; border: 1px solid var(--border); color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px;">📊 Dashboard</a>
        <a href="episodes.php" class="ribbon-btn <?= ($current_page == 'episodes.php') ? 'active' : '' ?>" style="background: <?= ($current_page == 'episodes.php') ? 'var(--primary)' : 'rgba(255,255,255,0.04)' ?>; border: 1px solid var(--border); color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px;">🎬 Episodes</a>
        <a href="add_edit.php" class="ribbon-btn <?= ($current_page == 'add_edit.php') ? 'active' : '' ?>" style="background: <?= ($current_page == 'add_edit.php') ? 'var(--primary)' : 'rgba(255,255,255,0.04)' ?>; border: 1px solid var(--border); color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px;">➕ Add Episode</a>
        <a href="export_import.php" class="ribbon-btn <?= ($current_page == 'export_import.php') ? 'active' : '' ?>" style="background: <?= ($current_page == 'export_import.php') ? 'var(--primary)' : 'rgba(255,255,255,0.04)' ?>; border: 1px solid var(--border); color: #fff; padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px;">📂 Excel / Ribbon DB</a>
    </div>
</div>
