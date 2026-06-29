-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: Library
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (1,'Suhas Vibhute','admin@gmail.com','admin','$2y$10$XQ1VHiBwlqAb4z3vY2h4ceQWjB6iBhRD6ItrKwJPW/iuQxcvjTWBS','2026-06-26 14:53:16');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `start_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `booking_status` enum('Pending','Active','Expired','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`booking_id`),
  UNIQUE KEY `booking_reference` (`booking_reference`),
  KEY `user_id` (`user_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`table_id`) REFERENCES `library_tables` (`table_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `library_tables`
--

DROP TABLE IF EXISTS `library_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `library_tables`
--

LOCK TABLES `library_tables` WRITE;
/*!40000 ALTER TABLE `library_tables` DISABLE KEYS */;
INSERT INTO `library_tables` VALUES (1,'TBL-001',1,'Available',NULL,'2026-06-26 15:15:19'),(2,'TBL-002',2,'Available',NULL,'2026-06-26 15:15:19'),(3,'TBL-003',3,'Available',NULL,'2026-06-26 15:15:19'),(4,'TBL-004',4,'Available',NULL,'2026-06-26 15:15:19'),(5,'TBL-005',5,'Available',NULL,'2026-06-26 15:15:19'),(6,'TBL-006',6,'Available',NULL,'2026-06-26 15:15:19'),(7,'TBL-007',7,'Available',NULL,'2026-06-26 15:15:19'),(8,'TBL-008',8,'Available',NULL,'2026-06-26 15:15:19'),(9,'TBL-009',9,'Available',NULL,'2026-06-26 15:15:19'),(10,'TBL-010',10,'Available',NULL,'2026-06-26 15:15:19'),(11,'TBL-011',11,'Available',NULL,'2026-06-26 15:15:19'),(12,'TBL-012',12,'Available',NULL,'2026-06-26 15:15:19'),(13,'TBL-013',13,'Available',NULL,'2026-06-26 15:15:19'),(14,'TBL-014',14,'Available',NULL,'2026-06-26 15:15:19'),(15,'TBL-015',15,'Available',NULL,'2026-06-26 15:15:19'),(16,'TBL-016',16,'Available',NULL,'2026-06-26 15:15:19'),(17,'TBL-017',17,'Available',NULL,'2026-06-26 15:15:19'),(18,'TBL-018',18,'Available',NULL,'2026-06-26 15:15:19'),(19,'TBL-019',19,'Available',NULL,'2026-06-26 15:15:19'),(20,'TBL-020',20,'Available',NULL,'2026-06-26 15:15:19'),(21,'TBL-021',21,'Available',NULL,'2026-06-26 15:15:19'),(22,'TBL-022',22,'Available',NULL,'2026-06-26 15:15:19'),(23,'TBL-023',23,'Available',NULL,'2026-06-26 15:15:19'),(24,'TBL-024',24,'Available',NULL,'2026-06-26 15:15:19'),(25,'TBL-025',25,'Available',NULL,'2026-06-26 15:15:19'),(26,'TBL-026',26,'Available',NULL,'2026-06-26 15:15:19'),(27,'TBL-027',27,'Available',NULL,'2026-06-26 15:15:19'),(28,'TBL-028',28,'Available',NULL,'2026-06-26 15:15:19'),(29,'TBL-029',29,'Available',NULL,'2026-06-26 15:15:19'),(30,'TBL-030',30,'Available',NULL,'2026-06-26 15:15:19'),(31,'TBL-031',31,'Available',NULL,'2026-06-26 15:15:19'),(32,'TBL-032',32,'Available',NULL,'2026-06-26 15:15:19'),(33,'TBL-033',33,'Available',NULL,'2026-06-26 15:15:19'),(34,'TBL-034',34,'Available',NULL,'2026-06-26 15:15:19'),(35,'TBL-035',35,'Available',NULL,'2026-06-26 15:15:19'),(36,'TBL-036',36,'Available',NULL,'2026-06-26 15:15:19'),(37,'TBL-037',37,'Available',NULL,'2026-06-26 15:15:19'),(38,'TBL-038',38,'Available',NULL,'2026-06-26 15:15:19'),(41,'TBL-039',39,'Available',NULL,'2026-06-27 06:53:40');
/*!40000 ALTER TABLE `library_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otifications`
--

DROP TABLE IF EXISTS `otifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `itle` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ype` enum('Payment','Booking','Expiry','General') DEFAULT 'General',
  `is_read` tinyint(1) DEFAULT 0,
  `elated_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otifications`
--

LOCK TABLES `otifications` WRITE;
/*!40000 ALTER TABLE `otifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `otifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plans` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(100) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`plan_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'1 Month',30,1.00,1),(2,'3 Months',90,2100.00,1);
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_notifications`
--

DROP TABLE IF EXISTS `system_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_notifications`
--

LOCK TABLES `system_notifications` WRITE;
/*!40000 ALTER TABLE `system_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_login_logs`
--

DROP TABLE IF EXISTS `user_login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_login_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_login_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_login_logs`
--

LOCK TABLES `user_login_logs` WRITE;
/*!40000 ALTER TABLE `user_login_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_login_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_subscriptions`
--

DROP TABLE IF EXISTS `user_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_subscriptions`
--

LOCK TABLES `user_subscriptions` WRITE;
/*!40000 ALTER TABLE `user_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_user_id` (`unique_user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'LIB-1506','Varun Athawale','athawalevarun3@gmail.com','1234567890',NULL,'$2y$10$h9LXlQvv4dZggjAH2e0GNOywgMuIiUhe73RpeDX4YsNU1pk1tpoha','2026-06-27 08:04:54','Active',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-27 15:09:59
