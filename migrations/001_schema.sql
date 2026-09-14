-- Základní schéma. Všechny částky jsou v haléřích.

CREATE TABLE partner (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	name VARCHAR(100) NOT NULL,
	api_key_hash CHAR(64) NOT NULL COMMENT 'SHA-256 API klíče',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY uq_partner_api_key_hash (api_key_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE user (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	email VARCHAR(190) NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	role ENUM('admin', 'partner') NOT NULL,
	partner_id INT UNSIGNED NULL,
	PRIMARY KEY (id),
	UNIQUE KEY uq_user_email (email),
	CONSTRAINT fk_user_partner FOREIGN KEY (partner_id) REFERENCES partner (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE voucher (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	code VARCHAR(20) NOT NULL,
	initial_amount INT UNSIGNED NOT NULL,
	balance INT UNSIGNED NOT NULL,
	expires_at DATE NOT NULL COMMENT 'poukaz platí včetně tohoto dne',
	status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
	customer_email VARCHAR(190) NULL,
	note TEXT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	UNIQUE KEY uq_voucher_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

CREATE TABLE redemption (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	voucher_id INT UNSIGNED NOT NULL,
	partner_id INT UNSIGNED NULL COMMENT 'NULL = uplatněno v administraci',
	user_id INT UNSIGNED NULL,
	amount INT UNSIGNED NOT NULL,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY idx_redemption_voucher (voucher_id),
	KEY idx_redemption_partner_created (partner_id, created_at),
	CONSTRAINT fk_redemption_voucher FOREIGN KEY (voucher_id) REFERENCES voucher (id),
	CONSTRAINT fk_redemption_partner FOREIGN KEY (partner_id) REFERENCES partner (id),
	CONSTRAINT fk_redemption_user FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;
