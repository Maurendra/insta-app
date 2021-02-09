-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2021 at 07:15 AM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sevima`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_comment`
--

CREATE TABLE `app_comment` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `app_comment`
--

INSERT INTO `app_comment` (`id`, `post_id`, `user_id`, `comment`, `createdDate`, `lastUpdate`) VALUES
(1, 1, 2, 'asa', '2021-02-09 02:28:50', '2021-02-09 02:28:50'),
(2, 1, 2, 'cupu', '2021-02-09 04:22:19', '2021-02-09 04:22:19'),
(3, 1, 2, 'lalala', '2021-02-09 05:30:32', '2021-02-09 05:30:32'),
(4, 4, 2, 'Kenangan sih', '2021-02-09 06:00:55', '2021-02-09 06:00:55'),
(5, 1, 3, 'Not bad lah', '2021-02-09 06:05:07', '2021-02-09 06:05:07'),
(6, 5, 2, 'Good one', '2021-02-09 06:08:58', '2021-02-09 06:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `app_like`
--

CREATE TABLE `app_like` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `app_like`
--

INSERT INTO `app_like` (`id`, `user_id`, `post_id`, `createdDate`, `lastUpdate`) VALUES
(7, 2, 3, '2021-02-09 05:42:41', '2021-02-09 05:42:41'),
(8, 2, 1, '2021-02-09 06:00:33', '2021-02-09 06:00:33'),
(9, 2, 2, '2021-02-09 06:00:38', '2021-02-09 06:00:38'),
(10, 3, 1, '2021-02-09 06:04:56', '2021-02-09 06:04:56'),
(11, 3, 4, '2021-02-09 06:07:45', '2021-02-09 06:07:45'),
(12, 3, 5, '2021-02-09 06:08:11', '2021-02-09 06:08:11'),
(13, 2, 5, '2021-02-09 06:08:50', '2021-02-09 06:08:50');

-- --------------------------------------------------------

--
-- Table structure for table `app_post`
--

CREATE TABLE `app_post` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `title` varchar(50) NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `app_post`
--

INSERT INTO `app_post` (`id`, `user_id`, `file_name`, `description`, `title`, `createdDate`, `lastUpdate`) VALUES
(1, 2, '3e7e0b9ef0991ab76504a10756fc322d.png', '--', 'Post pertama', '2021-02-09 01:12:51', '2021-02-09 01:12:51'),
(2, 2, 'f521eca39ef81559c47e1affb900de52.PNG', '', 'Post kedua', '2021-02-09 01:33:13', '2021-02-09 01:33:13'),
(3, 2, '8869bc247181190d381b7cb6c5870935.jpg', '', 'Post ketiga', '2021-02-09 01:34:20', '2021-02-09 01:34:20'),
(4, 2, '3a6c841a6bd409b086bda2676d60812d.PNG', '', 'Post keempat', '2021-02-09 01:34:43', '2021-02-09 01:34:43'),
(5, 3, 'a7606e6235fa81fa9d9d66301ddab3dd.PNG', '====', 'Test', '2021-02-09 06:08:05', '2021-02-09 06:08:05');

-- --------------------------------------------------------

--
-- Table structure for table `app_user`
--

CREATE TABLE `app_user` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(200) NOT NULL,
  `createdDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `app_user`
--

INSERT INTO `app_user` (`id`, `username`, `password`, `createdDate`, `lastUpdate`) VALUES
(2, 'maurendra', '$2y$10$tOhK79q6tSsRLJN1fxTFOON4VTpWYErwk5ErLntnLQzX2.35mB6Ri', '2021-02-09 01:05:01', '2021-02-09 01:05:01'),
(3, 'maur', '$2y$10$LueZJg1DTEjI3CHUKvPCw.h4Ny1UoUFN8mTJhtHF4.10Wdk7R8Ahy', '2021-02-09 06:04:52', '2021-02-09 06:04:52');

-- --------------------------------------------------------

--
-- Table structure for table `test1_nilai`
--

CREATE TABLE `test1_nilai` (
  `nim` int(11) NOT NULL,
  `kodeMk` varchar(5) NOT NULL,
  `namaMk` varchar(50) NOT NULL,
  `nilaiAngka` int(11) NOT NULL,
  `nilaiHuruf` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `test1_user`
--

CREATE TABLE `test1_user` (
  `nim` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `Umur` int(11) NOT NULL,
  `Telp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_comment`
--
ALTER TABLE `app_comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_like`
--
ALTER TABLE `app_like`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_post`
--
ALTER TABLE `app_post`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `app_user`
--
ALTER TABLE `app_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test1_user`
--
ALTER TABLE `test1_user`
  ADD PRIMARY KEY (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_comment`
--
ALTER TABLE `app_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `app_like`
--
ALTER TABLE `app_like`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `app_post`
--
ALTER TABLE `app_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `app_user`
--
ALTER TABLE `app_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `test1_user`
--
ALTER TABLE `test1_user`
  MODIFY `nim` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
