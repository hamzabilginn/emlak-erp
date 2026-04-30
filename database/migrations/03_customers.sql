-- 03_customers.sql
-- Müşteriler verilerini (Emlak alıcısı veya satıcısı/kiracısı) barındıracak tablo.
-- Multi-tenant yapıda, müşteriler sadece ilgili Tenant'a (Ofis'e) görünür olur.
-- PostgreSQL Uyumluluğu

CREATE TABLE IF NOT EXISTS customers (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL CHECK (type IN ('buyer', 'seller', 'tenant')),
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    demand_details JSONB NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_customers_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);
