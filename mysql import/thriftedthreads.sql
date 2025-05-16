-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 16, 2025 at 08:26 AM
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
(11, 'nother', 'nhehe@heheh.com', 'hehez', 'dropoff', '../donation_uploads/68230de503173_Screenshot 2024-08-20 122300.png', '2025-05-13 09:16:21', '12312312312', 'pending'),
(12, 'dj khaled', 'dj@khaled.com', 'WE THE BEST MUSIC', 'pickup', '../donation_uploads/68230fcd7e897_Screenshot 2024-07-25 195612.png', '2025-05-13 09:24:29', '69696969696', 'pending');

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
  `order_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
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
(13, 'Vintage Jacket', 'A cool vintage jacket perfect for layering and adding a rugged yet stylish look.', 799.00, 'uploads/6826d63da10df.webp', '2025-05-16 06:07:57', 1, 'Denim, Cotton', 'Like New / Gently Used', 'Hand wash cold, hang dry.', 'Oversized Fit'),
(14, 'Denim Jeans', 'Classic vintage denim jeans with a distressed look, perfect for a casual yet stylish outfit.', 599.00, 'uploads/6826d6c06ebf7.webp', '2025-05-16 06:10:08', 1, 'Denim, Cotton, Spandex (for stretch)', 'Like New / Gently Used', 'Machine wash cold, inside out. Tumble dry low.', 'Slim Fit, High-Waisted'),
(15, 'Retro Sneakers', 'Iconic retro sneakers with a bold design, offering both comfort and a vintage aesthetic.', 1299.00, 'uploads/6826d766520e1.webp', '2025-05-16 06:12:54', 1, 'Leather, Suede, Rubber Sole', 'Like New / Gently Used', 'Wipe with a damp cloth. Air dry. Avoid direct sunlight.', 'True to Size, Chunky Sole'),
(16, 'Retro Watch', 'A timeless retro watch with a sleek design, perfect for both casual and formal wear.', 1499.00, 'uploads/6826d842e7dfb.webp', '2025-05-16 06:16:34', 1, 'Stainless Steel, Leather Strap', 'Like New / Gently Used', 'Wipe with a dry cloth. Avoid water exposure.', 'Adjustable Strap, Minimalist Dial'),
(17, 'Stylish Hat', 'A trendy hat that adds a chic touch to any outfit, great for sun protection and style.', 399.00, 'uploads/6826d90cc1094.webp', '2025-05-16 06:19:56', 1, 'Wool, Cotton Blend', 'Like New / Gently Used', 'Spot clean only. Air dry.', 'One Size Fits Most, Wide Brim'),
(18, 'Vintage Bag', 'A charming vintage bag with a unique patina, offering both functionality and retro appeal.', 899.00, 'uploads/6826d95ba29b2.webp', '2025-05-16 06:21:15', 1, 'Leather, Brass Hardware', 'Like New / Gently Used', 'Wipe with leather cleaner. Store in a dust bag.', 'Crossbody, Medium Size');

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
  `contact` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `remember_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `contact_number`, `password`, `created_at`, `address`, `profile_picture`, `contact`, `role`, `remember_token`) VALUES
(1, 'Joshua Angelo Antiporda', 'joshua.angelo1001@gmail.com', '09934406760', '$2y$10$mwitkRe76GxjpNRkfVUp4.JXLDlNcgeKNtKVuP.L/rbS/KFLApCXi', '2025-04-09 06:49:27', '123 Hehe St. Huhu B. Quezon City', 'images/profile/67f9fedcd446c_Snapinst.app_480396432_17858008299373107_6994601677136471545_n_1080.jpg', NULL, 'user', NULL),
(5, 'admin', 'admin@admin.com', NULL, '$2y$10$LAigQsMAst/ilbk18IF0hew3XdZlvy5V2INCxotdYo51DyvHzDZB6', '2025-05-13 06:09:11', NULL, 'images/profile/default_profile.jpg', '099334406760', 'admin', NULL),
(6, 'Erfen Monts', 'erfen.monts@gmail.com', NULL, '$2y$10$6pvyD10vm/YEACN4I6cYK.zaJ5tif1/ePRsQ0Dcum.okCJMcYJoWO', '2025-05-13 11:04:34', NULL, 'images/profile/default_profile.jpg', '09081379388', 'user', NULL);

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
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
