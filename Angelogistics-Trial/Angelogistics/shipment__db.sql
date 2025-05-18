-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 05:59 PM
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
-- Database: `shipment__db`
--

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `encoding_status` varchar(50) DEFAULT NULL,
  `shipping_status` varchar(50) DEFAULT NULL,
  `importer` varchar(100) DEFAULT NULL,
  `commodity` varchar(100) DEFAULT NULL,
  `num_containers` int(11) DEFAULT NULL,
  `cd_status` varchar(20) DEFAULT NULL,
  `shipping_line` varchar(100) DEFAULT NULL,
  `eta` date DEFAULT NULL,
  `days_remaining` int(11) DEFAULT NULL,
  `bl_number` varchar(50) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `container_size` varchar(20) DEFAULT NULL,
  `container_numbers` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `encoding_status`, `shipping_status`, `importer`, `commodity`, `num_containers`, `cd_status`, `shipping_line`, `eta`, `days_remaining`, `bl_number`, `invoice_number`, `container_size`, `container_numbers`, `created_at`) VALUES
(23, 'FOR DT/PC', 'BILLED', 'Earl Dulay', '312', 123, 'NO CD', '3123', '0000-00-00', 15, '13', '231', '13', '123', '2025-05-13 13:53:37'),
(26, 'FOR PRESAD', 'NEW SHIPMENT', '', '', 0, 'NO CD', '', '0000-00-00', 15, '', '', '', '', '2025-05-13 14:43:51'),
(27, 'FOR PRESAD', 'NEW SHIPMENT', '', '', 0, 'NO CD', '', '0000-00-00', 15, '', '', '', '', '2025-05-13 14:43:54'),
(28, 'UPLOADED', 'CONTAINER DEPOSIT', '', '', 0, 'NO CD', '', '0000-00-00', 15, '', '', '', '', '2025-05-13 14:43:56'),
(29, 'FOR PRESAD', 'NEW SHIPMENT', '', '', 0, 'NO CD', '', '0000-00-00', 15, '', '', '', '', '2025-05-13 14:44:00'),
(30, 'FOR PRESAD', 'NEW SHIPMENT', 'Earl Dulay', '4', 2, 'CD', '4', '2025-05-15', 15, '4', '4', 'Large', '4', '2025-05-13 15:01:27'),
(31, 'FOR PRESAD', 'NEW SHIPMENT', '', '', 0, 'NO CD', '', '0000-00-00', 15, '', '', '13', '', '2025-05-13 15:46:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
