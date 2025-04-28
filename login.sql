-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 28, 2025 at 04:54 PM
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
-- Database: `login`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `posted_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `posted_by`, `created_at`) VALUES
(2, 'hi hi ', 'anouncement test 1 2 wohoho', 'Admin Tisoy User', '2025-04-28 13:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `sitin`
--

CREATE TABLE `sitin` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `lab` varchar(50) NOT NULL,
  `sessions` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sitin`
--

INSERT INTO `sitin` (`id`, `student_id`, `purpose`, `lab`, `sessions`, `date_created`, `end_time`, `status`) VALUES
(1, 'meow', 'Project', '536', 5, '2025-03-24 18:13:31', '2025-03-24 18:17:17', 'completed'),
(2, 'meow', 'Project', '536', 1, '2025-03-24 18:29:29', '2025-03-24 18:30:46', 'completed'),
(3, 'dummy', 'Project', '536', 1, '2025-03-24 18:31:10', '2025-03-24 18:31:24', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` bigint(255) NOT NULL,
  `Lastname` varchar(255) NOT NULL,
  `Firstname` varchar(255) NOT NULL,
  `Midname` varchar(255) NOT NULL,
  `course` varchar(50) NOT NULL,
  `year_level` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `PROFILE_IMG` varchar(255) DEFAULT 'images/default.jpg',
  `role` varchar(20) DEFAULT 'user',
  `sessions_remaining` int(11) DEFAULT 30,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Lastname`, `Firstname`, `Midname`, `course`, `year_level`, `username`, `password`, `PROFILE_IMG`, `role`, `sessions_remaining`, `email`) VALUES
(1, 'Abaddon', 'Dr. Cherry', '', 'BSIT', 3, 'admin', 'admin', 'images/adminpp.jpeg', 'admin', 30, ''),
(2147483648, 'Bravo', 'John', 'asd', 'BSIT', 4, 'johnreyancero', 'Gwapoko1245!', 'images/profile_johnreyancero.jpg', 'user', 30, 'gwapokoancero@gmail.com'),
(2147483649, 'Ancero', 'John Rey', 'Tac-an', 'BSIT', 3, 'gwapokoancero', 'Gwapokoancero12345!', 'images/default.jpg', 'user', 30, ''),
(2147483650, 'asd', 'asd', 'ASD', 'BSCRIM', 4, 'ASD', 'ASD123!a', 'images/default.jpg', 'user', 30, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sitin`
--
ALTER TABLE `sitin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sitin`
--
ALTER TABLE `sitin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483651;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
