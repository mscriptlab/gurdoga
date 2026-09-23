-- Per-language slugs for categories and pages (products & posts already have them).
-- The install runner tolerates "duplicate column/key" so this is safe to re-run.
ALTER TABLE category_translations ADD COLUMN slug VARCHAR(190) NOT NULL DEFAULT '' AFTER language_id;
ALTER TABLE category_translations ADD KEY idx_ct_slug (language_id, slug);
ALTER TABLE page_translations ADD COLUMN slug VARCHAR(190) NOT NULL DEFAULT '' AFTER language_id;
ALTER TABLE page_translations ADD KEY idx_pt_slug (language_id, slug);

-- Backfill from the base slug so nothing 404s before localized slugs are entered.
UPDATE category_translations ct JOIN categories c ON c.id = ct.category_id
  SET ct.slug = c.slug WHERE ct.slug = '';
UPDATE page_translations pt JOIN pages p ON p.id = pt.page_id
  SET pt.slug = p.slug WHERE pt.slug = '';
