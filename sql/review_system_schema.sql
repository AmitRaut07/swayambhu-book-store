-- Complete Database Schema for Review/Comment System
-- Run this SQL file to set up all necessary tables
-- UPDATED: Uses 'qty' column name instead of 'quantity'

-- 1. RATINGS TABLE (for product reviews and ratings)
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `rating` TINYINT(1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `review` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product` (`user_id`, `product_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ORDERS TABLE (for tracking customer orders)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `shipping_address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. ORDER_ITEMS TABLE (for tracking products in each order)
-- IMPORTANT: Column is named 'qty' not 'quantity'
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `qty` INT(11) NOT NULL DEFAULT 1,
  `price` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Add description column to products table if it doesn't exist
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL AFTER `author`;

-- NOTES:
-- 1. The 'ratings' table stores user reviews with 1-5 star ratings
-- 2. The UNIQUE constraint ensures one review per user per product
-- 3. The 'orders' table tracks customer purchases
-- 4. The 'order_items' table uses 'qty' column (not 'quantity')
-- 5. Status values: 'pending', 'completed', 'delivered', 'cancelled'
