SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` (`name`, `email`, `username`, `password`, `created_at`) VALUES ('Super Admin', 'admin@gmail.com', 'admin', '$2y$10$NpYF/55ZUy7dRkz4sTQI8e4sFuzULvsfS2o5GPAyXnE1To8Lvdv6S', NOW());

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time NOT NULL,
  `status` enum('Present','Absent','Late') DEFAULT 'Present',
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `user_date_unique` (`user_id`, `attendance_date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `result` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `booking_status` enum('Pending','Active','Expired','Cancelled','Maintenance') DEFAULT 'Pending',
  `booking_price` decimal(10,2) DEFAULT NULL,
  `plan_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `library_tables` (`table_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `library_tables`;
CREATE TABLE `library_tables` (
  `table_id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_table_id` varchar(50) NOT NULL,
  `table_number` int(11) NOT NULL,
  `status` enum('Available','Booked','Maintenance') DEFAULT 'Available',
  `current_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`table_id`),
  UNIQUE KEY `unique_table_id` (`unique_table_id`),
  UNIQUE KEY `table_number` (`table_number`),
  KEY `current_user_id` (`current_user_id`),
  CONSTRAINT `library_tables_ibfk_1` FOREIGN KEY (`current_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `notifications`;
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

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_reference` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `user_id` (`user_id`),
  KEY `subscription_id` (`subscription_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`subscription_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE `subscription_plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `system_notifications`;
CREATE TABLE `system_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_login_logs`;
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
  CONSTRAINT `user_login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE `user_subscriptions` (
  `subscription_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed','Refunded') DEFAULT 'Pending',
  `subscription_status` enum('Active','Expired','Cancelled') DEFAULT 'Active',
  PRIMARY KEY (`subscription_id`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `library_tables` (`table_id`) ON DELETE CASCADE,
  CONSTRAINT `user_subscriptions_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `users`;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS=1;
