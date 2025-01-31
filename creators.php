<?php
$creatorsFile = 'data/creators.json';

// Initialize or load creators
if (!file_exists($creatorsFile)) {
    file_put_contents($creatorsFile, json_encode([], JSON_PRETTY_PRINT));
}
$creators = json_decode(file_get_contents($creatorsFile), true) ?? [];

// Sort creators by name
usort($creators, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['creatorName'])) {
            $newCreator = [
                'id' => uniqid(),
                'name' => trim($_POST['creatorName'])
            ];
            $creators[] = $newCreator;
            usort($creators, function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
            file_put_contents($creatorsFile, json_encode($creators, JSON_PRETTY_PRINT));
            header('Location: creators.php?success=1');
            exit;
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['creatorId'])) {
            $creators = array_filter($creators, fn($c) => $c['id'] !== $_POST['creatorId']);
            file_put_contents($creatorsFile, json_encode(array_values($creators), JSON_PRETTY_PRINT));
            header('Location: creators.php?success=2');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Creators - Mod Tracker</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Creators</h1>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert success">
                <?php if($_GET['success'] == 1): ?>
                    Creator added successfully!
                <?php elseif($_GET['success'] == 2): ?>
                    Creator deleted successfully!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="creator-form">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label for="creatorName">New Creator Name:</label>
                <div class="input-group">
                    <input type="text" name="creatorName" id="creatorName" required>
                    <button type="submit">Add Creator</button>
                </div>
            </div>
        </form>

        <?php if (!empty($creators)): ?>
            <div class="creators-list">
                <h2>Current Creators</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Creator Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($creators as $creator): ?>
                                <tr>
                                    <td><?= htmlspecialchars($creator['name']) ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="creatorId" value="<?= $creator['id'] ?>">
                                            <button type="submit" class="button delete" onclick="return confirm('Are you sure you want to delete this creator?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="index.php" class="button">Back to Form</a>
        </div>
    </div>
</body>
</html>
