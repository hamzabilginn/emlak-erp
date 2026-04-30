-- 07_shared_pool.sql
-- Ortak Portfoy (Paslasma Agi) icin 'properties' tablosuna 'is_shared_pool' ekliyoruz.
-- Ayrica tenant'larin birbiriyle iletisime gecebilmesi icin 'tenants' tablosuna 'phone' sutunu ekliyoruz.

ALTER TABLE properties ADD COLUMN IF NOT EXISTS is_shared_pool BOOLEAN DEFAULT FALSE;

ALTER TABLE tenants ADD COLUMN IF NOT EXISTS phone VARCHAR(20) DEFAULT '';
