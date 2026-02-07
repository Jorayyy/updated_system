# MEBS Hiyas HR & Payroll System

A comprehensive HR and Payroll management system designed for BPO and call center environments, featuring multi-site support, account-specific scheduling, and automated DTR processing.

## 🚀 Quick Start (Any Windows PC)

If you have just downloaded the system or restored it from a full backup, you can set up the entire environment automatically:

1.  Ensure you have **XAMPP** (or PHP 8.1+ and MySQL), **Node.js**, and **Composer** installed.
2.  Open PowerShell in the project directory.
3.  Run the setup script:
    ```powershell
    ./setup-environment.ps1
    ```
4.  Follow the prompts to install dependencies, generate keys, and initialize the database.

## 📦 Backup & Recovery

The system includes a built-in Backup Manager:
- **Database Backups**: `.sql` snapshots of your data.
- **Full System Backups**: `.zip` files containing both the **Database** and the **Source Code**.
  - *Note: Huge folders like `vendor` and `node_modules` are excluded from the zip to save space. They are automatically re-created when you run the setup script.*

## 🛠 Key Features

- **Multi-Site Management**: Categorize employees by physical site (e.g., Tacloban, Cebu).
- **Account-Based Scheduling**: Different campaigns (Accounts) can have their own unique work hours.
- **Night Shift Logic**: Native support for shifts spanning across midnight (e.g., 9:00 PM - 6:00 AM) with accurate logical dating.
- **Automated DTR**: Calculates lates, undertimes, and overtime based on the account's assigned schedule.
- **Bulk Operations**: Move large groups of employees between sites or accounts with one click.
- **Ticketing System**: Internal concern and ticket management with tracking.

## 💻 Tech Stack

- **Backend**: Laravel 10/11
- **Frontend**: Blade, Alpine.js, Tailwind CSS
- **Database**: MySQL / MariaDB

## 👥 Default Credentials

- **Admin**: admin@mebs.com / password
- **HR**: hr@mebs.com / password
