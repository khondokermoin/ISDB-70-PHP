-- =====================================================
-- Watch Inventory — ডাটাবেজ সেটআপ SQL
-- phpMyAdmin বা MySQL CLI তে রান করুন
-- =====================================================

CREATE DATABASE IF NOT EXISTS watch_inventory
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE watch_inventory;

-- ঘড়ির মূল তথ্য
CREATE TABLE IF NOT EXISTS watches (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(150)   NOT NULL,
    model         VARCHAR(100)   NOT NULL,
    brand         VARCHAR(80)    DEFAULT NULL,
    buying_price  DECIMAL(10,2)  NOT NULL DEFAULT 0,
    selling_price DECIMAL(10,2)  NOT NULL DEFAULT 0,
    description   TEXT           DEFAULT NULL,
    quantity      INT            NOT NULL DEFAULT 0,
    created_at    DATETIME       DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_brand (brand),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ঘড়ির ছবি (একটি ঘড়িতে একাধিক ছবি)
CREATE TABLE IF NOT EXISTS watch_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    watch_id   INT UNSIGNED NOT NULL,
    image_url  VARCHAR(255) NOT NULL,
    sort_order TINYINT      NOT NULL DEFAULT 0,
    FOREIGN KEY (watch_id) REFERENCES watches(id) ON DELETE CASCADE,
    INDEX idx_watch (watch_id)
) ENGINE=InnoDB;

-- অ্যাডমিন ইউজার
CREATE TABLE IF NOT EXISTS admins (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- প্রথম অ্যাডমিন তৈরি করতে ব্রাউজারে যান:
-- http://localhost/watch-app/admin/create_admin.php?token=CHANGE_THIS_TO_A_RANDOM_STRING_12345
-- (create_admin.php ফাইলে SETUP_TOKEN এবং পাসওয়ার্ড পরিবর্তন করুন)
-- =====================================================
