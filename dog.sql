-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2025 at 03:07 PM
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
-- Database: `dog`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoptions`
--

CREATE TABLE `adoptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dog_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoptions`
--

INSERT INTO `adoptions` (`id`, `user_id`, `dog_id`, `status`, `created_at`, `completed_at`, `archived`) VALUES
(163, 22, 106, 'completed', '2025-07-02 17:15:28', '2025-07-02 17:15:36', 0),
(164, 20, 104, 'completed', '2025-07-02 17:25:40', '2025-07-03 14:35:26', 0),
(166, 23, 108, 'completed', '2025-07-03 14:14:48', '2025-07-03 14:14:57', 0),
(168, 20, 105, 'pending', '2025-07-04 04:44:56', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `adoption_requests`
--

CREATE TABLE `adoption_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dog_name` varchar(100) NOT NULL,
  `dog_image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `breed` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `completed_at` datetime DEFAULT NULL,
  `dog_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoption_requests`
--

INSERT INTO `adoption_requests` (`id`, `user_id`, `dog_name`, `dog_image`, `description`, `status`, `created_at`, `breed`, `age`, `completed_at`, `dog_id`) VALUES
(62, 14, 'Hello', 'uploads/1751381984_german shepard.png', 'no', 'approved', '2025-07-01 14:59:44', 'Bice', 2, NULL, 84),
(63, 15, 'okay', 'uploads/1751382075_j.png', 'okay', 'approved', '2025-07-01 15:01:15', 'okay', 2, NULL, 85),
(68, 19, 'di', 'uploads/1751433406_j.png', 'huw', 'approved', '2025-07-02 05:16:46', 'di', 2, NULL, 90),
(71, 20, 'bu', 'uploads/1751433966_german shepard.png', 'dsaj', 'approved', '2025-07-02 05:26:06', 'bdu', 2, NULL, 93),
(72, 20, 'duu', 'uploads/1751434245_j.png', 'das', 'approved', '2025-07-02 05:30:45', 'a', 2, NULL, 94),
(73, 20, 'djals', 'uploads/1751434281_j.png', 'djasl', 'approved', '2025-07-02 05:31:21', 'askldj', 22, NULL, 95),
(74, 19, 'nice', 'uploads/1751434320_german shepard.png', 'okqy', 'approved', '2025-07-02 05:32:00', 'good', 2, NULL, 96),
(76, 14, 'Apple', 'uploads/1751472925_j.png', 'okaoy', 'approved', '2025-07-02 16:15:25', 'Apple', 21, NULL, 99),
(79, 20, 'Daisy', 'uploads/dog_686568cfa378b0.89074149_beagle.png', 'A cheerful and lively female Beagle who brightens every room.', 'approved', '2025-07-02 17:13:51', 'Beagle', 6, NULL, 107),
(80, 20, 'Shadow', 'uploads/dog_68656f3b1fc481.10065024_siberian husky.png', 'A quiet and observant male Siberian Husky who forms deep bonds.', 'approved', '2025-07-02 17:41:15', 'Siberian Husky', 1, NULL, 108),
(81, 23, 'Leo', 'uploads/dog_68668f31198c74.56978263_labrador retriever.png', 'leo', 'approved', '2025-07-03 14:09:53', 'Golden Retriever', 9, NULL, 109),
(82, 23, 'Shadow', 'uploads/dog_686690f358f768.78546229_siberian husky.png', 'Male', 'approved', '2025-07-03 14:17:23', 'Siberian Husky', 7, NULL, 110);

-- --------------------------------------------------------

--
-- Table structure for table `breed_inquiries`
--

CREATE TABLE `breed_inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `breed_name` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dogs`
--

CREATE TABLE `dogs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `breed` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `listed_by` int(11) DEFAULT NULL,
  `adoption_status` enum('available','pending','adopted') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dogs`
--

INSERT INTO `dogs` (`id`, `name`, `breed`, `age`, `description`, `image`, `listed_by`, `adoption_status`) VALUES
(102, 'Max', 'Labrador Retriever', 10, 'A playful and loyal male dog who loves outdoor adventures.\r\n\r\n', 'uploads/dog_686564e970d452.28105758.png', 2, 'available'),
(103, 'Bella', 'Golden Retriever', 5, 'A gentle and affectionate female Golden Retriever with a calm temperament.\r\n\r\n', 'uploads/dog_6865655427b912.41008880.png', 2, 'available'),
(104, 'Rocky', 'German Shepherd', 4, 'A strong and energetic male German Shepherd who’s always ready to protect.\r\n\r\n', 'uploads/dog_68656577958584.28017723.png', 2, 'pending'),
(105, 'Luna', 'Border Collie', 7, 'A curious and intelligent female Border Collie who enjoys cuddles.\r\n\r\n', 'uploads/dog_686566022950e7.51621211.png', 2, 'pending'),
(106, 'Bruno', 'Rottweiler', 2, 'A brave and friendly male Rottweiler with a big heart.\r\n\r\n', 'uploads/dog_68656676df1394.27776168.png', 2, 'pending'),
(107, 'Daisy', 'Beagle', 6, 'A cheerful and lively female Beagle who brightens every room.', 'uploads/dog_686568cfa378b0.89074149_beagle.png', 20, 'available'),
(108, 'Shadow', 'Siberian Husky', 1, 'A quiet and observant male Siberian Husky who forms deep bonds.', 'uploads/dog_68656f3b1fc481.10065024_siberian husky.png', 20, 'pending'),
(109, 'Leo', 'Golden Retriever', 9, 'leo', 'uploads/dog_68668f31198c74.56978263_labrador retriever.png', 23, 'available'),
(110, 'Shadow', 'Siberian Husky', 7, 'Male', 'uploads/dog_686690f358f768.78546229_siberian husky.png', 23, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `breed` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','replied') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `user_id`, `breed`, `message`, `reply`, `date`, `status`, `created_at`) VALUES
(23, 8, 'Doberman Pinscher', 'I\'m interested in adopting dajk.\r\n\r\n', NULL, '2025-06-07 04:29:16', 'pending', '2025-06-07 04:29:16'),
(24, 7, 'Labrador Retriever', 'I\'m interested in adopting Buddy.\r\n\r\n', NULL, '2025-06-07 14:25:27', 'pending', '2025-06-07 14:25:27'),
(31, 14, 'Apple', 'I\'m interested in adopting Apple.\r\n\r\n', NULL, '2025-07-01 16:06:18', 'pending', '2025-07-01 16:06:18'),
(32, 14, 'Apple', 'I\'m interested in adopting Apple.\r\n\r\n', NULL, '2025-07-01 16:10:24', 'pending', '2025-07-01 16:10:24'),
(33, 17, 'Apple', 'I\'m interested in adopting Apple.\r\n\r\n', NULL, '2025-07-02 03:34:28', 'pending', '2025-07-02 03:34:28'),
(34, 20, 'di', 'I\'m interested in adopting di.\r\n\r\n', NULL, '2025-07-02 05:23:23', 'pending', '2025-07-02 05:23:23'),
(35, 20, 'Bulldog', 'I\'m interested in adopting buce.\r\n\r\n', NULL, '2025-07-02 07:46:34', 'pending', '2025-07-02 07:46:34'),
(36, 23, 'Border Collie', 'I\'m interested in adopting Luna.\r\n\r\n', NULL, '2025-07-03 14:08:11', 'pending', '2025-07-03 14:08:11'),
(37, 23, 'Siberian Husky', 'I\'m interested in adopting Shadow.\r\nAvailable?\r\n', NULL, '2025-07-03 14:08:26', 'pending', '2025-07-03 14:08:26'),
(38, 20, 'Border Collie', 'I\'m interested in adopting Luna.\r\nHas it been vaccinated?\r\n', NULL, '2025-07-04 04:45:43', 'pending', '2025-07-04 04:45:43');

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_replies`
--

CREATE TABLE `inquiry_replies` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `sender` enum('admin','user') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiry_replies`
--

INSERT INTO `inquiry_replies` (`id`, `inquiry_id`, `sender`, `message`, `created_at`) VALUES
(30, 23, 'admin', 'okay', '2025-06-07 04:31:43'),
(31, 24, 'admin', 'ok', '2025-06-07 14:25:46'),
(38, 31, 'admin', 'q', '2025-07-01 16:06:26'),
(39, 35, 'admin', 'okay', '2025-07-02 07:49:14'),
(40, 37, 'admin', 'ok', '2025-07-03 14:15:58'),
(41, 36, 'admin', 'ok', '2025-07-03 14:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_status` enum('pending','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `is_cancelled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `product_id`, `quantity`, `total_price`, `shipping_address`, `payment_method`, `status`, `created_at`, `delivery_status`, `is_cancelled`) VALUES
(38, 14, 8, 10, 2000.00, 'ne', 'paynow', 'paid', '2025-06-29 10:11:51', 'delivered', 0),
(39, 14, 8, 1, 200.00, 'snaj', 'mobile_banking', 'paid', '2025-06-29 10:15:24', 'pending', 0),
(40, 16, 8, 1, 200.00, 'ok', 'cash_on_delivery', 'paid', '2025-07-01 15:09:19', 'shipped', 0),
(41, 16, 9, 1, 300.00, 'ok', 'cash_on_delivery', 'paid', '2025-07-01 15:09:19', 'pending', 0),
(43, 16, 8, 21, 4200.00, 'pl', 'paynow', 'paid', '2025-07-01 15:10:24', 'pending', 1),
(44, 16, 14, 10, 2000.00, 'ok', 'cash_on_delivery', 'paid', '2025-07-01 15:12:20', 'delivered', 0),
(45, 17, 8, 2, 400.00, 'kathmandu', 'paynow', 'paid', '2025-07-02 03:35:54', 'delivered', 0),
(46, 17, 8, 3, 600.00, 'sqw', 'cash_on_delivery', 'paid', '2025-07-02 03:48:43', 'pending', 0),
(47, 20, 8, 1, 200.00, 'dsa', 'paynow', 'paid', '2025-07-02 07:50:52', 'delivered', 0),
(48, 23, 8, 1, 200.00, 'ok', 'cash_on_delivery', 'paid', '2025-07-03 14:10:41', 'delivered', 0),
(49, 23, 9, 1, 300.00, 'ok', 'cash_on_delivery', 'paid', '2025-07-03 14:10:41', 'cancelled', 0),
(50, 23, 14, 10, 2000.00, 'ok', 'cash_on_delivery', 'paid', '2025-07-03 14:10:41', 'cancelled', 1),
(51, 20, 8, 1, 200.00, 'jh', 'paynow', 'paid', '2025-07-04 03:15:33', 'pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `listed_by` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `category`, `listed_by`, `quantity`) VALUES
(8, 'Pedigree', 'Food for dog', 200.00, 'assets/images/products/683d33c4b7dee.png', 'Food', 2, 37),
(9, 'Royal Canin Mini Puppy	', 'foods', 300.00, 'assets/images/products/683d36699eb8a.png', 'Food', 2, 994),
(14, 'Apple', 'Apple', 200.00, 'assets/images/products/6863fab7d177c.png', 'Food', 2, 90);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `reset_token`, `reset_expires`) VALUES
(2, 'Jenish', 'jnishxrestha@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bEdSOS9iQndwOTJVei81Zw$lS0udronvLL/p2JwtJ+0FOYvbYqFnr2FfszA9nwJI4c', 'admin', '629269', '2025-05-25 05:02:09'),
(3, 'Jnish', 'jnishxrestha1@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$T0Jkd3dpZ0VlRGI4L0hndQ$m2ZS/NnScu3tKDHik6EbvaUTsLv5i0tK72oNQEatMBU', 'user', NULL, NULL),
(6, 'Zenith', 'zenith123@gmail.com', '$2y$10$gQ7rzYrF2zBMfT1QksVIUuGJAqgQKHeGMcVF88Fi7u.cF2zGIcCJC', 'user', NULL, NULL),
(7, 'jinash', 'jinash.shrestha@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$RkhVQ0Jla0hUZEJKaTJpaA$etw1uOD8ItNHx5hS8nUq+rd6zXvLE8nvhlWxYYOCs+w', 'user', NULL, NULL),
(8, 'Zenith', 'zenith@gmail.com', '$2y$10$LeZDUgBzZdGXcShn3a/uleAtWIo1PeWJiJkPCTKAHS/Fsm36a.jTC', 'user', NULL, NULL),
(11, 'Qwert', 'qwert@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$WlRoTWxJMzdNRWp5R28uTQ$2mO2K4eYZksdOtTuzwUYsPFhdUg8Jbpx88ckkjUpSAE', 'user', NULL, NULL),
(12, 'Suroj', 'suroj@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bEMzVW1MWUsxbWM3OXU1bw$eNmz/KhZyLvAUyNjLltjIDAnMLSkN4akhSozwwN3q1Q', 'user', NULL, NULL),
(13, 'Zenith', 'jnishxresth@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$WGhqL0huQVJUd0s3OWVRVQ$pm6Fmc9NyRLXCM6BKRaIE7RgzP1GzBeY/TYr3NvCaHk', 'user', NULL, NULL),
(14, 'All', 'all@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$YjFTdkgyLk5GUmltVDFUNA$4jve7Hv+nlPQGVlo0DAofeF8c6D9OXfvxhdQWoPF3gQ', 'user', NULL, NULL),
(15, 'j', 'jnishxrestha11@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$ZmJuZnQ0N1FRSEtsNHBTeQ$o5W5jf2f6dUO2XjrUcz34YsljGdCZe+5fgc5SMX4Bg4', 'user', NULL, NULL),
(16, 'jin', 'jin@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$LzhXRTdJajhodFBXT0I1ZQ$ZJUqncIu4w7F50Ewk8qkQWeilqAOl+MTbd8z8V8PpQI', 'user', NULL, NULL),
(17, 'Bishal', 'ghartimagarbishal87@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$ZUtGUjVmWkxaOXFuMFRyMw$en4xbh5xHjTMMoBgER+GVDHzwjExiESULQtwrXvMCGk', 'user', NULL, NULL),
(18, 'Jenish', 'j@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$b3RLeG13WXVvdmV2WjNCbw$TzvhCG2kvV4ROpUm9DknUAy4l9HBC5FDBlB0+/GwyzY', 'user', NULL, NULL),
(19, 'dsn mdr', 'dsn123@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bE1Nc3R6bFRGUEh4Zllrdw$y6BDLJF9TlKVpdRoyji0vfmTHaFwF6OtCrXW9Ktc5Dg', 'user', NULL, NULL),
(20, 'Jenish', 'jnishxrestha2@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$QWpvWTdOMzNwWlhYak4yTA$NSI0fjGOYJL8HvYWa6kMCkgxYYqwCd4vdyjGPF950TM', 'user', NULL, NULL),
(21, 'Zenith', 'znith@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$V1VBNkdXVEouN2F1enRvMg$EcboXOcaQrTuKl2v3f9DHptSI/xrwdPcc6n9jRh0IEM', 'user', NULL, NULL),
(22, 'Jnish', 'jnishxrestha3@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$N3lzVGtKdmw4aDFudXZGZg$/GojCiB5Kq2eoC2u1NXTD/sm3RSyE2HygpwzXDbba18', 'user', NULL, NULL),
(23, 'testdog', 'dog@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$STBtdm9zYy9Oc1BrM2RFWQ$hWAH7y01c2k1yDtStvKhivIu20N6gBKlC4HEoxrbaFM', 'user', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `dog_id` (`dog_id`),
  ADD KEY `idx_adoptions_dog_id` (`dog_id`),
  ADD KEY `idx_adoptions_user_id` (`user_id`),
  ADD KEY `idx_adoptions_status` (`status`);

--
-- Indexes for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `breed_inquiries`
--
ALTER TABLE `breed_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `cart_ibfk_2` (`product_id`);

--
-- Indexes for table `dogs`
--
ALTER TABLE `dogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listed_by` (`listed_by`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `inquiry_replies`
--
ALTER TABLE `inquiry_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inquiry_id` (`inquiry_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listed_by` (`listed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adoptions`
--
ALTER TABLE `adoptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `breed_inquiries`
--
ALTER TABLE `breed_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dogs`
--
ALTER TABLE `dogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `inquiry_replies`
--
ALTER TABLE `inquiry_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD CONSTRAINT `adoptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `adoptions_ibfk_2` FOREIGN KEY (`dog_id`) REFERENCES `dogs` (`id`);

--
-- Constraints for table `adoption_requests`
--
ALTER TABLE `adoption_requests`
  ADD CONSTRAINT `adoption_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `breed_inquiries`
--
ALTER TABLE `breed_inquiries`
  ADD CONSTRAINT `breed_inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dogs`
--
ALTER TABLE `dogs`
  ADD CONSTRAINT `dogs_ibfk_1` FOREIGN KEY (`listed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `inquiry_replies`
--
ALTER TABLE `inquiry_replies`
  ADD CONSTRAINT `inquiry_replies_ibfk_1` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`listed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
