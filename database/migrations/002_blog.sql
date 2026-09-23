-- Blog / knowledge articles
SET NAMES utf8mb4;

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
