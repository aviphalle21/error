<?php
require_once __DIR__ . '/../USER/config.php';
require_once __DIR__ . '/../includes/Logger.php';

try {
    $pdo->beginTransaction();
    
    // Find all pending bookings older than 10 minutes
    $stmt = $pdo->query("SELECT booking_id, table_id, user_id, booking_reference 
                         FROM bookings 
                         WHERE booking_status = 'PENDING_PAYMENT' 
                         AND booking_date < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
    $expiredBookings = $stmt->fetchAll();
    
    $cancelledCount = 0;
    
    foreach ($expiredBookings as $b) {
        // Cancel Booking
        $updBook = $pdo->prepare("UPDATE bookings SET booking_status = 'CANCELLED' WHERE booking_id = ?");
        $updBook->execute([$b['booking_id']]);
        
        // Release Table
        $updTable = $pdo->prepare("UPDATE library_tables SET status = 'Available', current_user_id = NULL WHERE table_id = ?");
        $updTable->execute([$b['table_id']]);
        
        // Cancel Subscription
        $updSub = $pdo->prepare("UPDATE user_subscriptions SET subscription_status = 'CANCELLED', payment_status = 'FAILED' WHERE user_id = ? AND subscription_status = 'PENDING_PAYMENT'");
        $updSub->execute([$b['user_id']]);
        
        // Fail Payment
        $updPay = $pdo->prepare("UPDATE payments SET payment_status = 'FAILED' WHERE user_id = ? AND payment_status = 'PENDING_PAYMENT'");
        $updPay->execute([$b['user_id']]);
        
        Logger::logAudit($pdo, 'System Cleanup', "Cancelled expired pending booking: " . $b['booking_reference'], $b['user_id'], null);
        $cancelledCount++;
    }
    
    $pdo->commit();
    
    echo json_encode(['status' => 'success', 'cancelled_count' => $cancelledCount]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
