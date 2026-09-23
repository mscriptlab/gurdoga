-- "Anasayfa Slider" tables. Managed at /admin/sliders — starts empty.

CREATE TABLE IF NOT EXISTS `sliders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) NOT NULL DEFAULT '',
  `sort` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `slider_translations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `slider_id` int unsigned NOT NULL,
  `language_id` int unsigned NOT NULL,
  `eyebrow` varchar(190) NOT NULL DEFAULT '',
  `title` varchar(190) NOT NULL DEFAULT '',
  `description` text,
  `button_text` varchar(80) NOT NULL DEFAULT '',
  `link_url` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slider_lang` (`slider_id`,`language_id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
