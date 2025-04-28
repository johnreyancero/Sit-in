-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2025 at 08:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

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
(2, 'WALAY KLASE UGMA', 'WUHOOO', 'Admin Tisoy User', '2025-04-07 15:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date_submitted` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `student_id`, `subject`, `message`, `date_submitted`) VALUES
(1, 'dummy', 'ADMIN POGI MAKA LUYA', 'TURIP IP IP IP TURIP IP IP IP', '2025-04-08 01:54:59');

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
(3, 'dummy', 'Project', '536', 1, '2025-03-24 18:31:10', '2025-03-24 18:31:24', 'completed'),
(4, 'dummy', 'Programming', '536', 1, '2025-04-07 17:32:42', '2025-04-07 18:09:01', 'completed'),
(5, 'pangit', 'Programming', '535', 1, '2025-04-07 18:28:12', '2025-04-08 01:15:51', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Lastname` varchar(255) NOT NULL,
  `Firstname` varchar(255) NOT NULL,
  `Midname` varchar(255) NOT NULL,
  `course` varchar(50) NOT NULL,
  `year_level` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `PROFILE_IMG` varchar(255) DEFAULT 'images/default.jpg',
  `role` varchar(20) DEFAULT 'user',
  `sessions_remaining` int(11) DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Lastname`, `Firstname`, `Midname`, `course`, `year_level`, `username`, `password`, `PROFILE_IMG`, `role`, `sessions_remaining`) VALUES
(0, 'User', 'Admin Tisoy', '', 'BSIT', 3, 'admin', 'admin123', 'images/pluh-cat.png', 'admin', 30),
(123, 'Caumeran', 'Damien', 'wala', 'BSIT', 3, 'pangit', 'damien123@', 'images/default.jpg', 'user', 29),
(20205837, 'SOLON', 'JHON RICHMON', 'ALFORQUE', 'BSIT', 3, 'richmon', 'richmongwapo123@', 'images/default.jpg', 'user', 30),
(2147483647, 'luffy', 'monke', 'D', 'BSIT', 2, 'dummy', 'dummy123@', 'images/monkey.jpg', 'user', 28);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
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
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sitin`
--
ALTER TABLE `sitin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
