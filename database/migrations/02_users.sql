-- 02_users.sql
-- Sistemi kullanan 'Admin'ler ve 'Emlak Danışmanları'nı barındıracak tablo.
-- Her bir kullanıcı, bağlı olduğu Tenant (Ofis) ID'sine zorunlu olarak aittir.
-- PostgreSQL Uyumluluğu

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'consultant' CHECK (role IN ('admin', 'consultant')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);
