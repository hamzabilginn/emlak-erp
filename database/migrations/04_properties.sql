-- 04_properties.sql
-- İlgili dükkana / ofise ait portföyleri (Satılık Ev, Kiralık İş Yeri vb.) temsil eder.
-- PostgreSQL Uyumluluğu

CREATE TABLE IF NOT EXISTS properties (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    category VARCHAR(50) NOT NULL CHECK (category IN ('residential', 'land', 'commercial')),
    status VARCHAR(50) NOT NULL CHECK (status IN ('for_sale', 'for_rent', 'sold', 'rented')),
    price NUMERIC(15, 2) NOT NULL,
    city VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    details JSONB NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_properties_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);
