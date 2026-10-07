-- initial_amount je duplicitní s balance, už se nikde nepoužívá
ALTER TABLE voucher DROP COLUMN initial_amount;
