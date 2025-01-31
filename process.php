<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store the reset preference
    $_SESSION['reset_after_save'] = isset($_POST['resetAfterSave']);
    
    $data = [
        'creator' => $_POST['creator'] ?? '',
        'modType' => $_POST['modType'] ?? '',
        'modName' => $_POST['modName'] ?? '',
        'lastChecked' => $_POST['lastChecked'] ?? '',
    ];
    
    // Add sites and their statuses
    for ($i = 1; $i <= 5; $i++) {
        $data["site$i"] = $_POST["site$i"] ?? '';
        $data["status$i"] = $_POST["status$i"] ?? '';
    }
    
    // Read existing data
    $jsonFile = 'data/mods.json';
    $existingData = [];
    
    if (file_exists($jsonFile)) {
        $existingData = json_decode(file_get_contents($jsonFile), true) ?? [];
    }
    
    // Add new data
    $existingData[] = $data;
    
    // Create data directory if it doesn't exist
    if (!file_exists('data')) {
        mkdir('data', 0777, true);
    }
    
    // Save to JSON file
    file_put_contents($jsonFile, json_encode($existingData, JSON_PRETTY_PRINT));
    
    // If not resetting, store the selected values
    if (!isset($_POST['resetAfterSave'])) {
        $_SESSION['selected_creator'] = $_POST['creator'];
        $_SESSION['selected_mod_type'] = $_POST['modType'];
    } else {
        // Clear the stored values if resetting
        $_SESSION['selected_creator'] = '';
        $_SESSION['selected_mod_type'] = '';
    }
    
    header('Location: index.php?success=1');
    exit;
}
?>
