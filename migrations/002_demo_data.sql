-- Demo data. Data jsou relativní k dnešku, proto je spouštějte v den zkoušky (bin/migrate.php --fresh).
-- Heslo všech uživatelů: heslo123

INSERT INTO partner (id, name, api_key_hash) VALUES
	(1, 'Trafika Alfa', SHA2('alfa-test-key-123', 256)),
	(2, 'Pokladní síť Beta', SHA2('beta-test-key-456', 256));

INSERT INTO user (id, email, password_hash, role, partner_id) VALUES
	(1, 'admin@example.com', '$2y$10$v8ksnXF/kX8PixPzfKmuLePC1uunqixOMI5BB2V6ZEN2THq6V8xi2', 'admin', NULL),
	(2, 'alfa@example.com', '$2y$10$v8ksnXF/kX8PixPzfKmuLePC1uunqixOMI5BB2V6ZEN2THq6V8xi2', 'partner', 1),
	(3, 'beta@example.com', '$2y$10$v8ksnXF/kX8PixPzfKmuLePC1uunqixOMI5BB2V6ZEN2THq6V8xi2', 'partner', 2);

INSERT INTO voucher (id, code, initial_amount, balance, expires_at, status, customer_email, note, created_at) VALUES
	(1, 'HK-PLAT-2345', 100000, 100000, CURDATE() + INTERVAL 6 MONTH, 'active', 'jana.novakova@example.com', NULL, NOW() - INTERVAL 20 DAY),
	(2, 'HK-DNES-2346', 50000, 50000, CURDATE(), 'active', 'petr.svoboda@example.com', 'Dárek k narozeninám', NOW() - INTERVAL 1 YEAR),
	(3, 'HK-EXPR-2347', 200000, 150000, CURDATE() - INTERVAL 3 DAY, 'active', NULL, NULL, NOW() - INTERVAL 13 MONTH),
	(4, 'HK-ZABL-2348', 100000, 100000, CURDATE() + INTERVAL 3 MONTH, 'blocked', 'lucie.dvorakova@example.com', 'Zablokováno na žádost zákaznice, poukaz ztratila.', NOW() - INTERVAL 2 MONTH),
	(5, 'HK-NULA-2349', 30000, 0, CURDATE() + INTERVAL 2 MONTH, 'active', NULL, NULL, NOW() - INTERVAL 4 MONTH),
	(6, 'HK-K7MQ-R4TZ', 150000, 90000, CURDATE() + INTERVAL 9 MONTH, 'active', 'tomas.marek@example.com', 'Firemní benefit, oddělení IT.\nPředáno osobně na recepci.', NOW() - INTERVAL 40 DAY),
	(7, 'HK-W3PX-8NDA', 100000, 100000, CURDATE() + INTERVAL 1 YEAR, 'active', NULL, NULL, NOW() - INTERVAL 2 DAY),
	(8, 'HK-HJ6V-2CEB', 50000, 20000, CURDATE() + INTERVAL 1 MONTH, 'active', 'eva.kralova@example.com', NULL, NOW() - INTERVAL 5 MONTH);

INSERT INTO redemption (id, voucher_id, partner_id, user_id, amount, created_at) VALUES
	(1, 3, 1, NULL, 50000, NOW() - INTERVAL 2 MONTH),
	(2, 5, 2, NULL, 30000, NOW() - INTERVAL 3 MONTH),
	(3, 6, 1, NULL, 40000, NOW() - INTERVAL 20 DAY),
	(4, 6, 2, NULL, 20000, NOW() - INTERVAL 5 DAY),
	(5, 8, 2, NULL, 30000, NOW() - INTERVAL 1 MONTH);
