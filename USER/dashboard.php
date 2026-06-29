<?php
// User/dashboard.php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Fetch latest user details
$stmt = $pdo->prepare("SELECT full_name, unique_user_id, email, phone, account_status FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['account_status'] !== 'Active') {
    session_destroy();
    header("Location: index.php");
    exit;
}

$firstLetter = strtoupper(substr($user['full_name'], 0, 1));

// Fetch active subscription
$subStmt = $pdo->prepare("
    SELECT s.expiry_date, s.amount_paid, t.table_number, p.plan_name 
    FROM user_subscriptions s
    JOIN library_tables t ON s.table_id = t.table_id
    JOIN subscription_plans p ON s.plan_id = p.plan_id
    WHERE s.user_id = ? AND s.subscription_status IN ('ACTIVE', 'CONFIRMED')
    ORDER BY s.expiry_date DESC LIMIT 1
");
$subStmt->execute([$user_id]);
$activeSub = $subStmt->fetch();

$expiryAlert = false;
$daysLeft = 0;
if ($activeSub) {
    $expiryDate = new DateTime($activeSub['expiry_date']);
    $today = new DateTime();
    $today->setTime(0,0,0);
    $diff = $today->diff($expiryDate);
    $daysLeft = $diff->invert ? -$diff->days : $diff->days;
    if ($daysLeft <= 3 && $daysLeft >= 0) {
        $expiryAlert = true;
    }
}

// Build dynamic user notifications
$userNotifications = [];
if ($activeSub) {
    $userNotifications[] = [
        'title' => 'Table Booked',
        'message' => 'You have an active booking for Table T-' . htmlspecialchars($activeSub['table_number']) . ' on the ' . htmlspecialchars($activeSub['plan_name']) . ' plan.',
        'icon' => '🪑'
    ];
    
    if ($expiryAlert) {
        $userNotifications[] = [
            'title' => 'Subscription Expiring Soon',
            'message' => 'Your subscription for Table T-' . htmlspecialchars($activeSub['table_number']) . ' is expiring in ' . $daysLeft . ' day(s)! Please renew soon.',
            'icon' => '⚠️'
        ];
    }
    
    $tableCheck = $pdo->prepare("SELECT status FROM library_tables WHERE table_number = ?");
    $tableCheck->execute([$activeSub['table_number']]);
    $tableStatus = $tableCheck->fetchColumn();
    
    if ($tableStatus === 'Maintenance') {
        $userNotifications[] = [
            'title' => 'Table Under Maintenance',
            'message' => 'Your assigned Table T-' . htmlspecialchars($activeSub['table_number']) . ' is currently in maintenance mode.',
            'icon' => '🔧'
        ];
    }
}
$unreadCount = count($userNotifications);

// Fetch all tables
$tablesStmt = $pdo->query("SELECT table_number, status FROM library_tables ORDER BY table_number ASC");
$tables = $tablesStmt->fetchAll();

// Fetch plans for Payment QR Generator
$plansStmt = $pdo->query("SELECT plan_id, plan_name, price FROM subscription_plans WHERE active = 1");
$plans = $plansStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Saraswati Abhyasika</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css?v=<?= time() ?>">
    <script src="theme.js"></script>
</head>
<body>

<nav class="navbar">
    <div class="nav-left">
        <img src="../IMAGES/SHREE SARASWATI ABHYASIKA LOGO.png" alt="Logo" id="profileToggle" style="cursor: pointer;" title="Open Profile Menu">
        <h1>सरस्वती अभ्यासिका</h1>
    </div>
    <div class="nav-right">
        <div class="notification-wrapper" id="notifToggle" style="margin-top: 5px;">
            <div class="bell-icon">
                🔔
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge"><?= $unreadCount ?></span>
                <?php endif; ?>
            </div>
        </div>
        <button class="theme-toggle" id="theme-toggle">🌙 Dark Mode</button>
        <div class="profile-avatar"><?= htmlspecialchars($firstLetter) ?></div>
        <a href="?logout=1" class="btn-logout">Sign Out</a>
    </div>
</nav>

<!-- Drawer Overlay -->
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- Profile Drawer -->
<div class="profile-drawer" id="profileDrawer">
    <button class="drawer-close" id="drawerClose">&times;</button>
    <div style="margin-top: 40px;">
        <h2>My Profile</h2>
        <div style="margin-top: 20px; font-size: 1.05rem; line-height: 1.8;">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
            <p><strong>Unique ID:</strong> <?= htmlspecialchars($user['unique_user_id']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
            <p><strong>Status:</strong> <span style="color:var(--success); font-weight:bold;"><?= htmlspecialchars($user['account_status']) ?></span></p>
            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; display:flex; flex-direction:column; gap:10px;">
                <a href="attendance.php" class="btn-primary" style="display:block; text-align:center; padding: 10px; text-decoration: none; background: #3b82f6; color:#fff; border-radius:8px;">Attendance History</a>
                <a href="change_password.php" class="btn-secondary" style="display:block; text-align:center; padding: 10px; text-decoration: none;">Change Password</a>
            </div>
        </div>
    </div>
</div>

<!-- Notification Drawer -->
<div class="notification-drawer" id="notificationDrawer">
    <div class="drawer-header">
        <h3>Notifications</h3>
        <button class="drawer-close" id="notifClose" style="position:static; margin-top:0;">&times;</button>
    </div>
    <div class="drawer-content">
        <?php if (count($userNotifications) > 0): ?>
            <?php foreach ($userNotifications as $notif): ?>
                <div class="notification-item">
                    <div class="notif-body">
                        <div class="notif-title"><?= $notif['icon'] ?> <?= htmlspecialchars($notif['title']) ?></div>
                        <div class="notif-msg"><?= htmlspecialchars($notif['message']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">No notifications here!</div>
        <?php endif; ?>
    </div>
</div>

<div class="dashboard-container single-col-center">
    
    <?php if ($expiryAlert): ?>
        <div class="alert-expiry" style="width: 100%;">
            ⚠️ Warning: Your subscription for Table T-<?= htmlspecialchars($activeSub['table_number']) ?> is expiring in <?= $daysLeft ?> day(s)! Please renew soon to keep your table.
        </div>
    <?php endif; ?>

    <!-- Center Column: Tables Grid -->
    <div class="card">
        <h2 style="text-align: center;">Library Tables Map</h2>
            <p style="margin-bottom: 20px; color: var(--text-muted);">
                <span style="display:inline-block; width:12px; height:12px; background:#ef4444; margin-right:5px; border-radius:2px;"></span> Booked
                <span style="display:inline-block; width:12px; height:12px; background:#10b981; margin-left:15px; margin-right:5px; border-radius:2px;"></span> Available
                <span style="display:inline-block; width:12px; height:12px; background:#3b82f6; margin-left:15px; margin-right:5px; border-radius:2px;"></span> Maintenance
                <span style="display:inline-block; width:12px; height:12px; background:#f59e0b; margin-left:15px; margin-right:5px; border-radius:2px;"></span> Reserved (Pending Payment)
            </p>
            
            <div class="tables-grid">
                <?php foreach($tables as $table): ?>
                    <?php 
                        $isAvailable = ($table['status'] === 'Available');
                        if ($table['status'] === 'Maintenance') {
                            $class = 'table-blue maintenance-table';
                            $content = 'T-' . htmlspecialchars($table['table_number']) . '<br><span style="color:white; font-weight:bold; font-size:1.2em;">M</span>';
                        } else if ($table['status'] === 'Booked') {
                            $class = 'table-red';
                            $content = 'T-' . htmlspecialchars($table['table_number']);
                        } else if ($table['status'] === 'Reserved') {
                            $class = 'table-orange';
                            $content = 'T-' . htmlspecialchars($table['table_number']) . '<br><span style="color:white; font-size:0.8em;">Reserved</span>';
                        } else {
                            $class = 'table-green available-table';
                            $content = 'T-' . htmlspecialchars($table['table_number']);
                        }
                        $onClick = $isAvailable ? 'onclick="selectTable(this, \'' . htmlspecialchars($table['table_number']) . '\')"' : '';
                    ?>
                    <div class="table-box <?= $class ?>" <?= $onClick ?>>
                        <?= $content ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <div style="text-align: center; margin-top: 30px;">
            <button id="bookTableBtn" class="btn-primary" style="width: auto; padding: 15px 40px; font-size: 1.1rem; opacity: 0.5; cursor: not-allowed;" disabled>Proceed to Book Table</button>
        </div>
    </div>

</div>

<script>
    // Theme logic
    const themeToggle = document.getElementById('theme-toggle');
    const htmlEl = document.documentElement;
    
    // Set initial text
    if (htmlEl.getAttribute('data-theme') === 'dark') {
        themeToggle.textContent = '☀️ Light Mode';
    }

    themeToggle.addEventListener('click', () => {
        if (htmlEl.getAttribute('data-theme') === 'dark') {
            htmlEl.setAttribute('data-theme', 'light');
            themeToggle.textContent = '🌙 Dark Mode';
            localStorage.setItem('userTheme', 'light');
        } else {
            htmlEl.setAttribute('data-theme', 'dark');
            themeToggle.textContent = '☀️ Light Mode';
            localStorage.setItem('userTheme', 'dark');
        }
    });

    // Drawer Logic
    const profileToggle = document.getElementById('profileToggle');
    const profileDrawer = document.getElementById('profileDrawer');
    const drawerClose = document.getElementById('drawerClose');
    const drawerOverlay = document.getElementById('drawerOverlay');

    function openDrawer() {
        profileDrawer.classList.add('open');
        drawerOverlay.classList.add('open');
    }

    function closeDrawer() {
        profileDrawer.classList.remove('open');
        drawerOverlay.classList.remove('open');
    }

    profileToggle.addEventListener('click', openDrawer);
    drawerClose.addEventListener('click', closeDrawer);
    
    // Notifications Drawer Logic
    const notifToggle = document.getElementById('notifToggle');
    const notificationDrawer = document.getElementById('notificationDrawer');
    const notifClose = document.getElementById('notifClose');

    function openNotifDrawer() {
        notificationDrawer.classList.add('open');
        drawerOverlay.classList.add('open');
    }

    function closeNotifDrawer() {
        notificationDrawer.classList.remove('open');
        drawerOverlay.classList.remove('open');
    }

    notifToggle.addEventListener('click', openNotifDrawer);
    notifClose.addEventListener('click', closeNotifDrawer);

    // General Overlay Click
    drawerOverlay.addEventListener('click', () => {
        closeDrawer();
        closeNotifDrawer();
    });

    // Table Selection Logic
    const bookTableBtn = document.getElementById('bookTableBtn');
    let currentSelectedTable = null;

    function selectTable(element, tableNumber) {
        // Deselect previous
        const previousSelected = document.querySelector('.table-selected');
        if (previousSelected) {
            previousSelected.classList.remove('table-selected');
        }

        // Select new
        element.classList.add('table-selected');
        currentSelectedTable = tableNumber;

        // Enable Book button
        bookTableBtn.disabled = false;
        bookTableBtn.style.opacity = '1';
        bookTableBtn.style.cursor = 'pointer';
    }

    // Book Table Button Click - Redirect to Payment Flow
    bookTableBtn.addEventListener('click', function() {
        if (currentSelectedTable) {
            window.location.href = 'payment.php?table_id=' + currentSelectedTable;
        }
    });
</script>

</body>
</html>
