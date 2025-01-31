<?php
$jsonFile = 'data/mods.json';

if (!file_exists($jsonFile)) {
    die('No data available for export');
}

$data = json_decode(file_get_contents($jsonFile), true);
$format = $_GET['format'] ?? 'json';

if ($format === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mods_export.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    fputcsv($output, ['Creator', 'Mod Type', 'Mod Name', 'Site 1', 'Status 1', 'Site 2', 'Status 2', 
                     'Site 3', 'Status 3', 'Site 4', 'Status 4', 'Site 5', 'Status 5', 
                     'Date Last Checked']);
    
    // Write data rows
    foreach ($data as $row) {
        $csvRow = [
            $row['creator'],
            $row['modType'],
            $row['modName'],
            $row['site1'],
            $row['status1'],
            $row['site2'],
            $row['status2'],
            $row['site3'],
            $row['status3'],
            $row['site4'],
            $row['status4'],
            $row['site5'],
            $row['status5'],
            $row['lastChecked']
        ];
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
} else {
    // JSON export
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="mods_export.json"');
    echo json_encode($data, JSON_PRETTY_PRINT);
}
?>
