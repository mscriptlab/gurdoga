-- Per-page menu placement (corporate submenu / top-level link / hidden from nav).
ALTER TABLE pages ADD COLUMN nav_placement VARCHAR(20) NOT NULL DEFAULT 'corporate' AFTER template;
