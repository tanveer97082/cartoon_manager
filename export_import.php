<?php
// Turn off error display for clean operations
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'config.php';

$msg = "";

// Handle Dynamic Custom Column Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_column'])) {
    $col_name = trim($_POST['column_name']);
    // Sanitize column name (alphanumeric and underscores only)
    $col_name = preg_replace('/[^a-zA-Z0-9_]/', '_', $col_name);
    
    if (!empty($col_name)) {
        try {
            $db->exec("ALTER TABLE episodes ADD COLUMN $col_name TEXT DEFAULT ''");
            $msg = "Successfully added new column: '$col_name' to database!";
        } catch (PDOException $e) {
            $msg = "Error adding column (it might already exist): " . $e->getMessage();
        }
    }
}

// Handle Full Database Grid Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_all_columns'])) {
    if (isset($_POST['row_id']) && is_array($_POST['row_id'])) {
        // Fetch current table columns dynamically
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
        $msg = "All database columns updated successfully!";
    }
}

// Handle Row Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM episodes WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: export_import.php");
    exit;
}

// Fetch table column names dynamically
$columns_query = $db->query("PRAGMA table_info(episodes)");
$all_columns = [];
while ($col = $columns_query->fetch(PDO::FETCH_ASSOC)) {
    $all_columns[] = $col['name'];
}

// Sorting logic from header clicks
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
    <title>✨ Full Database Excel Studio</title>
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

        .spreadsheet-container { background: var(--card); border: 1px solid var(--border); border-radius: 16px; overflow-x: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); max-height: 75vh; }
        table { width: max-content; border-collapse: collapse; text-align: left; font-size: 13px; }
        th { background: rgba(15,23,42,0.95); color: var(--accent); padding: 12px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10; white-space: nowrap; }
        th a { color: var(--accent); text-decoration: none; display: flex; align-items: center; justify-content: space-between; gap: 6px; }
        th a:hover { color: #fff; }
        td { padding: 8px 10px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:hover { background: rgba(255,255,255,0.02); }

        .cell-input { width: 180px; background: rgba(0,0,0,0.3); border: 1px solid transparent; color: var(--text); padding: 7px 10px; border-radius: 6px; font-size: 13px; box-sizing: border-box; }
        .cell-input:focus { outline: none; background: rgba(15,23,42,0.9); border-color: var(--primary); width: 280px; }

        .alert { background: rgba(5, 150, 105, 0.2); border: 1px solid rgba(5, 150, 105, 0.4); color: #34d399; padding: 10px; border-radius: 8px; margin-bottom: 14px; font-size: 13px; font-weight: 600; }
        .del-cell-btn { background: rgba(220,38,38,0.2); color: #f87171; border: 1px solid rgba(220,38,38,0.4); padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 600; }
        .del-cell-btn:hover { background: #dc2626; color: #fff; }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php include 'header.php'; ?>

        <?php if (!empty($msg)): ?>
            <div class="alert"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="excel-ribbon">
                <div class="ribbon-group">
                    <span style="font-weight: 700; color: #fff; font-size: 14px;">📊 Excel Database Studio (Sorting & Column Manager)</span>
                </div>
                <div class="ribbon-group">
                    <button type="submit" name="save_all_columns" class="excel-btn btn-save">💾 Save All Changes</button>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('addColumnModal').style.display='flex';" class="excel-btn">➕ Add New Column</a>
                </div>
            </div>

            <div class="spreadsheet-container">
                <table>
                    <thead>
                        <tr>
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
                            <th style="text-align: right; position: sticky; right: 0; background: #0f172a;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rows): foreach ($rows as $r): ?>
                        <tr>
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
                            <td style="text-align: right; position: sticky; right: 0; background: #0f172a;">
                                <a href="export_import.php?delete_id=<?= $r['id'] ?>" class="del-cell-btn" onclick="return confirm('Delete this row?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="<?= count($all_columns)+1 ?>" style="text-align: center; color: var(--muted); padding: 30px;">No records found in database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

    <!-- Add Custom Column Modal -->
    <div id="addColumnModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(3,7,18,0.85); backdrop-filter:blur(5px); align-items:center; justify-content:center; z-index:100;">
        <div style="background:#1e293b; padding:24px; border-radius:16px; border:1px solid var(--border); width:100%; max-width:400px;">
            <h2 style="margin-top:0; color:#fff; font-size:18px;">➕ Add New Database Column</h2>
            <form method="POST">
                <div style="margin-bottom: 16px;">
                    <label style="font-size:11px; color:var(--accent); font-weight:700;">COLUMN NAME (e.g. director, duration)</label>
                    <input type="text" name="column_name" placeholder="column_name" class="cell-input" style="width:100%; margin-top:6px;" required>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="add_column" class="excel-btn btn-save" style="flex:1; justify-content:center;">Create Column</button>
                    <button type="button" onclick="document.getElementById('addColumnModal').style.display='none';" class="excel-btn" style="flex:1; justify-content:center; background:rgba(255,255,255,0.1);">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
