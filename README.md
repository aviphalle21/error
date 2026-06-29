# Saraswati Abhyasika - Library Management System

A professional, deployable SaaS platform for managing physical library spaces, table reservations, subscriptions, and memberships.

## Overview
This software provides a robust backend to handle real-world operations for a library/study center. It includes table locks, transaction-safe booking flows, dynamic subscription plans, automatic pending-payment cancellation, and email notifications (Brevo/SMTP).

## Key Features
- **Interactive Table Map**: Visual representation of booked, available, and reserved tables.
- **Strict Concurrency**: Prevents double-booking through `SELECT ... FOR UPDATE` row locks.
- **Dynamic Configuration**: Setup wizard for database and email configuration without touching PHP files.
- **Secure Architecture**: CSRF protection, password hashing, and session strictness.
- **Multi-Role**: Dedicated Admin Dashboard and User Portal.

## Installation
For detailed installation instructions, please refer to [INSTALLATION.md](INSTALLATION.md).

1. Upload the files to your web server.
2. Navigate to `http://your-domain.com/install` in your browser.
3. Follow the Setup Wizard.

## Support
For issues or setup help, please refer to the `ADMIN_GUIDE.md`.
