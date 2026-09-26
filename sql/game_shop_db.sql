-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2026 at 10:29 AM
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
-- Database: `game_shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_games`
--

CREATE TABLE `tb_games` (
  `game_id` int(11) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `game_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_orders`
--

CREATE TABLE `tb_orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL,
  `total_price` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_posts`
--

CREATE TABLE `tb_posts` (
  `post_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `post_title` varchar(200) NOT NULL,
  `post_detail` text NOT NULL,
  `post_price` float NOT NULL,
  `post_img` varchar(255) NOT NULL,
  `status` enum('availble','sold') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_users`
--

CREATE TABLE `tb_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','merchant','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_users`
--

INSERT INTO `tb_users` (`user_id`, `username`, `password_hash`, `email`, `role`) VALUES
(1, 'admin', '$2y$10$kNkO0DUNxDEPVzmdHW3kRO.VwSq.BX8m6COy5inRqKERuGOIcjxja', 'admin@gmail.com', 'admin'),
(2, 'adminxx', '$2y$10$AquK66ok1epiz8NpOL7cKu45qYHKEVBPNDi9SWM/GgyrNovihlUZS', 'adminxx@gmail.com', 'user'),
(3, 'adminxx', '$2y$10$6BiMOlzNP2.4OAu3ILcdJe5eK60Kk737e3wZPKeVRkAtJcAarISrm', 'adminxx@gmail.com', 'user'),
(4, 'seller1', '$2y$10$zZ.rlCWytJWIchMhAyOfAulxot6Y6YPN9XySmelbKXj0SkpUbwThi', 'seller@gmail.com', 'merchant'),
(5, 'seller1', '$2y$10$2Pk858p1eW36smBwPoLPWuTOFaTt5mPz20UUHd191ZSHgswAxMaG.', 'seller@gmail.com', 'merchant');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_games`
--
ALTER TABLE `tb_games`
  ADD PRIMARY KEY (`game_id`);

--
-- Indexes for table `tb_orders`
--
ALTER TABLE `tb_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `tb_posts`
--
ALTER TABLE `tb_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `post_id` (`post_id`,`seller_id`),
  ADD KEY `post_id_2` (`post_id`,`game_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_games`
--
ALTER TABLE `tb_games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_orders`
--
ALTER TABLE `tb_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_posts`
--
ALTER TABLE `tb_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_orders`
--
ALTER TABLE `tb_orders`
  ADD CONSTRAINT `tb_orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `tb_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_orders_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `tb_posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_posts`
--
ALTER TABLE `tb_posts`
  ADD CONSTRAINT `tb_posts_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `tb_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_posts_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `tb_games` (`game_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
