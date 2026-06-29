<?php
session_start();
$installedLock = __DIR__ . '/../.installed';
if (file_exists($installedLock)) {
    die("<h1>Already Installed</h1><p>The system is already installed. If you wish to reinstall, delete the .installed file.</p>");
}

$reqs = [
    'PHP Version (>= 7.4)' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'PDO Extension' => extension_loaded('pdo'),
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'cURL Extension' => extension_loaded('curl'),
    'MBString Extension' => extension_loaded('mbstring'),
];

// Check permissions
$dirs = [
    '../config',
    '../storage/logs',
    '../database'
];

$permReqs = [];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $permReqs["Write Access: " . $dir] = is_writable($dir);
}

$allPassed = !in_array(false, $reqs, true) && !in_array(false, $permReqs, true);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saraswati Abhyasika - Setup Wizard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; color: #374151; margin: 0; padding: 40px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #111827; text-align: center; }
        .step { text-align: center; color: #6b7280; font-size: 14px; margin-bottom: 20px; }
        .req-list { list-style: none; padding: 0; }
        .req-list li { padding: 10px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; }
        .pass { color: #10b981; font-weight: bold; }
        .fail { color: #ef4444; font-weight: bold; }
        .btn { display: block; width: 100%; padding: 12px; background: #4f46e5; color: white; text-align: center; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; text-decoration: none; margin-top: 20px; box-sizing: border-box; }
        .btn:hover { background: #4338ca; }
        .btn:disabled, .btn.disabled { background: #9ca3af; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Saraswati Abhyasika Setup</h1>
        <div class="step">Step 1 of 5: System Requirements</div>
        
        <h3>Server Requirements</h3>
        <ul class="req-list">
            <?php foreach ($reqs as $name => $passed): ?>
                <li><?= $name ?> <span class="<?= $passed ? 'pass' : 'fail' ?>"><?= $passed ? '✅ Passed' : '❌ Failed' ?></span></li>
            <?php endforeach; ?>
        </ul>

        <h3>Folder Permissions</h3>
        <ul class="req-list">
            <?php foreach ($permReqs as $name => $passed): ?>
                <li><?= $name ?> <span class="<?= $passed ? 'pass' : 'fail' ?>"><?= $passed ? '✅ Writable' : '❌ Unwritable' ?></span></li>
            <?php endforeach; ?>
        </ul>

        <?php if ($allPassed): ?>
            <a href="step2.php" class="btn">Next: Database Configuration</a>
        <?php else: ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 5px; margin-top: 20px;">
                Please resolve the failed requirements before continuing. Refresh this page once fixed.
            </div>
            <button class="btn" disabled>Next: Database Configuration</button>
        <?php endif; ?>
    </div>
</body>
</html>
