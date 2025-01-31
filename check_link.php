<?php
require_once 'includes/link_checker.php';

header('Content-Type: application/json');

if (!isset($_GET['url'])) {
    echo json_encode(['error' => 'No URL provided']);
    exit;
}

$url = $_GET['url'];
$checker = new LinkChecker();

// Check for specific domains
if (strpos($url, 'buzzheavier.com') !== false) {
    $result = $checker->checkBuzzheavier($url);
} elseif (strpos($url, '1fichier.com') !== false) {
    $result = $checker->check1Fichier($url);
} else {
    $result = $checker->checkLink($url);
}

echo json_encode($result);
