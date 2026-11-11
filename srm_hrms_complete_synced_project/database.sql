-- SRM HRMS MySQL Database Schema & Initial Data
-- Platform: SRM HRMS (Admin: Sabiha Rumman Medha)
-- Compatible with MySQL 5.7+ / 8.0+ and MariaDB

CREATE DATABASE IF NOT EXISTS `srm_hrms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `srm_hrms`;

-- 1. Department Table (Matching PDF Schema)
DROP TABLE IF EXISTS `Attendance`;
DROP TABLE IF EXISTS `Salary`;
DROP TABLE IF EXISTS `Employee`;
DROP TABLE IF EXISTS `Department`;

CREATE TABLE `Department` (
  `department_id` INT AUTO_INCREMENT PRIMARY KEY,
  `department_name` VARCHAR(100) NOT NULL,
  `department_code` VARCHAR(20) DEFAULT NULL,
  `office_location` VARCHAR(150) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Employee Table (Matching PDF Schema with designation, phone & former status)
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

-- =========================================================
-- SEED DATA (Departments)
-- =========================================================
INSERT INTO `Department` (`department_id`, `department_name`, `department_code`, `office_location`) VALUES
(1, 'IT & Engineering', 'TECH', 'Tech Tower - 6th Floor'),
(2, 'Finance & Accounts', 'FIN', 'Block A - 3rd Floor'),
(3, 'Human Resources', 'HR', 'Block A - 4th Floor'),
(4, 'Marketing & Sales', 'MKT', 'Block A - 2nd Floor'),
(5, 'Operations & Logistics', 'OPS', 'Warehouse Hub - 1st Floor');

-- =========================================================
-- SEED DATA (Employees, Salaries, Attendance)
-- =========================================================
INSERT INTO `Employee` (`employee_id`, `employee_code`, `employee_name`, `department_id`, `designation`, `email`, `phone`, `joining_date`, `status`) VALUES
(101, '#101', 'Md. Rakibul Islam', 1, 'Senior Software Engineer', 'rakibul1992@gmail.com', '+880 1711-123456', '2025-01-15', 'Active'),
(102, '#102', 'Siam Hossain', 1, 'Full Stack Developer', 'dr.siamhossain.dev@gmail.com', '+880 1722-234567', '2025-02-01', 'Active'),
(103, '#103', 'Md. Radoun Hossin Mukra', 1, 'Database Administrator', 'radwonhossain22@gmail.com', '+880 1733-345678', '2025-02-15', 'Active'),
(104, '#104', 'Md. Shaharior Ahmed Karon', 2, 'Financial Analyst', 'shkaron001@gmail.com', '+880 1744-456789', '2025-03-01', 'Active'),
(105, '#105', 'Mst. Anamika Jahan', 3, 'HR Operations Lead', 'anamikajahan90@gmail.com', '+880 1755-567890', '2025-03-10', 'Active'),
(106, '#106', 'Tanvir Ahmed Shanto', 1, 'Lead Systems Architect', 'tanvir.sys@gmail.com', '+880 1766-678901', '2025-01-10', 'Active'),
(107, '#107', 'Mst. Ananna Khandaker', 4, 'Brand & Marketing Strategist', 'gibranumarani105@gmail.com', '+880 1777-789012', '2025-04-01', 'Active'),
(108, '#108', 'Md. Rakib Hasan Riyad', 2, 'Senior Accounts Officer', 'hasanriyad2015@gmail.com', '+880 1788-890123', '2025-04-15', 'Active'),
(109, '#109', 'Most. Khadija Khatun', 3, 'Talent Acquisition Specialist', 'dharanemarakiat307@gmail.com', '+880 1799-901234', '2025-05-01', 'Active'),
(110, '#110', 'Md. Rezwan Ahmed Ratul', 1, 'Frontend Engineer', '07rezwanahmedratul.tech@gmail.com', '+880 1811-123456', '2025-05-15', 'Active'),
(111, '#111', 'Marufina Abdullah Rifat', 4, 'Digital Content Executive', 'dharanemarakiatsulat@gmail.com', '+880 1822-234567', '2025-06-01', 'Active'),
(112, '#112', 'Md. Rabbi', 5, 'Supply Chain Coordinator', '07rabbi188@gmail.com', '+880 1833-345678', '2025-06-15', 'Active'),
(113, '#113', 'Mst. Mushkat Jahan Shila', 4, 'Public Relations Officer', 'mushkatshila@gmail.com', '+880 1844-456789', '2025-07-01', 'Active'),
(114, '#114', 'Saj Ahmad Rafi', 1, 'Network & Security Engineer', '174rafi@gmail.com', '+880 1855-567890', '2025-07-15', 'Active'),
(115, '#115', 'Md. Sabbir Hossain Rahat', 5, 'Logistics Operations Manager', 'sabbir190@gmail.com', '+880 1866-678901', '2025-08-01', 'Active'),
(116, '#116', 'Abid Hasan Hujayfa', 2, 'Tax & Compliance Executive', '03abidhassanhuzaifa@gmail.com', '+880 1877-789012', '2025-08-15', 'Active'),
(118, '#118', 'Md. Irtaj Hassan Nibir', 1, 'Quality Assurance Engineer', 'irtajnibir789@gmail.com', '+880 1899-901234', '2025-09-01', 'Active');

-- Former Employee Seed
INSERT INTO `Employee` (`employee_id`, `employee_code`, `employee_name`, `department_id`, `designation`, `email`, `phone`, `joining_date`, `status`, `separation_date`, `separation_reason`, `separation_notes`) VALUES
(98, '#098', 'Nayeem Hasan', 1, 'Senior Frontend Developer', 'nayeem.dev@gmail.com', '+880 1711-998877', '2024-05-01', 'Former', '2026-09-30', 'Voluntary Resignation', 'Accepted position abroad. Completed project handover and asset clearance.');

-- Salaries
INSERT INTO `Salary` (`employee_id`, `basic_salary`, `bonus`) VALUES
(101, 75000.00, 8000.00),
(102, 70000.00, 7000.00),
(103, 75000.00, 8000.00),
(104, 58000.00, 6000.00),
(105, 65000.00, 6000.00),
(106, 85000.00, 10000.00),
(107, 60000.00, 6000.00),
(108, 60000.00, 5500.00),
(109, 52000.00, 4500.00),
(110, 58000.00, 5000.00),
(111, 48000.00, 4000.00),
(112, 52000.00, 4500.00),
(113, 48000.00, 4000.00),
(114, 70000.00, 7000.00),
(115, 55000.00, 6000.00),
(116, 54000.00, 4500.00),
(118, 55000.00, 6000.00),
(98, 68000.00, 6000.00);

-- Attendance (for 2026-11-10)
INSERT INTO `Attendance` (`employee_id`, `attendance_date`, `status`) VALUES
(101, '2026-11-10', 'Present'),
(102, '2026-11-10', 'Present'),
(103, '2026-11-10', 'Present'),
(104, '2026-11-10', 'Present'),
(105, '2026-11-10', 'Present'),
(106, '2026-11-10', 'Present'),
(107, '2026-11-10', 'Present'),
(108, '2026-11-10', 'Present'),
(109, '2026-11-10', 'Present'),
(110, '2026-11-10', 'Present'),
(111, '2026-11-10', 'Present'),
(112, '2026-11-10', 'Present'),
(113, '2026-11-10', 'Present'),
(114, '2026-11-10', 'Present'),
(115, '2026-11-10', 'Present'),
(116, '2026-11-10', 'Present'),
(118, '2026-11-10', 'Present');

-- System Announcement
INSERT INTO `SystemInfo` (`id`, `company_name`, `admin_name`, `admin_role`, `notice_text`) VALUES
(1, 'SRM HRMS', 'Sabiha Rumman Medha', 'Head of HR & Admin', 'Head of HR: Sabiha Rumman Medha • November 2026 workforce records and attendance tracking are actively operational.');
