-- ==============================================================================
-- SRM HRMS: Database Model & DDL Schema
-- System Administrator: Sabiha Rumman Medha (Head of HR & Admin)
-- Database Engine: MySQL 5.7+ / 8.0+ / MariaDB
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `srm_hrms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `srm_hrms`;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS `Attendance`;
DROP TABLE IF EXISTS `Salary`;
DROP TABLE IF EXISTS `Employee`;
DROP TABLE IF EXISTS `Department`;
DROP TABLE IF EXISTS `SystemInfo`;

-- 1. Department Table (Matching PDF Schema)
CREATE TABLE `Department` (
  `department_id` INT AUTO_INCREMENT PRIMARY KEY,
  `department_name` VARCHAR(100) NOT NULL,
  `department_code` VARCHAR(20) DEFAULT NULL,
  `office_location` VARCHAR(150) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Employee Table (Matching PDF Schema with Designation, Phone & Former Status)
CREATE TABLE `Employee` (
  `employee_id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_code` VARCHAR(20) UNIQUE NOT NULL,
  `employee_name` VARCHAR(100) NOT NULL,
  `department_id` INT NOT NULL,
  `designation` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `joining_date` DATE DEFAULT NULL,
  `status` ENUM('Active', 'Probation', 'On Leave', 'Former') DEFAULT 'Active',
  `separation_date` DATE DEFAULT NULL,
  `separation_reason` VARCHAR(150) DEFAULT NULL,
  `separation_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_dept` (`department_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`department_id`) REFERENCES `Department`(`department_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Salary Table (Matching PDF Schema)
CREATE TABLE `Salary` (
  `salary_id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `basic_salary` DECIMAL(10,2) NOT NULL,
  `bonus` DECIMAL(10,2) DEFAULT 0.00,
  `deduction_rate` DECIMAL(5,2) DEFAULT 5.00,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_emp_salary` (`employee_id`),
  FOREIGN KEY (`employee_id`) REFERENCES `Employee`(`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Attendance Table (Matching PDF Schema)
CREATE TABLE `Attendance` (
  `attendance_id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Present',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_emp_date` (`employee_id`, `attendance_date`),
  INDEX `idx_att_date` (`attendance_date`),
  FOREIGN KEY (`employee_id`) REFERENCES `Employee`(`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. System Settings & Announcement Table
CREATE TABLE `SystemInfo` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `company_name` VARCHAR(100) DEFAULT 'SRM HRMS',
  `admin_name` VARCHAR(100) DEFAULT 'Sabiha Rumman Medha',
  `admin_role` VARCHAR(100) DEFAULT 'Head of HR & Admin',
  `notice_text` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
