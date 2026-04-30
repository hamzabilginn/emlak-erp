-- 08_keys_and_deals.sql
-- Anahtar Takibi ve İşi Bağlama (Satış/Kiralama) İşlemleri

-- properties tablosuna Anahtar sütunları ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS key_status VARCHAR(50) DEFAULT 'Bizde' CHECK (key_status IN ('Bizde', 'Mülk Sahibinde', 'Diğer Emlakçıda'));
ALTER TABLE properties ADD COLUMN IF NOT EXISTS key_number VARCHAR(100) DEFAULT '';

-- deals (İşlemler/Satışlar) tablosu
CREATE TABLE IF NOT EXISTS deals (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    customer_id INTEGER NOT NULL REFERENCES customers(id) ON DELETE RESTRICT,
    deal_type VARCHAR(50) NOT NULL CHECK (deal_type IN ('Satış', 'Kiralama')),
    price DECIMAL(15, 2) NOT NULL,
    commission_earned DECIMAL(15, 2) NOT NULL DEFAULT 0,
    deposit_taken DECIMAL(15, 2) NOT NULL DEFAULT 0,
    deal_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
