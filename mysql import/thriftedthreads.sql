-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 13, 2025 at 04:02 PM
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
-- Database: `thriftedthreads`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(13, 1, 7, 1, '2025-04-13 12:49:31', '2025-04-13 12:49:31'),
(15, 1, 8, 1, '2025-04-13 13:47:44', '2025-04-13 13:47:44');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `donation_method` varchar(20) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `donated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact` varchar(15) NOT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `full_name`, `email`, `item_description`, `donation_method`, `photo_path`, `donated_at`, `contact`, `status`) VALUES
(9, 'yes', 'joshua.angelo1001@gmail.com', 'asadasdsad', 'dropoff', 'donation_uploads/67fbaac320326_Snapinst.app_480396432_17858008299373107_6994601677136471545_n_1080.jpg', '2025-04-13 12:14:59', '23131313131', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `total_items` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock` int(11) NOT NULL DEFAULT 0,
  `material` varchar(255) DEFAULT NULL,
  `condition_note` varchar(255) DEFAULT NULL,
  `care_instructions` text DEFAULT NULL,
  `fit_style` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `created_at`, `stock`, `material`, `condition_note`, `care_instructions`, `fit_style`) VALUES
(7, 'test', 'hehe', 99999999.00, 'uploads/67fb98dc40400.jpg', '2025-04-13 10:58:36', 1, '100% Cotton', 'Minor Wear', 'hehe', 'Relaxed & Comfy'),
(8, 'test', 'asdasdadasd', 123.00, 'uploads/67fbb1e81dc27.jpg', '2025-04-13 12:45:28', 1, '100% Cotton', 'adsada', 'asdasd', '0');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'images/profile/default_profile.jpg',
  `contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `contact_number`, `password`, `created_at`, `address`, `profile_picture`, `contact`) VALUES
(1, 'Joshua Angelo Antiporda', 'joshua.angelo1001@gmail.com', '09934406760', '$2y$10$mwitkRe76GxjpNRkfVUp4.JXLDlNcgeKNtKVuP.L/rbS/KFLApCXi', '2025-04-09 06:49:27', 'hehe', 'images/profile/67f9fedcd446c_Snapinst.app_480396432_17858008299373107_6994601677136471545_n_1080.jpg', NULL),
(2, 'hehe', 'joshua.angelo10@gmail.com', NULL, '$2y$10$cyWbR0wi/.LeZKVkMSZGeO1c3Gs49sEesq4LMZ31KktSMx97ffKDi', '2025-04-09 06:52:06', NULL, 'images/profile/default_profile.jpg', NULL),
(3, 'hehe', 'asdas@gasfa.com', NULL, '$2y$10$6UmCR2jROt2zG/qBGgOAj.b09MRfkBfwcx8SGUD7Ge2/NGlj0V.WK', '2025-04-09 08:44:25', NULL, 'images/profile/default_profile.jpg', NULL),
(4, 'hehe', 'hehe@hehe.com', NULL, '$2y$10$hcIkO3ov8B4QB1iOhodh3OS5BfavGFM8WrkLO9cagfdMviN8dU222', '2025-04-09 08:51:34', NULL, 'images/profile/default_profile.jpg', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
