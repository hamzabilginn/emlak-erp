-- 12_property_slug.sql
-- Properties tablosuna SEO dostu slug sütunu ekleniyor.

ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS slug VARCHAR(255);

CREATE INDEX IF NOT EXISTS idx_properties_slug ON properties (slug);
