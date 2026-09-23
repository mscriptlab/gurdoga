-- Satış noktaları (store locator) — simple, single-language list.
CREATE TABLE IF NOT EXISTS sales_points (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(190) NOT NULL,
  channel    VARCHAR(100) NOT NULL DEFAULT '',
  city       VARCHAR(100) NOT NULL DEFAULT '',
  district   VARCHAR(100) NOT NULL DEFAULT '',
  address    VARCHAR(400) NOT NULL DEFAULT '',
  phone      VARCHAR(60)  NOT NULL DEFAULT '',
  lat        DECIMAL(10,7) NULL,
  lng        DECIMAL(10,7) NULL,
  sort       INT          NOT NULL DEFAULT 0,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at DATETIME     NULL,
  updated_at DATETIME     NULL,
  INDEX (city), INDEX (channel), INDEX (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
