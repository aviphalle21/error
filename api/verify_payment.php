<?php
require_once __DIR__ . '/../USER/config.php';
require_once __DIR__ . '/../includes/Security.php';
require_once __DIR__ . '/../services/EmailService.php';

$booking_ref = $_GET['booking_ref'] ?? '';

if (empty($booking_ref)) {
    die("Invalid request. Missing booking reference.");
}

// Fetch booking data
$stmt = $pdo->prepare("SELECT b.booking_id, b.table_id, b.user_id, b.booking_status, b.plan_price, u.full_name, u.email, u.phone, t.table_number 
                       FROM bookings b 
                       JOIN users u ON b.user_id = u.user_id 
                       JOIN library_tables t ON b.table_id = t.table_id 
                       WHERE b.booking_reference = ?");
$stmt->execute([$booking_ref]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}

if ($booking['booking_status'] !== 'PENDING_PAYMENT') {
    die("This booking is no longer pending payment. Current status: " . htmlspecialchars($booking['booking_status']));
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !Security::validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid request. CSRF token failed.';
    } else {
        $action = $_POST['action'] ?? '';
        
        try {
            $pdo->beginTransaction();
            
            if ($action === 'pay') {
                // Confirm Payment & Booking
                $updBook = $pdo->prepare("UPDATE bookings SET booking_status = 'CONFIRMED' WHERE booking_id = ?");
                $updBook->execute([$booking['booking_id']]);
                
                $updTable = $pdo->prepare("UPDATE library_tables SET status = 'Booked' WHERE table_id = ?");
                $updTable->execute([$booking['table_id']]);
                
                // We need the subscription ID and payment ID connected to this booking.
                // Assuming 1 active pending sub/payment per user for simplicity (matching payment.php logic).
                $updSub = $pdo->prepare("UPDATE user_subscriptions SET subscription_status = 'ACTIVE', payment_status = 'PAYMENT_VERIFIED' WHERE user_id = ? AND subscription_status = 'PENDING_PAYMENT'");
                $updSub->execute([$booking['user_id']]);
                
                $updPay = $pdo->prepare("UPDATE payments SET payment_status = 'PAYMENT_VERIFIED' WHERE user_id = ? AND payment_status = 'PENDING_PAYMENT'");
                $updPay->execute([$booking['user_id']]);
                
                $pdo->commit();
                
                // Send Emails & Admin Notifications (Post Commit)
                $bookingData = [
                    'id' => $booking_ref,
                    'table_number' => 'T-' . $booking['table_number'],
                    'date' => date('Y-m-d'),
                    'start_time' => date('H:i'),
                    'end_time' => 'Till Expiry'
                ];
                $paymentData = [
                    'transaction_id' => 'TXN-' . mt_rand(100000, 999999),
                    'amount' => 'Rs ' . $booking['plan_price'],
                    'plan_name' => 'Library Subscription',
                    'payment_date' => date('Y-m-d H:i:s')
                ];
                
                EmailService::sendBookingConfirmation($pdo, $booking['email'], $booking['full_name'], $bookingData);
                EmailService::sendPaymentReceipt($pdo, $booking['email'], $booking['full_name'], $paymentData);
                EmailService::sendAdminNotification($pdo, 'New Booking Confirmed', $booking['full_name'] . ' paid and confirmed Table T-' . $booking['table_number'] . ' (Ref: ' . $booking_ref . ').');
                
                $success = true;
                
            } elseif ($action === 'cancel') {
                // Cancel booking
                $updBook = $pdo->prepare("UPDATE bookings SET booking_status = 'CANCELLED' WHERE booking_id = ?");
                $updBook->execute([$booking['booking_id']]);
                
                $updTable = $pdo->prepare("UPDATE library_tables SET status = 'Available', current_user_id = NULL WHERE table_id = ?");
                $updTable->execute([$booking['table_id']]);
                
                $updSub = $pdo->prepare("UPDATE user_subscriptions SET subscription_status = 'CANCELLED', payment_status = 'FAILED' WHERE user_id = ? AND subscription_status = 'PENDING_PAYMENT'");
                $updSub->execute([$booking['user_id']]);
                
                $updPay = $pdo->prepare("UPDATE payments SET payment_status = 'FAILED' WHERE user_id = ? AND payment_status = 'PENDING_PAYMENT'");
                $updPay->execute([$booking['user_id']]);
                
                $pdo->commit();
                header("Location: ../USER/dashboard.php?msg=cancelled");
                exit;
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment Gateway (Mock)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .gateway-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
        h2 { color: #1f2937; margin-bottom: 20px; }
        .amount { font-size: 32px; font-weight: bold; color: #10b981; margin: 20px 0; }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: 600; width: 100%; margin-bottom: 15px; }
        .btn-pay { background: #4f46e5; color: white; }
        .btn-pay:hover { background: #4338ca; }
        .btn-cancel { background: #ef4444; color: white; }
        .btn-cancel:hover { background: #dc2626; }
        .btn-home { background: #10b981; color: white; text-decoration: none; display: inline-block; box-sizing: border-box; }
        .alert-error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .security-badge { font-size: 12px; color: #6b7280; display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="gateway-box">
        <?php if ($success): ?>
            <h2 style="color: #10b981;">Payment Successful!</h2>
            <p>Your booking for Table T-<?= htmlspecialchars($booking['table_number']) ?> has been confirmed.</p>
            <p>Receipt sent to <?= htmlspecialchars($booking['email']) ?></p>
            <a href="../USER/dashboard.php" class="btn btn-home">Return to Dashboard</a>
        <?php else: ?>
            <h2>Secure Checkout</h2>
            <p>Booking Reference: <?= htmlspecialchars($booking_ref) ?></p>
            <div class="amount">₹<?= htmlspecialchars($booking['plan_price']) ?></div>
            
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                <button type="submit" name="action" value="pay" class="btn btn-pay">Simulate Successful Payment</button>
                <button type="submit" name="action" value="cancel" class="btn btn-cancel" formnovalidate>Cancel Payment</button>
            </form>
            
            <div class="security-badge">
                🔒 256-bit Encrypted Mock Gateway
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
