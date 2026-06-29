<?php
// pratik2002/attendance.php
require_once 'config.php';

// Export to CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Attendance Status', 'Username', 'Phone Number', 'Unique ID', 'Date', 'Check-in Time']);

    $q = "SELECT u.unique_user_id, u.full_name, u.phone, a.attendance_date, a.check_in_time, a.status FROM attendance a JOIN users u ON a.user_id = u.user_id ORDER BY a.attendance_date DESC";
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
        fputcsv($output, [$row['status'], $row['full_name'], $row['phone'], $row['unique_user_id'], $row['attendance_date'], $row['check_in_time']]);
    }
    fclose($output);
    exit;
}

// Analytics
$today = date('Y-m-d');
$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');

try {
    // Today's total attendance
    $td = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date = ? AND status = 'Present'");
    $td->execute([$today]);
    $todayTotal = $td->fetchColumn();

    // Monthly total
    $mo = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date BETWEEN ? AND ? AND status = 'Present'");
    $mo->execute([$monthStart, $monthEnd]);
    $monthTotal = $mo->fetchColumn();

    // Total Users
    $tu = $pdo->query("SELECT COUNT(*) FROM users WHERE account_status = 'Active'")->fetchColumn();

    // Fetch records
    $search = $_GET['search'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
    $monthFilter = $_GET['month'] ?? '';

    $where = [];
    $params = [];
    
    if ($search) {
        $where[] = "(u.full_name LIKE ? OR u.unique_user_id LIKE ? OR a.attendance_date LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if ($statusFilter) {
        $where[] = "a.status = ?";
        $params[] = $statusFilter;
    }
    if ($monthFilter) {
        $where[] = "DATE_FORMAT(a.attendance_date, '%Y-%m') = ?";
        $params[] = $monthFilter;
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';
    $query = "SELECT a.*, u.full_name, u.unique_user_id, u.phone FROM attendance a JOIN users u ON a.user_id = u.user_id $whereClause ORDER BY a.attendance_date DESC, a.check_in_time DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
} catch (PDOException $e) {
    // schema not updated yet
    $records = [];
    $todayTotal = 0;
    $monthTotal = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - Admin</title>
    <link rel="stylesheet" href="Dashboard.css">
    <style>
        .minimal-card {
            background: #fff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        .minimal-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            transform: translateY(-2px);
        }
        .minimal-card h3 { font-size: 0.9rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .minimal-card p { font-size: 2rem; color: #0f172a; font-weight: 700; margin: 0; }
        
        .filter-container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; margin-bottom: 25px; }
        .filter-input { padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.95rem; outline: none; transition: border-color 0.2s; }
        .filter-input:focus { border-color: #3b82f6; }
        .btn-minimal { padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; border: none; }
        .btn-blue { background: #3b82f6; color: #fff; }
        .btn-blue:hover { background: #2563eb; }
        .btn-gray { background: #f1f5f9; color: #475569; }
        .btn-gray:hover { background: #e2e8f0; }
        .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>

<div class="content-body" style="padding: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:15px;">
        <h2 style="font-family: 'Merriweather', serif; color: #0f172a;">Attendance Management</h2>
        <a href="attendance.php?export=csv" class="btn-primary" style="background:#10b981; border:none; text-decoration:none; padding:10px 20px;">Export to CSV</a>
    </div>


    <!-- Dashboard Widgets -->
    <div class="dashboard-cards" style="margin-bottom: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        <div class="minimal-card">
            <h3>Today's Attendance</h3>
            <p><?= $todayTotal ?> <span style="font-size:1rem; color:#64748b; font-weight:500;">Present</span></p>
        </div>
        <div class="minimal-card">
            <h3>Monthly Attendance</h3>
            <p><?= $monthTotal ?> <span style="font-size:1rem; color:#64748b; font-weight:500;">Check-ins</span></p>
        </div>
        <div class="minimal-card">
            <h3>Total Active Users</h3>
            <p><?= $tu ?></p>
        </div>
        <div class="minimal-card">
            <h3>Attendance Rate</h3>
            <p><?= $tu > 0 ? round(($todayTotal / $tu) * 100, 1) : 0 ?>%</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-container">
        <form method="GET" style="display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
            <input type="text" name="search" class="filter-input" placeholder="Search user or date..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" style="flex:1; min-width:220px;">
            <select name="status" class="filter-input">
                <option value="">All Statuses</option>
                <option value="Present" <?= (($_GET['status']??'') == 'Present') ? 'selected' : '' ?>>Present</option>
                <option value="Absent" <?= (($_GET['status']??'') == 'Absent') ? 'selected' : '' ?>>Absent</option>
                <option value="Late" <?= (($_GET['status']??'') == 'Late') ? 'selected' : '' ?>>Late</option>
            </select>
            <input type="month" name="month" class="filter-input" value="<?= htmlspecialchars($_GET['month'] ?? '') ?>">
            <button type="submit" class="btn-minimal btn-blue">Filter</button>
            <a href="attendance.php" class="btn-minimal btn-gray" style="text-decoration:none;">Clear</a>
        </form>
    </div>

    <!-- Records Table -->
    <div class="card" style="overflow-x:auto;">
        <table class="data-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="background: rgba(0,0,0,0.02); border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px; color: #475569; font-weight: 600;">Attendance</th>
                    <th style="padding: 15px; color: #475569; font-weight: 600;">Username</th>
                    <th style="padding: 15px; color: #475569; font-weight: 600;">Phone Number</th>
                    <th style="padding: 15px; color: #475569; font-weight: 600;">Unique ID</th>
                    <th style="padding: 15px; color: #475569; font-weight: 600;">Login Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) > 0): ?>
                    <?php foreach ($records as $r): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td data-label="Attendance" style="padding: 15px;">
                                <span class="status-badge" style="background:<?= $r['status']=='Present' ? 'rgba(16,185,129,0.1)' : ($r['status']=='Absent' ? 'rgba(239,68,68,0.1)' : 'rgba(245,158,11,0.1)') ?>; color:<?= $r['status']=='Present' ? '#047857' : ($r['status']=='Absent' ? '#b91c1c' : '#b45309') ?>;">
                                    <?= htmlspecialchars($r['status']) ?>
                                </span>
                            </td>
                            <td data-label="Username" style="padding: 15px; font-weight: 500; color: #0f172a;">
                                <?= htmlspecialchars($r['full_name']) ?>
                            </td>
                            <td data-label="Phone Number" style="padding: 15px; color: #64748b;">
                                <?= htmlspecialchars($r['phone']) ?>
                            </td>
                            <td data-label="Unique ID" style="padding: 15px; font-weight: 600; color: #3b82f6;">
                                <?= htmlspecialchars($r['unique_user_id']) ?>
                            </td>
                            <td data-label="Login Time" style="padding: 15px; color: #475569;">
                                <div style="font-weight: 500;"><?= date('h:i A', strtotime($r['check_in_time'])) ?></div>
                                <div style="font-size: 0.85rem; color: #94a3b8;"><?= date('d M Y', strtotime($r['attendance_date'])) ?></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color: #94a3b8;">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
