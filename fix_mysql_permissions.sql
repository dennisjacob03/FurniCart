-- Fix MySQL/MariaDB User Permissions
-- Run this in phpMyAdmin SQL tab or MySQL command line

-- Check current users
SELECT user, host FROM mysql.user WHERE user = 'root';

-- Grant all privileges to root from localhost
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY '' WITH GRANT OPTION;

-- Grant all privileges to root from 127.0.0.1
GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' IDENTIFIED BY '' WITH GRANT OPTION;

-- If root@localhost doesn't exist, create it
CREATE USER IF NOT EXISTS 'root'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;

-- Flush privileges to apply changes
FLUSH PRIVILEGES;

-- Verify the changes
SELECT user, host FROM mysql.user WHERE user = 'root';
