# DocuTrack — Document Tracking System

A responsive web-based CRUD application built with **PHP**, **MySQL**, **HTML5**, **CSS3**, and **Bootstrap 5** for the *Web Systems Technologies (PHP and MySQL Development)* Final Project.

---

## Project Structure & Requirements Checklist

### A. Front-End (Bootstrap 5 + HTML/CSS)
- Responsive Navigation Bar, KPI Summary Cards, Search/Filter Controls, Data Table, Modals, and Forms (`index.php`, `create.php`, `edit.php`, `style.css`)

### B. Back-End (PHP + MySQL CRUD Operations)
- **INSERT** (`create.php`): Logs a new document record with an auto-generated tracking code (`DTS-YYYY-XXXX`).
- **SELECT** (`index.php`): Views all documents, calculates live KPI counts, and filters by keyword, routing status, or priority.
- **UPDATE** (`edit.php`): Updates document routing location, status, priority, and remarks.
- **DELETE** (`delete.php`): Removes a document record after modal confirmation.

### C. Database Design (`database.sql`)
- Table: `documents` (13 fields including `id` `PRIMARY KEY AUTO_INCREMENT` and `tracking_number` `UNIQUE KEY`, with proper `VARCHAR`, `ENUM`, `DATE`, `TEXT`, and `TIMESTAMP` data types).

---

## How to Run Locally on XAMPP

1. Copy this project folder into `C:\xampp\htdocs\document-tracking-system`.
2. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Open your browser and visit:
   `http://localhost/document-tracking-system/`
   *(Note: `db_connect.php` automatically creates `document_tracking_db` and the `documents` table on localhost! You can click **"Load 5 Sample Documents"** right on the dashboard, or import `database.sql` in `http://localhost/phpmyadmin`.)*

---

## How to Deploy on InfinityFree

1. Log in to [InfinityFree](https://www.infinityfree.com/) and create a Hosting Account.
2. Go to **Control Panel → MySQL Databases** and create a new database.
3. Open `db_connect.php` and replace the 4 variables with your InfinityFree credentials:
   ```php
   $db_host = "sqlXXX.infinityfree.com";
   $db_user = "if0_XXXXXXXX";
   $db_pass = "your_vpanel_password";
   $db_name = "if0_XXXXXXXX_document_db";
   ```
4. Open **phpMyAdmin** on InfinityFree for your new database and import `database.sql` (Note: on InfinityFree phpMyAdmin, if it blocks `CREATE DATABASE`, simply run from the `CREATE TABLE IF NOT EXISTS documents ...` line onward).
5. Upload all project files (`db_connect.php`, `index.php`, `create.php`, `edit.php`, `delete.php`, `style.css`) into the `htdocs/` folder using InfinityFree's **File Manager**.
