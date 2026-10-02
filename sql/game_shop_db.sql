-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 04:53 AM
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
-- Table structure for table `tb_chat_messages`
--

CREATE TABLE `tb_chat_messages` (
  `message_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `message_img` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_chat_messages`
--

INSERT INTO `tb_chat_messages` (`message_id`, `room_id`, `sender_id`, `message_text`, `message_img`, `is_read`, `created_at`) VALUES
(1, 1, 2, 'll', NULL, 0, '2026-09-28 17:24:39'),
(2, 2, 6, 'fff', NULL, 0, '2026-09-29 15:45:14');

-- --------------------------------------------------------

--
-- Table structure for table `tb_chat_rooms`
--

CREATE TABLE `tb_chat_rooms` (
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `room_type` enum('trade','support') NOT NULL DEFAULT 'trade',
  `status` enum('active','closed') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_chat_rooms`
--

INSERT INTO `tb_chat_rooms` (`room_id`, `user_id`, `receiver_id`, `post_id`, `room_type`, `status`, `created_at`) VALUES
(1, 2, 1, 2, 'trade', 'active', '2026-09-28 17:24:05'),
(2, 6, 1, 2, 'trade', 'active', '2026-09-29 15:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `tb_games`
--

CREATE TABLE `tb_games` (
  `game_id` int(11) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `game_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_games`
--

INSERT INTO `tb_games` (`game_id`, `game_name`, `category_id`, `game_img`) VALUES
(1, 'Genshin Impact', NULL, ''),
(2, 'Minecraft', NULL, ''),
(3, 'Valorant', NULL, ''),
(4, 'ROV', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `tb_game_categories`
--

CREATE TABLE `tb_game_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_game_categories`
--

INSERT INTO `tb_game_categories` (`category_id`, `category_name`) VALUES
(1, '1'),
(2, 'valo'),
(3, '1'),
(4, 'valo');

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

--
-- Dumping data for table `tb_posts`
--

INSERT INTO `tb_posts` (`post_id`, `seller_id`, `game_id`, `post_title`, `post_detail`, `post_price`, `post_img`, `status`) VALUES
(2, 1, 2, 'minecraft', ';klklklk', 50, 'upload/post_1790590685_6aba3edd1570b.png', 'availble'),
(3, 1, 3, 'valorant', 'fafafadfadfa', 500, 'upload/post_1790672467_6abb7e531e0a6.png', 'availble');

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
(5, 'seller1', '$2y$10$2Pk858p1eW36smBwPoLPWuTOFaTt5mPz20UUHd191ZSHgswAxMaG.', 'seller@gmail.com', 'merchant'),
(6, 'merchant1', '$2y$10$k6lTHtcPM9CBF2IwhNpKwOzBhv4Td.dZ6XqvHo37GYZRQJKNq/4gm', 'fdsfsdgsgsg@gmail.com', 'merchant');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_chat_messages`
--
ALTER TABLE `tb_chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `tb_chat_rooms`
--
ALTER TABLE `tb_chat_rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `tb_games`
--
ALTER TABLE `tb_games`
  ADD PRIMARY KEY (`game_id`),
  ADD KEY `fk_games_category` (`category_id`);

--
-- Indexes for table `tb_game_categories`
--
ALTER TABLE `tb_game_categories`
  ADD PRIMARY KEY (`category_id`);

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
-- AUTO_INCREMENT for table `tb_chat_messages`
--
ALTER TABLE `tb_chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_chat_rooms`
--
ALTER TABLE `tb_chat_rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_games`
--
ALTER TABLE `tb_games`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_game_categories`
--
ALTER TABLE `tb_game_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_orders`
--
ALTER TABLE `tb_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_posts`
--
ALTER TABLE `tb_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_chat_messages`
--
ALTER TABLE `tb_chat_messages`
  ADD CONSTRAINT `fk_msg_room` FOREIGN KEY (`room_id`) REFERENCES `tb_chat_rooms` (`room_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `tb_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_chat_rooms`
--
ALTER TABLE `tb_chat_rooms`
  ADD CONSTRAINT `fk_chat_post` FOREIGN KEY (`post_id`) REFERENCES `tb_posts` (`post_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `tb_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_chat_user` FOREIGN KEY (`user_id`) REFERENCES `tb_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_games`
--
ALTER TABLE `tb_games`
  ADD CONSTRAINT `fk_games_category` FOREIGN KEY (`category_id`) REFERENCES `tb_game_categories` (`category_id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
