<?php
session_start();

$modTypesFile = 'data/mod_types.json';

// Initialize or load mod types
if (!file_exists($modTypesFile)) {
    $defaultTypes = [
        ['name' => 'Character'],
        ['name' => 'Arena'],
        ['name' => 'Pack']
    ];
    file_put_contents($modTypesFile, json_encode($defaultTypes, JSON_PRETTY_PRINT));
}

$modTypes = json_decode(file_get_contents($modTypesFile), true) ?? [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['modTypeName'])) {
            $newType = [
                'name' => trim($_POST['modTypeName'])
            ];
            $modTypes[] = $newType;
            file_put_contents($modTypesFile, json_encode($modTypes, JSON_PRETTY_PRINT));
            header('Location: mod_types.php');
            exit;
        } elseif ($_POST['action'] === 'delete' && isset($_POST['index'])) {
            array_splice($modTypes, $_POST['index'], 1);
            file_put_contents($modTypesFile, json_encode($modTypes, JSON_PRETTY_PRINT));
            header('Location: mod_types.php');
            exit;
        }
    }
}

// Sort mod types alphabetically
usort($modTypes, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Mod Types - Mod Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Current Mod Types</h1>
        
        <div class="form-group">
            <label for="modTypeName">New Mod Type:</label>
            <div class="input-group">
                <input type="text" id="modTypeName" name="modTypeName" placeholder="Enter mod type name">
                <button type="button" class="add-button" onclick="addModType()">Add Mod Type</button>
            </div>
        </div>

        <div class="list-table">
            <div class="table-header">
                <div class="header-cell">Mod Type Name</div>
                <div class="header-cell">Action</div>
            </div>
            <?php foreach($modTypes as $index => $type): ?>
                <div class="table-row">
                    <div class="cell"><?= htmlspecialchars($type['name']) ?></div>
                    <div class="cell">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="index" value="<?= $index ?>">
                            <button type="submit" class="delete-button">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="index.php" class="back-button">Back to Form</a>
    </div>

    <script>
    function addModType() {
        const input = document.getElementById('modTypeName');
        if (input.value.trim()) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="modTypeName" value="${input.value.trim()}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
