<?php
$current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<style>
    .studio-header { background: var(--card); border: 1px solid var(--border); padding: 20px; border-radius: 18px; margin-bottom: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
    .studio-title { margin: 0 0 4px 0; font-size: 20px; color: #fff; font-weight: 800; display: flex; align-items: center; gap: 8px; letter-spacing: 0.3px; }
    .studio-subtitle { font-size: 12px; color: var(--muted); margin-bottom: 16px; font-weight: 500; }
    
    .nav-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; background: rgba(0,0,0,0.4); padding: 10px; border-radius: 12px; border: 1px solid var(--border); }
    @media(min-width: 600px) { .nav-grid { display: flex; flex-wrap: wrap; gap: 8px; } }
    
    .nav-btn { background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: var(--text); padding: 10px 16px; border-radius: 9px; text-decoration: none; font-weight: 600; font-size: 13px; text-align: center; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex-grow: 1; }
    .nav-btn:hover { background: rgba(139,92,246,0.2); border-color: var(--primary); color: #fff; }
    .nav-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; box-shadow: 0 4px 15px rgba(139,92,246,0.4); }
</style>

<div class="studio-header">
    <div class="studio-title">✨ AI Cartoon Hub Pro</div>
    <div class="studio-subtitle">Enterprise Studio Analytics & Command Center</div>
    
    <div class="nav-grid">
        <a href="/" class="nav-btn <?= ($current_uri == '/' || $current_uri == '/index' || $current_uri == '/index.php') ? 'active' : '' ?>">📊 Dashboard</a>
        <a href="/episodes" class="nav-btn <?= ($current_uri == '/episodes' || $current_uri == '/episodes.php') ? 'active' : '' ?>">🎬 Episodes</a>
        <a href="/add_edit" class="nav-btn <?= ($current_uri == '/add_edit' || $current_uri == '/add_edit.php') ? 'active' : '' ?>">➕ Add Episode</a>
        <a href="/excel_studio" class="nav-btn <?= ($current_uri == '/excel_studio' || $current_uri == '/excel_studio.php') ? 'active' : '' ?>">📂 Excel Studio</a>
    </div>
</div>

<!-- JavaScript URL Cleaner: Automatically removes .php from address bar -->
<script>
    if (window.location.pathname.endsWith('.php')) {
        let cleanPath = window.location.pathname.replace('.php', '');
        window.history.replaceState({}, '', cleanPath + window.location.search);
    }
</script>
