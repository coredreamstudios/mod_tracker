<?php
$jsonFile = 'data/mods.json';
$data = [];

if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true) ?? [];
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $indexToDelete = $_POST['index'] ?? -1;
    if ($indexToDelete >= 0) {
        // Get the original data before filtering
        $originalData = [];
        if (file_exists($jsonFile)) {
            $originalData = json_decode(file_get_contents($jsonFile), true) ?? [];
        }
        
        // Remove the item from original data
        if (isset($originalData[$indexToDelete])) {
            array_splice($originalData, $indexToDelete, 1);
            file_put_contents($jsonFile, json_encode($originalData, JSON_PRETTY_PRINT));
            header('Location: report.php' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
            exit;
        }
    }
}

// Filter handling
$typeFilter = $_GET['type'] ?? '';
$creatorFilter = $_GET['creator'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Get unique values for filters
$types = array_unique(array_column($data, 'modType'));
$creators = array_unique(array_column($data, 'creator'));
sort($types);
sort($creators);

// Apply filters
if ($typeFilter) {
    $data = array_filter($data, fn($row) => $row['modType'] === $typeFilter);
}
if ($creatorFilter) {
    $data = array_filter($data, fn($row) => $row['creator'] === $creatorFilter);
}
if ($statusFilter) {
    $data = array_filter($data, function($row) use ($statusFilter) {
        for ($i = 1; $i <= 5; $i++) {
            if ($row["status$i"] === $statusFilter) return true;
        }
        return false;
    });
}

// Sort data by Creator, ModType, then ModName
usort($data, function($a, $b) {
    // First sort by creator
    $creatorCompare = strcasecmp($a['creator'], $b['creator']);
    if ($creatorCompare !== 0) {
        return $creatorCompare;
    }
    
    // Then by modType
    $typeCompare = strcasecmp($a['modType'], $b['modType']);
    if ($typeCompare !== 0) {
        return $typeCompare;
    }
    
    // Finally by modName
    return strcasecmp($a['modName'], $b['modName']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mod Tracker Report</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container report-container">
        <h1>Mod Report</h1>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="type">Filter by Type:</label>
                    <select name="type" id="type">
                        <option value="">All Types</option>
                        <?php foreach($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $typeFilter === $type ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="creator">Filter by Creator:</label>
                    <select name="creator" id="creator">
                        <option value="">All Creators</option>
                        <?php foreach($creators as $creator): ?>
                            <option value="<?= htmlspecialchars($creator) ?>" <?= $creatorFilter === $creator ? 'selected' : '' ?>>
                                <?= htmlspecialchars($creator) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Filter by Status:</label>
                    <select name="status" id="status">
                        <option value="">All Statuses</option>
                        <option value="OK" <?= $statusFilter === 'OK' ? 'selected' : '' ?>>OK</option>
                        <option value="Dead Link" <?= $statusFilter === 'Dead Link' ? 'selected' : '' ?>>Dead Link</option>
                    </select>
                </div>
                
                <button type="submit">Apply Filters</button>
                <a href="report.php" class="button">Clear Filters</a>
            </form>
        </div>

        <?php if (empty($data)): ?>
            <div class="alert info">No entries found.</div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Creator</th>
                            <th>Mod Type</th>
                            <th>Mod Name</th>
                            <th>Sites Status</th>
                            <th>Last Checked</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data as $index => $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['creator']) ?></td>
                                <td><?= htmlspecialchars($row['modType']) ?></td>
                                <td><?= htmlspecialchars($row['modName']) ?></td>
                                <td>
                                    <div class="site-statuses">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if (!empty($row["site$i"])): ?>
                                                <div class="site-status <?= strtolower(str_replace(' ', '-', $row["status$i"])) ?>">
                                                    <a href="<?= htmlspecialchars($row["site$i"]) ?>" 
                                                       target="_blank"
                                                       title="<?= htmlspecialchars($row["site$i"]) ?>"
                                                       data-url="<?= htmlspecialchars($row["site$i"]) ?>"
                                                       class="site-link">
                                                        Site <?= $i ?>: <?= $row["status$i"] ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($row['lastChecked']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit.php?index=<?= $index ?><?= $_SERVER['QUERY_STRING'] ? '&' . $_SERVER['QUERY_STRING'] : '' ?>" class="edit-button">Edit</a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="index" value="<?= $index ?>">
                                            <button type="submit" class="delete-button">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="button-group">
            <a href="index.php" class="button">Back to Form</a>
            <a href="export.php?format=csv" class="button">Export CSV</a>
            <a href="export.php?format=json" class="button">Export JSON</a>
        </div>
    </div>

    <div class="context-menu" id="contextMenu">
        <div class="menu-item" id="copyUrl">Copy URL</div>
    </div>

    <script>
    document.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('contextmenu', e => {
            e.preventDefault();
            const menu = document.getElementById('contextMenu');
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
            
            document.getElementById('copyUrl').onclick = async () => {
                try {
                    await navigator.clipboard.writeText(link.href);
                    menu.style.display = 'none';
                } catch (err) {
                    console.error('Copy failed:', err);
                }
            };
        });
    });

    document.addEventListener('click', () => {
        document.getElementById('contextMenu').style.display = 'none';
    });

    // Add proper delete functionality
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this mod?')) {
                this.closest('form').submit();
            }
        });
    });
    </script>
</body>
</html>
