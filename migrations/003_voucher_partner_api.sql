-- Partner a transaction ID u poukazu (API vydání + idempotence).

ALTER TABLE voucher
	ADD COLUMN partner_id INT UNSIGNED NULL COMMENT 'NULL = vydáno v administraci' AFTER note,
	ADD COLUMN transaction_id VARCHAR(64) NULL COMMENT 'ID transakce partnera; jen API' AFTER partner_id,
	ADD KEY idx_voucher_partner (partner_id),
	ADD UNIQUE KEY uq_voucher_partner_transaction (partner_id, transaction_id),
	ADD CONSTRAINT fk_voucher_partner FOREIGN KEY (partner_id) REFERENCES partner (id);
