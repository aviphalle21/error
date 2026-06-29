-- Drop the old OTP columns from users table if they exist
ALTER TABLE users 
DROP COLUMN IF EXISTS reset_otp, 
DROP COLUMN IF EXISTS reset_otp_expires, 
DROP COLUMN IF EXISTS reset_otp_requested_at, 
DROP COLUMN IF EXISTS otp_resend_count;

-- Create the new password_reset_tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(255) NOT NULL, -- Storing the hashed OTP
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    verified TINYINT(1) NOT NULL DEFAULT 0,
    attempts INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
