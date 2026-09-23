-- Zoen Tekstil — schema
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

CREATE TABLE IF NOT EXISTS languages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code        VARCHAR(10)  NOT NULL UNIQUE,
  name        VARCHAR(60)  NOT NULL,
  locale      VARCHAR(20)  NOT NULL DEFAULT '',
  is_default  TINYINT(1)   NOT NULL DEFAULT 0,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  sort        INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  last_login    DATETIME     NULL,
  created_at    DATETIME     NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS categories (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id  INT UNSIGNED NULL,
  slug       VARCHAR(190) NOT NULL UNIQUE,
  image      VARCHAR(255) NOT NULL DEFAULT '',
  sort       INT          NOT NULL DEFAULT 0,
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at DATETIME     NULL,
  updated_at DATETIME     NULL,
  INDEX (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS category_translations (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id      INT UNSIGNED NOT NULL,
  language_id      INT UNSIGNED NOT NULL,
  slug             VARCHAR(190) NOT NULL DEFAULT '',
  name             VARCHAR(190) NOT NULL DEFAULT '',
  description      TEXT NULL,
  meta_title       VARCHAR(190) NOT NULL DEFAULT '',
  meta_description VARCHAR(300) NOT NULL DEFAULT '',
  UNIQUE KEY uq_cat_lang (category_id, language_id),
  INDEX (language_id),
  INDEX idx_ct_slug (language_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS products (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NULL,
  sku         VARCHAR(80)  NOT NULL DEFAULT '',
  buy_url     VARCHAR(500) NOT NULL DEFAULT '',
  cover_image VARCHAR(255) NOT NULL DEFAULT '',
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  is_featured TINYINT(1)   NOT NULL DEFAULT 0,
  sort        INT          NOT NULL DEFAULT 0,
  created_at  DATETIME     NULL,
  updated_at  DATETIME     NULL,
  INDEX (category_id), INDEX (is_active), INDEX (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS product_translations (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id       INT UNSIGNED NOT NULL,
  language_id      INT UNSIGNED NOT NULL,
  slug             VARCHAR(190) NOT NULL DEFAULT '',
  name             VARCHAR(190) NOT NULL DEFAULT '',
  short_desc       VARCHAR(500) NOT NULL DEFAULT '',
  description      MEDIUMTEXT NULL,
  meta_title       VARCHAR(190) NOT NULL DEFAULT '',
  meta_description VARCHAR(300) NOT NULL DEFAULT '',
  UNIQUE KEY uq_prod_lang (product_id, language_id),
  UNIQUE KEY uq_slug_lang (slug, language_id),
  INDEX (language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS product_images (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id INT UNSIGNED NOT NULL,
  path       VARCHAR(255) NOT NULL,
  alt        VARCHAR(190) NOT NULL DEFAULT '',
  sort       INT NOT NULL DEFAULT 0,
  INDEX (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS product_attributes (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id  INT UNSIGNED NOT NULL,
  language_id INT UNSIGNED NOT NULL,
  label       VARCHAR(120) NOT NULL,
  value       VARCHAR(255) NOT NULL DEFAULT '',
  INDEX (product_id, language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS pages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug       VARCHAR(190) NOT NULL UNIQUE,
  template   VARCHAR(50)  NOT NULL DEFAULT 'default',
  nav_placement VARCHAR(20) NOT NULL DEFAULT 'corporate',
  is_active  TINYINT(1)   NOT NULL DEFAULT 1,
  sort       INT          NOT NULL DEFAULT 0,
  created_at DATETIME     NULL,
  updated_at DATETIME     NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS page_translations (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id          INT UNSIGNED NOT NULL,
  language_id      INT UNSIGNED NOT NULL,
  slug             VARCHAR(190) NOT NULL DEFAULT '',
  title            VARCHAR(190) NOT NULL DEFAULT '',
  body             MEDIUMTEXT NULL,
  meta_title       VARCHAR(190) NOT NULL DEFAULT '',
  meta_description VARCHAR(300) NOT NULL DEFAULT '',
  og_image         VARCHAR(255) NOT NULL DEFAULT '',
  UNIQUE KEY uq_page_lang (page_id, language_id),
  INDEX idx_pt_slug (language_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS menu_items (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  location  VARCHAR(20)  NOT NULL DEFAULT 'header',
  parent_id INT UNSIGNED NULL,
  type      VARCHAR(20)  NOT NULL DEFAULT 'url',
  ref_id    INT UNSIGNED NULL,
  url       VARCHAR(255) NOT NULL DEFAULT '',
  sort      INT NOT NULL DEFAULT 0,
  INDEX (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS menu_item_translations (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_item_id INT UNSIGNED NOT NULL,
  language_id  INT UNSIGNED NOT NULL,
  label        VARCHAR(120) NOT NULL DEFAULT '',
  UNIQUE KEY uq_menu_lang (menu_item_id, language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS settings (
  `key`   VARCHAR(80) NOT NULL PRIMARY KEY,
  `value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS setting_translations (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key`       VARCHAR(80) NOT NULL,
  language_id INT UNSIGNED NOT NULL,
  `value`     TEXT NULL,
  UNIQUE KEY uq_setting_lang (`key`, language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS translations (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  language_id INT UNSIGNED NOT NULL,
  `key`       VARCHAR(120) NOT NULL,
  `value`     TEXT NULL,
  UNIQUE KEY uq_tr_lang_key (language_id, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS quote_requests (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type       ENUM('quote','contact') NOT NULL DEFAULT 'quote',
  name       VARCHAR(150) NOT NULL DEFAULT '',
  company    VARCHAR(150) NOT NULL DEFAULT '',
  email      VARCHAR(190) NOT NULL DEFAULT '',
  phone      VARCHAR(60)  NOT NULL DEFAULT '',
  country    VARCHAR(100) NOT NULL DEFAULT '',
  message    TEXT NULL,
  product_id INT UNSIGNED NULL,
  locale     VARCHAR(10) NOT NULL DEFAULT '',
  ip         VARCHAR(45) NOT NULL DEFAULT '',
  status     ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  created_at DATETIME NULL,
  INDEX (status), INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS posts (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cover_image  VARCHAR(255) NOT NULL DEFAULT '',
  author       VARCHAR(120) NOT NULL DEFAULT '',
  is_active    TINYINT(1)   NOT NULL DEFAULT 1,
  published_at DATETIME     NULL,
  created_at   DATETIME     NULL,
  updated_at   DATETIME     NULL,
  INDEX (is_active, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS post_translations (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  post_id          INT UNSIGNED NOT NULL,
  language_id      INT UNSIGNED NOT NULL,
  slug             VARCHAR(190) NOT NULL DEFAULT '',
  title            VARCHAR(190) NOT NULL DEFAULT '',
  excerpt          VARCHAR(400) NOT NULL DEFAULT '',
  body             MEDIUMTEXT NULL,
  meta_title       VARCHAR(190) NOT NULL DEFAULT '',
  meta_description VARCHAR(300) NOT NULL DEFAULT '',
  UNIQUE KEY uq_post_lang (post_id, language_id),
  UNIQUE KEY uq_pslug_lang (slug, language_id),
  INDEX (language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS redirects (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  from_path VARCHAR(190) NOT NULL UNIQUE,
  to_path   VARCHAR(255) NOT NULL,
  code      SMALLINT NOT NULL DEFAULT 301,
  hits      INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

SET foreign_key_checks = 1;
