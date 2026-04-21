-- Create Database
CREATE DATABASE IF NOT EXISTS pos_db;
USE pos_db;

-- 1. Create Tables
CREATE TABLE IF NOT EXISTS Manufacturer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    address VARCHAR(100) NOT NULL,
    contact_no VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS Product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price INT(5) NOT NULL,
    manufacturer_id INT(10) NOT NULL
);

-- 2. Create Stored Procedure
DROP PROCEDURE IF EXISTS add_manufacturer;
DELIMITER //
CREATE PROCEDURE add_manufacturer(
    IN m_name VARCHAR(50),
    IN m_address VARCHAR(100),
    IN m_contact VARCHAR(50)
)
BEGIN
    INSERT INTO Manufacturer(name, address, contact_no)
    VALUES (m_name, m_address, m_contact);
END //
DELIMITER ;

-- 3. Create Trigger
DROP TRIGGER IF EXISTS after_manufacturer_delete;
DELIMITER //
CREATE TRIGGER after_manufacturer_delete
AFTER DELETE ON Manufacturer
FOR EACH ROW
BEGIN
    DELETE FROM Product WHERE manufacturer_id = OLD.id;
END //
DELIMITER ;

-- 4. Create View
DROP VIEW IF EXISTS expensive_products;
CREATE VIEW expensive_products AS
SELECT * FROM Product WHERE price > 5000;

-- Insert some dummy products to test the view
INSERT INTO Manufacturer (name, address, contact_no)
VALUES ('Tech Corp', 'Dhaka', '01700000000');

INSERT INTO Product (name, price, manufacturer_id)
VALUES ('Laptop', 45000, 1), ('Mouse', 800, 1), ('Monitor', 12000, 1);
