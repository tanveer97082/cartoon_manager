<?php
// Turn off error display for clean operations
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

$msg = "";

// Handle Dynamic Custom Column Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_column'])) {
    $col_name = trim($_POST['column_name']);
    $col_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $col_name);
    if (!empty($col_name)) {
        try {
            $db->exec("ALTER TABLE episodes ADD COLUMN $col_name TEXT DEFAULT ''");
            $msg = "Successfully added column: '$col_name'!";
        } catch (PDOException $e) {
            $msg = "Error adding column: " . $e->getMessage();
        }
    }
}

// Handle Single Row Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: export_import.php");
    exit;
}

// Handle Bulk Actions from Dropdown Menu (Delete Selected / Export Selected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!empty($_POST['selected_ids'])) {
        $ids = $_POST['selected_ids'];
        if ($_POST['bulk_action'] === 'delete_selected') {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("DELETE FROM episodes WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            $msg = "Selected rows deleted successfully!";
        } elseif ($_POST['bulk_action'] === 'export_selected') {
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=selected_cartoon_database.csv');
            
            $out = fopen('php://output', 'w');
            $columns_query = $db->query("PRAGMA table_info(episodes)");
            $cols = [];
            while ($col = $columns_query->fetch(PDO::FETCH_ASSOC)) { $cols[] = $col['name']; }
            fputcsv($out, $cols, ',', '"', '\\');
            
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT * FROM episodes WHERE id IN ($placeholders)");
            $stmt->execute($ids);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($out, $row, ',', '"', '\\');
            }
            fclose($out);
            exit;
        }
    }
}

// Handle Full Database Grid Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_columns'])) {
    if (isset($_POST['row_id']) && is_array($_POST['row_id'])) {
        $columns_query = $db->query("PRAGMA table_info(episodes)");
        $table_cols = [];
        while ($col = $columns_query->fetch(PDO::FETCH_ASSOC)) {
            if ($col['name'] !== 'id' && $col['name'] !== 'created_at') {
                $table_cols[] = $col['name'];
            }
        }

        for ($i = 0; $i < count($_POST['row_id']); $i++) {
            $id = $_POST['row_id'][$i];
            $update_parts = [];
            $params = [];

            foreach ($table_cols as $col) {
                if (isset($_POST[$col][$i])) {
                    $update_parts[] = "$col = ?";
                    $params[] = $_POST[$col][$i];
                }
            }
            $params[] = $id;

            $sql = "UPDATE episodes SET " . implode(', ', $update_parts) . " WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
        }
        $msg = "All database changes saved successfully!";
    }
}

// Handle Insert New Row
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_row'])) {
    $stmt = $db->prepare("INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, status, target_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['new_series'] ?? 'Munki & Trunk',
        $_POST['new_season'] ?? 'Season 1',
        $_POST['new_ep'] ?? 'S1E1',
        $_POST['new_title'] ?? 'New Episode',
        $_POST['new_yt'] ?? 'YouTube Title',
        'Script Ready',
        date('Y-m-d')
    ]);
    $msg = "New episode row inserted!";
}

// Fetch columns and rows
$columns_query = $db->query("PRAGMA table_info(episodes)");
$all_columns = [];
while ($col = $columns_query->fetch(PDO::FETCH_ASSOC)) {
    $all_columns[] = $col['name'];
}

$sort = $_GET['sort'] ?? 'id_desc';
$order_sql = "ORDER BY id DESC";
if (isset($_GET['col'])) {
    $col_sort = $_GET['col'];
    $dir = $_GET['dir'] ?? 'asc';
    if (in_array($col_sort, $all_columns)) {
        $order_sql = "ORDER BY $col_sort " . ($dir === 'desc' ? 'DESC' : 'ASC');
    }
}

$rows = $db->query("SELECT * FROM episodes $order_sql")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>✨ Ultimate Excel Database Studio</title>
    <style>
        :root { 
            --bg: #030712; --card: #0f172a; --border: rgba(255, 255, 255, 0.1);
            --primary: #8b5cf6; --accent: #06b6d4; --text: #f3f4f6; --muted: #9ca3af;
        }
        body { font-family: system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 12px; }
        .wrapper { max-width: 100%; width: 100%; box-sizing: border-box; margin: auto; }
        
        .excel-ribbon { background: var(--card); border: 1px solid var(--border); padding: 14px; border-radius: 14px; margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .ribbon-group { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .excel-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text); padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .excel-btn:hover { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-save { background: linear-gradient(135deg, #059669, #10b981); border-color: #059669; color: white; }

        .search-input { padding: 9px 12px; background: rgba(0,0,0,0.3); border: 1px solid var(--border); border-radius: 8px; color: #fff; font-size: 13px; width: 180px; }
        .search-input:focus { outline: none; border-color: var(--primary); }

        .spreadsheet-container { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); max-height: 75vh; }
        table { width: max-content; border-collapse: collapse; text-align: left; font-size: 13px; }
        th { background: rgba(15,23,42,0.95); color: var(--accent); padding: 12px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10; white-space: nowrap; }
        th a { color: var(--accent); text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 6px; }
        th a:hover { color: #fff; }
        td { padding: 8px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .cell-input { width: 180px; background: rgba(0,0,0,0.3); border: 1px solid transparent; color: var(--text); padding: 7px 10px; border-radius: 6px; font-size: 13px; box-sizing: border-box; }
        .cell-input:focus { outline: none; background: rgba(15,23,42,0.9); border-color: var(--primary); width: 260px; }

        /* Action Dropdown Menu (Fixed Overlap & No Sticky Action Column) */
        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background: var(--primary); color: white; padding: 6px 12px; font-size: 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .dropdown-content { display: none; position: absolute; right: 0; background: #1e293b; min-width: 160px; box-shadow: 0px 10px 25px rgba(0,0,0,0.8); z-index: 999; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        .dropdown-content a, .dropdown-content button { color: var(--text); padding: 9px 14px; text-decoration: none; display: block; font-size: 12px; background: none; border: none; width: 100%; text-align: left; cursor: pointer; }
        .dropdown-content a:hover, .dropdown-content button:hover { background: var(--primary); color: #fff; }
        .dropdown:hover .dropdown-content { display: block; }

        .batch-actions-menu { position: relative; display: inline-block; }
        .batch-btn { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; }
        .batch-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        .batch-content { display: none; position: absolute; left: 0; background: #1e293b; min-width: 150px; box-shadow: 0px 10px 25px rgba(0,0,0,0.8); z-index: 999; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        .batch-content button { color: var(--text); padding: 9px 14px; display: block; font-size: 12px; background: none; border: none; width: 100%; text-align: left; cursor: pointer; }
        .batch-content button:hover { background: var(--primary); color: #fff; }
        .batch-actions-menu:hover .batch-content { display: block; }

        .alert { background: rgba(5, 150, 105, 0.2); border: 1px solid rgba(5, 150, 105, 0.4); color: #34d399; padding: 10px; border-radius: 8px; margin-bottom: 14px; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>

        <?php if (!empty($msg)): ?>
            <div class="alert"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST" id="excelForm">
            <input type="hidden" name="bulk_action" id="bulkActionInput" value="">

            <div class="excel-ribbon">
                <div class="ribbon-group">
                    <span style="font-weight: 700; color: #fff; font-size: 14px;">📊 Excel Studio Grid</span>
                    <input type="text" id="gridSearch" class="search-input" placeholder="🔍 Quick search grid..." onkeyup="filterGrid()">
                </div>
                <div class="ribbon-group">
                    <button type="submit" name="save_all_columns" class="excel-btn btn-save">💾 Save All Changes</button>
                    
                    <!-- Batch Actions Dropdown (Enabled only when checkbox selected) -->
                    <div class="batch-actions-menu">
                        <button type="button" id="batchBtn" class="batch-btn" disabled>⚙️ Batch Actions ▾</button>
                        <div class="batch-content">
                            <button type="submit" onclick="submitBulk('delete_selected')" style="color:#f87171;">🗑️ Delete Selected</button>
                            <button type="submit" onclick="submitBulk('export_selected')">📥 Export Selected</button>
                        </div>
                    </div>

                    <a href="#" onclick="event.preventDefault(); document.getElementById('addRowModal').style.display='flex';" class="excel-btn">➕ Insert Row</a>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('addColumnModal').style.display='flex';" class="excel-btn">⚙️ Add Column</a>
                </div>
            </div>

            <div class="spreadsheet-container">
                <table id="excelTable">
                    <thead>
                        <tr>
                            <th style="width: 35px;"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                            <?php foreach ($all_columns as $col): 
                                $next_dir = (isset($_GET['col']) && $_GET['col'] === $col && ($_GET['dir'] ?? 'asc') === 'asc') ? 'desc' : 'asc';
                                $arrow = (isset($_GET['col']) && $_GET['col'] === $col) ? (($_GET['dir'] ?? 'asc') === 'asc' ? '↑' : '↓') : '↕';
                            ?>
                            <th>
                                <a href="export_import.php?col=<?= $col ?>&dir=<?= $next_dir ?>">
                                    <?= htmlspecialchars(str_replace('_', ' ', $col)) ?> <span><?= $arrow ?></span>
                                </a>
                            </th>
                            <?php endforeach; ?>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows): foreach ($rows as $r): ?>
                        <tr class="grid-row" data-search="<?= htmlspecialchars(strtolower(implode(' ', $r))) ?>">
                            <td><input type="checkbox" name="selected_ids[]" value="<?= $r['id'] ?>" onclick="checkSelection()"></td>
                            <?php foreach ($all_columns as $col): ?>
                            <td>
                                <?php if ($col === 'id'): ?>
                                    <strong><?= $r['id'] ?></strong>
                                    <input type="hidden" name="row_id[]" value="<?= $r['id'] ?>">
                                <?php elseif ($col === 'status'): ?>
                                    <select name="status[]" class="cell-input" style="width: 130px;">
                                        <option value="Script Ready" <?= $r['status']=='Script Ready'?'selected':'' ?>>Script Ready</option>
                                        <option value="In Production" <?= $r['status']=='In Production'?'selected':'' ?>>In Production</option>
                                        <option value="Published" <?= $r['status']=='Published'?'selected':'' ?>>Published</option>
                                    </select>
                                <?php elseif ($col === 'target_date'): ?>
                                    <input type="date" name="target_date[]" class="cell-input" value="<?= htmlspecialchars($r['target_date']) ?>" style="width: 135px;">
                                <?php elseif ($col === 'created_at'): ?>
                                    <span style="color: var(--muted); font-size: 11px;"><?= htmlspecialchars($r['created_at']) ?></span>
                                <?php else: ?>
                                    <input type="text" name="<?= $col ?>[]" class="cell-input" value="<?= htmlspecialchars($r[$col] ?? '') ?>">
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                            <td style="text-align: right;">
                                <div class="dropdown">
                                    <button type="button" class="dropbtn">Actions ▾</button>
                                    <div class="dropdown-content">
                                        <a href="add_edit.php?action=view&id=<?= $r['id'] ?>">👁️ View Data</a>
                                        <button type="button" onclick="copyEpisodeData(<?= htmlspecialchars(json_encode($r)) ?>)">📋 Copy Next Ep Data</button>
                                        <a href="export_import.php?delete_id=<?= $r['id'] ?>" onclick="return confirm('Delete this row?');" style="color:#f87171;">🗑️ Delete</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="<?= count($all_columns)+2 ?>" style="text-align: center; color: var(--muted); padding: 30px;">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- Modals for Add Row / Add Column -->
    <div id="addRowModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(3,7,18,0.85); backdrop-filter:blur(5px); align-items:center; justify-content:center; z-index:100;">
        <div style="background:#1e293b; padding:24px; border-radius:16px; border:1px solid var(--border); width:100%; max-width:400px;">
            <h2 style="margin-top:0; color:#fff; font-size:18px;">➕ Insert New Row</h2>
            <form method="POST">
                <div style="margin-bottom: 10px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">SERIES NAME</label><input type="text" name="new_series" value="Munki & Trunk" class="cell-input" style="width:100%;" required></div>
                <div style="margin-bottom: 10px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">SEASON NAME</label><input type="text" name="new_season" value="Season 1" class="cell-input" style="width:100%;" required></div>
                <div style="margin-bottom: 10px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">EPISODE NUMBER</label><input type="text" name="new_ep" placeholder="S1E5" class="cell-input" style="width:100%;" required></div>
                <div style="margin-bottom: 16px;"><label style="font-size:11px; color:var(--accent); font-weight:700;">EPISODE TITLE</label><input type="text" name="new_title" placeholder="Title" class="cell-input" style="width:100%;" required></div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="add_row" class="excel-btn btn-save" style="flex:1; justify-content:center;">Insert Row</button>
                    <button type="button" onclick="document.getElementById('addRowModal').style.display='none';" class="excel-btn" style="flex:1; justify-content:center; background:rgba(255,255,255,0.1);">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="addColumnModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(3,7,18,0.85); backdrop-filter:blur(5px); align-items:center; justify-content:center; z-index:100;">
        <div style="background:#1e293b; padding:24px; border-radius:16px; border:1px solid var(--border); width:100%; max-width:400px;">
            <h2 style="margin-top:0; color:#fff; font-size:18px;">⚙️ Add Database Column</h2>
            <form method="POST">
                <div style="margin-bottom: 16px;">
                    <label style="font-size:11px; color:var(--accent); font-weight:700;">COLUMN NAME (e.g. voice_artist)</label>
                    <input type="text" name="column_name" placeholder="column_name" class="cell-input" style="width:100%; margin-top:6px;" required>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="add_column" class="excel-btn btn-save" style="flex:1; justify-content:center;">Create Column</button>
                    <button type="button" onclick="document.getElementById('addColumnModal').style.display='none';" class="excel-btn" style="flex:1; justify-content:center; background:rgba(255,255,255,0.1);">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function filterGrid() {
            let val = document.getElementById('gridSearch').value.toLowerCase();
            let rows = document.getElementsByClassName('grid-row');
            for (let r of rows) {
                let text = r.getAttribute('data-search');
                r.style.display = text.includes(val) ? "" : "none";
            }
        }
        function toggleAll(source) {
            let checkboxes = document.getElementsByName('selected_ids[]');
            for(let cb of checkboxes) { cb.checked = source.checked; }
            checkSelection();
        }
        function checkSelection() {
            let checkboxes = document.getElementsByName('selected_ids[]');
            let checkedCount = 0;
            for(let cb of checkboxes) { if(cb.checked) checkedCount++; }
            let batchBtn = document.getElementById('batchBtn');
            batchBtn.disabled = (checkedCount === 0);
        }
        function submitBulk(action) {
            document.getElementById('bulkActionInput').value = action;
            if(action === 'delete_selected') {
                if(!confirm('Are you sure you want to delete selected rows?')) return;
            }
            document.getElementById('excelForm').submit();
        }
        function copyEpisodeData(row) {
            let text = `=== SERIES: ${row.series_name} | ${row.season_name} | ${row.episode_num} ===\nTITLE: ${row.title}\nYT TITLE: ${row.yt_title}\nDESC: ${row.yt_desc}\nTAGS: ${row.yt_tags}\nSCRIPT:\n${row.script}\n\nREELS CAPTION:\n${row.reels_tiktok_caption}\n\nTHUMBNAIL PROMPT:\n${row.thumbnail_prompt}`;
            navigator.clipboard.writeText(text).then(() => alert(`🚀 Copied complete metadata for ${row.episode_num}!`));
        }
    </script>
</body>
</html>
