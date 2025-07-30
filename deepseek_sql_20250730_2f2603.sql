CREATE DATABASE IF NOT EXISTS matcha_moami;
USE matcha_moami;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    image VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(50) NOT NULL,
    date DATETIME NOT NULL,
    items TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL
);

-- Insert sample products
INSERT INTO products (name, price, stock, image) VALUES 
('Basic Matcha', 45000, 10, 'https://via.placeholder.com/150/2a5a2a/FFFFFF?text=Basic+Matcha'),
('Premium Matcha', 60000, 10, 'https://via.placeholder.com/150/3a7a3a/FFFFFF?text=Premium+Matcha'),
('Deluxe Matcha', 75000, 10, 'https://via.placeholder.com/150/4a9a4a/FFFFFF?text=Deluxe+Matcha');