

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert a default admin account
INSERT INTO admins (email, password) VALUES ('admin@gmail.com', 'admin123');

CREATE TABLE deposits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE payment_methods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50),
    details TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

CREATE TABLE currencies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(3),
    symbol VARCHAR(5),
    rate DECIMAL(10,4)
);

INSERT INTO currencies (code, symbol, rate) VALUES 
('USD', '$', 1),
('NGN', '₦', 450.50),
('EUR', '€', 0.0011),
('GBP', '£', 0.00096),
('PHP', '₱', 56.50),
('ZAR', 'R', 18.75),
('ZMW', 'K', 19.85);


ALTER TABLE deposits 
ADD COLUMN currency_code VARCHAR(3) DEFAULT 'NGN';

ALTER TABLE deposits 
ADD COLUMN reference VARCHAR(100),
ADD COLUMN notes TEXT;

CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type ENUM('credit', 'debit'),
    amount DECIMAL(10,2),
    description TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type ENUM('credit', 'debit'),
    amount DECIMAL(10,2),
    description TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    currency_code VARCHAR(3) DEFAULT '',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE withdrawals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10,2),
    currency_code VARCHAR(3),
    payment_method VARCHAR(50),
    wallet_address TEXT,
    bank_details TEXT,
    status ENUM('pending', 'completed', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE loans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10,2),
    currency_code VARCHAR(3) DEFAULT 'NGN',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

ALTER TABLE users 
ADD COLUMN referral_code VARCHAR(50) UNIQUE,
ADD COLUMN referred_by VARCHAR(50);