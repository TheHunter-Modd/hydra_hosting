-- ============================================================
--  hydra_p2p_db.sql
--  Run this once in phpMyAdmin to create the database.
-- ============================================================

CREATE DATABASE IF NOT EXISTS hydra_p2p_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hydra_p2p_db;

-- ── Users ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    username    VARCHAR(100) NOT NULL,
    email       VARCHAR(150)          DEFAULT NULL,
    phone       VARCHAR(20)           DEFAULT NULL,
    pwd         VARCHAR(255) NOT NULL,
    role        ENUM('trader','admin') NOT NULL DEFAULT 'trader',
    is_verified TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email),
    UNIQUE KEY uq_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Saved rates ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS rates (
    id         INT(11)        NOT NULL AUTO_INCREMENT,
    user_id    INT(11)        NOT NULL,
    rate       DECIMAL(12,4)  NOT NULL,
    mode       ENUM('buy','sell') NOT NULL DEFAULT 'buy',
    saved_at   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    CONSTRAINT fk_rates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Trades ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS trades (
    id          INT(11)        NOT NULL AUTO_INCREMENT,
    user_id     INT(11)        NOT NULL,
    amount_ngn  DECIMAL(15,2)  NOT NULL,
    usdt_qty    DECIMAL(15,6)  NOT NULL,
    rate        DECIMAL(12,4)  NOT NULL,
    mode        ENUM('buy','sell') NOT NULL,
    status      ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_at  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    CONSTRAINT fk_trades_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
