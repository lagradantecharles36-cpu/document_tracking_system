-- ============================================================
-- Document Tracking System - MySQL Database Dump (database.sql)
-- Course: PHP and MySQL Development (Web Systems Technologies)
-- ============================================================
-- Local XAMPP / InfinityFree phpMyAdmin Import Instructions:
-- 1. Select your database in phpMyAdmin (e.g., `document_tracking_db` locally
--    or your `if0_XXXXXXX_dbname` database on InfinityFree).
-- 2. Go to the "Import" tab, choose this `database.sql` file, and click Import.
-- ============================================================

CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tracking_number` VARCHAR(30) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `doc_type` VARCHAR(60) NOT NULL,
  `sender_name` VARCHAR(100) NOT NULL,
  `origin_dept` VARCHAR(100) NOT NULL,
  `current_office` VARCHAR(100) NOT NULL,
  `priority` ENUM('Low', 'Normal', 'High', 'Urgent') NOT NULL DEFAULT 'Normal',
  `status` ENUM('Pending', 'In Transit', 'Under Review', 'Approved', 'Completed', 'Returned') NOT NULL DEFAULT 'Pending',
  `date_received` DATE NOT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tracking_number` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data for Testing CRUD Operations
INSERT INTO `documents` (
  `tracking_number`, `title`, `doc_type`, `sender_name`, `origin_dept`,
  `current_office`, `priority`, `status`, `date_received`, `remarks`
) VALUES
('DTS-2026-1001', 'Annual IT Equipment Procurement Request', 'Purchase Request', 'Engr. Marco Villanueva', 'IT Department', 'Budget & Finance Office', 'High', 'Under Review', '2026-11-10', 'Awaiting budget clearance signature from Finance Director.'),
('DTS-2026-1002', 'Memorandum of Agreement - Industry Internship', 'Legal Contract', 'Dr. Elena Soriano', 'Academic Affairs', 'Legal Office', 'Normal', 'In Transit', '2026-11-12', 'Forwarded for legal review and notarization.'),
('DTS-2026-1003', 'Q4 Facility Maintenance & Safety Audit Report', 'Audit Report', 'Arch. Dennis Ramos', 'General Services', 'Office of the President', 'Urgent', 'Approved', '2026-11-14', 'Approved for immediate implementation of electrical upgrades.'),
('DTS-2026-1004', 'Faculty Travel Authority - National Conference', 'Travel Order', 'Prof. Clarissa Mendoza', 'College of Computing', 'Human Resources', 'Normal', 'Completed', '2026-11-08', 'Signed copy released to the requesting faculty member.'),
('DTS-2026-1005', 'Revised Student Handbook Policy Addendum', 'Policy Memo', 'Dean Roberto Cruz', 'Student Affairs Office', 'Records Section', 'Low', 'Pending', '2026-11-15', 'Received at front desk; queued for routing to Academic Council.');
