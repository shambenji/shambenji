-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 05:07 PM
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
-- Database: `doctor_shift_scheduler`
--

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `availability` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`availability`)),
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','doctor') DEFAULT 'doctor',
  `is_active` tinyint(1) DEFAULT 1,
  `receive_sms` tinyint(1) DEFAULT 1,
  `sms_provider` varchar(50) DEFAULT NULL,
  `sms_verified` tinyint(1) DEFAULT 0,
  `last_sms_sent` timestamp NULL DEFAULT NULL
) ;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `name`, `email`, `phone`, `password`, `specialization`, `availability`, `preferences`, `active`, `created_at`, `role`, `is_active`, `receive_sms`, `sms_provider`, `sms_verified`, `last_sms_sent`) VALUES
(1, 'byabato', 'byabatoinnocent21@gmail.com', '0628466597', '$2y$10$YcTg7DPLlEmXNE4MzLJlCOIAlkUywgvGrBGIxE3uZVG5iaEY2gnSK', '', NULL, NULL, 1, '2025-06-10 20:45:36', 'doctor', 1, 1, NULL, 0, NULL),
(2, 'byabato', 'byabatoinnocent2@gmail.com', '0717948360', '$2y$10$87jgRMftNCwftMMoDJ/DzOlYEdOubivudxtQkMDGVHQ94eOvPxD6S', '', NULL, NULL, 1, '2025-06-10 20:51:53', 'doctor', 1, 1, NULL, 0, NULL),
(3, 'Dr. Admin', 'byabato@gmail.com', '0788575514', '$2y$10$gNL44lcW7dZ4CIK0BwPZcOquf1oMQNMOk1Z/XqR2PKbo7IpCvoFRC', '', NULL, NULL, 1, '2025-06-10 21:20:25', 'admin', 1, 1, NULL, 0, NULL),
(4, 'A.56', 'byabatoinnocent1@gmail.com', '0622636255', '$2y$10$JNf37jXTF6ZEdpMIg7qpw.RpTc9PMJf72WhNFkM/.hIO9XTZbEphy', '', NULL, NULL, 1, '2025-06-11 05:55:44', 'doctor', 1, 1, NULL, 0, NULL),
(5, 'inno', 'byabatoinnocent@gmail.com', '0628466597', '$2y$10$Fb3iooA63pP6Vn.cnmbHw.hnY2VAabfUXgmfKDvmb2jVi2aBk52O.', '', NULL, NULL, 1, '2025-06-11 22:40:50', 'doctor', 1, 1, NULL, 0, NULL),
(7, 'RELI', 'reli@gmail.com', '+255628466597', '$2y$10$xKiEe2xHq/BHnHleLcpqYOgBH.w2fmdIGC0n89PSfh85LfoCksFZG', NULL, NULL, NULL, 1, '2025-06-12 21:01:42', 'doctor', 1, 1, NULL, 0, NULL),
(8, 'shambeji', 'shambeji@gmail.com', '0628466597', '$2y$10$9xgdE5i7FPN12F28wxZ.cOvhj2moHwGMztXlWvj5UQWeHOZHC/o8e', '', NULL, NULL, 1, '2025-06-16 01:19:56', 'admin', 1, 1, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('morning','evening','night') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `auto_scheduled` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `sms_queued` tinyint(1) DEFAULT 0,
  `sms_sent` tinyint(1) DEFAULT 0,
  `sms_sent_at` timestamp NULL DEFAULT NULL,
  `sms_error` varchar(255) DEFAULT NULL,
  `notification_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `doctor_id`, `shift_date`, `shift_type`, `created_at`, `auto_scheduled`, `is_active`, `sms_queued`, `sms_sent`, `sms_sent_at`, `sms_error`, `notification_sent`) VALUES
(28, 4, '2025-06-22', '', '2025-06-11 22:39:35', 0, 1, 0, 0, NULL, NULL, 0),
(34, 5, '2025-06-21', '', '2025-06-11 22:46:37', 0, 1, 0, 0, NULL, NULL, 0),
(35, 5, '2025-06-22', '', '2025-06-11 22:46:37', 0, 1, 0, 0, NULL, NULL, 0),
(43, 1, '2025-06-13', 'morning', '2025-06-12 00:30:17', 0, 1, 0, 0, NULL, NULL, 0),
(46, 1, '2025-06-13', '', '2025-06-12 01:10:00', 0, 1, 0, 0, NULL, NULL, 0),
(875, 7, '2025-06-14', '', '2025-06-12 21:18:26', 0, 1, 0, 0, NULL, NULL, 0),
(1017, 4, '2025-06-19', 'morning', '2025-06-18 01:00:05', 0, 1, 0, 0, NULL, NULL, 0),
(1225, 2, '2025-06-21', 'night', '2025-06-18 01:23:01', 0, 1, 0, 0, NULL, NULL, 0),
(1571, 3, '2025-07-01', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1572, 4, '2025-07-01', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1573, 1, '2025-07-01', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1574, 2, '2025-07-02', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1575, 5, '2025-07-02', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1576, 7, '2025-07-02', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1577, 8, '2025-07-03', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1578, 3, '2025-07-03', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1579, 4, '2025-07-03', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1580, 1, '2025-07-04', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1581, 2, '2025-07-04', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1582, 5, '2025-07-04', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1583, 7, '2025-07-07', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1584, 8, '2025-07-07', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1585, 3, '2025-07-07', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1586, 4, '2025-07-08', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1587, 1, '2025-07-08', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1588, 2, '2025-07-08', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1589, 5, '2025-07-09', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1590, 7, '2025-07-09', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1591, 8, '2025-07-09', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1592, 3, '2025-07-10', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1593, 4, '2025-07-10', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1594, 5, '2025-07-10', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1595, 7, '2025-07-11', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1596, 5, '2025-07-11', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1597, 7, '2025-07-11', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1598, 8, '2025-07-14', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1599, 3, '2025-07-14', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1600, 4, '2025-07-14', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1601, 1, '2025-07-15', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1602, 2, '2025-07-15', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1603, 5, '2025-07-15', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1604, 7, '2025-07-16', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1605, 8, '2025-07-16', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1606, 3, '2025-07-16', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1607, 4, '2025-07-17', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1608, 1, '2025-07-17', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1609, 2, '2025-07-17', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1610, 5, '2025-07-18', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1611, 7, '2025-07-18', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1612, 8, '2025-07-18', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1613, 3, '2025-07-21', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1614, 4, '2025-07-21', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1615, 1, '2025-07-21', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1616, 2, '2025-07-22', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1617, 5, '2025-07-22', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1618, 7, '2025-07-22', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1619, 8, '2025-07-23', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1620, 3, '2025-07-23', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1621, 4, '2025-07-23', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1622, 1, '2025-07-24', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1623, 2, '2025-07-24', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1624, 5, '2025-07-24', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1625, 7, '2025-07-25', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1626, 8, '2025-07-25', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1627, 3, '2025-07-25', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1628, 4, '2025-07-28', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1629, 1, '2025-07-28', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1630, 2, '2025-07-28', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1631, 5, '2025-07-29', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1632, 7, '2025-07-29', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1633, 8, '2025-07-29', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1634, 3, '2025-07-30', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1635, 4, '2025-07-30', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1636, 1, '2025-07-30', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1637, 2, '2025-07-31', 'morning', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1638, 5, '2025-07-31', '', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1639, 7, '2025-07-31', 'night', '2025-06-18 01:36:13', 1, 1, 0, 0, NULL, NULL, 0),
(1640, 3, '2025-08-01', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1641, 4, '2025-08-01', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1642, 1, '2025-08-01', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1643, 2, '2025-08-04', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1644, 5, '2025-08-04', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1645, 7, '2025-08-04', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1646, 8, '2025-08-05', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1647, 3, '2025-08-05', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1648, 4, '2025-08-05', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1649, 1, '2025-08-06', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1650, 2, '2025-08-06', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1651, 5, '2025-08-06', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1652, 7, '2025-08-07', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1653, 8, '2025-08-07', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1654, 3, '2025-08-07', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1655, 4, '2025-08-08', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1656, 1, '2025-08-08', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1657, 2, '2025-08-08', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1658, 5, '2025-08-11', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1659, 7, '2025-08-11', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1660, 8, '2025-08-11', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1661, 3, '2025-08-12', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1662, 4, '2025-08-12', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1663, 1, '2025-08-12', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1664, 2, '2025-08-13', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1665, 5, '2025-08-13', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1666, 7, '2025-08-13', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1667, 8, '2025-08-14', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1668, 3, '2025-08-14', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1669, 4, '2025-08-14', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1670, 1, '2025-08-15', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1671, 2, '2025-08-15', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1672, 5, '2025-08-15', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1673, 7, '2025-08-18', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1674, 8, '2025-08-18', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1675, 3, '2025-08-18', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1676, 4, '2025-08-19', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1677, 1, '2025-08-19', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1678, 2, '2025-08-19', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1679, 5, '2025-08-20', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1680, 7, '2025-08-20', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1681, 8, '2025-08-20', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1682, 3, '2025-08-21', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1683, 4, '2025-08-21', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1684, 1, '2025-08-21', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1685, 2, '2025-08-22', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1686, 5, '2025-08-22', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1687, 7, '2025-08-22', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1688, 8, '2025-08-25', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1689, 3, '2025-08-25', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1690, 4, '2025-08-25', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1691, 1, '2025-08-26', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1692, 2, '2025-08-26', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1693, 5, '2025-08-26', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1694, 7, '2025-08-27', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1695, 8, '2025-08-27', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1696, 3, '2025-08-27', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1697, 4, '2025-08-28', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1698, 1, '2025-08-28', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1699, 2, '2025-08-28', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1700, 5, '2025-08-29', 'morning', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1701, 7, '2025-08-29', '', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0),
(1702, 8, '2025-08-29', 'night', '2025-07-14 09:56:48', 1, 1, 0, 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `shift_swaps`
--

CREATE TABLE `shift_swaps` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `requested_shift_id` int(11) NOT NULL,
  `target_doctor_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` text DEFAULT NULL,
  `requesting_doctor_id` int(11) DEFAULT NULL,
  `target_shift_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shift_swaps`
--

INSERT INTO `shift_swaps` (`id`, `doctor_id`, `requested_shift_id`, `target_doctor_id`, `status`, `created_at`, `reason`, `requesting_doctor_id`, `target_shift_id`) VALUES
(22, 1, 1580, 2, 'pending', '2025-06-25 20:29:05', NULL, NULL, NULL),
(23, 1, 1594, 5, 'approved', '2025-06-25 20:30:40', NULL, NULL, NULL),
(24, 2, 1637, 8, 'rejected', '2025-06-25 20:32:03', NULL, NULL, NULL),
(25, 2, 1595, 7, 'approved', '2025-06-25 20:32:12', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `shift_swap_requests`
--

CREATE TABLE `shift_swap_requests` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `target_doctor_id` int(11) NOT NULL,
  `requested_shift_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `swap_requests`
--

CREATE TABLE `swap_requests` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('morning','evening','night') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor') NOT NULL DEFAULT 'doctor',
  `is_active` tinyint(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `shift_swaps`
--
ALTER TABLE `shift_swaps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `target_doctor_id` (`target_doctor_id`),
  ADD KEY `requested_shift_id` (`requested_shift_id`);

--
-- Indexes for table `shift_swap_requests`
--
ALTER TABLE `shift_swap_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requester_id` (`requester_id`),
  ADD KEY `target_doctor_id` (`target_doctor_id`),
  ADD KEY `requested_shift_id` (`requested_shift_id`);

--
-- Indexes for table `swap_requests`
--
ALTER TABLE `swap_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1703;

--
-- AUTO_INCREMENT for table `shift_swaps`
--
ALTER TABLE `shift_swaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `shift_swap_requests`
--
ALTER TABLE `shift_swap_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `swap_requests`
--
ALTER TABLE `swap_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shift_swaps`
--
ALTER TABLE `shift_swaps`
  ADD CONSTRAINT `shift_swaps_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shift_swaps_ibfk_2` FOREIGN KEY (`target_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shift_swaps_ibfk_3` FOREIGN KEY (`requested_shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shift_swap_requests`
--
ALTER TABLE `shift_swap_requests`
  ADD CONSTRAINT `shift_swap_requests_ibfk_1` FOREIGN KEY (`requester_id`) REFERENCES `doctors` (`id`),
  ADD CONSTRAINT `shift_swap_requests_ibfk_2` FOREIGN KEY (`target_doctor_id`) REFERENCES `doctors` (`id`),
  ADD CONSTRAINT `shift_swap_requests_ibfk_3` FOREIGN KEY (`requested_shift_id`) REFERENCES `shifts` (`id`);

--
-- Constraints for table `swap_requests`
--
ALTER TABLE `swap_requests`
  ADD CONSTRAINT `swap_requests_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
