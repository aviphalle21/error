<?php
$pdo = new PDO('mysql:host=localhost;dbname=Library', 'root', '');
$hash = password_hash('admin1234', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE admin SET email = 'admin@gmail.com', password = ? WHERE email = 'admin@gmail.com' OR username = 'admin'");
$stmt->execute([$hash]);

if ($stmt->rowCount() == 0) {
    // Insert if not exists
    $ins = $pdo->prepare("INSERT INTO admin (name, email, username, password) VALUES ('Admin', 'admin@gmail.com', 'admin', ?)");
    $ins->execute([$hash]);
    echo "Admin created.\n";
} else {
    echo "Admin password updated.\n";
}
// Also create attendance table here since previous mysql command failed
$pdo->exec("CREATE TABLE IF NOT EXISTS `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time NOT NULL,
  `status` enum('Present','Absent','Late') DEFAULT 'Present',
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `user_date_unique` (`user_id`, `attendance_date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
echo "Attendance table ensured.\n";
?>
