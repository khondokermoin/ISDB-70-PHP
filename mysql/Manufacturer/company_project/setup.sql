CREATE DATABASE IF NOT EXISTS company;
USE company;

DROP VIEW IF EXISTS view_product;
DROP TRIGGER IF EXISTS after_manufacturer_delete;
DROP PROCEDURE IF EXISTS add_manufacture;
DROP PROCEDURE IF EXISTS add_product;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS manufacturer;

CREATE TABLE manufacturer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL
);

CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    manufacturer_id INT NOT NULL,
    INDEX (manufacturer_id)
);

DELIMITER $$
CREATE PROCEDURE add_manufacture(
    IN p_name VARCHAR(100),
    IN p_contact VARCHAR(20)
)
BEGIN
    INSERT INTO manufacturer(name, contact)
    VALUES (p_name, p_contact);
END $$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE add_product(
    IN p_name VARCHAR(100),
    IN p_price DECIMAL(10,2),
    IN p_mid INT
)
BEGIN
    INSERT INTO product(name, price, manufacturer_id)
    VALUES (p_name, p_price, p_mid);
END $$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER after_manufacturer_delete
AFTER DELETE ON manufacturer
FOR EACH ROW
BEGIN
    DELETE FROM product WHERE manufacturer_id = OLD.id;
END $$
DELIMITER ;

CREATE VIEW view_product AS
SELECT
    p.id,
    p.name,
    p.price,
    m.name AS manufacturer_name,
    m.contact AS manufacturer_contact
FROM product p
INNER JOIN manufacturer m ON p.manufacturer_id = m.id;

INSERT INTO manufacturer (name, contact) VALUES
('Walton', '01711111111'),
('Samsung', '01822222222');

INSERT INTO product (name, price, manufacturer_id) VALUES
('Monitor', 12000, 1),
('Mobile', 25000, 2);
