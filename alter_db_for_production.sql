-- We first need to safely coerce existing invalid data to a safe state so the ALTER TABLE works.
UPDATE bookings SET booking_status = 'ACTIVE' WHERE booking_status = 'Active';
UPDATE bookings SET booking_status = 'PENDING_PAYMENT' WHERE booking_status = 'Pending';
UPDATE bookings SET booking_status = 'CANCELLED' WHERE booking_status = 'Cancelled';
UPDATE bookings SET booking_status = 'EXPIRED' WHERE booking_status = 'Expired';

UPDATE user_subscriptions SET subscription_status = 'ACTIVE' WHERE subscription_status = 'Active';
UPDATE user_subscriptions SET subscription_status = 'CANCELLED' WHERE subscription_status = 'Cancelled';
UPDATE user_subscriptions SET subscription_status = 'EXPIRED' WHERE subscription_status = 'Expired';

UPDATE payments SET payment_status = 'PENDING_PAYMENT' WHERE payment_status = 'Pending';
UPDATE payments SET payment_status = 'PAYMENT_VERIFIED' WHERE payment_status = 'Paid';
UPDATE payments SET payment_status = 'FAILED' WHERE payment_status = 'Failed';
UPDATE payments SET payment_status = 'REFUNDED' WHERE payment_status = 'Refunded';

-- Now alter the tables
ALTER TABLE bookings MODIFY COLUMN booking_status ENUM('PENDING_PAYMENT', 'PAYMENT_VERIFIED', 'CONFIRMED', 'ACTIVE', 'COMPLETED', 'CANCELLED', 'EXPIRED', 'REFUNDED', 'FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT';

ALTER TABLE user_subscriptions MODIFY COLUMN subscription_status ENUM('PENDING_PAYMENT', 'PAYMENT_VERIFIED', 'CONFIRMED', 'ACTIVE', 'COMPLETED', 'CANCELLED', 'EXPIRED', 'REFUNDED', 'FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT';
ALTER TABLE user_subscriptions MODIFY COLUMN payment_status ENUM('PENDING_PAYMENT', 'PAYMENT_VERIFIED', 'CONFIRMED', 'ACTIVE', 'COMPLETED', 'CANCELLED', 'EXPIRED', 'REFUNDED', 'FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT';

ALTER TABLE payments MODIFY COLUMN payment_status ENUM('PENDING_PAYMENT', 'PAYMENT_VERIFIED', 'CONFIRMED', 'ACTIVE', 'COMPLETED', 'CANCELLED', 'EXPIRED', 'REFUNDED', 'FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT';

ALTER TABLE library_tables MODIFY COLUMN status ENUM('Available','Reserved','Booked','Maintenance') DEFAULT 'Available';
