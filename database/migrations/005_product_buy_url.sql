-- Per-product external "Satın Al" link (e.g. Lezzet Kurye product page)
ALTER TABLE products ADD COLUMN buy_url VARCHAR(500) NOT NULL DEFAULT '' AFTER sku;
