# Installation Guide

This application is designed to be installed on any standard LAMP stack (Linux, Apache, MySQL, PHP).

## Prerequisites
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Extensions**: `pdo_mysql`, `curl`, `mbstring`

## Step-by-Step Installation

1. **Upload Files**
   Upload the entire project folder to your web server (e.g., `public_html` or `/var/www/html/library`).

2. **Run the Installer**
   Open your browser and navigate to the `/install` directory (e.g., `http://your-domain.com/install`).
   The Setup Wizard will guide you through the process.

3. **Step 1: System Check**
   The installer will automatically verify that your PHP version and folder permissions are correct. If any folders (like `config` or `database`) are unwritable, adjust their permissions (e.g., `chmod 755`) and reload.

4. **Step 2: Database Setup**
   Enter your MySQL credentials. The installer will automatically:
   - Test the connection.
   - Create the database.
   - Import the required tables from `database/schema.sql`.
   - Generate the hidden `.env` file containing your encrypted credentials.

5. **Step 3: App Configuration**
   Enter your Library's details, timezone, and currency. This populates the `system_settings` database table.

6. **Step 4: Administrator Account**
   Create the primary Super Admin account. Choose a secure password.

7. **Step 5: Email Setup**
   Configure your preferred email provider (Brevo API is highly recommended for deliverability, or standard SMTP). 
   This is required for OTPs, bookings, and receipts.

8. **Completion**
   Upon success, a `.installed` file is created in the root directory. This securely locks the installer to prevent malicious resets. You will be redirected to the Admin Dashboard to log in.

## Post-Installation
- **Cron Jobs**: To ensure abandoned carts (pending payments) are cleaned up automatically, set up a cron job on your server to hit the API every 5 minutes:
  ```bash
  */5 * * * * php /path/to/project/api/cleanup_pending.php
  ```
