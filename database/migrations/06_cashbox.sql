-- Esnaf Kasası (Gelir/Gider Defteri) Tablosu (Mahalle Emlakçısına Özel)
-- tenant_id: Her emlak ofisi sadece kendi gelir giderini görebilmeli.
-- type: 'Gelir' veya 'Gider'
-- category: Komisyon, Kira, Çay/Kahve, Aidat vb. pratik kategoriler.
-- amount: Ondalık sayılar için uygun DECIMAL alan.

CREATE TABLE IF NOT EXISTS cashbox (
    id SERIAL PRIMARY KEY,
    tenant_id INTEGER NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    type VARCHAR(10) CHECK (type IN ('Gelir', 'Gider')) NOT NULL,
    category VARCHAR(100) NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    description TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
