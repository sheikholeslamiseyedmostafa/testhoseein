-- ============================================================================
-- نرم‌افزار حسابداری - طرح پایگاه داده (MySQL 8.0+ / MariaDB 10.4+)
-- ایجاد پایگاه داده (یک بار، با کاربری که دسترسی ساخت دارد):
--   CREATE DATABASE accounting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- سپس با نصب‌کننده جداول را بسازید:  php db/install.php
-- ============================================================================

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL,
    full_name     VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(20)  NOT NULL,
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_username UNIQUE (username),
    CONSTRAINT chk_users_role CHECK (role IN ('admin', 'accountant', 'viewer'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS accounts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    code       VARCHAR(20)  NOT NULL,
    name       VARCHAR(150) NOT NULL,
    type       VARCHAR(20)  NOT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_accounts_code UNIQUE (code),
    CONSTRAINT chk_accounts_type CHECK (type IN ('asset', 'liability', 'equity', 'revenue', 'expense'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vouchers (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    voucher_no   INT UNSIGNED NOT NULL,
    voucher_date DATE         NOT NULL,
    description  VARCHAR(500) NOT NULL DEFAULT '',
    created_by   INT UNSIGNED NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NULL,
    CONSTRAINT uq_vouchers_no UNIQUE (voucher_no),
    CONSTRAINT fk_vouchers_user FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voucher_lines (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    voucher_id   INT UNSIGNED NOT NULL,
    account_id   INT UNSIGNED NOT NULL,
    line_no      INT          NOT NULL DEFAULT 0,
    description  VARCHAR(255) NOT NULL DEFAULT '',
    debit_cents  BIGINT       NOT NULL DEFAULT 0,
    credit_cents BIGINT       NOT NULL DEFAULT 0,
    CONSTRAINT fk_lines_voucher FOREIGN KEY (voucher_id) REFERENCES vouchers (id) ON DELETE CASCADE,
    CONSTRAINT fk_lines_account FOREIGN KEY (account_id) REFERENCES accounts (id),
    CONSTRAINT chk_lines_amount CHECK (debit_cents >= 0 AND credit_cents >= 0 AND (debit_cents = 0 OR credit_cents = 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ایندکس‌ها (نصب‌کننده خطای «already exists» را نادیده می‌گیرد)
CREATE INDEX idx_vouchers_date ON vouchers (voucher_date);
CREATE INDEX idx_lines_account ON voucher_lines (account_id);
CREATE INDEX idx_lines_voucher ON voucher_lines (voucher_id);
