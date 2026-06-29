SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `notification_queue`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `verification_tokens`;
DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `user_login_logs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `user_subscriptions`;
DROP TABLE IF EXISTS `library_tables`;
DROP TABLE IF EXISTS `subscription_plans`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `system_notifications`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` (`name`, `email`, `username`, `password`, `created_at`) VALUES
('Super Admin', 'admin@gmail.com', 'admin', '$2y$10$NpYF/55ZUy7dRkz4sTQI8e4sFuzULvsfS2o5GPAyXnE1To8Lvdv6S', NOW());

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_user_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_status` enum('Active','Inactive','Suspended') DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `reset_otp` varchar(255) DEFAULT NULL,
  `reset_otp_expires` datetime DEFAULT NULL,
  `registration_ip` varchar(45) DEFAULT NULL,
  `registration_device` varchar(50) DEFAULT NULL,
  `registration_browser` varchar(100) DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `reset_otp_requested_at` datetime DEFAULT NULL,
  `otp_resend_count` int(11) DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_user_id` (`unique_user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('library_name', 'Saraswati Abhyasika'),
('library_logo', 'assets/logo.png'),
('support_email', 'support@saraswatiabhyasika.com'),
('contact_number', '+91 0000000000'),
('timezone', 'Asia/Kolkata'),
('currency', 'INR'),
('email_provider', 'Brevo'),
('brevo_api_key', ''),
('smtp_host', ''),
('smtp_port', ''),
('smtp_user', ''),
('smtp_pass', ''),
('smtp_encryption', 'tls');

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `result` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `audit_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_admin_fk` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('Payment','Booking','Expiry','General') DEFAULT 'General',
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `system_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `subscription_plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `subscription_plans` (`plan_name`, `duration_days`, `price`, `active`) VALUES
('Monthly', 30, 1000.00, 1),
('Quarterly', 90, 2700.00, 1);

CREATE TABLE `library_tables` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_table_id` varchar(50) NOT NULL,
  `table_number` int(11) NOT NULL,
  `status` enum('Available','Reserved','Booked','Maintenance') DEFAULT 'Available',
  `current_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `unique_table_id` (`unique_table_id`),
  UNIQUE KEY `table_number` (`table_number`),
  KEY `current_user_id` (`current_user_id`),
  CONSTRAINT `library_tables_user_fk` FOREIGN KEY (`current_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `library_tables` (`unique_table_id`, `table_number`, `status`, `current_user_id`) VALUES
('T1', 1, 'Available', NULL),('T2', 2, 'Available', NULL),('T3', 3, 'Available', NULL),('T4', 4, 'Available', NULL),('T5', 5, 'Available', NULL),('T6', 6, 'Available', NULL),('T7', 7, 'Available', NULL),('T8', 8, 'Available', NULL),('T9', 9, 'Available', NULL),('T10', 10, 'Available', NULL),('T11', 11, 'Available', NULL),('T12', 12, 'Available', NULL),('T13', 13, 'Available', NULL),('T14', 14, 'Available', NULL),('T15', 15, 'Available', NULL),('T16', 16, 'Available', NULL),('T17', 17, 'Available', NULL),('T18', 18, 'Available', NULL),('T19', 19, 'Available', NULL),('T20', 20, 'Available', NULL),('T21', 21, 'Available', NULL),('T22', 22, 'Available', NULL),('T23', 23, 'Available', NULL),('T24', 24, 'Available', NULL),('T25', 25, 'Available', NULL),('T26', 26, 'Available', NULL),('T27', 27, 'Available', NULL),('T28', 28, 'Available', NULL),('T29', 29, 'Available', NULL),('T30', 30, 'Available', NULL),('T31', 31, 'Available', NULL),('T32', 32, 'Available', NULL),('T33', 33, 'Available', NULL),('T34', 34, 'Available', NULL),('T35', 35, 'Available', NULL),('T36', 36, 'Available', NULL),('T37', 37, 'Available', NULL),('T38', 38, 'Available', NULL),('T39', 39, 'Available', NULL);

CREATE TABLE `user_subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_status` enum('PENDING_PAYMENT','PAYMENT_VERIFIED','CONFIRMED','ACTIVE','COMPLETED','CANCELLED','EXPIRED','REFUNDED','FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT',
  `subscription_status` enum('PENDING_PAYMENT','PAYMENT_VERIFIED','CONFIRMED','ACTIVE','COMPLETED','CANCELLED','EXPIRED','REFUNDED','FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT',
  PRIMARY KEY (`subscription_id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `user_subscriptions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_table_fk` FOREIGN KEY (`table_id`) REFERENCES `library_tables` (`table_id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_plan_fk` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`plan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `booking_status` enum('PENDING_PAYMENT','PAYMENT_VERIFIED','CONFIRMED','ACTIVE','COMPLETED','CANCELLED','EXPIRED','REFUNDED','FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT',
  `booking_price` decimal(10,2) DEFAULT NULL,
  `plan_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `bookings_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_table_fk` FOREIGN KEY (`table_id`) REFERENCES `library_tables` (`table_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_reference` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('PENDING_PAYMENT','PAYMENT_VERIFIED','CONFIRMED','ACTIVE','COMPLETED','CANCELLED','EXPIRED','REFUNDED','FAILED') NOT NULL DEFAULT 'PENDING_PAYMENT',
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `user_id` (`user_id`),
  KEY `subscription_id` (`subscription_id`),
  CONSTRAINT `payments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_subscription_fk` FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`subscription_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_login_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `status` enum('Success','Failed','Locked') DEFAULT 'Success',
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_login_logs_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time NOT NULL,
  `status` enum('Present','Absent','Late') DEFAULT 'Present',
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `user_date_unique` (`user_id`, `attendance_date`),
  CONSTRAINT `attendance_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `verification_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `verification_tokens_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  CONSTRAINT `password_reset_tokens_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notification_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `payload` json NOT NULL,
  `status` enum('pending','processed','failed') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
