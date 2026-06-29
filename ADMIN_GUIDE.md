# Administrator Guide

Welcome to the Saraswati Abhyasika Administration Panel. This guide outlines how to manage your library.

## Logging In
Access the admin portal via `http://your-domain.com/pratik2002/`.
Use the credentials you created during the Setup Wizard.

## Dashboard
The dashboard provides a real-time overview of:
- Total registered members
- Available and Booked tables
- Total and Monthly Revenue
- Recent transactions and bookings

## Managing System Settings
Instead of manually editing code, you can customize your library via **System Settings** in the Admin panel.
1. Click on **Settings** in the sidebar.
2. Edit your **Library Name**, **Website URL**, **Support Email**, and **Timezone**.
3. Manage your **Email Integration** (Brevo API or SMTP) easily. Changes apply immediately.
4. Customize the **Theme** of your admin dashboard using pre-built government-style themes or a custom color picker.

## Table Management
- The system automatically handles table availability. 
- A table is locked temporarily while a user attempts to pay. If they abandon checkout, the system releases the table automatically after 5 minutes.
- You can manually override a table's status in the Tables section if a table needs maintenance.

## Security & Maintenance
- Do **not** delete the `.installed` file in the root directory. If this file is removed, the installation wizard will unlock, and someone could overwrite your database.
- Database backups should be taken regularly via phpMyAdmin or your hosting provider's tools.
