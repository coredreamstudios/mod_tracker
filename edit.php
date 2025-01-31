<?php
$jsonFile = 'data/mods.json';
$data = [];

if (file_exists($jsonFile)) {
    $data = json_decode(file_get_contents($jsonFile), true) ?? [];
}

$index = $_GET['index'] ?? -1;
$mod = $data[$index] ?? null;

if ($mod === null) {
    header('Location: report.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mod = [
        'modType' => $_POST['modType'] ?? '',
        'creator' => $_POST['creator'] ?? '',
        'modName' => $_POST['modName'] ?? '',
        'lastChecked' => $_POST['lastChecked'] ?? date('Y-m-d')
    ];
    
    // Handle sites and statuses
    for ($i = 1; $i <= 5; $i++) {
        $mod["site$i"] = $_POST["site$i"] ?? '';
        $mod["status$i"] = $_POST["status$i"] ?? '';
    }
    
    $data[$index] = $mod;
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    
    // Redirect back to report with any existing filters
    $query = $_GET;
    unset($query['index']); // Remove the index from query
    $queryString = http_build_query($query);
    header('Location: report.php' . ($queryString ? "?$queryString" : ''));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mod Entry</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Mod Entry</h1>
        
        <form method="POST" id="modForm">
            <div class="form-group">
                <label for="modType">Mod Type:</label>
                <input type="text" name="modType" id="modType" required value="<?= htmlspecialchars($mod['modType']) ?>">
            </div>
            
            <div class="form-group">
                <label for="creator">Creator:</label>
                <input type="text" name="creator" id="creator" required value="<?= htmlspecialchars($mod['creator']) ?>">
            </div>
            
            <div class="form-group">
                <label for="modName">Mod Name:</label>
                <input type="text" name="modName" id="modName" required value="<?= htmlspecialchars($mod['modName']) ?>">
            </div>
            
            <?php for($i = 1; $i <= 5; $i++): ?>
                <div class="site-group">
                    <div class="form-group">
                        <label for="site<?= $i ?>">Site <?= $i ?>:</label>
                        <input type="url" name="site<?= $i ?>" id="site<?= $i ?>" value="<?= htmlspecialchars($mod["site$i"] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status<?= $i ?>">Status <?= $i ?>:</label>
                        <select name="status<?= $i ?>" id="status<?= $i ?>">
                            <option value="">Select Status</option>
                            <option value="OK" <?= ($mod["status$i"] ?? '') === 'OK' ? 'selected' : '' ?>>OK</option>
                            <option value="Dead Link" <?= ($mod["status$i"] ?? '') === 'Dead Link' ? 'selected' : '' ?>>Dead Link</option>
                        </select>
                    </div>
                </div>
            <?php endfor; ?>
            
            <div class="bottom-form-group">
                <div class="date-field">
                    <label for="lastChecked">Date Last Checked:</label>
                    <input type="date" name="lastChecked" id="lastChecked" required value="<?= htmlspecialchars($mod['lastChecked']) ?>">
                </div>

                <div class="action-buttons">
                    <button type="submit">Save Changes</button>
                    <a href="report.php<?= $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '' ?>" class="button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
