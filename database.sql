SET FOREIGN_KEY_CHECKS = 0;

-- 1. Database Initialization
CREATE DATABASE IF NOT EXISTS `leave_management_db`;
USE `leave_management_db`;

-- 2. Drop Old Conflict Tables
DROP TABLE IF EXISTS `leave_applications`;
DROP TABLE IF EXISTS `leave_types`;
DROP TABLE IF EXISTS `employess`;
DROP TABLE IF EXISTS `admiin`;

-- 3. Admin Table Structure & Baseline Seed
CREATE TABLE IF NOT EXISTS `admiin` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `Email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL
);

INSERT INTO `admiin` (`Email`, `password_hash`) 
VALUES ('adminnexus@tech.com', '$2y$10$8CgDkR5aZk0Z7Fqf/Puw7OfxKjG26C88s1cT.4g83bB4Pmx6g8oEq');

-- 4. Employees Table Structure & Baseline Seed
CREATE TABLE IF NOT EXISTS `employess` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `department` VARCHAR(50) NOT NULL,
    `joining_date` DATE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active'
);

INSERT INTO `employess` (`id`, `full_name`, `email`, `phone`, `department`, `joining_date`, `password_hash`, `status`) 
VALUES (1, 'Master Tester', 'masteremployee@nexus.com', '9999999999', 'IT Department', '2026-07-17', '$2y$10$y582V4m.s1D2s8e7F2K8Oe.G6mBw5X7cRk3M9Vp9zY7J7q9P2B2Sy', 'Active');

-- 5. Leave Types Table Structure
CREATE TABLE IF NOT EXISTS `leave_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `max_days` INT NOT NULL DEFAULT 60,
    `status` ENUM('Active', 'Inactive') DEFAULT 'Active'
);

INSERT INTO `leave_types` (`name`, `description`, `max_days`, `status`) VALUES
('Casual Leave', 'For personal reasons', 12, 'Active'),
('Sick Leave', 'For medical emergencies', 15, 'Active'),
('Paid Leave', 'Annual earned leaves', 20, 'Active'),
('Unpaid Leave', 'Leaves without salary', 30, 'Active');

-- 6. Leave Applications Core Log Table
CREATE TABLE IF NOT EXISTS `leave_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `employee_name` VARCHAR(100) NOT NULL,
    `leave_type` VARCHAR(50) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `duration` INT NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    `admin_comment` TEXT DEFAULT NULL,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`employee_id`) REFERENCES `employess`(`id`) ON DELETE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;


TRUNCATE TABLE `employess`;


SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `employess`;
TRUNCATE TABLE `leave_applications`;

SET FOREIGN_KEY_CHECKS = 1;