-- NOC Trouble Ticket System - Database Schema
-- MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS noc_ticket_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE noc_ticket_system;

-- ============================================================
-- Tickets table
-- ============================================================
CREATE TABLE IF NOT EXISTS tickets (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    tt_customer   VARCHAR(100)    NOT NULL,
    tt_tbg        VARCHAR(100)    NOT NULL,
    tt_description TEXT           NOT NULL,
    device_segment VARCHAR(100)   NOT NULL,
    regional      VARCHAR(100)    NOT NULL,
    vendor        VARCHAR(100)    NOT NULL,
    segment_problem VARCHAR(200)  NOT NULL,
    cid           VARCHAR(100)    NOT NULL,
    segment_length VARCHAR(100)   DEFAULT NULL,
    start_time    DATETIME        NOT NULL,
    resolved_time DATETIME        DEFAULT NULL,
    root_cause    TEXT            DEFAULT NULL,
    responsibility VARCHAR(200)   DEFAULT NULL,
    problem_coordinate VARCHAR(200) DEFAULT NULL,
    restoration_action TEXT       DEFAULT NULL,
    status        ENUM('Open','Progress','Escalated','Closed') NOT NULL DEFAULT 'Open',
    progress_log  TEXT            DEFAULT NULL,
    created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tt_customer (tt_customer),
    KEY idx_status (status),
    KEY idx_regional (regional),
    KEY idx_vendor (vendor),
    KEY idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Regional options table
-- ============================================================
CREATE TABLE IF NOT EXISTS regional_options (
    id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name   VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_regional_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Vendor options table
-- ============================================================
CREATE TABLE IF NOT EXISTS vendor_options (
    id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name   VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vendor_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed default options
-- ============================================================
INSERT IGNORE INTO regional_options (name) VALUES
    ('Regional 1 - Sumatera'),
    ('Regional 2 - Jabodetabek Banten'),
    ('Regional 3 - Jawa Barat'),
    ('Regional 4 - Jawa Tengah & DIY'),
    ('Regional 5 - Jawa Timur'),
    ('Regional 6 - Kalimantan'),
    ('Regional 7 - Sulawesi'),
    ('Regional 8 - Maluku & Papua'),
    ('Regional 9 - Bali & Nusa Tenggara');

INSERT IGNORE INTO vendor_options (name) VALUES
    ('Huawei'),
    ('Nokia'),
    ('Ericsson'),
    ('ZTE'),
    ('Ciena'),
    ('Fiberhome'),
    ('Coriant'),
    ('Infinera');
