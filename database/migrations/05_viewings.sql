-- 05_viewings.sql
-- Müşterilere Mülk Gösterme (Randevu / Ajanda / Sözleşme) Tablosu
-- PostgreSQL Uyumluluğu

CREATE TABLE IF NOT EXISTS viewings (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    customer_id INTEGER NOT NULL,
    property_id INTEGER NOT NULL,
    viewing_date TIMESTAMP NOT NULL,
    status VARCHAR(50) DEFAULT 'Bekliyor' CHECK (status IN ('Bekliyor', 'Gösterildi', 'İptal')),
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Dış Anahtarlar (Foreign Keys)
    CONSTRAINT fk_viewings_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) ON DELETE CASCADE,
    CONSTRAINT fk_viewings_customer FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE CASCADE,
    CONSTRAINT fk_viewings_property FOREIGN KEY (property_id) 
        REFERENCES properties(id) ON DELETE CASCADE
);