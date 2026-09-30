-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2026 at 10:45 AM
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
-- Database: `plantshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Indoor', 'indoor'),
(2, 'Succulents', 'succulents'),
(3, 'Flowering', 'flowering'),
(4, 'Air Purifying', 'air-purifying');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','shipped','delivered') NOT NULL DEFAULT 'pending',
  `shipping_name` varchar(100) NOT NULL,
  `shipping_email` varchar(150) NOT NULL,
  `shipping_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `light` varchar(50) DEFAULT NULL,
  `water` varchar(50) DEFAULT NULL,
  `pot_size` varchar(30) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(150) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `light`, `water`, `pot_size`, `price`, `stock`, `image`, `featured`, `created_at`) VALUES
(1, 1, 'Monstera Deliciosa', 'monstera-deliciosa', 'The iconic Swiss cheese plant with dramatic split leaves. Loves bright, indirect light.', 'Bright indirect', 'Weekly', '6 inch', 32.00, 15, 'monstera-deliciosa.jpeg', 1, '2026-09-17 04:36:18'),
(2, 1, 'Fiddle Leaf Fig', 'fiddle-leaf-fig', 'A statement plant with large violin-shaped leaves. A favourite for bright living rooms.', 'Bright indirect', 'Weekly', '10 inch', 48.00, 8, 'fiddle-leaf-fig.jpeg', 1, '2026-09-17 04:36:18'),
(3, 1, 'Golden Pothos', 'golden-pothos', 'Easy-care trailing vine with heart-shaped variegated leaves. Tolerates low light.', 'Low to bright', 'Every 10 days', '6 inch', 22.00, 30, 'golden-pothos.jpeg', 0, '2026-09-17 04:36:18'),
(4, 1, 'Snake Plant', 'snake-plant', 'Architectural upright leaves that thrive on neglect. Perfect for beginners.', 'Low to bright', 'Every 2-3 weeks', '8 inch', 28.00, 20, 'snake-plant.jpeg', 1, '2026-09-17 04:36:18'),
(5, 2, 'Echeveria Elegans', 'echeveria-elegans', 'Classic rosette-shaped succulent with powdery blue-green leaves.', 'Bright direct', 'Every 2 weeks', '4 inch', 12.00, 40, 'echeveria-elegans.jpeg', 0, '2026-09-17 04:36:18'),
(6, 2, 'Aloe Vera', 'aloe-vera', 'Soothing gel-filled succulent. Thrives in sunny windowsills.', 'Bright direct', 'Every 2-3 weeks', '6 inch', 16.00, 25, 'aloe-vera.jpeg', 0, '2026-09-17 04:36:18'),
(7, 2, 'Jade Plant', 'jade-plant', 'Money plant with glossy oval leaves. Symbol of good fortune.', 'Bright direct', 'Every 2 weeks', '5 inch', 14.00, 22, 'jade-plant.jpeg', 0, '2026-09-17 04:36:18'),
(8, 3, 'Peace Lily', 'peace-lily', 'Elegant white blooms and glossy green leaves. Also purifies the air.', 'Medium indirect', 'Weekly', '6 inch', 34.00, 12, 'peace-lily.jpeg', 1, '2026-09-17 04:36:18'),
(9, 3, 'Anthurium Red', 'anthurium-red', 'Waxy red heart-shaped blooms that last for weeks.', 'Bright indirect', 'Weekly', '6 inch', 38.00, 10, 'anthurium-red.jpeg', 0, '2026-09-17 04:36:18'),
(10, 3, 'Phalaenopsis Orchid', 'phalaenopsis-orchid', 'Long-lasting elegant blooms on graceful arching stems.', 'Medium indirect', 'Weekly (ice cube)', '5 inch', 42.00, 6, 'phalaenopsis-orchid.jpeg', 1, '2026-09-17 04:36:18'),
(11, 4, 'Boston Fern', 'boston-fern', 'Lush arching fronds. Known for filtering formaldehyde from the air.', 'Medium indirect', 'Twice weekly', '8 inch', 26.00, 18, 'boston-fern.jpeg', 0, '2026-09-17 04:36:18'),
(12, 4, 'Spider Plant', 'spider-plant', 'Fast-growing, air-purifying plant with striped leaves and baby offshoots.', 'Bright indirect', 'Weekly', '6 inch', 18.00, 24, 'spider-plant.jpeg', 0, '2026-09-17 04:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'aa', 'aa@gmail.com', '$2y$10$yHcEkIMZtXR8WP6PzV./IeshtDJIlN.ROTc/D2eTTRC7MfYtcSKfu', 'customer', '2026-09-30 08:15:54'),
(3, 'Admin', 'admin@plantology.com', '$2y$10$Q7Ybso/sqcDp7zKNCQCqHu3QkO/xQ5e42qelWbc1NLVWfaovek3gy', 'admin', '2026-09-30 08:21:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
