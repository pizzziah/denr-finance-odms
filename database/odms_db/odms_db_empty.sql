-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2026 at 08:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `odms_db_empty`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget_review_processes`
--

CREATE TABLE `budget_review_processes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `budget_id` bigint(20) UNSIGNED NOT NULL,
  `date_returned` datetime DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_accounting`
--

CREATE TABLE `odms_accounting` (
  `accounting_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `budget_id` int(11) DEFAULT NULL,
  `obr_no` varchar(50) DEFAULT NULL,
  `dv_no` varchar(50) DEFAULT NULL,
  `payee` varchar(255) DEFAULT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `particulars_remark` varchar(255) DEFAULT NULL,
  `uac_codes` varchar(50) DEFAULT NULL,
  `debit` decimal(15,2) DEFAULT NULL,
  `credit` decimal(15,2) DEFAULT NULL,
  `tax_percent` varchar(50) DEFAULT NULL,
  `tax_remarks` varchar(100) DEFAULT NULL,
  `returned_remarks` varchar(255) DEFAULT NULL,
  `signed_by_accountant` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `budget_year` int(11) DEFAULT NULL,
  `ors_no` varchar(50) DEFAULT NULL,
  `source_month` varchar(20) DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `date_processed` datetime DEFAULT NULL,
  `obr_date` datetime DEFAULT NULL,
  `date_signed` datetime DEFAULT NULL,
  `date_forwarded` datetime DEFAULT NULL,
  `signed` enum('Yes','No') DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_accounting_2026_q1`
--

CREATE TABLE `odms_accounting_2026_q1` (
  `q1_id` varchar(50) NOT NULL,
  `emds_date` varchar(50) DEFAULT NULL,
  `date_processed` varchar(50) DEFAULT NULL,
  `dv_no` varchar(100) DEFAULT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `transaction_type` enum('Adjustment','Signed DV','NCA/NTA Received','NCA/NTA Downloaded') DEFAULT NULL,
  `amount` varchar(100) DEFAULT NULL,
  `balance` varchar(100) DEFAULT NULL,
  `ada_no` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_accounting_2026_q2`
--

CREATE TABLE `odms_accounting_2026_q2` (
  `q2_id` varchar(50) NOT NULL,
  `emds_date` varchar(50) DEFAULT NULL,
  `date_processed` varchar(50) DEFAULT NULL,
  `dv_no` varchar(100) DEFAULT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `transaction_type` enum('Adjustment','Signed DV','NCA/NTA Received','NCA/NTA Downloaded') DEFAULT NULL,
  `amount` varchar(100) DEFAULT NULL,
  `balance` varchar(100) DEFAULT NULL,
  `ada_no` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_accounting_2026_q3`
--

CREATE TABLE `odms_accounting_2026_q3` (
  `q3_id` bigint(20) UNSIGNED NOT NULL,
  `emds_date` varchar(100) DEFAULT NULL,
  `date_processed` varchar(100) DEFAULT NULL,
  `dv_no` varchar(100) DEFAULT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `transaction_type` enum('Adjustment','Signed DV','NCA/NTA Received','NCA/NTA Downloaded') DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `balance` double DEFAULT NULL,
  `ada_no` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_accounting_uac_codes`
--

CREATE TABLE `odms_accounting_uac_codes` (
  `classification` varchar(15) DEFAULT NULL,
  `order_title` varchar(203) DEFAULT NULL,
  `uac_codes` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_admin_quarter_locks`
--

CREATE TABLE `odms_admin_quarter_locks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `quarter` int(11) NOT NULL,
  `status` enum('open','locked') NOT NULL DEFAULT 'open',
  `requires_admin_unlock` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_admin_users`
--

CREATE TABLE `odms_admin_users` (
  `id` int(11) NOT NULL,
  `department` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','accountant','budget','bookkeeper') NOT NULL,
  `permission_level` enum('restricted','special') DEFAULT NULL,
  `is_active` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_budget`
--

CREATE TABLE `odms_budget` (
  `budget_id` bigint(20) UNSIGNED NOT NULL,
  `ors_no` varchar(7) DEFAULT NULL,
  `payee` varchar(48) DEFAULT NULL,
  `particulars` varchar(305) DEFAULT NULL,
  `uac_codes` varchar(16) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `issuing_office` varchar(100) DEFAULT NULL,
  `classification` varchar(100) DEFAULT NULL,
  `particulars_remark` varchar(255) DEFAULT NULL,
  `remarks_1` varchar(255) DEFAULT NULL,
  `remarks_2` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'Pending',
  `final_remarks` varchar(255) DEFAULT NULL,
  `returned_remarks` text DEFAULT NULL,
  `archive_year` year(4) DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `date_returned_1` datetime DEFAULT NULL,
  `date_received_1` datetime DEFAULT NULL,
  `date_forwarded_1` datetime DEFAULT NULL,
  `date_ors_received` datetime DEFAULT NULL,
  `date_returned_2` datetime DEFAULT NULL,
  `date_received_2` datetime DEFAULT NULL,
  `date_forwarded_accounting` datetime DEFAULT NULL,
  `total_time_1` time DEFAULT NULL,
  `time_1` time DEFAULT NULL,
  `total_time_2` time DEFAULT NULL,
  `time_2` time DEFAULT NULL,
  `total_time_3` time DEFAULT NULL,
  `time_3` time DEFAULT NULL,
  `total_time_budget` time DEFAULT NULL,
  `total_time` time DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `display_total_time` varchar(20) DEFAULT NULL,
  `display_total_time_budget` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_budget_archive`
--

CREATE TABLE `odms_budget_archive` (
  `budget_id` bigint(20) UNSIGNED NOT NULL,
  `ors_no` varchar(7) DEFAULT NULL,
  `payee` varchar(48) DEFAULT NULL,
  `particulars` varchar(305) DEFAULT NULL,
  `uac_codes` varchar(16) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `issuing_office` varchar(100) DEFAULT NULL,
  `classification` varchar(100) DEFAULT NULL,
  `particulars_remark` varchar(255) DEFAULT NULL,
  `remarks_1` varchar(255) DEFAULT NULL,
  `remarks_2` varchar(255) DEFAULT NULL,
  `status` varchar(100) DEFAULT 'Pending',
  `final_remarks` varchar(255) DEFAULT NULL,
  `archive_year` year(4) DEFAULT NULL,
  `date_received` datetime DEFAULT NULL,
  `date_returned_1` datetime DEFAULT NULL,
  `date_received_1` datetime DEFAULT NULL,
  `date_forwarded_1` datetime DEFAULT NULL,
  `date_ors_received` datetime DEFAULT NULL,
  `date_returned_2` datetime DEFAULT NULL,
  `date_received_2` datetime DEFAULT NULL,
  `date_forwarded_accounting` datetime DEFAULT NULL,
  `total_time_1` time DEFAULT NULL,
  `time_1` time DEFAULT NULL,
  `total_time_2` time DEFAULT NULL,
  `time_2` time DEFAULT NULL,
  `total_time_3` time DEFAULT NULL,
  `time_3` time DEFAULT NULL,
  `total_time_budget` time DEFAULT NULL,
  `total_time` time DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `display_total_time` varchar(20) DEFAULT NULL,
  `display_total_time_budget` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_budget_notifications`
--

CREATE TABLE `odms_budget_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `related_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `target_role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_budget_uac_codes`
--

CREATE TABLE `odms_budget_uac_codes` (
  `old_uac` varchar(32) DEFAULT NULL,
  `new_uac` varchar(20) DEFAULT NULL,
  `uac_title` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_dropdowns`
--

CREATE TABLE `odms_dropdowns` (
  `dropdown_id` bigint(20) UNSIGNED NOT NULL,
  `classifications` varchar(28) DEFAULT NULL,
  `issuing_office` varchar(14) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `odms_uacs_mapping`
--

CREATE TABLE `odms_uacs_mapping` (
  `id` int(11) NOT NULL,
  `budget_uac` varchar(50) NOT NULL,
  `accounting_uac` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quarter_locks`
--

CREATE TABLE `quarter_locks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `quarter` int(11) NOT NULL,
  `status` enum('open','locked') NOT NULL DEFAULT 'open',
  `requires_admin_unlock` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budget_review_processes`
--
ALTER TABLE `budget_review_processes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `budget_review_processes_budget_id_foreign` (`budget_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `odms_accounting`
--
ALTER TABLE `odms_accounting`
  ADD PRIMARY KEY (`accounting_id`);

--
-- Indexes for table `odms_accounting_2026_q1`
--
ALTER TABLE `odms_accounting_2026_q1`
  ADD PRIMARY KEY (`q1_id`);

--
-- Indexes for table `odms_accounting_2026_q2`
--
ALTER TABLE `odms_accounting_2026_q2`
  ADD PRIMARY KEY (`q2_id`);

--
-- Indexes for table `odms_accounting_2026_q3`
--
ALTER TABLE `odms_accounting_2026_q3`
  ADD PRIMARY KEY (`q3_id`);

--
-- Indexes for table `odms_admin_quarter_locks`
--
ALTER TABLE `odms_admin_quarter_locks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quarter_locks_year_quarter_unique` (`year`,`quarter`);

--
-- Indexes for table `odms_admin_users`
--
ALTER TABLE `odms_admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `odms_budget`
--
ALTER TABLE `odms_budget`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `odms_budget_archive`
--
ALTER TABLE `odms_budget_archive`
  ADD PRIMARY KEY (`budget_id`);

--
-- Indexes for table `odms_budget_notifications`
--
ALTER TABLE `odms_budget_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `odms_dropdowns`
--
ALTER TABLE `odms_dropdowns`
  ADD PRIMARY KEY (`dropdown_id`);

--
-- Indexes for table `odms_uacs_mapping`
--
ALTER TABLE `odms_uacs_mapping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `quarter_locks`
--
ALTER TABLE `quarter_locks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quarter_locks_year_quarter_unique` (`year`,`quarter`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budget_review_processes`
--
ALTER TABLE `budget_review_processes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `odms_accounting`
--
ALTER TABLE `odms_accounting`
  MODIFY `accounting_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5600;

--
-- AUTO_INCREMENT for table `odms_accounting_2026_q3`
--
ALTER TABLE `odms_accounting_2026_q3`
  MODIFY `q3_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `odms_admin_quarter_locks`
--
ALTER TABLE `odms_admin_quarter_locks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `odms_admin_users`
--
ALTER TABLE `odms_admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `odms_budget`
--
ALTER TABLE `odms_budget`
  MODIFY `budget_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5513;

--
-- AUTO_INCREMENT for table `odms_budget_archive`
--
ALTER TABLE `odms_budget_archive`
  MODIFY `budget_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4068;

--
-- AUTO_INCREMENT for table `odms_budget_notifications`
--
ALTER TABLE `odms_budget_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `odms_dropdowns`
--
ALTER TABLE `odms_dropdowns`
  MODIFY `dropdown_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `odms_uacs_mapping`
--
ALTER TABLE `odms_uacs_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=393466;

--
-- AUTO_INCREMENT for table `quarter_locks`
--
ALTER TABLE `quarter_locks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budget_review_processes`
--
ALTER TABLE `budget_review_processes`
  ADD CONSTRAINT `budget_review_processes_budget_id_foreign` FOREIGN KEY (`budget_id`) REFERENCES `odms_budget` (`budget_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
