-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 29, 2024 at 12:22 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `huanfitnesspal`
--

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE IF NOT EXISTS `consultations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `preferredDate` date NOT NULL,
  `preferredTime` time NOT NULL,
  `consultantName` varchar(100) NOT NULL,
  `notes` mediumtext,
  `payment_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_payment` (`payment_id`),
  KEY `idx_user_date` (`user_id`,`preferredDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `user_id`, `preferredDate`, `preferredTime`, `consultantName`, `notes`, `payment_id`) VALUES
(1, 1, '2024-10-30', '18:00:00', 'Default Consultant', 'dont go home first', 16);

-- --------------------------------------------------------

--
-- Table structure for table `daily_completion`
--

CREATE TABLE IF NOT EXISTS `daily_completion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `completion_date` date NOT NULL,
  `has_completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_daily_completion` (`user_id`,`completion_date`),
  KEY `idx_user_date` (`user_id`,`completion_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `daily_completion`
--

INSERT INTO `daily_completion` (`id`, `user_id`, `completion_date`, `has_completed`) VALUES
(1, 1, '2024-10-28', 0),
(2, 1, '2024-10-29', 1),
(3, 3, '2024-10-29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE IF NOT EXISTS `exercises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `starred` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `starred`, `created_at`) VALUES
(1, 'Bench Press', 0, '2024-10-25 19:38:53'),
(2, 'Deadlift', 0, '2024-10-25 19:38:53'),
(3, 'Squat', 1, '2024-10-25 19:38:53'),
(4, 'Pull-up', 0, '2024-10-25 19:38:53'),
(5, 'Push-up', 0, '2024-10-25 19:38:53'),
(6, 'Shoulder Press', 1, '2024-10-25 19:38:53'),
(8, 'Stretching', 0, '2024-10-26 07:55:50'),
(9, 'Running', 1, '2024-10-26 07:56:15'),
(10, 'Plank', 0, '2024-10-27 07:46:19'),
(11, 'Jumping Jacks', 1, '2024-10-27 14:39:49');

-- --------------------------------------------------------

--
-- Table structure for table `exercise_sessions`
--

CREATE TABLE IF NOT EXISTS `exercise_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `exercise_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `session_time` time NOT NULL,
  `duration` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT '0',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_completed_date` (`user_id`,`completed`,`session_date`),
  KEY `idx_date_user_completed` (`session_date`,`user_id`,`completed`),
  KEY `fk_exercise_sessions_exercise` (`exercise_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `exercise_sessions`
--

INSERT INTO `exercise_sessions` (`id`, `user_id`, `exercise_id`, `session_date`, `session_time`, `duration`, `completed`, `last_updated`) VALUES
(2, 1, 1, '2024-10-27', '18:50:00', 15, 0, '2024-10-27 14:38:27'),
(3, 1, 3, '2024-10-27', '18:00:00', 15, 0, '2024-10-27 14:38:26'),
(4, 2, 9, '2024-10-27', '23:00:00', 15, 0, '2024-10-27 14:40:41'),
(5, 1, 11, '2024-10-28', '06:20:00', 15, 0, '2024-10-27 16:19:51'),
(6, 1, 9, '2024-10-28', '19:04:00', 15, 0, '2024-10-28 05:04:08'),
(7, 2, 11, '2024-10-28', '16:30:00', 15, 0, '2024-10-28 05:29:23'),
(8, 2, 9, '2024-10-28', '19:00:00', 60, 0, '2024-10-28 05:29:57'),
(9, 1, 11, '2024-10-29', '03:00:00', 15, 1, '2024-10-29 10:22:20'),
(10, 1, 9, '2024-10-29', '04:00:00', 60, 1, '2024-10-29 10:22:22'),
(11, 1, 5, '2024-10-29', '17:00:00', 45, 1, '2024-10-29 10:22:24'),
(12, 3, 11, '2024-10-29', '19:05:00', 15, 1, '2024-10-29 11:04:20'),
(13, 1, 3, '2024-10-29', '20:30:00', 15, 1, '2024-10-29 12:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE IF NOT EXISTS `memberships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `membership_type` enum('Gold','Silver','Bronze') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_id` int(11) NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_dates` (`start_date`,`end_date`),
  KEY `idx_payment` (`payment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `user_id`, `membership_type`, `start_date`, `end_date`, `payment_id`, `status`) VALUES
(1, 1, '', '2024-10-28', '2025-10-28', 1, 'active'),
(2, 1, '', '2024-10-28', '2025-10-28', 2, 'active'),
(3, 1, '', '2024-10-28', '2025-10-28', 3, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `payment_type` enum('Consultation','Gold Membership','Silver Membership','Bronze Membership') NOT NULL,
  `payment_method` enum('Bank Transfer','Touch n Go','Credit Card') NOT NULL,
  `payment_amount` text NOT NULL,
  `payment_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_date` (`user_id`,`payment_date`),
  KEY `idx_status_type` (`payment_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `payment_type`, `payment_method`, `payment_amount`, `payment_date`) VALUES
(1, 1, 'Silver Membership', 'Bank Transfer', '0.00', '2024-10-28 17:05:35'),
(2, 1, 'Gold Membership', 'Bank Transfer', '0.00', '2024-10-28 17:18:56'),
(3, 1, 'Gold Membership', 'Touch n Go', '0.00', '2024-10-28 18:47:58'),
(4, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:07:05'),
(5, 1, 'Consultation', 'Credit Card', 'RM 20', '2024-10-29 02:07:57'),
(6, 1, 'Consultation', 'Credit Card', 'RM 20', '2024-10-29 02:08:08'),
(7, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:08:20'),
(8, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:09:45'),
(9, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:11:04'),
(10, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:11:16'),
(11, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:12:00'),
(12, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:12:02'),
(13, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:12:04'),
(14, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 02:12:27'),
(15, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 10:58:22'),
(16, 1, 'Consultation', 'Credit Card', 'RM 20', '2024-10-29 11:02:56'),
(17, 1, 'Consultation', 'Touch n Go', 'RM 20', '2024-10-29 11:05:24'),
(18, 3, 'Consultation', 'Credit Card', 'RM 20', '2024-10-29 11:21:08'),
(19, 3, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 11:21:24'),
(20, 3, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 11:22:44'),
(21, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 11:26:11'),
(23, 3, 'Consultation', 'Credit Card', 'RM 20', '2024-10-29 11:39:16'),
(24, 3, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 11:47:47'),
(25, 3, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 11:56:58'),
(26, 1, 'Consultation', 'Bank Transfer', 'RM 20', '2024-10-29 12:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user',
  `gender` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `gender`) VALUES
(1, 'mykrizx', 'krizx@gmail.com', '123456', 'user', 'Female'),
(3, 'syahminerz', 'syah@gmail.com', '123456', 'user', 'Male'),
(2, 'test', 'test@gmail.com', '123456', 'user', 'prefer-not-to-say'),
(4, 'admin', 'admin@gmail.com', 'admin123', 'admin', 'male');

-- --------------------------------------------------------

--
-- Table structure for table `water`
--

CREATE TABLE IF NOT EXISTS `water` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `amount` int(10) NOT NULL,
  `entry_time` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_water_user` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=48 ;

--
-- Dumping data for table `water`
--

INSERT INTO `water` (`id`, `amount`, `entry_time`, `user_id`) VALUES
(31, 250, '2024-10-27 15:27:03', 1),
(32, 400, '2024-10-27 15:27:05', 1),
(33, 150, '2024-10-27 22:42:23', 2),
(34, 200, '2024-10-27 22:42:24', 2),
(35, 350, '2024-10-27 22:42:25', 2),
(36, 150, '2024-10-28 00:19:47', 1),
(37, 250, '2024-10-28 00:19:47', 1),
(38, 300, '2024-10-28 00:22:55', 3),
(39, 450, '2024-10-28 00:22:57', 3),
(40, 300, '2024-10-28 13:30:31', 2),
(41, 400, '2024-10-28 13:30:33', 2),
(42, 150, '2024-10-28 13:30:35', 2),
(43, 150, '2024-10-28 22:05:32', 1),
(44, 200, '2024-10-29 02:09:14', 1),
(45, 350, '2024-10-29 02:09:15', 1),
(46, 300, '2024-10-29 19:04:53', 3),
(47, 200, '2024-10-29 20:04:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `weight_log`
--

CREATE TABLE IF NOT EXISTS `weight_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_date` (`user_id`,`entry_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `weight_log`
--

INSERT INTO `weight_log` (`id`, `user_id`, `entry_date`, `weight`, `last_updated`) VALUES
(1, 1, '2024-10-28', '47.80', '2024-10-28 18:29:06'),
(2, 3, '2024-10-28', '47.60', '2024-10-28 14:50:29'),
(3, 2, '2024-10-28', '45.90', '2024-10-28 14:48:47'),
(4, 1, '2024-10-27', '46.80', '2024-10-28 18:18:18'),
(5, 1, '2024-10-29', '36.00', '2024-10-29 00:53:12'),
(6, 3, '2024-10-29', '48.00', '2024-10-29 11:04:25');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
