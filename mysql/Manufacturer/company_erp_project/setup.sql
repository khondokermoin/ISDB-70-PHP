CREATE DATABASE IF NOT EXISTS company;
USE company;

DROP VIEW IF EXISTS view_invoice_report;
DROP VIEW IF EXISTS view_product;
DROP PROCEDURE IF EXISTS add_product;
DROP PROCEDURE IF EXISTS add_manufacture;
DROP TABLE IF EXISTS product_return;
DROP TABLE IF EXISTS invoice_item;
DROP TABLE IF EXISTS invoice;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS manufacturer;

CREATE TABLE manufacturer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_qty INT NOT NULL DEFAULT 0,
    manufacturer_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_manufacturer FOREIGN KEY (manufacturer_id)
        REFERENCES manufacturer(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE invoice (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(40) NOT NULL UNIQUE,
    customer_name VARCHAR(100) NOT NULL DEFAULT 'Walk-in Customer',
    sale_date DATETIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    returned_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE invoice_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_id INT NOT NULL,
    qty INT NOT NULL,
    returned_qty INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_invoice_item_invoice FOREIGN KEY (invoice_id)
        REFERENCES invoice(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_invoice_item_product FOREIGN KEY (product_id)
        REFERENCES product(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE product_return (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    invoice_item_id INT NOT NULL,
    product_id INT NOT NULL,
    return_qty INT NOT NULL,
    return_amount DECIMAL(10,2) NOT NULL,
    return_date DATETIME NOT NULL,
    reason VARCHAR(255) DEFAULT '',
    CONSTRAINT fk_return_invoice FOREIGN KEY (invoice_id)
        REFERENCES invoice(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_return_invoice_item FOREIGN KEY (invoice_item_id)
        REFERENCES invoice_item(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_return_product FOREIGN KEY (product_id)
        REFERENCES product(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
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
    IN p_stock INT,
    IN p_manufacturer_id INT
)
BEGIN
    INSERT INTO product(name, price, stock_qty, manufacturer_id)
    VALUES (p_name, p_price, p_stock, p_manufacturer_id);
END $$
DELIMITER ;

CREATE VIEW view_product AS
SELECT
    p.id,
    p.name,
    p.price,
    p.stock_qty,
    m.name AS manufacturer_name,
    m.contact AS manufacturer_contact,
    p.created_at
FROM product p
INNER JOIN manufacturer m ON p.manufacturer_id = m.id;

CREATE VIEW view_invoice_report AS
SELECT
    i.id,
    i.invoice_no,
    i.customer_name,
    i.sale_date,
    i.total_amount,
    i.returned_amount,
    (i.total_amount - i.returned_amount) AS net_total
FROM invoice i;

INSERT INTO manufacturer (name, contact) VALUES
('Walton', '01711111111'),
('Samsung', '01822222222'),
('Sony', '01933333333');

CALL add_product('Monitor', 12000, 15, 1);
CALL add_product('Mobile', 25000, 20, 2);
CALL add_product('Headphone', 3500, 30, 3);
