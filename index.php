<?php
$creatorsFile = 'data/creators.json';
$modTypesFile = 'data/mod_types.json';

$creators = [];
$modTypes = [];

if (file_exists($creatorsFile)) {
    $creators = json_decode(file_get_contents($creatorsFile), true) ?? [];
    usort($creators, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
}

if (file_exists($modTypesFile)) {
    $modTypes = json_decode(file_get_contents($modTypesFile), true) ?? [];
    usort($modTypes, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
}

// Get the previous values from session if they exist
session_start();
$selectedCreator = $_SESSION['selected_creator'] ?? '';
$selectedModType = $_SESSION['selected_mod_type'] ?? '';
$resetAfterSave = $_SESSION['reset_after_save'] ?? true; // Default to true
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mod Tracker</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .header h1 {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="settings-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="resetAfterSave" id="resetAfterSave" 
                           <?= $resetAfterSave ? 'checked' : '' ?>>
                    Reset form after save
                </label>
            </div>
            <h1>Mod Tracker</h1>
            <div class="manage-buttons">
                <a href="creators.php" class="button">Manage Creators</a>
                <a href="mod_types.php" class="button">Manage Mod Types</a>
            </div>
        </div>

        <form id="modForm" method="POST" action="process.php">
            <div class="top-form-group">
                <div class="form-row">
                    <div class="form-group">
                        <label for="creator">Creator:</label>
                        <select name="creator" id="creator" required>
                            <option value="">Select Creator</option>
                            <?php foreach($creators as $creator): ?>
                                <option value="<?= htmlspecialchars($creator['name']) ?>" 
                                    <?= $selectedCreator === $creator['name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($creator['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modType">Mod Type:</label>
                        <select name="modType" id="modType" required>
                            <option value="">Select Type</option>
                            <?php foreach($modTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type['name']) ?>" 
                                    <?= $selectedModType === $type['name'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group mod-name">
                        <label for="modName">Mod Name:</label>
                        <input type="text" name="modName" id="modName" required>
                    </div>
                </div>
            </div>

            <?php
            for($i = 1; $i <= 5; $i++) {
                echo '<div class="site-group">';
                echo '<div class="form-group site-link">';
                echo '<label for="site'.$i.'">Site '.$i.' Link:</label>';
                echo '<input type="url" name="site'.$i.'" id="site'.$i.'" placeholder="https://">';
                echo '</div>';
                
                echo '<div class="form-group site-status">';
                echo '<label for="status'.$i.'">Status '.$i.':</label>';
                echo '<select name="status'.$i.'" id="status'.$i.'">';
                echo '<option value="">Select Status</option>';
                echo '<option value="OK">OK</option>';
                echo '<option value="Dead Link">Dead Link</option>';
                echo '</select>';
                echo '</div>';
                echo '</div>';
            }
            ?>

            <div class="bottom-form-group">
                <div class="date-field">
                    <label for="lastChecked">Date Last Checked:</label>
                    <input type="date" name="lastChecked" id="lastChecked" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="action-buttons">
                    <button type="submit">Save</button>
                    <a href="report.php" class="button">View Report</a>
                    <a href="export.php?format=csv" class="button">Export CSV</a>
                    <a href="export.php?format=json" class="button">Export JSON</a>
                </div>
            </div>
        </form>
    </div>

    <script>
    // Handle checkbox state persistence
    const resetCheckbox = document.getElementById('resetAfterSave');
    resetCheckbox.addEventListener('change', function() {
        fetch('save_settings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'resetAfterSave=' + (this.checked ? '1' : '0')
        });
    });
    </script>
</body>
</html>
