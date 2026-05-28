-- Online shopping registration system Database Schema Initialization
-- Target: MySQL/MariaDB (fullstack_db)

CREATE DATABASE IF NOT EXISTS `fullstack_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fullstack_db`;

-- ── 1. Settings Table ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) UNIQUE NOT NULL,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Categories Table ────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) UNIQUE NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'fas fa-tag'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Products Table ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) UNIQUE NOT NULL,
  `short_description` TEXT NULL,
  `description` TEXT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `sale_price` DECIMAL(10,2) DEFAULT 0.00,
  `stock` INT DEFAULT 0,
  `sku` VARCHAR(50) UNIQUE NULL,
  `image` VARCHAR(255) NULL,
  `rating` DECIMAL(2,1) DEFAULT 0.0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Users Table ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NULL,
  `avatar` VARCHAR(255) DEFAULT 'default.png',
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. Cart Table ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Wishlist Table ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product_unique` (`user_id`, `product_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. Orders Table ────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `city_id` INT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) NOT NULL,
  `shipping` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`city_id`) REFERENCES `cities`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 8. Order Items Table ───────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT DEFAULT 1,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 9. Reviews Table ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `rating` INT NOT NULL,
  `comment` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 10. Messages Table ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 11. Notifications Table ────────────────────────────
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'fa-bell',
  `message` TEXT NOT NULL,
  `is_read` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 12. Cities Table ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `region` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Ethiopian cities
INSERT INTO `cities` (`name`, `region`) VALUES
('Wolkite', 'Southern Nations'),
('Addis Ababa', 'Addis Ababa'),
('Dire Dawa', 'Dire Dawa'),
('Mekelle', 'Tigray'),
('Bahir Dar', 'Amhara');


-- ═══════════════════════════════════════════════════════
-- DATA SEEDING
-- ═══════════════════════════════════════════════════════

-- ── Seed Global settings ───────────────────────────────
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'Online shopping registration system'),
('site_description', 'Premium Online Shopping Experience. We offer the best catalog at the best prices.'),
('contact_email', 'support@onlineshoppingregistrationsystem.com'),
('contact_phone', '+1 (555) 867-5309')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- ── Seed Categories ────────────────────────────────────
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`) VALUES
(1, 'Electronics', 'electronics', 'fas fa-laptop'),
(2, 'Fashion', 'fashion', 'fas fa-tshirt'),
(3, 'Home & Kitchen', 'home-kitchen', 'fas fa-couch'),
(4, 'Sports & Fitness', 'sports-fitness', 'fas fa-dumbbell'),
(5, 'Books & Media', 'books-media', 'fas fa-book')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `icon` = VALUES(`icon`);

-- ── Seed Products ──────────────────────────────────────
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `short_description`, `description`, `price`, `sale_price`, `stock`, `sku`, `image`, `rating`) VALUES
(1, 1, 'Pro Wireless Headset', 'pro-wireless-headset', 'Ultra low latency, crystal clear active noise canceling headphones.', 'Elevate your listening experience with the Pro Wireless Headset. Engineered with high-fidelity acoustic drivers and state-of-the-art Hybrid Active Noise Cancellation, it allows you to immerse yourself fully in music, gaming, or calls. A robust 40-hour rechargeable battery, low-latency Bluetooth connectivity, and soft memory foam ear cushions ensure ultimate reliability and comfort.', 199.99, 149.99, 25, 'SKU-HEADSET-PRO', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=60', 4.8),
(2, 1, 'Smart Fitness Watch X', 'smart-fitness-watch-x', 'Track your heart rate, sleep, steps, and sports metrics in style.', 'The Smart Fitness Watch X combines modern aesthetic styling with high-performance tracking. Featuring an AMOLED touch display, oxygen saturation monitoring, sleep stage logging, multi-sport activity auto-tracking, and a sleek waterproof alloy shell, it serves as the perfect day-to-day companion for busy lifestyles. Expect 7+ days of battery life on a single charge.', 129.99, 0.00, 15, 'SKU-WATCH-SMART', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=60', 4.5),
(3, 2, 'Classic Leather Jacket', 'classic-leather-jacket', 'Genuine, durable leather jacket with modern slim fit tailoring.', 'Crafted from hand-selected lambskin, this leather jacket offers supreme durability and soft wear from day one. Featuring double-stitched zippers, quilted polyester interior, robust lapel buttons, and multiple secure zip pockets, it brings a timeless, versatile edge to your casual wardrobe.', 299.99, 249.99, 10, 'SKU-JACKET-LEATHER', 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800&auto=format&fit=crop&q=60', 4.7),
(4, 3, 'Ergonomic Office Chair', 'ergonomic-office-chair', 'Breathable mesh back with adjustable lumbar, armrests, and recline.', 'Say goodbye to back strain with our premiere Ergonomic Office Chair. Built with a heavy-duty nylon base, highly responsive adjustable armrests, customizable headrest support, and breathable performance mesh backing, it adjusts dynamically to your natural posture. Ideal for home offices and long workspace hours.', 189.99, 169.99, 8, 'SKU-CHAIR-ERGONOMIC', 'https://images.unsplash.com/photo-1505797149-43b0069ec26b?w=800&auto=format&fit=crop&q=60', 4.3),
(5, 4, 'Premium Yoga Mat', 'premium-yoga-mat', 'Non-slip eco-friendly rubber yoga mat with alignment grid guides.', 'Practice your poses with confidence on this extra-thick, eco-friendly TPE foam yoga mat. Features double-sided non-slip grip grids, a laser-engraved central alignment guidance vector, and lightweight water-resistant density that is easy to roll up and carry to your workouts.', 49.99, 0.00, 40, 'SKU-YOGA-MAT', 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=800&auto=format&fit=crop&q=60', 4.6),
(6, 2, 'Minimalist Leather Wallet', 'minimalist-leather-wallet', 'RFID blocking credit card sleeve built from full grain cowhide.', 'Keep your essentials secure and low profile. Handcrafted from premium vegetable-tanned full-grain cowhide leather, this ultra-slim wallet features 6 card slots, a secure middle cash compartment, and embedded RFID blocking material to keep digital pickpockets at bay.', 39.99, 29.99, 50, 'SKU-WALLET-MIN', 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800&auto=format&fit=crop&q=60', 4.9),
(7, 1, 'Mechanical RGB Keyboard', 'mechanical-rgb-keyboard', 'Tactile blue switches with fully programmable backlighting macros.', 'Achieve high speed accuracy and clicky gratification. This full-sized keyboard boasts durable double-shot injection keycaps, tactile mechanical switches, an elegant brushed aluminum frame, full N-key rollover, and interactive customizable per-key RGB backlighting software.', 89.99, 79.99, 18, 'SKU-KEYBOARD-RGB', 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=60', 4.4),
(8, 3, 'Smart Coffee Grinder', 'smart-coffee-grinder', 'Precision conical burr grinder with digital timers and portion weight.', 'Grind your favorite coffee beans exactly how your brewer needs them. Engineered with high-strength stainless steel conical burrs, 40 distinct precision micro-adjustments, and a smart digital timer display, it delivers highly uniform grinds every morning from coarse French Press to fine Espresso.', 149.99, 0.00, 12, 'SKU-GRINDER-COFFEE', 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=800&auto=format&fit=crop&q=60', 4.7)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `price` = VALUES(`price`), `sale_price` = VALUES(`sale_price`), `stock` = VALUES(`stock`);

-- ── Seed Users ─────────────────────────────────────────
-- Passwords are 'admin123' and 'user123' respectively, hashed using standard bcrypt.
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `avatar`, `role`, `status`) VALUES
(1, 'admin', 'admin@onlineshoppingregistrationsystem.com', '$2y$10$8C5V4C1sM/aMep94.yVn/.H57Z9i/b13fEExbK4c5qFfHn98p4GKu', 'Victoria Vance', 'admin-avatar.png', 'admin', 'active'),
(2, 'johndoe', 'john@gmail.com', '$2y$10$8C5V4C1sM/aMep94.yVn/.H57Z9i/b13fEExbK4c5qFfHn98p4GKu', 'John Doe', 'default.png', 'user', 'active')
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`), `role` = VALUES(`role`), `status` = VALUES(`status`);
