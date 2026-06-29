SET FOREIGN_KEY_CHECKS=0;

-- 1. Wipe all User and Transactional Data
TRUNCATE TABLE `attendance`;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `bookings`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `system_notifications`;
TRUNCATE TABLE `user_login_logs`;
TRUNCATE TABLE `user_subscriptions`;
TRUNCATE TABLE `users`;

-- 2. Wipe Library Tables Data
TRUNCATE TABLE `library_tables`;

-- 3. Re-seed exactly tables T1 through T39 as requested
INSERT INTO `library_tables` (`unique_table_id`, `table_number`, `status`, `current_user_id`) VALUES
('T1', 1, 'Available', NULL),
('T2', 2, 'Available', NULL),
('T3', 3, 'Available', NULL),
('T4', 4, 'Available', NULL),
('T5', 5, 'Available', NULL),
('T6', 6, 'Available', NULL),
('T7', 7, 'Available', NULL),
('T8', 8, 'Available', NULL),
('T9', 9, 'Available', NULL),
('T10', 10, 'Available', NULL),
('T11', 11, 'Available', NULL),
('T12', 12, 'Available', NULL),
('T13', 13, 'Available', NULL),
('T14', 14, 'Available', NULL),
('T15', 15, 'Available', NULL),
('T16', 16, 'Available', NULL),
('T17', 17, 'Available', NULL),
('T18', 18, 'Available', NULL),
('T19', 19, 'Available', NULL),
('T20', 20, 'Available', NULL),
('T21', 21, 'Available', NULL),
('T22', 22, 'Available', NULL),
('T23', 23, 'Available', NULL),
('T24', 24, 'Available', NULL),
('T25', 25, 'Available', NULL),
('T26', 26, 'Available', NULL),
('T27', 27, 'Available', NULL),
('T28', 28, 'Available', NULL),
('T29', 29, 'Available', NULL),
('T30', 30, 'Available', NULL),
('T31', 31, 'Available', NULL),
('T32', 32, 'Available', NULL),
('T33', 33, 'Available', NULL),
('T34', 34, 'Available', NULL),
('T35', 35, 'Available', NULL),
('T36', 36, 'Available', NULL),
('T37', 37, 'Available', NULL),
('T38', 38, 'Available', NULL),
('T39', 39, 'Available', NULL);

-- Admin credentials (table `admin`) and Subscription Plans (`subscription_plans`) are preserved by omission from truncation.

SET FOREIGN_KEY_CHECKS=1;
