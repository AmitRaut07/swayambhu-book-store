-- E-commerce Enhancement Schema Updates
-- Run this file to add new tables and columns for enhanced features

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create wishlist table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create ratings table
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create discount_codes table
CREATE TABLE IF NOT EXISTS discount_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount_percentage DECIMAL(5,2) NOT NULL,
    valid_from DATE,
    valid_until DATE,
    max_uses INT DEFAULT NULL,
    times_used INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add new columns to products table if they don't exist
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS category_id INT DEFAULT NULL,
ADD COLUMN IF NOT EXISTS featured BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS discount_percentage DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS description TEXT,
ADD FOREIGN KEY IF NOT EXISTS (category_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Insert some default categories
INSERT INTO categories (name, slug, description) VALUES
('Fiction', 'fiction', 'Fictional books and novels'),
('Non-Fiction', 'non-fiction', 'Non-fictional books'),
('Science', 'science', 'Science and technology books'),
('History', 'history', 'Historical books'),
('Biography', 'biography', 'Biographies and memoirs'),
('Self-Help', 'self-help', 'Self-help and personal development'),
('Children', 'children', 'Children\'s books')
ON DUPLICATE KEY UPDATE name=name;

-- Insert some sample discount codes
INSERT INTO discount_codes (code, discount_percentage, valid_from, valid_until, max_uses) VALUES
('WELCOME10', 10.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), NULL),
('SAVE20', 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH), 100),
('FIRST50', 50.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 MONTH), 50)
ON DUPLICATE KEY UPDATE code=code;
