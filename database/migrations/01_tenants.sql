-- 01_tenants.sql
-- Multi-tenant SaaS (Çoklu Ofis Sistemi) için Ana Ofis/Dükkan Tablosu
-- Her ofis (emlakçı) sisteme bir "Tenant" olarak tanımlanır. Ve diğer tüm veriler bu tenant'ın ID'si ile ilişkilendirilir.
-- PostgreSQL Uyumluluğu

CREATE TABLE IF NOT EXISTS tenants (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'suspended')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
